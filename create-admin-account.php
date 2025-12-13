<?php
/**
 * Create Admin Account
 * Sets up an admin user for accessing the admin dashboard
 */

require_once __DIR__ . '/autoload.php';

use FixItMati\Core\Database;

try {
    $db = Database::getInstance()->getConnection();
    
    echo "🔧 Creating Admin Account for FixItMati\n";
    echo str_repeat("=", 60) . "\n\n";
    
    // Admin user details
    $adminData = [
        'email' => 'admin@fixitmati.com',
        'password' => 'admin123',
        'first_name' => 'Admin',
        'last_name' => 'User',
        'phone' => '09123456789',
        'role' => 'admin'
    ];
    
    // Check if admin already exists
    $checkSql = "SELECT id, email, role FROM users WHERE email = :email";
    $checkStmt = $db->prepare($checkSql);
    $checkStmt->execute(['email' => $adminData['email']]);
    $existing = $checkStmt->fetch(PDO::FETCH_ASSOC);
    
    if ($existing) {
        echo "ℹ️  Admin account already exists!\n\n";
        echo "📧 Email: {$existing['email']}\n";
        echo "👤 Role: {$existing['role']}\n";
        echo "🔑 Password: admin123 (if not changed)\n\n";
        
        // Update to admin role if not already
        if ($existing['role'] !== 'admin') {
            echo "🔄 Updating user to admin role...\n";
            $updateSql = "UPDATE users SET role = 'admin' WHERE id = :id";
            $updateStmt = $db->prepare($updateSql);
            $updateStmt->execute(['id' => $existing['id']]);
            echo "✅ User role updated to admin!\n\n";
        }
    } else {
        // Hash password
        $hashedPassword = password_hash($adminData['password'], PASSWORD_BCRYPT);
        
        // Create admin account
        $sql = "INSERT INTO users (
                    email, 
                    password_hash, 
                    first_name, 
                    last_name, 
                    phone, 
                    role
                ) VALUES (
                    :email,
                    :password_hash,
                    :first_name,
                    :last_name,
                    :phone,
                    :role
                ) RETURNING id, email, role";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([
            'email' => $adminData['email'],
            'password_hash' => $hashedPassword,
            'first_name' => $adminData['first_name'],
            'last_name' => $adminData['last_name'],
            'phone' => $adminData['phone'],
            'role' => $adminData['role']
        ]);
        
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($admin) {
            echo "✅ Admin account created successfully!\n\n";
            echo "📧 Email: {$admin['email']}\n";
            echo "👤 Role: {$admin['role']}\n";
            echo "🔑 Password: {$adminData['password']}\n\n";
        }
    }
    
    echo "🌐 Access the admin dashboard at:\n";
    echo "   http://localhost:8000/admin-dashboard.php\n\n";
    
    echo "🔐 Login Credentials:\n";
    echo "   Email: admin@fixitmati.com\n";
    echo "   Password: admin123\n\n";
    
    echo "⚠️  IMPORTANT: Change the admin password after first login!\n\n";
    
    // Also create a staff account for testing
    echo "➕ Creating Staff Account...\n\n";
    
    $staffData = [
        'email' => 'staff@fixitmati.com',
        'password' => 'staff123',
        'first_name' => 'Staff',
        'last_name' => 'Member',
        'phone' => '09187654321',
        'role' => 'staff'
    ];
    
    // Check if staff already exists
    $checkStmt->execute(['email' => $staffData['email']]);
    $existingStaff = $checkStmt->fetch(PDO::FETCH_ASSOC);
    
    if ($existingStaff) {
        echo "ℹ️  Staff account already exists!\n";
        echo "📧 Email: {$existingStaff['email']}\n";
        echo "👤 Role: {$existingStaff['role']}\n";
        echo "🔑 Password: staff123 (if not changed)\n\n";
        
        // Update to staff role if not already
        if ($existingStaff['role'] !== 'staff' && $existingStaff['role'] !== 'admin') {
            $updateSql = "UPDATE users SET role = 'staff' WHERE id = :id";
            $updateStmt = $db->prepare($updateSql);
            $updateStmt->execute(['id' => $existingStaff['id']]);
            echo "✅ User role updated to staff!\n\n";
        }
    } else {
        $hashedStaffPassword = password_hash($staffData['password'], PASSWORD_BCRYPT);
        
        $stmt->execute([
            'email' => $staffData['email'],
            'password_hash' => $hashedStaffPassword,
            'first_name' => $staffData['first_name'],
            'last_name' => $staffData['last_name'],
            'phone' => $staffData['phone'],
            'role' => $staffData['role']
        ]);
        
        $staff = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($staff) {
            echo "✅ Staff account created successfully!\n\n";
            echo "📧 Email: {$staff['email']}\n";
            echo "👤 Role: {$staff['role']}\n";
            echo "🔑 Password: {$staffData['password']}\n\n";
        }
    }
    
    echo "📋 Summary of Admin/Staff Accounts:\n";
    echo str_repeat("-", 60) . "\n";
    
    $usersSql = "SELECT id, email, first_name, last_name, role FROM users WHERE role IN ('admin', 'staff') ORDER BY role, email";
    $usersStmt = $db->query($usersSql);
    $adminUsers = $usersStmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($adminUsers as $user) {
        $roleIcon = $user['role'] === 'admin' ? '👑' : '👤';
        echo "{$roleIcon} {$user['first_name']} {$user['last_name']} ({$user['email']}) - {$user['role']}\n";
    }
    
    echo "\n✅ Setup complete! You can now login to the admin dashboard.\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
