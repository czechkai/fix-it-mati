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

    // ============================================
    // ADMIN BILLING METHODS
    // ============================================

    /**
     * Get all transactions for admin view
     */
    public function getAllTransactions(?string $status = null): array
    {
        $sql = "SELECT 
                    t.id,
                    t.reference_number,
                    t.user_id,
                    CASE 
                        WHEN u.first_name IS NOT NULL AND u.last_name IS NOT NULL 
                        THEN CONCAT(u.first_name, ' ', u.last_name)
                        ELSE u.email
                    END as user_name,
                    u.email as user_email,
                    t.amount,
                    CASE 
                        WHEN t.status = 'completed' THEN 'success'
                        ELSE t.status
                    END as status,
                    p.payment_method,
                    t.payment_id,
                    p.bill_month,
                    COALESCE(
                        (SELECT pi.category FROM payment_items pi WHERE pi.payment_id = p.id LIMIT 1),
                        'general'
                    ) as payment_type,
                    t.created_at,
                    t.notes
                FROM transactions t
                LEFT JOIN users u ON t.user_id = u.id
                LEFT JOIN payments p ON t.payment_id = p.id";

        $params = [];

        if ($status && $status !== 'All') {
            $sql .= " WHERE ";
            if ($status === 'Success') {
                $sql .= "t.status IN ('completed', 'success')";
            } else {
                $sql .= "t.status = :status";
                $params['status'] = strtolower($status);
            }
        }

        $sql .= " ORDER BY t.created_at DESC LIMIT 100";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error fetching all transactions: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get billing statistics for admin dashboard
     */
    public function getBillingStats(): array
    {
        try {
            // Total revenue for current month
            $revenueSql = "SELECT COALESCE(SUM(amount), 0) as total_revenue
                          FROM transactions
                          WHERE status IN ('success', 'completed', 'paid')
                          AND EXTRACT(MONTH FROM created_at) = EXTRACT(MONTH FROM CURRENT_DATE)
                          AND EXTRACT(YEAR FROM created_at) = EXTRACT(YEAR FROM CURRENT_DATE)";
            $stmt = $this->db->query($revenueSql);
            $revenue = $stmt->fetch(\PDO::FETCH_ASSOC)['total_revenue'];

            // Pending verification count
            $pendingSql = "SELECT COUNT(*) as pending_count
                          FROM transactions
                          WHERE status = 'pending'";
            $stmt = $this->db->query($pendingSql);
            $pendingCount = $stmt->fetch(\PDO::FETCH_ASSOC)['pending_count'];

            // Collection rate (paid vs total due)
            $collectionSql = "SELECT 
                                CASE 
                                    WHEN COUNT(*) > 0 THEN 
                                        ROUND((COUNT(CASE WHEN status IN ('paid', 'success', 'completed') THEN 1 END)::numeric / COUNT(*)::numeric) * 100, 0)
                                    ELSE 0
                                END as collection_rate
                              FROM payments
                              WHERE EXTRACT(MONTH FROM created_at) = EXTRACT(MONTH FROM CURRENT_DATE)
                              AND EXTRACT(YEAR FROM created_at) = EXTRACT(YEAR FROM CURRENT_DATE)";
            $stmt = $this->db->query($collectionSql);
            $collectionRate = $stmt->fetch(\PDO::FETCH_ASSOC)['collection_rate'];

            return [
                'totalRevenue' => (float) $revenue,
                'pendingCount' => (int) $pendingCount,
                'collectionRate' => (int) $collectionRate
            ];
        } catch (\PDOException $e) {
            error_log("Error fetching billing stats: " . $e->getMessage());
            return [
                'totalRevenue' => 0,
                'pendingCount' => 0,
                'collectionRate' => 0
            ];
        }
    }

    /**
     * Get all payments/invoices for admin view
     */
    public function getAllPaymentsAdmin(): array
    {
        $sql = "SELECT 
                    p.id,
                    p.user_id,
                    CASE 
                        WHEN u.first_name IS NOT NULL AND u.last_name IS NOT NULL 
                        THEN CONCAT(u.first_name, ' ', u.last_name)
                        ELSE u.email
                    END as user_name,
                    u.email as user_email,
                    p.bill_month,
                    p.amount,
                    p.status,
                    p.due_date,
                    p.paid_date,
                    p.payment_method,
                    p.reference_number,
                    p.created_at,
                    p.updated_at
                FROM payments p
                LEFT JOIN users u ON p.user_id = u.id
                ORDER BY p.created_at DESC
                LIMIT 100";

        try {
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error fetching all payments: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Create invoice manually (admin function)
     */
    public function createInvoice(array $data): ?array
    {
        try {
            $this->db->beginTransaction();

            // Map bill type to category
            $categoryMap = [
                'water' => 'water',
                'electric' => 'electricity'
            ];

            $category = $categoryMap[$data['bill_type']] ?? 'water';

            // Get bill type display name
            $billTypeNames = [
                'water' => 'Water Bill',
                'electric' => 'Electric Bill'
            ];
            $billTypeName = $billTypeNames[$data['bill_type']] ?? 'Water Bill';

            // Create payment record
            $sql = "INSERT INTO payments (
                user_id, 
                bill_month, 
                amount, 
                status, 
                due_date,
                created_at,
                updated_at
            ) VALUES (
                :user_id,
                :bill_month,
                :amount,
                :status,
                :due_date,
                NOW(),
                NOW()
            ) RETURNING *";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'user_id' => $data['user_id'],
                'bill_month' => date('F Y'),
                'amount' => $data['amount'],
                'status' => $data['status'] ?? 'unpaid',
                'due_date' => $data['due_date']
            ]);

            $payment = $stmt->fetch(\PDO::FETCH_ASSOC);

            // Add payment item
            $itemSql = "INSERT INTO payment_items (
                payment_id,
                description,
                amount,
                category
            ) VALUES (
                :payment_id,
                :description,
                :amount,
                :category
            )";

            $stmt = $this->db->prepare($itemSql);
            $stmt->execute([
                'payment_id' => $payment['id'],
                'description' => $data['description'] ?: ($billTypeName . ' - ' . date('F Y')),
                'amount' => $data['amount'],
                'category' => $category
            ]);

            // Create notification for user
            $notificationSql = "INSERT INTO notifications (
                user_id,
                title,
                message,
                type,
                channel,
                is_read,
                created_at
            ) VALUES (
                :user_id,
                :title,
                :message,
                :type,
                :channel,
                false,
                NOW()
            )";

            $stmt = $this->db->prepare($notificationSql);
            $stmt->execute([
                'user_id' => $data['user_id'],
                'title' => 'New Bill Generated',
                'message' => "A new {$billTypeName} of ₱" . number_format($data['amount'], 2) . " has been generated. Due date: " . date('M d, Y', strtotime($data['due_date'])),
                'type' => 'payment',
                'channel' => 'in_app'
            ]);

            $this->db->commit();

            return $payment;
        } catch (\PDOException $e) {
            $this->db->rollBack();
            error_log("Error creating invoice: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Approve pending transaction
     */
    public function approveTransaction(string $transactionId, string $adminId): bool
    {
        try {
            $this->db->beginTransaction();

            // Get transaction details first
            $getTxnSql = "SELECT t.*, p.bill_month, t.user_id, t.amount 
                         FROM transactions t
                         LEFT JOIN payments p ON t.payment_id = p.id
                         WHERE t.id = :transaction_id";
            $stmt = $this->db->prepare($getTxnSql);
            $stmt->execute(['transaction_id' => $transactionId]);
            $transaction = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$transaction) {
                $this->db->rollBack();
                return false;
            }

            // Update transaction status
            $sql = "UPDATE transactions
                    SET status = 'completed'
                    WHERE id = :transaction_id
                    AND status = 'pending'
                    RETURNING payment_id";

            $stmt = $this->db->prepare($sql);
            $stmt->execute(['transaction_id' => $transactionId]);
            
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);

            if ($result && $result['payment_id']) {
                // Update related payment status
                $paymentSql = "UPDATE payments
                              SET status = 'paid',
                                  paid_date = NOW(),
                                  updated_at = NOW()
                              WHERE id = :payment_id";
                
                $stmt = $this->db->prepare($paymentSql);
                $stmt->execute(['payment_id' => $result['payment_id']]);
            }

            // Create notification for user
            $notificationSql = "INSERT INTO notifications (
                user_id,
                title,
                message,
                type,
                channel,
                is_read,
                created_at
            ) VALUES (
                :user_id,
                :title,
                :message,
                :type,
                :channel,
                false,
                NOW()
            )";

            $stmt = $this->db->prepare($notificationSql);
            $stmt->execute([
                'user_id' => $transaction['user_id'],
                'title' => 'Payment Approved',
                'message' => "Your payment of ₱" . number_format($transaction['amount'], 2) . " has been approved and processed successfully.",
                'type' => 'payment',
                'channel' => 'in_app'
            ]);

            $this->db->commit();
            return true;
        } catch (\PDOException $e) {
            $this->db->rollBack();
            error_log("Error approving transaction: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Reject pending transaction
     */
    public function rejectTransaction(string $transactionId, string $adminId, string $reason): bool
    {
        try {
            $this->db->beginTransaction();

            // Get transaction details first
            $getTxnSql = "SELECT user_id, amount FROM transactions WHERE id = :transaction_id";
            $stmt = $this->db->prepare($getTxnSql);
            $stmt->execute(['transaction_id' => $transactionId]);
            $transaction = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$transaction) {
                $this->db->rollBack();
                return false;
            }

            // Update transaction status
            $sql = "UPDATE transactions
                    SET status = 'failed',
                        notes = :reason
                    WHERE id = :transaction_id
                    AND status = 'pending'";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'transaction_id' => $transactionId,
                'reason' => $reason
            ]);

            if ($stmt->rowCount() > 0) {
                // Create notification for user
                $notificationSql = "INSERT INTO notifications (
                    user_id,
                    title,
                    message,
                    type,
                    channel,
                    is_read,
                    created_at
                ) VALUES (
                    :user_id,
                    :title,
                    :message,
                    :type,
                    :channel,
                    false,
                    NOW()
                )";

                $stmt = $this->db->prepare($notificationSql);
                $stmt->execute([
                    'user_id' => $transaction['user_id'],
                    'title' => 'Payment Rejected',
                    'message' => "Your payment of ₱" . number_format($transaction['amount'], 2) . " has been rejected. Reason: {$reason}",
                    'type' => 'payment',
                    'channel' => 'in_app'
                ]);

                $this->db->commit();
                return true;
            }

            $this->db->rollBack();
            return false;
        } catch (\PDOException $e) {
            $this->db->rollBack();
            error_log("Error rejecting transaction: " . $e->getMessage());
            return false;
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
