<?php
require_once __DIR__ . '/../autoload.php';

use FixItMati\Models\Payment;

$payment = new Payment();
$transactions = $payment->getAllTransactions();

echo "Found " . count($transactions) . " transactions\n\n";

if (count($transactions) > 0) {
    foreach (array_slice($transactions, 0, 5) as $t) {
        echo "{$t['reference_number']}: ₱{$t['amount']} ({$t['status']}) - {$t['user_name']}\n";
    }
}
