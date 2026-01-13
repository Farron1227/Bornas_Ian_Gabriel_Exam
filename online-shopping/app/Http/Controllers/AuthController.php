<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    use ApiResponse;

    /**
     * Register new user
     */
    public function register(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8|confirmed',
            ]);

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => 'customer',
                'is_active' => true,
            ]);

            $token = $user->createToken('auth_token')->plainTextToken;

            return $this->successResponse([
                'user' => $user,
                'token' => $token,
            ], 'Registration successful', 201);
        } catch (ValidationException $e) {
            return $this->errorResponse('Validation failed', 422, $e->errors());
        } catch (\Exception $e) {
            Log::error('Error during registration: ' . $e->getMessage());
            return $this->errorResponse('Registration failed', 500);
        }
    }

    /**
     * Login user
     */
    public function login(Request $request)
    {
        try {
            $validated = $request->validate([
                'email' => 'required|email',
                'password' => 'required',
            ]);

            $email = $validated['email'];
            $lockKey = 'login_locked_' . $email;
            $attemptsKey = 'login_attempts_' . $email;

            // Check if user exists first (before rate limiting)
            $user = User::where('email', $email)->first();
            
            if (!$user) {
                return $this->errorResponse('No account found with this email address.', 404);
            }

            // Check if email is locked (only for existing users)
            if (Cache::has($lockKey)) {
                $remainingTime = Cache::get($lockKey) - now()->timestamp;
                $minutes = ceil($remainingTime / 60);
                return $this->errorResponse("Too many failed attempts. Please try again in {$minutes} minute(s).", 429);
            }

            // Check password
            if (!Hash::check($validated['password'], $user->password)) {
                // Increment failed attempts
                $attempts = Cache::get($attemptsKey, 0) + 1;
                Cache::put($attemptsKey, $attempts, now()->addMinutes(15));

                // Lock account after 5 failed attempts
                if ($attempts >= 5) {
                    $lockUntil = now()->addMinutes(5)->timestamp;
                    Cache::put($lockKey, $lockUntil, now()->addMinutes(5));
                    Cache::forget($attemptsKey);
                    return $this->errorResponse('Too many failed attempts. Your account has been locked for 5 minutes.', 429);
                }

                $remainingAttempts = 5 - $attempts;
                return $this->errorResponse("Invalid password. {$remainingAttempts} attempt(s) remaining.", 401);
            }

            if (!$user->is_active) {
                return $this->errorResponse('Unable to access account', 403);
            }

            // Clear failed attempts on successful login
            Cache::forget($attemptsKey);
            Cache::forget($lockKey);

            $token = $user->createToken('auth_token')->plainTextToken;

            return $this->successResponse([
                'user' => $user,
                'token' => $token,
            ], 'Login successful');
        } catch (ValidationException $e) {
            return $this->errorResponse('Validation failed', 422, $e->errors());
        } catch (\Exception $e) {
            Log::error('Error during login: ' . $e->getMessage());
            return $this->errorResponse('Login failed', 500);
        }
    }

    /**
     * Logout user
     */
    public function logout(Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete();
            return $this->successResponse(null, 'Logged out successfully');
        } catch (\Exception $e) {
            Log::error('Error during logout: ' . $e->getMessage());
            return $this->errorResponse('Logout failed', 500);
        }
    }

    /**
     * Get authenticated user
     */
    public function user(Request $request)
    {
        try {
            return $this->successResponse($request->user(), 'User retrieved successfully');
        } catch (\Exception $e) {
            Log::error('Error fetching user: ' . $e->getMessage());
            return $this->errorResponse('Failed to fetch user', 500);
        }
    }
}
