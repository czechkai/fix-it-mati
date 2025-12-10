<?php
require_once __DIR__ . '/Core/Database.php';

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

use FixItMati\Core\Database;

$db = Database::getInstance()->getConnection();
$stmt = $db->query('SELECT id, email, role, full_name FROM users');
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "\nAll Users in Database:\n";
echo "────────────────────────────────────────────\n";
foreach ($users as $u) {
    echo "{$u['email']} | Role: {$u['role']} | Name: {$u['full_name']}\n";
    echo "ID: {$u['id']}\n\n";
}

// Check payments for each user
echo "\nPayments by User:\n";
echo "────────────────────────────────────────────\n";
$payStmt = $db->query('SELECT p.user_id, u.email, COUNT(p.id) as bill_count, SUM(CASE WHEN p.status IN (\'unpaid\', \'overdue\') THEN p.amount ELSE 0 END) as total_due FROM payments p JOIN users u ON p.user_id = u.id GROUP BY p.user_id, u.email');
$payments = $payStmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($payments as $p) {
    echo "{$p['email']}: {$p['bill_count']} bills, ₱" . number_format($p['total_due'], 2) . " due\n";
}
