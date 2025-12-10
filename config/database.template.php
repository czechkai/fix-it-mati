<?php
/**
 * Database Configuration Template
 * INSTRUCTIONS: Copy this file to create your database config
 * 
 * Option 1: Use .env file (Recommended for production)
 *   - Copy .env.example to .env
 *   - Update values in .env
 *   - This file will load them automatically
 * 
 * Option 2: Direct configuration (Quick setup for teams)
 *   - Uncomment the define() statements below
 *   - Replace with your actual credentials
 *   - Comment out or remove the .env loading section
 */

// ===========================================
// OPTION 1: Load from .env file
// ===========================================
// (Comment this section if using Option 2)
/*
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0 || strpos($line, '=') === false) {
            continue;
        }
        list($key, $value) = explode('=', $line, 2);
        define(trim($key), trim($value));
    }
}
*/

// ===========================================
// OPTION 2: Direct configuration (TEAM SETUP)
// ===========================================
// Team credentials - already configured!

define('DB_HOST', 'db.qyuwbrougimcexrjvrcm.supabase.co');
define('DB_PORT', '5432');
define('DB_NAME', 'postgres');
define('DB_USER', 'postgres');
define('DB_PASSWORD', 'fIxITmAtI123');

// ===========================================
// Alternative: Local PostgreSQL
// ===========================================
// If using local PostgreSQL instead of Supabase:
/*
define('DB_HOST', 'localhost');
define('DB_PORT', '5432');
define('DB_NAME', 'fixitmati');
define('DB_USER', 'your_username');
define('DB_PASSWORD', 'your_password');
*/
