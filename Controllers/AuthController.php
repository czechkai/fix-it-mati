<?php
/**
 * Authentication Controller
 * Handles API endpoints for authentication
 */

namespace FixItMati\Controllers;

use FixItMati\Core\Request;
use FixItMati\Core\Response;
use FixItMati\Services\AuthService;
use FixItMati\Models\User;

class AuthController {
    private $authService;
    
    public function __construct() {
        $this->authService = AuthService::getInstance();
    }
    
    /**
     * POST /api/auth/register
     * Register a new user
     */
    public function register(Request $request): Response {
        $data = $request->all();
        
        $result = $this->authService->register($data);
        
        if ($result['success']) {
            // Generate JWT token for the new user (use user_object, not array)
            $token = $this->authService->generateToken($result['user_object']);
            
            return Response::created([
                'user' => $result['user'],
                'token' => $token
            ], $result['message']);
        }
        
        return Response::validationError(
            'Registration failed',
            $result['errors'] ?? null
        );
    }
    
    /**
     * POST /api/auth/login
     * Login user
     */
    public function login(Request $request): Response {
        $email = $request->input('email');
        $password = $request->input('password');
        $remember = $request->input('remember', false);
        
        if (empty($email) || empty($password)) {
            return Response::badRequest('Email and password are required');
        }
        
        $result = $this->authService->login($email, $password, $remember);
        
        if ($result['success']) {
            // Generate JWT token for API authentication
            $user = $this->authService->user();
            $token = $this->authService->generateToken($user);
            
            return Response::success([
                'user' => $result['user'],
                'token' => $token
            ], $result['message']);
        }
        
        return Response::unauthorized($result['message']);
    }
    
    /**
     * POST /api/auth/logout
     * Logout user
     */
    public function logout(Request $request): Response {
        $this->authService->logout();
        
        return Response::success(null, 'Logout successful');
    }
    
    /**
     * GET /api/auth/me
     * Get currently authenticated user
     */
    public function me(Request $request): Response {
        // Get user from request (set by AuthMiddleware)
        $user = $request->user();
        
        if (!$user) {
            return Response::unauthorized('Not authenticated');
        }
        
        // If it's from JWT (array with only user_id, email, role), fetch full user data from database
        if (is_array($user) && isset($user['user_id'])) {
            $fullUser = User::find($user['user_id']);
            
            if ($fullUser) {
                return Response::success($fullUser->toArray());
            }
            
            // Fallback to JWT data if user not found in DB (shouldn't happen)
            return Response::success($user);
        }
        
        // If user is already a User object (from session), convert to array
        if (is_object($user) && method_exists($user, 'toArray')) {
            return Response::success($user->toArray());
        }
        
        return Response::success($user);
    }
    
    /**
     * POST /api/auth/refresh
     * Refresh JWT token
     */
    public function refresh(Request $request): Response {
        $token = $request->bearerToken();
        
        if (!$token) {
            return Response::unauthorized('Token not provided');
        }
        
        $payload = $this->authService->verifyToken($token);
        
        if (!$payload) {
            return Response::unauthorized('Invalid or expired token');
        }
        
        // Get user and generate new token
        $user = $this->authService->user();
        
        if (!$user) {
            return Response::unauthorized('User not found');
        }
        
        $newToken = $this->authService->generateToken($user);
        
        return Response::success([
            'token' => $newToken
        ], 'Token refreshed successfully');
    }
    
    /**
     * PUT /api/auth/profile
     * Update user profile
     */
    public function updateProfile(Request $request): Response {
        $user = $request->user();
        
        if (!$user) {
            return Response::unauthorized('Not authenticated');
        }
        
        $data = $request->all();
        
        // Validate required fields
        if (empty($data['first_name']) || empty($data['last_name'])) {
            return Response::validationError('First name and last name are required');
        }
        
        if (empty($data['email'])) {
            return Response::validationError('Email address is required');
        }
        
        // Validate email
        if (!empty($data['email'])) {
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                return Response::validationError('Invalid email address');
            }
            
            // Check if email changed
            if ($data['email'] !== $user['email']) {
                // Check if email already exists
                $existingUser = User::findByEmail($data['email']);
                if ($existingUser && $existingUser->id !== $user['id']) {
                    return Response::validationError('Email address is already in use');
                }
            }
        }
        
        // Check if password change is requested
        if (!empty($data['new_password'])) {
            if (empty($data['current_password'])) {
                return Response::validationError('Current password is required to change password');
            }
            
            // Verify current password
            $result = $this->authService->verifyPassword($user['email'], $data['current_password']);
            if (!$result) {
                return Response::validationError('Current password is incorrect');
            }
            
            if ($data['new_password'] !== $data['confirm_password']) {
                return Response::validationError('New passwords do not match');
            }
            
            if (strlen($data['new_password']) < 6) {
                return Response::validationError('New password must be at least 6 characters');
            }
        }
        
        // Update profile
        $updateData = [
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'] ?? $user['email'],
            'phone' => $data['phone'] ?? null,
            'address' => $data['address'] ?? null
        ];
        
        if (!empty($data['new_password'])) {
            $updateData['password'] = password_hash($data['new_password'], PASSWORD_DEFAULT);
        }
        
        $result = $this->authService->updateProfile($user['id'], $updateData);
        
        if ($result['success']) {
            return Response::success($result['user'], 'Profile updated successfully');
        }
        
        return Response::error('Failed to update profile');
    }
}
