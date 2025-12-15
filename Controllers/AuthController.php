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

    /**
     * POST /api/auth/send-verification-code
     * Generate and send verification code to email
     */
    public function sendVerificationCode(Request $request): Response {
        $data = $request->all();
        $email = $data['email'] ?? null;

        if (!$email) {
            return Response::validationError('Email is required');
        }

        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return Response::validationError('Invalid email format');
        }

        // Check if email already exists
        $user = User::findByEmail($email);
        if ($user) {
            return Response::error('This email is already registered', 400);
        }

        try {
            // Generate 6-digit verification code
            $verificationCode = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            
            // Log the generated code
            error_log("=== SEND VERIFICATION CODE ===");
            error_log("Session ID: " . session_id());
            error_log("Email: $email");
            error_log("Generated Code: $verificationCode");
            
            // Store in session with expiration (15 minutes from now)
            $_SESSION['verification'] = [
                'code' => $verificationCode,
                'email' => $email,
                'expires_at' => time() + (15 * 60),
                'attempts' => 0
            ];
            
            error_log("Stored in session: " . json_encode($_SESSION['verification']));

            // Try to send email
            $emailSent = $this->authService->sendVerificationEmail($email, $verificationCode);

            if (!$emailSent) {
                error_log("Failed to send verification email to: " . $email);
                // Don't reveal email sending failure - just say code was sent
                // This is a security practice
            }

            return Response::success([
                'email' => $email,
                'message' => 'Verification code sent successfully'
            ], 'Verification code sent to ' . $email);

        } catch (\Exception $e) {
            error_log("Error sending verification code: " . $e->getMessage());
            return Response::error('Failed to send verification code');
        }
    }

    /**
     * POST /api/auth/verify-code
     * Verify the verification code
     */
    public function verifyCode(Request $request): Response {
        $data = $request->all();
        $verificationCode = $data['code'] ?? null;
        $email = $data['email'] ?? null;

        if (!$verificationCode || !$email) {
            return Response::validationError('Code and email are required');
        }

        // Check if verification data exists in session
        if (!isset($_SESSION['verification'])) {
            return Response::error('No verification code was sent. Please send a new code.', 400);
        }

        $verification = $_SESSION['verification'];

        // Check if code has expired
        if (time() > $verification['expires_at']) {
            unset($_SESSION['verification']);
            return Response::error('Verification code has expired. Please request a new code.', 400);
        }

        // Check if email matches
        if ($verification['email'] !== $email) {
            return Response::error('Email does not match the one verification code was sent to.', 400);
        }

        // Check verification attempts (max 5 attempts)
        if ($verification['attempts'] >= 5) {
            unset($_SESSION['verification']);
            return Response::error('Too many attempts. Please request a new code.', 400);
        }

        // Verify code
        if ($verification['code'] !== $verificationCode) {
            // Increment attempts
            $_SESSION['verification']['attempts']++;
            
            $remaining = 5 - $_SESSION['verification']['attempts'];
            return Response::error('Invalid verification code. ' . $remaining . ' attempts remaining.', 400);
        }

        // Code is valid - clean up session
        unset($_SESSION['verification']);

        // Store email in session for registration process
        $_SESSION['verified_email'] = $email;

        return Response::success([
            'email' => $email
        ], 'Email verified successfully');
    }

    /**
     * POST /api/auth/verify-and-register
     * Verify code and create account in one request
     */
    public function verifyAndRegister(Request $request): Response {
        $data = $request->all();
        $email = $data['email'] ?? null;
        $verificationCode = $data['verification_code'] ?? null;

        error_log("=== VERIFY AND REGISTER ===");
        error_log("Session ID: " . session_id());
        error_log("Received Email: $email");
        error_log("Received Code (raw): $verificationCode");

        if (!$verificationCode || !$email) {
            return Response::validationError('Verification code and email are required');
        }

        // Remove spaces from verification code (user might paste with spaces)
        $verificationCode = preg_replace('/\s+/', '', $verificationCode);
        error_log("Received Code (cleaned): $verificationCode");

        // Check if verification data exists in session
        if (!isset($_SESSION['verification'])) {
            error_log("ERROR: Session verification not found!");
            error_log("Available session keys: " . implode(', ', array_keys($_SESSION)));
            error_log("Full session: " . json_encode($_SESSION));
            return Response::error('No verification code was sent. Please click "Create Account" first.', 400);
        }

        $verification = $_SESSION['verification'];
        error_log("Stored Code: " . $verification['code']);
        error_log("Stored Email: " . $verification['email']);

        // Check if code has expired
        $expiresAt = $verification['expires_at'];
        $currentTime = time();
        error_log("Current time: $currentTime, Expires at: $expiresAt, Expired: " . ($currentTime > $expiresAt ? 'YES' : 'NO'));
        
        if ($currentTime > $expiresAt) {
            unset($_SESSION['verification']);
            return Response::error('Verification code has expired. Please request a new code.', 400);
        }

        // Check if email matches
        if ($verification['email'] !== $email) {
            error_log("ERROR: Email mismatch!");
            return Response::error('Email does not match. Please use the same email.', 400);
        }

        // Check verification attempts (max 5 attempts)
        if ($verification['attempts'] >= 5) {
            unset($_SESSION['verification']);
            return Response::error('Too many attempts. Please request a new code.', 400);
        }

        // Verify code (also remove spaces from stored code just in case)
        $storedCode = preg_replace('/\s+/', '', $verification['code']);
        error_log("Comparing: stored='$storedCode' vs received='$verificationCode'");
        error_log("Match: " . ($storedCode === $verificationCode ? 'YES' : 'NO'));
        
        if ($storedCode !== $verificationCode) {
            error_log("ERROR: Code mismatch!");
            $_SESSION['verification']['attempts']++;
            $remaining = 5 - $_SESSION['verification']['attempts'];
            return Response::error('Invalid verification code. ' . $remaining . ' attempts remaining.', 400);
        }

        error_log("SUCCESS: Code verified! Proceeding with registration...");

        // Code is valid - proceed with registration
        unset($_SESSION['verification']);

        // Now register the user
        $result = $this->authService->register($data);

        if ($result['success']) {
            error_log("User registered successfully: " . $result['user']['email']);
            // Generate JWT token
            $token = $this->authService->generateToken($result['user_object']);

            return Response::created([
                'user' => $result['user'],
                'token' => $token,
                'redirect' => '/public/pages/dashboard.php'
            ], 'Account created and verified successfully! Redirecting...');
        }

        error_log("Registration failed: " . json_encode($result));
        return Response::validationError(
            'Registration failed',
            $result['errors'] ?? null
        );
    }
}
