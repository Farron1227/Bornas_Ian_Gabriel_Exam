<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
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

            $user = User::where('email', $validated['email'])->first();

            if (!$user || !Hash::check($validated['password'], $user->password)) {
                return $this->errorResponse('The provided credentials are incorrect', 401);
            }

            if (!$user->is_active) {
                return $this->errorResponse('Your account has been deactivated', 403);
            }

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
