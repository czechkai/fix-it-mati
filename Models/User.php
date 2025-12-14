<?php
/**
 * User Model
 * Represents a user in the system (Customer, Admin, Technician)
 * Handles user data and database operations
 */

namespace FixItMati\Models;

use FixItMati\Core\Database;
use PDO;
use Exception;

class User {
    private $db;
    
    // User properties
    public $id;
    public $email;
    public $full_name;
    public $first_name;
    public $last_name;
    public $phone;
    public $address;
    public $account_number;
    public $profile_image;
    public $role; // 'customer', 'admin', 'technician'
    public $created_at;
    public $updated_at;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Find user by ID
     */
    public static function find($id): ?self {
        $user = new self();
        $conn = $user->db->getConnection();
        
        $sql = "SELECT * FROM users WHERE id = :id LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->execute(['id' => $id]);
        
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($data) {
            $user->fill($data);
            return $user;
        }
        
        return null;
    }
    
    /**
     * Find user by email
     */
    public static function findByEmail(string $email): ?self {
        $user = new self();
        $conn = $user->db->getConnection();
        
        $sql = "SELECT * FROM users WHERE email = :email LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->execute(['email' => $email]);
        
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($data) {
            $user->fill($data);
            return $user;
        }
        
        return null;
    }
    
    /**
     * Find user by account number
     */
    public static function findByAccountNumber(string $accountNumber): ?self {
        $user = new self();
        $conn = $user->db->getConnection();
        
        $sql = "SELECT * FROM users WHERE account_number = :account_number LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->execute(['account_number' => $accountNumber]);
        
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($data) {
            $user->fill($data);
            return $user;
        }
        
        return null;
    }
    
    /**
     * Get all users (with optional filtering)
     */
    public static function all(array $filters = []): array {
        $user = new self();
        $conn = $user->db->getConnection();
        
        $sql = "SELECT * FROM users WHERE 1=1";
        $params = [];
        
        // Filter by role
        if (!empty($filters['role'])) {
            $sql .= " AND role = :role";
            $params['role'] = $filters['role'];
        }
        
        // Search by name or email
        if (!empty($filters['search'])) {
            $sql .= " AND (full_name ILIKE :search OR email ILIKE :search)";
            $params['search'] = "%{$filters['search']}%";
        }
        
        $sql .= " ORDER BY created_at DESC";
        
        // Pagination
        if (!empty($filters['limit'])) {
            $sql .= " LIMIT :limit";
            $params['limit'] = (int)$filters['limit'];
        }
        
        if (!empty($filters['offset'])) {
            $sql .= " OFFSET :offset";
            $params['offset'] = (int)$filters['offset'];
        }
        
        $stmt = $conn->prepare($sql);
        
        // Bind integer parameters separately for PostgreSQL
        foreach ($params as $key => $value) {
            if ($key === 'limit' || $key === 'offset') {
                $stmt->bindValue(":$key", $value, PDO::PARAM_INT);
            } else {
                $stmt->bindValue(":$key", $value);
            }
        }
        
        $stmt->execute();
        
        $users = [];
        while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $userObj = new self();
            $userObj->fill($data);
            $users[] = $userObj;
        }
        
