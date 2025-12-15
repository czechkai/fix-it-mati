<?php

namespace FixItMati\Models;

use FixItMati\Core\Database;

/**
 * Payment Model
 * 
 * Handles database operations for payments, payment items, and transactions
 */
class Payment
{
    private \PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Get current unpaid bills for a user
     */
    public function getCurrentBills(string $userId): array
    {
        $sql = "SELECT p.*, 
                       COALESCE(json_agg(
                           json_build_object(
                               'id', pi.id,
                               'description', pi.description,
                               'amount', pi.amount,
                               'category', pi.category
                           )
                       ) FILTER (WHERE pi.id IS NOT NULL), '[]'::json) as items
                FROM payments p
                LEFT JOIN payment_items pi ON p.id = pi.payment_id
                WHERE p.user_id = :user_id 
                AND p.status IN ('unpaid', 'overdue', 'partial')
                GROUP BY p.id
                ORDER BY p.due_date ASC";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['user_id' => $userId]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error fetching current bills: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get payment history for a user
     */
    public function getHistory(string $userId, int $limit = 10): array
    {
        $sql = "SELECT t.*, p.bill_month, p.amount as bill_amount
                FROM transactions t
                LEFT JOIN payments p ON t.payment_id = p.id
                WHERE t.user_id = :user_id
                ORDER BY t.created_at DESC
                LIMIT :limit";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':user_id', $userId);
            $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error fetching payment history: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Create a new payment record
     */
    public function createPayment(array $data): ?array
    {
        $sql = "INSERT INTO payments (
            user_id, bill_month, amount, status, due_date
        ) VALUES (
            :user_id, :bill_month, :amount, :status, :due_date
        ) RETURNING *";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'user_id' => $data['user_id'],
                'bill_month' => $data['bill_month'],
                'amount' => $data['amount'],
                'status' => $data['status'] ?? 'unpaid',
                'due_date' => $data['due_date']
            ]);

