<?php
require_once __DIR__ . '/../autoload.php';

use FixItMati\Core\Database;

$db = Database::getInstance()->getConnection();

// Check transactions
$stmt = $db->query('SELECT COUNT(*) as count FROM transactions');
$result = $stmt->fetch(PDO::FETCH_ASSOC);
echo "Transactions in database: " . $result['count'] . "\n";

// Get sample transactions
$stmt = $db->query('SELECT id, reference_number, amount, status FROM transactions LIMIT 5');
$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($transactions as $txn) {
    echo "- {$txn['reference_number']}: ₱{$txn['amount']} ({$txn['status']})\n";
}
