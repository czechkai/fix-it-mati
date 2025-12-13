<?php
/**
 * Authentication Service
 * Handles user registration, login, and session management
 * Implements SINGLETON PATTERN for consistent auth state management
 */

namespace FixItMati\Services;

use FixItMati\Models\User;
use FixItMati\Core\Database;
use Exception;

class AuthService {
    private static $instance = null;
    private $currentUser = null;
    
    /**
     * Private constructor (Singleton Pattern)
     */
    private function __construct() {
        $this->initializeSession();
    }
    
    /**
     * Get singleton instance (SINGLETON PATTERN)
     */
    public static function getInstance(): self {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Initialize session
     */
    private function initializeSession(): void {
        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            session_start();
        }
    }
    
    /**
     * Register new user
     */
    public function register(array $data): array {
        // Validate required fields
        $errors = $this->validateRegistration($data);
        
        if (!empty($errors)) {
            return [
                'success' => false,
                'errors' => $errors
            ];
        }
        
        // Check if email already exists
        if (User::findByEmail($data['email'])) {
            return [
                'success' => false,
                'errors' => ['email' => 'Email already registered']
            ];
        }
        
        try {
            // Create user
            $user = new User();
            $user->create($data);
            
            return [
                'success' => true,
                'message' => 'Registration successful',
                'user' => $user->toArray(),
                'user_object' => $user // Return object for token generation
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'errors' => ['general' => 'Registration failed: ' . $e->getMessage()]
            ];
        }
    }
    
    /**
     * Login user
     */
    public function login(string $email, string $password, bool $remember = false): array {
        // Find user by email
        $user = User::findByEmail($email);
        
        if (!$user) {
            return [
                'success' => false,
                'message' => 'Invalid credentials'
            ];
        }
        
        // Verify password
        if (!$user->verifyPassword($password)) {
            return [
                'success' => false,
                'message' => 'Invalid credentials'
            ];
        }
        
        // Set session
        $_SESSION['user_id'] = $user->id;
        $_SESSION['user_role'] = $user->role;
        $_SESSION['user_email'] = $user->email;
        
        // Set remember me cookie
        if ($remember) {
            $this->setRememberMeCookie($user->id);
        }
        
        $this->currentUser = $user;
        
        return [
            'success' => true,
            'message' => 'Login successful',
            'user' => $user->toArray()
        ];
    }
    
    /**
     * Logout user
     */
    public function logout(): void {
        // Clear session variables
        $_SESSION = [];
        
        // Delete session cookie
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        // Destroy session
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
        
        // Clear remember me cookie
        if (isset($_COOKIE['remember_token'])) {
            setcookie('remember_token', '', time() - 3600, '/');
        }
        
        $this->currentUser = null;
    }
    
    /**
     * Get currently authenticated user
     */
    public function user(): ?User {
        if ($this->currentUser !== null) {
            return $this->currentUser;
        }
        
        // Check session
        if (!empty($_SESSION['user_id'])) {
            $this->currentUser = User::find($_SESSION['user_id']);
            return $this->currentUser;
        }
        
        // Check remember me cookie
        if (!empty($_COOKIE['remember_token'])) {
            $userId = $this->validateRememberToken($_COOKIE['remember_token']);
            if ($userId) {
                $this->currentUser = User::find($userId);
                
                // Restore session
                if ($this->currentUser) {
                    $_SESSION['user_id'] = $this->currentUser->id;
                    $_SESSION['user_role'] = $this->currentUser->role;
                    $_SESSION['user_email'] = $this->currentUser->email;
                }
                
                return $this->currentUser;
            }
        }
        
        return null;
    }
    
    /**
     * Check if user is authenticated
     */
    public function check(): bool {
        return $this->user() !== null;
    }
    
    /**
     * Check if user is guest (not authenticated)
     */
    public function guest(): bool {
        return !$this->check();
    }
    
    /**
     * Check if current user has specific role
     */
    public function hasRole(string $role): bool {
        $user = $this->user();
        return $user && $user->hasRole($role);
    }
    
    /**
     * Check if current user is admin
     */
    public function isAdmin(): bool {
        return $this->hasRole('admin');
    }
    
    /**
     * Check if current user is customer
     */
    public function isCustomer(): bool {
        return $this->hasRole('customer');
    }
    
    /**
     * Check if current user is technician
     */
    public function isTechnician(): bool {
        return $this->hasRole('technician');
    }
    
    /**
     * Generate JWT token (for API authentication)
     */
    public function generateToken(User $user): string {
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        
        $payload = json_encode([
            'user_id' => $user->id,
            'email' => $user->email,
            'role' => $user->role,
            'iat' => time(),
            'exp' => time() + (60 * 60 * 24) // 24 hours
        ]);
        
        $base64UrlHeader = $this->base64UrlEncode($header);
        $base64UrlPayload = $this->base64UrlEncode($payload);
        
        $secret = $_ENV['JWT_SECRET'] ?? 'your-secret-key-change-this';
        
        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, $secret, true);
        $base64UrlSignature = $this->base64UrlEncode($signature);
        