            return $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error creating payment: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Add payment items (breakdown)
     */
    public function addPaymentItems(string $paymentId, array $items): bool
    {
        $sql = "INSERT INTO payment_items (payment_id, description, amount, category)
                VALUES (:payment_id, :description, :amount, :category)";

        try {
            $stmt = $this->db->prepare($sql);

            foreach ($items as $item) {
                $stmt->execute([
                    'payment_id' => $paymentId,
                    'description' => $item['description'],
                    'amount' => $item['amount'],
                    'category' => $item['category']
                ]);
            }

            return true;
        } catch (\PDOException $e) {
            error_log("Error adding payment items: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Process payment and create transaction record
     */
    public function processPayment(string $paymentId, string $userId, array $paymentData): ?array
    {
        try {
            $this->db->beginTransaction();

            // Update payment status
            $updateSql = "UPDATE payments 
                         SET status = 'paid',
                             paid_date = NOW(),
                             payment_method = :payment_method,
                             reference_number = :reference_number,
                             updated_at = NOW()
                         WHERE id = :payment_id
                         RETURNING *";

            $stmt = $this->db->prepare($updateSql);
            $stmt->execute([
                'payment_id' => $paymentId,
                'payment_method' => $paymentData['payment_method'],
                'reference_number' => $paymentData['reference_number']
            ]);

            $payment = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$payment) {
                throw new \Exception("Payment not found");
            }

            // Create transaction record
            $transactionSql = "INSERT INTO transactions (
                user_id, payment_id, amount, type, status, 
                reference_number, notes
            ) VALUES (
                :user_id, :payment_id, :amount, 'payment', 'completed',
                :reference_number, :notes
            ) RETURNING *";

            $transStmt = $this->db->prepare($transactionSql);
            $transStmt->execute([
                'user_id' => $userId,
                'payment_id' => $paymentId,
                'amount' => $payment['amount'],
                'reference_number' => $paymentData['reference_number'],
                'notes' => $paymentData['notes'] ?? null
            ]);

            $transaction = $transStmt->fetch(\PDO::FETCH_ASSOC);

            $this->db->commit();

            return [
                'payment' => $payment,
                'transaction' => $transaction
            ];
        } catch (\Exception $e) {
            $this->db->rollBack();
            error_log("Error processing payment: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get total amount due for a user
     */
    public function getTotalDue(string $userId): float
    {
        $sql = "SELECT COALESCE(SUM(amount), 0) as total
                FROM payments
                WHERE user_id = :user_id
                AND status IN ('unpaid', 'overdue', 'partial')";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['user_id' => $userId]);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            return (float) ($result['total'] ?? 0);
        } catch (\PDOException $e) {
            error_log("Error calculating total due: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get payment by ID
     */
    public function find(string $id): ?array
    {
        $sql = "SELECT p.*,
                       COALESCE(json_agg(
                           json_build_object(
                               'id', pi.id,
                               'description', pi.description,
                               'amount', pi.amount,
                               'category', pi.category
                           )
                       ) FILTER (WHERE pi.id IS NOT NULL), '[]'::json) as items
                FROM payments p
                LEFT JOIN payment_items pi ON p.id = pi.payment_id
                WHERE p.id = :id
                GROUP BY p.id";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['id' => $id]);
            return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
        } catch (\PDOException $e) {
            error_log("Error finding payment: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get all payment items for a payment
     */
    public function getPaymentItems(string $paymentId): array
    {
        $sql = "SELECT * FROM payment_items 
                WHERE payment_id = :payment_id 
                ORDER BY category, description";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['payment_id' => $paymentId]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error fetching payment items: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get complete transaction history for a user
     * Returns all payment transactions with detailed information
     */
    public function getTransactionHistory(string $userId, ?string $type = null): array
    {
        $sql = "SELECT 
                    t.id,
                    t.reference_number,
                    t.payment_id,
                    t.amount,
                    t.status,
                    t.payment_method,
                    t.gateway,
                    t.gateway_reference,
                    t.created_at as transaction_date,
                    p.bill_month as billing_period,
                    p.due_date,
                    CASE 
                        WHEN pi.category = 'water' THEN 'Water'
                        WHEN pi.category = 'electricity' THEN 'Electricity'
                        ELSE 'Services'
                    END as type,
                    CASE 
                        WHEN pi.category = 'water' THEN 'Mati Water District'
                        WHEN pi.category = 'electricity' THEN 'Davao Light'
                        ELSE 'City Services'
                    END as biller
                FROM transactions t
                LEFT JOIN payments p ON t.payment_id = p.id
                LEFT JOIN payment_items pi ON p.id = pi.payment_id
                WHERE t.user_id = :user_id";

        $params = ['user_id' => $userId];

        if ($type && $type !== 'All') {
            $sql .= " AND pi.category = :type";
            $params['type'] = strtolower($type);
        }

        $sql .= " GROUP BY t.id, t.reference_number, t.payment_id, t.amount, t.status, 
                          t.payment_method, t.gateway, t.gateway_reference, t.created_at,
                          p.bill_month, p.due_date, pi.category
                  ORDER BY t.created_at DESC";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error fetching transaction history: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Check for overdue payments and update status
     */
    public function updateOverduePayments(): int
    {
        $sql = "UPDATE payments
                SET status = 'overdue',
                    updated_at = NOW()
                WHERE status = 'unpaid'
                AND due_date < CURRENT_DATE";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->rowCount();
        } catch (\PDOException $e) {
            error_log("Error updating overdue payments: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Update payment status and additional data
     * 
     * @param string $paymentId Payment ID
     * @param string $status New status (pending, paid, failed, cancelled)
     * @param array $additionalData Additional data to update (payment_method, reference_number, etc.)
     * @return bool Success status
     */
    public function updatePaymentStatus(string $paymentId, string $status, array $additionalData = []): bool
    {
        try {
            // Build dynamic UPDATE query based on additional data
            $fields = ['status = :status', 'updated_at = NOW()'];
            $params = [
                'payment_id' => $paymentId,
                'status' => $status
            ];

            // Add optional fields
            if (isset($additionalData['payment_method'])) {
                $fields[] = 'payment_method = :payment_method';
                $params['payment_method'] = $additionalData['payment_method'];
            }

            if (isset($additionalData['reference_number'])) {
                $fields[] = 'reference_number = :reference_number';
                $params['reference_number'] = $additionalData['reference_number'];
            }

            if (isset($additionalData['gateway_transaction_id'])) {
                $fields[] = 'gateway_transaction_id = :gateway_transaction_id';
                $params['gateway_transaction_id'] = $additionalData['gateway_transaction_id'];
            }

            if ($status === 'paid' && !isset($additionalData['paid_date'])) {
                $fields[] = 'paid_date = NOW()';
            }

            $sql = "UPDATE payments SET " . implode(', ', $fields) . " WHERE id = :payment_id";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);

            return $stmt->rowCount() > 0;
        } catch (\PDOException $e) {
            error_log("Error updating payment status: " . $e->getMessage());
            error_log("Payment ID: $paymentId, Status: $status");
            return false;
        }
    }
}
