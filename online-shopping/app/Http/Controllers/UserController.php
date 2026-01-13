<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    use ApiResponse;

    /**
     * Get all users
     */
    public function index()
    {
        try {
            $users = User::orderBy('created_at', 'desc')->get();
            return $this->successResponse($users, 'Users retrieved successfully');
        } catch (\Exception $e) {
            Log::error('Error fetching users: ' . $e->getMessage());
            return $this->errorResponse('Failed to fetch users', 500);
        }
    }

    /**
     * Create new user
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8|confirmed',
                'role' => 'nullable|in:customer,admin',
                'is_active' => 'nullable|boolean',
            ]);

            $validated['password'] = Hash::make($validated['password']);
            $validated['role'] = $validated['role'] ?? 'customer';
            $validated['is_active'] = $validated['is_active'] ?? true;

            $user = User::create($validated);

            return $this->successResponse($user, 'User created successfully', 201);
        } catch (ValidationException $e) {
            return $this->errorResponse('Validation failed', 422, $e->errors());
        } catch (\Exception $e) {
            Log::error('Error creating user: ' . $e->getMessage());
            return $this->errorResponse('Failed to create user', 500);
        }
    }

    /**
     * Update user
     */
    public function update(Request $request, $id)
    {
        try {
            $user = User::findOrFail($id);

            Log::info('Updating user', [
                'user_id' => $id,
                'request_data' => $request->all()
            ]);

            $validated = $request->validate([
                'name' => 'sometimes|required|string|max:255',
                'email' => 'sometimes|required|string|email|max:255|unique:users,email,' . $id,
                'password' => 'nullable|string|min:8|confirmed',
                'role' => 'nullable|in:customer,admin',
                'is_active' => 'nullable|boolean',
            ]);

            Log::info('Validated data', ['validated' => $validated]);

            if (isset($validated['password'])) {
                $validated['password'] = Hash::make($validated['password']);
            } else {
                unset($validated['password']);
            }

            $user->update($validated);

            Log::info('User updated successfully', ['user' => $user]);

            return $this->successResponse($user, 'User updated successfully');
        } catch (ValidationException $e) {
            Log::error('Validation failed', ['errors' => $e->errors()]);
            return $this->errorResponse('Validation failed', 422, $e->errors());
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->errorResponse('User not found', 404);
        } catch (\Exception $e) {
            Log::error('Error updating user: ' . $e->getMessage());
            return $this->errorResponse('Failed to update user', 500);
        }
    }

    /**
     * Delete user
     */
    public function destroy($id)
    {
        try {
            $user = User::findOrFail($id);
            
            // Prevent deleting yourself
            if ($user->id === auth()->id()) {
                return $this->errorResponse('Cannot delete your own account', 400);
            }

            $user->delete();

            return $this->successResponse(null, 'User deleted successfully');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->errorResponse('User not found', 404);
        } catch (\Exception $e) {
            Log::error('Error deleting user: ' . $e->getMessage());
            return $this->errorResponse('Failed to delete user', 500);
        }
    }
}