        return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
    }
    
    /**
     * Verify JWT token
     */
    public function verifyToken(string $token): ?array {
        $parts = explode('.', $token);
        
        if (count($parts) !== 3) {
            return null;
        }
        
        [$header, $payload, $signature] = $parts;
        
        $secret = $_ENV['JWT_SECRET'] ?? 'your-secret-key-change-this';
        
        $validSignature = $this->base64UrlEncode(
            hash_hmac('sha256', $header . "." . $payload, $secret, true)
        );
        
        if ($signature !== $validSignature) {
            return null;
        }
        
        $payloadData = json_decode($this->base64UrlDecode($payload), true);
        
        // Check expiration
        if (isset($payloadData['exp']) && $payloadData['exp'] < time()) {
            return null;
        }
        
        return $payloadData;
    }
    
    /**
     * Validate registration data
     */
    private function validateRegistration(array $data): array {
        $errors = [];
        
        if (empty($data['email'])) {
            $errors['email'] = 'Email is required';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Invalid email format';
        }
        
        if (empty($data['first_name'])) {
            $errors['first_name'] = 'First name is required';
        }
        
        if (empty($data['last_name'])) {
            $errors['last_name'] = 'Last name is required';
        }
        
        if (empty($data['password'])) {
            $errors['password'] = 'Password is required';
        } elseif (strlen($data['password']) < 6) {
            $errors['password'] = 'Password must be at least 6 characters';
        }
        
        if (!empty($data['password']) && empty($data['password_confirmation'])) {
            $errors['password_confirmation'] = 'Password confirmation is required';
        } elseif (!empty($data['password']) && $data['password'] !== $data['password_confirmation']) {
            $errors['password_confirmation'] = 'Passwords do not match';
        }
        
        return $errors;
    }
    
    /**
     * Set remember me cookie
     */
    private function setRememberMeCookie(string $userId): void {
        $token = bin2hex(random_bytes(32));
        
        // Store token in database (you'll need to create a remember_tokens table)
        // For now, we'll use a simple hash
        $hashedToken = hash('sha256', $token . $userId);
        
        // Set cookie for 30 days
        setcookie('remember_token', $hashedToken, time() + (30 * 24 * 60 * 60), '/', '', true, true);
    }
    
    /**
     * Validate remember me token
     */
    private function validateRememberToken(string $token): ?string {
        // This is a simplified version
        // In production, you should store tokens in database
        // For now, we'll just check if session exists
        return $_SESSION['user_id'] ?? null;
    }
    
    /**
     * Base64 URL encode
     */
    private function base64UrlEncode(string $data): string {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
    
    /**
     * Base64 URL decode
     */
    private function base64UrlDecode(string $data): string {
        return base64_decode(strtr($data, '-_', '+/'));
    }
    
    /**
     * Verify user password
     */
    public function verifyPassword(string $email, string $password): bool {
        $db = Database::getInstance();
        $conn = $db->getConnection();
        
        $stmt = $conn->prepare("SELECT password FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if (!$user) {
            return false;
        }
        
        return password_verify($password, $user['password']);
    }
    
    /**
     * Update user profile
     */
    public function updateProfile(string $userId, array $data): array {
        $db = Database::getInstance();
        $conn = $db->getConnection();
        
        try {
            // Build update query
            $fields = [];
            $params = [];
            
            if (isset($data['first_name'])) {
                $fields[] = "first_name = ?";
                $params[] = $data['first_name'];
            }
            
            if (isset($data['last_name'])) {
                $fields[] = "last_name = ?";
                $params[] = $data['last_name'];
            }
            
            if (isset($data['email'])) {
                $fields[] = "email = ?";
                $params[] = $data['email'];
            }
            
            if (isset($data['phone'])) {
                $fields[] = "phone = ?";
                $params[] = $data['phone'];
            }
            
            if (isset($data['address'])) {
                $fields[] = "address = ?";
                $params[] = $data['address'];
            }
            
            if (isset($data['password'])) {
                $fields[] = "password = ?";
                $params[] = $data['password'];
            }
            
            $fields[] = "updated_at = CURRENT_TIMESTAMP";
            $params[] = $userId;
            
            $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
            
            // Fetch updated user
            $userModel = new User();
            $updatedUser = $userModel->findById($userId);
            
            if ($updatedUser) {
                return [
                    'success' => true,
                    'user' => $updatedUser
                ];
            }
            
            return [
                'success' => false,
                'message' => 'User not found after update'
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to update profile: ' . $e->getMessage()
            ];
        }
    }
}
