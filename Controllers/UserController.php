<?php

namespace FixItMati\Controllers;

use FixItMati\Core\Request;
use FixItMati\Core\Response;
use FixItMati\Models\User;
use FixItMati\Models\Notification;

class UserController
{
    private $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    /**
     * Get all users (Admin only)
     * GET /api/users/all
     */
    public function getAllUsers(Request $request): Response
    {
        $user = $request->user();
        
        if (!$user || !in_array($user['role'], ['admin', 'staff'])) {
            return Response::error('Unauthorized access', 403);
        }

        try {
            $users = User::all();
            
            // Convert to array format
            $usersData = array_map(function($u) {
                return $u->toArray();
            }, $users);

            return Response::success($usersData);
        } catch (\Exception $e) {
            error_log("Error fetching users: " . $e->getMessage());
            return Response::error('Failed to fetch users', 500);
        }
    }

    /**
     * Create new user (Admin only)
     * POST /api/users/create
     */
    public function createUser(Request $request): Response
    {
        $user = $request->user();
        
        if (!$user || !in_array($user['role'], ['admin', 'staff'])) {
            return Response::error('Unauthorized access', 403);
        }

        try {
            $data = [
                'email' => $request->input('email'),
                'password' => $request->input('password'),
                'full_name' => $request->input('full_name'),
                'phone' => $request->input('phone'),
                'address' => $request->input('address'),
                'role' => $request->input('role', 'customer')
            ];

            // Validate required fields
            if (empty($data['email'])) {
                return Response::error('Email is required', 400);
            }
            if (empty($data['password']) || strlen($data['password']) < 8) {
                return Response::error('Password must be at least 8 characters', 400);
            }
            if (empty($data['full_name'])) {
                return Response::error('Full name is required', 400);
            }

            // Check if email already exists
            $existingUser = User::findByEmail($data['email']);
            if ($existingUser) {
                return Response::error('Email already exists', 400);
            }

            // Split full name into first and last name
            $nameParts = explode(' ', $data['full_name'], 2);
            $data['first_name'] = $nameParts[0];
            $data['last_name'] = $nameParts[1] ?? '';

            // Hash password
            $data['password_hash'] = password_hash($data['password'], PASSWORD_DEFAULT);
            unset($data['password']);

            // Generate account number
            $data['account_number'] = 'CIT-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

            $newUser = new User();
            $created = $newUser->create($data);

            if ($created) {
                // Create welcome notification
                $notificationModel = new Notification();
                $notificationModel->create([
                    'user_id' => $newUser->id,
                    'type' => 'system',
                    'title' => 'Welcome to FixItMati',
                    'message' => 'Your account has been created. You can now report service requests and manage your utilities.',
                    'channel' => 'in_app',
                    'status' => 'pending'
                ]);

                return Response::success([
                    'message' => 'User created successfully',
                    'user' => $newUser->toArray()
                ], 201);
            } else {
                return Response::error('Failed to create user', 500);
            }
        } catch (\Exception $e) {
            error_log("Error creating user: " . $e->getMessage());
            return Response::error('Failed to create user: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Verify user account (Admin only)
     * POST /api/users/{id}/verify
     */
    public function verifyUser(Request $request, array $params): Response
    {
        $adminUser = $request->user();
        
        if (!$adminUser || !in_array($adminUser['role'], ['admin', 'staff'])) {
            return Response::error('Unauthorized access', 403);
        }

        try {
            $userId = $params['id'] ?? null;
            
            if (!$userId) {
                return Response::error('User ID required', 400);
            }

            $user = User::find($userId);
            if (!$user) {
                return Response::error('User not found', 404);
            }

            // For verification, we'll ensure the user has customer role
            // In a real system, you'd have a separate verification flag
            $updated = true;
            if ($user->role !== 'customer') {
                $updated = $user->update([
                    'role' => 'customer'
                ]);
            }

            if ($updated) {
                // Create notification
                $notificationModel = new Notification();
                $notificationModel->create([
                    'user_id' => $userId,
                    'type' => 'system',
                    'title' => 'Account Verified',
                    'message' => 'Your account has been verified by the administrator. You now have full access to all services.',
                    'channel' => 'in_app',
                    'status' => 'pending'
                ]);

                return Response::success(['message' => 'User verified successfully']);
            } else {
                return Response::error('Failed to verify user', 500);
            }
        } catch (\Exception $e) {
            error_log("Error verifying user: " . $e->getMessage());
            return Response::error('Failed to verify user', 500);
        }
    }

    /**
     * Suspend user account (Admin only)
     * POST /api/users/{id}/suspend
     */
    public function suspendUser(Request $request, array $params): Response
    {
        $adminUser = $request->user();
        
        if (!$adminUser || !in_array($adminUser['role'], ['admin', 'staff'])) {
            return Response::error('Unauthorized access', 403);
        }

        try {
            $userId = $params['id'] ?? null;
            $reason = $request->input('reason', 'No reason provided');
            
            if (!$userId) {
                return Response::error('User ID required', 400);
            }

            $user = User::find($userId);
            if (!$user) {
                return Response::error('User not found', 404);
            }

            // For suspension, we can change role to indicate suspended status
            // In production, you'd add a separate status column
            $updated = $user->update([
                'role' => 'suspended'
            ]);

            if ($updated) {
                // Create notification
                $notificationModel = new Notification();
                $notificationModel->create([
                    'user_id' => $userId,
                    'type' => 'system',
                    'title' => 'Account Suspended',
                    'message' => "Your account has been suspended. Reason: {$reason}. Please contact support for assistance.",
                    'channel' => 'in_app',
                    'status' => 'pending'
                ]);

                return Response::success(['message' => 'User suspended successfully']);
            } else {
                return Response::error('Failed to suspend user', 500);
            }
        } catch (\Exception $e) {
            error_log("Error suspending user: " . $e->getMessage());
            return Response::error('Failed to suspend user', 500);
        }
    }

    /**
     * Reset user password (Admin only)
     * POST /api/users/{id}/reset-password
     */
    public function resetPassword(Request $request, array $params): Response
    {
        $adminUser = $request->user();
        
        if (!$adminUser || !in_array($adminUser['role'], ['admin', 'staff'])) {
            return Response::error('Unauthorized access', 403);
        }

        try {
            $userId = $params['id'] ?? null;
            
            if (!$userId) {
                return Response::error('User ID required', 400);
            }

            $user = User::find($userId);
            if (!$user) {
                return Response::error('User not found', 404);
            }

            // Generate temporary password
            $tempPassword = 'Temp' . rand(1000, 9999) . '!';
            
            // Update password
            $updated = $user->update([
                'password_hash' => password_hash($tempPassword, PASSWORD_DEFAULT)
            ]);

            if ($updated) {
                // Create notification with temporary password
                $notificationModel = new Notification();
                $notificationModel->create([
                    'user_id' => $userId,
                    'type' => 'system',
                    'title' => 'Password Reset',
                    'message' => "Your password has been reset by an administrator. Your temporary password is: {$tempPassword}. Please change it after logging in.",
                    'channel' => 'in_app',
                    'status' => 'pending'
                ]);

                return Response::success([
                    'message' => 'Password reset successfully',
                    'temporary_password' => $tempPassword
                ]);
            } else {
                return Response::error('Failed to reset password', 500);
            }
        } catch (\Exception $e) {
            error_log("Error resetting password: " . $e->getMessage());
            return Response::error('Failed to reset password', 500);
        }
    }

    /**
     * Delete user (Admin only)
     * DELETE /api/users/{id}
     */
    public function deleteUser(Request $request, array $params): Response
    {
        $adminUser = $request->user();
        
        if (!$adminUser || !in_array($adminUser['role'], ['admin', 'staff'])) {
            return Response::error('Unauthorized access', 403);
        }

        try {
            $userId = $params['id'] ?? null;
            
            if (!$userId) {
                return Response::error('User ID required', 400);
            }

            // Prevent self-deletion
            if ($userId === $adminUser['id']) {
                return Response::error('Cannot delete your own account', 400);
            }

            $user = User::find($userId);
            if (!$user) {
                return Response::error('User not found', 404);
            }

            $deleted = $user->delete();

            if ($deleted) {
                return Response::success(['message' => 'User deleted successfully']);
            } else {
                return Response::error('Failed to delete user', 500);
            }
        } catch (\Exception $e) {
            error_log("Error deleting user: " . $e->getMessage());
            return Response::error('Failed to delete user', 500);
        }
    }

    /**
     * Get user statistics (Admin only)
     * GET /api/users/stats
     */
    public function getUserStats(Request $request): Response
    {
        $user = $request->user();
        
        if (!$user || !in_array($user['role'], ['admin', 'staff'])) {
            return Response::error('Unauthorized access', 403);
        }

        try {
            $allUsers = User::all();
            
            $totalUsers = count($allUsers);
            $pendingUsers = count(array_filter($allUsers, function($u) {
                return $u->role === 'pending';
            }));
            
            // Calculate verified this month
            $now = new \DateTime();
            $thisMonth = new \DateTime($now->format('Y-m-01'));
            $verifiedThisMonth = count(array_filter($allUsers, function($u) use ($thisMonth) {
                $createdDate = new \DateTime($u->created_at);
                return $createdDate >= $thisMonth && $u->role === 'customer';
            }));

            return Response::success([
                'total_users' => $totalUsers,
                'pending_review' => $pendingUsers,
                'verified_this_month' => $verifiedThisMonth
            ]);
        } catch (\Exception $e) {
            error_log("Error fetching user stats: " . $e->getMessage());
            return Response::error('Failed to fetch statistics', 500);
        }
    }
}