        return $users;
    }
    
    /**
     * Create new user
     */
    public function create(array $data): bool {
        $conn = $this->db->getConnection();
        
        // Hash password if provided
        if (!empty($data['password'])) {
            $data['password_hash'] = password_hash($data['password'], PASSWORD_BCRYPT);
            unset($data['password']);
        }
        
        // Generate account number if not provided
        if (empty($data['account_number'])) {
            $data['account_number'] = $this->generateAccountNumber();
        }
        
        // Set default role if not provided
        if (empty($data['role'])) {
            $data['role'] = 'customer';
        }
        
        $sql = "INSERT INTO users (email, first_name, last_name, phone, address, account_number, role, password_hash, created_at, updated_at) 
                VALUES (:email, :first_name, :last_name, :phone, :address, :account_number, :role, :password_hash, NOW(), NOW()) 
                RETURNING id, email, first_name, last_name, phone, address, account_number, role, created_at, updated_at";
        
        try {
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                'email' => $data['email'],
                'first_name' => $data['first_name'] ?? '',
                'last_name' => $data['last_name'] ?? '',
                'phone' => $data['phone'] ?? null,
                'address' => $data['address'] ?? null,
                'account_number' => $data['account_number'],
                'role' => $data['role'],
                'password_hash' => $data['password_hash'] ?? null
            ]);
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Populate the object with the returned data
            $this->id = $result['id'];
            $this->email = $result['email'];
            $this->first_name = $result['first_name'];
            $this->last_name = $result['last_name'];
            $this->phone = $result['phone'];
            $this->address = $result['address'];
            $this->account_number = $result['account_number'];
            $this->role = $result['role'];
            $this->created_at = $result['created_at'];
            $this->updated_at = $result['updated_at'];
            
            return true;
        } catch (Exception $e) {
            throw new Exception("Failed to create user: " . $e->getMessage());
        }
    }
    
    /**
     * Update user
     */
    public function update(array $data): bool {
        if (empty($this->id)) {
            throw new Exception("Cannot update user without ID");
        }
        
        $conn = $this->db->getConnection();
        
        // Build dynamic update query
        $fields = [];
        $params = ['id' => $this->id];
        
        $allowedFields = ['email', 'full_name', 'phone', 'address', 'role'];
        
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $fields[] = "$field = :$field";
                $params[$field] = $data[$field];
            }
        }
        
        // Handle password update separately
        if (!empty($data['password'])) {
            $fields[] = "password_hash = :password_hash";
            $params['password_hash'] = password_hash($data['password'], PASSWORD_BCRYPT);
        }
        
        if (empty($fields)) {
            return false; // Nothing to update
        }
        
        $fields[] = "updated_at = NOW()";
        $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = :id";
        
        try {
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
            
            // Refresh user data
            $updated = self::find($this->id);
            if ($updated) {
                $this->fill($updated->toArray());
            }
            
            return true;
        } catch (Exception $e) {
            throw new Exception("Failed to update user: " . $e->getMessage());
        }
    }
    
    /**
     * Delete user
     */
    public function delete(): bool {
        if (empty($this->id)) {
            throw new Exception("Cannot delete user without ID");
        }
        
        $conn = $this->db->getConnection();
        
        $sql = "DELETE FROM users WHERE id = :id";
        
        try {
            $stmt = $conn->prepare($sql);
            $stmt->execute(['id' => $this->id]);
            return true;
        } catch (Exception $e) {
            throw new Exception("Failed to delete user: " . $e->getMessage());
        }
    }
    
    /**
     * Verify password
     */
    public function verifyPassword(string $password): bool {
        $conn = $this->db->getConnection();
        
        $sql = "SELECT password_hash FROM users WHERE id = :id LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->execute(['id' => $this->id]);
        
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($data && !empty($data['password_hash'])) {
            return password_verify($password, $data['password_hash']);
        }
        
        return false;
    }
    
    /**
     * Check if user has role
     */
    public function hasRole(string $role): bool {
        return $this->role === $role;
    }
    
    /**
     * Check if user is admin
     */
    public function isAdmin(): bool {
        return $this->role === 'admin';
    }
    
    /**
     * Check if user is customer
     */
    public function isCustomer(): bool {
        return $this->role === 'customer';
    }
    
    /**
     * Check if user is technician
     */
    public function isTechnician(): bool {
        return $this->role === 'technician';
    }
    
    /**
     * Fill model with data
     */
    private function fill(array $data): void {
        $this->id = $data['id'] ?? null;
        $this->email = $data['email'] ?? null;
        $this->full_name = $data['full_name'] ?? null;
        $this->phone = $data['phone'] ?? null;
        $this->address = $data['address'] ?? null;
        $this->account_number = $data['account_number'] ?? null;
        $this->role = $data['role'] ?? 'customer';
        $this->created_at = $data['created_at'] ?? null;
        $this->updated_at = $data['updated_at'] ?? null;
    }
    
    /**
     * Convert model to array
     */
    public function toArray(): array {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'full_name' => $this->full_name,
            'phone' => $this->phone,
            'address' => $this->address,
            'account_number' => $this->account_number,
            'profile_image' => $this->profile_image,
            'role' => $this->role,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
    
    /**
     * Generate unique account number
     */
    private function generateAccountNumber(): string {
        return 'ACC' . date('Y') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
    }
    
    /**
     * Get user settings
     */
    public function getSettings(string $userId): array {
        $conn = $this->db->getConnection();
        
        $sql = "SELECT * FROM user_settings WHERE user_id = :user_id";
        $stmt = $conn->prepare($sql);
        $stmt->execute(['user_id' => $userId]);
        
        $settings = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Return default settings if none exist
        if (!$settings) {
            return $this->getDefaultSettings();
        }
        
        // Remove unnecessary fields
        unset($settings['id'], $settings['user_id'], $settings['created_at'], $settings['updated_at']);
        
        return $settings;
    }
    
    /**
     * Update user settings
     */
    public function updateSettings(string $userId, array $settings): bool {
        $conn = $this->db->getConnection();
        
        // Check if settings exist
        $checkSql = "SELECT id FROM user_settings WHERE user_id = :user_id";
        $stmt = $conn->prepare($checkSql);
        $stmt->execute(['user_id' => $userId]);
        $exists = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$exists) {
            // Insert new settings with support PIN
            $settings['support_pin'] = $settings['support_pin'] ?? $this->generateSupportPin();
            
            $fields = array_keys($settings);
            $placeholders = array_map(fn($field) => ":$field", $fields);
            
            $sql = "INSERT INTO user_settings (user_id, " . implode(', ', $fields) . ") 
                    VALUES (:user_id, " . implode(', ', $placeholders) . ")";
            
            $stmt = $conn->prepare($sql);
            $settings['user_id'] = $userId;
            return $stmt->execute($settings);
        } else {
            // Update existing settings
            $setParts = [];
            foreach (array_keys($settings) as $field) {
                $setParts[] = "$field = :$field";
            }
            
            $sql = "UPDATE user_settings SET " . implode(', ', $setParts) . " 
                    WHERE user_id = :user_id";
            
            $stmt = $conn->prepare($sql);
            $settings['user_id'] = $userId;
            return $stmt->execute($settings);
        }
    }
    
    /**
     * Get payment methods for user
     */
    public function getPaymentMethods(string $userId): array {
        $conn = $this->db->getConnection();
        
        $sql = "SELECT * FROM payment_methods WHERE user_id = :user_id ORDER BY is_default DESC, created_at DESC";
        $stmt = $conn->prepare($sql);
        $stmt->execute(['user_id' => $userId]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get household members for user
     */
    public function getHouseholdMembers(string $userId): array {
        $conn = $this->db->getConnection();
        
        $sql = "SELECT * FROM household_members WHERE user_id = :user_id ORDER BY created_at ASC";
        $stmt = $conn->prepare($sql);
        $stmt->execute(['user_id' => $userId]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get default settings
     */
    private function getDefaultSettings(): array {
        return [
            'bill_reminders' => true,
            'bill_reminder_days' => 3,
            'high_consumption_water' => true,
            'high_consumption_power' => false,
            'water_interrupt_alerts' => true,
            'power_interrupt_alerts' => true,
            'auto_pay' => false,
            'paperless' => true,
            'calendar_sync' => false,
            'language' => 'English',
            'font_size' => 'Normal',
            'dark_mode' => false,
            'two_factor' => false,
            'support_pin' => $this->generateSupportPin()
        ];
    }
    
    /**
     * Generate random 4-digit support PIN
     */
    private function generateSupportPin(): string {
        return str_pad((string)rand(1000, 9999), 4, '0', STR_PAD_LEFT);
    }
}
