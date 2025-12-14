<?php
/**
 * Database Audit Script
 * Checks actual database structure vs expected schema
 */

require_once __DIR__ . '/Core/Database.php';

use FixItMati\Core\Database;

// Load env
$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') === false) continue;
        list($key, $value) = explode('=', $line, 2);
        $_ENV[trim($key)] = trim($value);
    }
}

try {
    $db = Database::getInstance()->getConnection();
    
    echo "═══════════════════════════════════════════════════════\n";
    echo "         DATABASE STRUCTURE AUDIT\n";
    echo "═══════════════════════════════════════════════════════\n\n";
    
    $tables = [
        'users' => ['id', 'email', 'full_name', 'phone', 'address', 'account_number', 'role', 'created_at', 'updated_at'],
        'service_requests' => ['id', 'user_id', 'title', 'description', 'category', 'status', 'priority', 'location', 'assigned_technician_id', 'created_at', 'updated_at', 'completed_at'],
        'request_updates' => ['id', 'request_id', 'status', 'message', 'created_by', 'created_at'],
        'announcements' => ['id', 'title', 'content', 'category', 'type', 'status', 'affected_areas', 'start_date', 'end_date', 'created_by', 'created_at', 'updated_at'],
        'announcement_comments' => ['id', 'announcement_id', 'user_id', 'comment', 'created_at'],
        'payments' => ['id', 'user_id', 'bill_month', 'amount', 'status', 'due_date', 'paid_date', 'payment_method', 'reference_number', 'created_at', 'updated_at'],
        'payment_items' => ['id', 'payment_id', 'description', 'amount', 'category'],
        'transactions' => ['id', 'user_id', 'payment_id', 'amount', 'type', 'status', 'reference_number', 'notes', 'created_at'],
        'technicians' => ['id', 'user_id', 'specialization', 'status', 'phone', 'assigned_area', 'created_at']
    ];
    
    foreach ($tables as $tableName => $expectedColumns) {
        echo "┌─ TABLE: {$tableName}\n";
        
        // Check if table exists
        $checkTable = $db->query("SELECT EXISTS (
            SELECT FROM information_schema.tables 
            WHERE table_schema = 'public' 
            AND table_name = '{$tableName}'
        )");
        $exists = $checkTable->fetchColumn();
        
        if (!$exists) {
            echo "│  ✗ TABLE MISSING!\n";
            echo "└─\n\n";
            continue;
        }
        
        echo "│  ✓ Table exists\n";
        
        // Get actual columns
        $columnsQuery = $db->query("
            SELECT column_name, data_type, is_nullable, column_default
            FROM information_schema.columns
            WHERE table_schema = 'public' AND table_name = '{$tableName}'
            ORDER BY ordinal_position
        ");
        $actualColumns = $columnsQuery->fetchAll(PDO::FETCH_ASSOC);
        
        // Get row count
        $countQuery = $db->query("SELECT COUNT(*) FROM {$tableName}");
        $rowCount = $countQuery->fetchColumn();
        
        echo "│  Rows: {$rowCount}\n";
        echo "│  \n";
        echo "│  Columns:\n";
        
        $actualColumnNames = array_column($actualColumns, 'column_name');
        
        // Check expected columns
        foreach ($expectedColumns as $col) {
            if (in_array($col, $actualColumnNames)) {
                $filtered = array_filter($actualColumns, fn($c) => $c['column_name'] === $col);
                if (!empty($filtered)) {
                    $colInfo = array_values($filtered)[0];
                    echo "│    ✓ {$col} ({$colInfo['data_type']})\n";
                } else {
                    echo "│    ✓ {$col}\n";
                }
            } else {
                echo "│    ✗ MISSING: {$col}\n";
            }
        }
        
        // Check for unexpected columns
        foreach ($actualColumnNames as $col) {
            if (!in_array($col, $expectedColumns)) {
                echo "│    ⚠ EXTRA: {$col}\n";
            }
        }
        
        echo "└─\n\n";
    }
    
    echo "═══════════════════════════════════════════════════════\n";
    echo "                    SUMMARY\n";
    echo "═══════════════════════════════════════════════════════\n";
    
    // Count data in each table
    foreach (array_keys($tables) as $table) {
        try {
            $stmt = $db->query("SELECT COUNT(*) FROM {$table}");
            $count = $stmt->fetchColumn();
            echo str_pad($table, 30) . ": {$count} rows\n";
        } catch (Exception $e) {
            echo str_pad($table, 30) . ": TABLE NOT FOUND\n";
        }
    }
    
    echo "\n";
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
