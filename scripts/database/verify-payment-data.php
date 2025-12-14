<?php
/**
 * Test Payment API Endpoints
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

$db = Database::getInstance()->getConnection();

echo "\n";
echo "═══════════════════════════════════════════════════════\n";
echo "           PAYMENT DATA VERIFICATION\n";
echo "═══════════════════════════════════════════════════════\n\n";

// Get all users with payments
$sql = "SELECT 
    u.id, 
    u.email, 
    u.full_name,
    COUNT(p.id) as payment_count,
    COALESCE(SUM(CASE WHEN p.status IN ('unpaid', 'overdue', 'partial') THEN p.amount ELSE 0 END), 0) as total_due
FROM users u
LEFT JOIN payments p ON u.id = p.user_id
WHERE u.role = 'user'
GROUP BY u.id, u.email, u.full_name
ORDER BY total_due DESC";

$stmt = $db->query($sql);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Users with Payment Data:\n";
echo "─────────────────────────────────────────────────────\n";

foreach ($users as $user) {
    echo "\n📧 {$user['email']} ({$user['full_name']})\n";
    echo "   ID: {$user['id']}\n";
    echo "   Bills: {$user['payment_count']}\n";
    echo "   Total Due: ₱" . number_format($user['total_due'], 2) . "\n";
    
    // Get detailed bill info
    $billSql = "SELECT bill_month, amount, status, due_date 
                FROM payments 
                WHERE user_id = :user_id 
                AND status IN ('unpaid', 'overdue', 'partial')
                ORDER BY due_date ASC";
    $billStmt = $db->prepare($billSql);
    $billStmt->execute(['user_id' => $user['id']]);
    $bills = $billStmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (!empty($bills)) {
        echo "   \n   Unpaid Bills:\n";
        foreach ($bills as $bill) {
            $statusColor = $bill['status'] === 'overdue' ? '🔴' : '🟡';
            echo "   {$statusColor} {$bill['bill_month']}: ₱" . number_format($bill['amount'], 2) . " - {$bill['status']} (Due: {$bill['due_date']})\n";
        }
    }
}

echo "\n";
echo "═══════════════════════════════════════════════════════\n\n";

// Check payment items
$itemsSql = "SELECT COUNT(*) FROM payment_items";
$itemsCount = $db->query($itemsSql)->fetchColumn();
echo "Total payment items in database: {$itemsCount}\n\n";

// Show API endpoints to test
echo "Test these API endpoints with a valid auth token:\n";
echo "─────────────────────────────────────────────────────\n";
echo "1. GET /api/payments/current\n";
echo "   Returns current unpaid bills for logged-in user\n\n";
echo "2. GET /api/payments/history\n";
echo "   Returns payment transaction history\n\n";
echo "3. POST /api/payments/process\n";
echo "   Process a payment\n\n";

echo "To test in browser:\n";
echo "1. Login at: http://localhost:8000/login.php\n";
echo "2. Visit: http://localhost:8000/payments.php\n";
echo "3. Check browser console for API calls\n\n";
