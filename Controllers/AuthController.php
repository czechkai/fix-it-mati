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
        try {
            $user = $request->user();
            
            if (!$user) {
                return Response::unauthorized('Not authenticated');
            }
            
            $data = $request->all();
            
            // Debug logging
            error_log("Profile update request data: " . json_encode($data));
            error_log("Files: " . json_encode($_FILES));
            error_log("Request method: " . $request->getMethod());
            
            // Validate required fields (only email is required)
            if (empty($data['email'])) {
                error_log("Email validation failed. Data received: " . json_encode($data));
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
            'email' => $data['email'] ?? $user['email'],
            'phone' => $data['phone'] ?? null,
            'address' => $data['address'] ?? null
        ];
        
        // Only include first_name and last_name if they are provided
        if (isset($data['first_name'])) {
            $updateData['first_name'] = $data['first_name'];
        }
        
        if (isset($data['last_name'])) {
            $updateData['last_name'] = $data['last_name'];
        }
        
        // Handle direct profile image fix (for database update without file upload)
        if (isset($data['fix_profile_image']) && !empty($data['fix_profile_image'])) {
            $filename = $data['fix_profile_image'];
            
            // Verify file exists
            $uploadDir = __DIR__ . '/../uploads/profiles';
            $filePath = $uploadDir . '/' . $filename;
            
            if (file_exists($filePath) && is_file($filePath)) {
                $updateData['profile_image'] = $filename;
            } else {
                return Response::validationError('Profile image file not found: ' . $filename);
            }
        }
        // Handle profile image upload
        else if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['profile_image'];
            
            // Validate file type using multiple methods
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            
            // Get file extension
            $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (!in_array($extension, $allowedExtensions)) {
                return Response::validationError('Invalid file extension. Only JPG, PNG, GIF, and WebP are allowed.');
            }
            
            // Validate MIME type using getimagesize (more reliable and doesn't require finfo)
            $imageInfo = @getimagesize($file['tmp_name']);
            if ($imageInfo === false) {
                return Response::validationError('Invalid image file. Please upload a valid image.');
            }
            
            $detectedMimeType = $imageInfo['mime'];
            if (!in_array($detectedMimeType, $allowedTypes)) {
                return Response::validationError('Invalid image file type. Only JPEG, PNG, GIF, and WebP are allowed.');
            }
            
            // Validate file size (max 5MB)
            if ($file['size'] > 5 * 1024 * 1024) {
                return Response::validationError('Image size must be less than 5MB');
            }
            
            // Create uploads directory outside public folder
            $uploadDir = __DIR__ . '/../uploads/profiles';
            if (!\is_dir($uploadDir)) {
                \mkdir($uploadDir, 0755, true);
            }
            
            // Generate unique filename
            $extension = \pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = 'profile_' . $user['id'] . '_' . \time() . '.' . $extension;
            $uploadPath = $uploadDir . '/' . $filename;
            
            // Move uploaded file
            if (\move_uploaded_file($file['tmp_name'], $uploadPath)) {
                // Delete old profile image if exists
                if (!empty($user['profile_image'])) {
                    // Try both old (public) and new (uploads) locations
                    $oldPaths = [
                        __DIR__ . '/../uploads/profiles/' . basename($user['profile_image']),
                        __DIR__ . '/../public/' . $user['profile_image']
                    ];
                    foreach ($oldPaths as $oldImagePath) {
                        if (\file_exists($oldImagePath) && \is_file($oldImagePath)) {
                            \unlink($oldImagePath);
                            break;
                        }
                    }
                }
                
                // Store just the filename in database (path will be handled by the API)
                $updateData['profile_image'] = $filename;
            } else {
                error_log("Failed to move uploaded file to: " . $uploadPath);
                return Response::error('Failed to upload profile image');
            }
        }
        
        if (!empty($data['new_password'])) {
            $updateData['password'] = password_hash($data['new_password'], PASSWORD_DEFAULT);
        }
        
        $result = $this->authService->updateProfile($user['id'], $updateData);
        
        if ($result['success']) {
            return Response::success($result['user'], 'Profile updated successfully');
        }
        
        // Log the error for debugging
        error_log("Profile update failed: " . ($result['message'] ?? 'Unknown error'));
        error_log("Update data: " . json_encode($updateData));
        
        return Response::error($result['message'] ?? 'Failed to update profile');
        
        } catch (\Exception $e) {
            error_log("Exception in updateProfile: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            return Response::error('An error occurred while updating profile: ' . $e->getMessage());
        }
    }
}
