<?php
require_once __DIR__ . '/../autoload.php';

$db = \FixItMati\Core\Database::getInstance();
$conn = $db->getConnection();
$conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

echo "=== PAYMENTS TABLE STRUCTURE ===\n";
$stmt = $conn->query("
    SELECT column_name, data_type, is_nullable, column_default
    FROM information_schema.columns
    WHERE table_name = 'payments'
    ORDER BY ordinal_position
");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "{$row['column_name']} ({$row['data_type']}) - Nullable: {$row['is_nullable']}\n";
}

echo "\n=== TRANSACTIONS TABLE STRUCTURE ===\n";
$stmt = $conn->query("
    SELECT column_name, data_type, is_nullable, column_default
    FROM information_schema.columns
    WHERE table_name = 'transactions'
    ORDER BY ordinal_position
");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "{$row['column_name']} ({$row['data_type']}) - Nullable: {$row['is_nullable']}\n";
}

echo "\n=== SAMPLE DATA FROM PAYMENTS ===\n";
$stmt = $conn->query("
    SELECT id, user_id, bill_month, amount, status, due_date, paid_date, payment_method
    FROM payments
    LIMIT 3
");
echo "Payments (Bills/Invoices):\n";
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "  ID: {$row['id']}\n";
    echo "  Amount: ₱{$row['amount']}\n";
    echo "  Status: {$row['status']}\n";
    echo "  Due Date: {$row['due_date']}\n";
    echo "  Paid Date: " . ($row['paid_date'] ?? 'Not paid') . "\n";
    echo "  Payment Method: " . ($row['payment_method'] ?? 'None') . "\n";
    echo "\n";
}

echo "\n=== SAMPLE DATA FROM TRANSACTIONS ===\n";
$stmt = $conn->query("
    SELECT id, user_id, payment_id, amount, type, status, reference_number, created_at
    FROM transactions
    LIMIT 3
");
echo "Transactions (Payment Submissions):\n";
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "  ID: {$row['id']}\n";
    echo "  Payment ID: " . ($row['payment_id'] ?? 'None') . "\n";
    echo "  Amount: ₱{$row['amount']}\n";
    echo "  Type: {$row['type']}\n";
    echo "  Status: {$row['status']}\n";
    echo "  Reference: {$row['reference_number']}\n";
    echo "  Created: {$row['created_at']}\n";
    echo "\n";
}

echo "\n=== RELATIONSHIP EXAMPLE ===\n";
$stmt = $conn->query("
    SELECT 
        p.id as payment_id,
        p.bill_month,
        p.amount as bill_amount,
        p.status as bill_status,
        t.id as transaction_id,
        t.amount as transaction_amount,
        t.status as transaction_status,
        t.reference_number
    FROM payments p
    LEFT JOIN transactions t ON t.payment_id = p.id
    WHERE t.id IS NOT NULL
    LIMIT 3
");
echo "Payment → Transaction Relationship:\n";
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "  Bill: {$row['bill_month']} - ₱{$row['bill_amount']} ({$row['bill_status']})\n";
    echo "  Transaction: {$row['reference_number']} - ₱{$row['transaction_amount']} ({$row['transaction_status']})\n";
    echo "\n";
}
