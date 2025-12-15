<?php
/**
 * Payment Controller
 * 
 * Handles payment processing through various gateways (Adapter Pattern)
 */

namespace FixItMati\Controllers;

use FixItMati\Core\Request;
use FixItMati\Core\Response;
use FixItMati\Models\Payment;
use FixItMati\DesignPatterns\Structural\Adapter\PaymentAdapterFactory;
use FixItMati\Services\ReceiptService;

class PaymentController
{
    private Payment $paymentModel;
    
    public function __construct()
    {
        $this->paymentModel = new Payment();
    }
    
    /**
     * Get current bills for authenticated user
     */
    public function getCurrentBills(Request $request): Response
    {
        $userId = $request->user()['id'] ?? null;
        
        if (!$userId) {
            return Response::json([
                'success' => false,
                'message' => 'User not authenticated'
            ], 401);
        }
        
        try {
            $bills = $this->paymentModel->getCurrentBills($userId);
            $totalDue = $this->paymentModel->getTotalDue($userId);
            
            // Process bills data for frontend
            $processedBills = [];
            foreach ($bills as $bill) {
                $items = json_decode($bill['items'], true);
                
                $processedBills[] = [
                    'id' => $bill['id'],
                    'bill_month' => $bill['bill_month'],
                    'amount' => (float) $bill['amount'],
                    'status' => $bill['status'],
                    'due_date' => $bill['due_date'],
                    'items' => $items
                ];
            }
            
            return Response::json([
                'success' => true,
                'data' => [
                    'bills' => $processedBills,
                    'total_due' => $totalDue,
                    'count' => count($processedBills)
                ]
            ]);
            
        } catch (\Exception $e) {
            error_log("Error fetching current bills: " . $e->getMessage());
            return Response::json([
                'success' => false,
                'message' => 'Failed to retrieve bills'
            ], 500);
        }
    }

    /**
     * Process payment through specified gateway
     */
    public function processPayment(Request $request): Response
    {
        $userId = $request->user()['id'] ?? null;
        
        if (!$userId) {
            return Response::json([
                'success' => false,
                'message' => 'User not authenticated'
            ], 401);
        }

        // Get data from JSON body (use input() not param())
        $gateway = $request->input('gateway');
        $paymentId = $request->input('payment_id');
        $amount = (float) $request->input('amount');
        
        if (!$gateway || !$paymentId || !$amount) {
            error_log("Payment validation failed - Gateway: $gateway, PaymentID: $paymentId, Amount: $amount");
            return Response::json([
                'success' => false,
                'message' => 'Gateway, payment ID, and amount are required'
            ], 400);
        }
        
        // Validate payment belongs to user
        $payment = $this->paymentModel->find($paymentId);
        if (!$payment || $payment['user_id'] !== $userId) {
            return Response::json([
                'success' => false,
                'message' => 'Payment not found or unauthorized'
            ], 404);
        }

        try {
            // Load payment gateway configuration
            $paymentConfig = require __DIR__ . '/../config/payment.php';
            $gatewayConfig = $paymentConfig[$gateway] ?? [];
            
            if (empty($gatewayConfig) || !($gatewayConfig['enabled'] ?? false)) {
                return Response::json([
                    'success' => false,
                    'message' => "Payment gateway '$gateway' is not enabled"
                ], 400);
            }
            
            // Create payment adapter
            try {
                $adapter = PaymentAdapterFactory::createGateway($gateway, $gatewayConfig);
            } catch (\Exception $e) {
                error_log("Failed to create payment adapter: " . $e->getMessage());
                return Response::json([
                    'success' => false,
                    'message' => 'Payment gateway initialization failed'
                ], 500);
            }
            
            // Prepare payment details
            $billMonth = $payment['bill_month'] ?? date('F Y');
            $paymentDetails = [
                'description' => "FixItMati Bill Payment - {$billMonth}",
                'return_url' => $request->input('return_url') ?? 
                    'http://localhost:8000/api/payments/' . $gateway . '/return',
                'cancel_url' => $request->input('cancel_url') ?? 
                    'http://localhost:8000/api/payments/' . $gateway . '/cancel',
                'webhook_url' => 'http://localhost:8000/api/webhooks/' . $gateway,
                'mobile_number' => $request->input('mobile_number')
            ];
            
            // Process payment through adapter
            $result = $adapter->processPayment($amount, $paymentDetails);
            
            if (!$result['success']) {
                return Response::json([
                    'success' => false,
                    'message' => $result['message'] ?? 'Payment processing failed'
                ], 400);
            }
            
            // Store transaction reference in database
            $referenceNumber = $result['transaction_id'];
            
            // Update payment record with gateway info
            $this->paymentModel->updatePaymentStatus($paymentId, 'pending', [
                'payment_method' => $gateway,
                'reference_number' => $referenceNumber,
                'gateway_transaction_id' => $referenceNumber
            ]);
            
            // Return payment URL for redirect
            return Response::json([
                'success' => true,
                'message' => 'Payment initiated successfully',
                'data' => [
                    'payment_url' => $result['payment_url'],
                    'transaction_id' => $referenceNumber,
                    'gateway' => $gateway,
                    'amount' => $amount
                ]
            ]);
            
        } catch (\Exception $e) {
            error_log("Payment processing error: " . $e->getMessage());
            return Response::json([
                'success' => false,
                'message' => 'Payment processing failed: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Refund payment
     */
    public function refundPayment(Request $request): Response
    {
        $gateway = $request->param('gateway');
        $transactionId = $request->param('transaction_id');
        $amount = (float) $request->param('amount');
        
        if (!$gateway || !$transactionId || !$amount) {
            return Response::json([
                'success' => false,
                'message' => 'Gateway, transaction ID, and amount are required'
            ], 400);
        }
        
        try {
            // Get gateway config
            $config = $this->getGatewayConfig($gateway);
            $paymentGateway = PaymentAdapterFactory::createGateway($gateway, $config);
            $result = $paymentGateway->refundPayment($transactionId, $amount);
            
            if ($result['success']) {
                return Response::json([
                    'success' => true,
                    'message' => 'Refund processed successfully',
                    'data' => $result
                ]);
            } else {
                return Response::json([
                    'success' => false,
                    'message' => $result['message']
                ], 400);
            }
            
        } catch (\Exception $e) {
            return Response::json([
                'success' => false,
                'message' => 'Refund processing failed: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get transaction status
     */
    public function getTransactionStatus(Request $request): Response
    {
        $gateway = $request->param('gateway');
        $transactionId = $request->param('transaction_id');
        
        if (!$gateway || !$transactionId) {
            return Response::json([
                'success' => false,
                'message' => 'Gateway and transaction ID are required'
            ], 400);
        }
        
        try {
            // Get gateway config
            $config = $this->getGatewayConfig($gateway);
            $paymentGateway = PaymentAdapterFactory::createGateway($gateway, $config);
            $status = $paymentGateway->getTransactionStatus($transactionId);
            
            return Response::json([
                'success' => true,
                'data' => $status
            ]);
            
        } catch (\Exception $e) {
            return Response::json([
                'success' => false,
                'message' => 'Failed to get transaction status: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get payment history for current user
     */
    public function getHistory(Request $request): Response
    {
        $userId = $request->user()['id'] ?? null;
        
        if (!$userId) {
            return Response::error('User not authenticated', 401);
        }
        
        try {
            $type = $request->query('type');
            $transactions = $this->paymentModel->getTransactionHistory($userId, $type);
            
            // Process transaction data for frontend
            $processedHistory = [];
            foreach ($transactions as $transaction) {
                $processedHistory[] = [
                    'id' => $transaction['id'],
                    'reference_number' => $transaction['reference_number'],
                    'amount' => (float) $transaction['amount'],
                    'type' => $transaction['type'],
                    'biller' => $transaction['biller'],
                    'status' => ucfirst($transaction['status']),
                    'payment_method' => $transaction['payment_method'] ?? 'N/A',
                    'billing_period' => $transaction['billing_period'] ?? 'N/A',
                    'transaction_date' => $transaction['transaction_date'],
                    'gateway' => $transaction['gateway'] ?? null,
                    'gateway_reference' => $transaction['gateway_reference'] ?? null
                ];
            }
            
            return Response::success($processedHistory);
            
        } catch (\Exception $e) {
            error_log("Error fetching payment history: " . $e->getMessage());
            return Response::json([
                'success' => false,
                'message' => 'Failed to retrieve payment history'
            ], 500);
        }
    }

    /**
     * Get supported payment gateways
     */
    public function getSupportedGateways(Request $request): Response
    {
        return Response::json([
            'success' => true,
            'data' => [
                'gateways' => [
                    [
                        'id' => 'gcash',
                        'name' => 'GCash',
                        'description' => 'GCash mobile wallet',
                        'supported' => true
                    ],
                    [
                        'id' => 'paypal',
                        'name' => 'PayPal',
                        'description' => 'PayPal digital payment',
                        'supported' => true
                    ],
                    [
                        'id' => 'card',
                        'name' => 'Card',
                        'description' => 'Credit/Debit card payments',
                        'supported' => true
                    ]
                ]
            ]
        ]);
    }
    
    /**
     * Get transaction history for authenticated user
     * GET /api/payments/history
     */
    public function getTransactionHistory(Request $request): Response
    {
        $userId = $request->user()['id'] ?? null;
        
        if (!$userId) {
            return Response::error('User not authenticated', 401);
        }

        try {
            $type = $request->query('type');
            $transactions = $this->paymentModel->getTransactionHistory($userId, $type);

            return Response::success($transactions);
        } catch (\Exception $e) {
            error_log("Error fetching transaction history: " . $e->getMessage());
            return Response::error('Failed to retrieve transaction history', 500);
        }
    }

    /**
     * Get gateway configuration by gateway type
     */
    private function getGatewayConfig(string $gateway): array
    {
        $configs = [
            'stripe' => [
                'api_key' => $_ENV['STRIPE_API_KEY'] ?? 'sk_test_...',
                'webhook_secret' => $_ENV['STRIPE_WEBHOOK_SECRET'] ?? ''
            ],
            'paypal' => [
                'client_id' => $_ENV['PAYPAL_CLIENT_ID'] ?? '',
                'client_secret' => $_ENV['PAYPAL_CLIENT_SECRET'] ?? '',
                'mode' => $_ENV['PAYPAL_MODE'] ?? 'sandbox'
            ],
            'gcash' => [
                'api_key' => $_ENV['GCASH_API_KEY'] ?? '',
                'merchant_id' => $_ENV['GCASH_MERCHANT_ID'] ?? ''
            ],
            'paymongo' => [
                'api_key' => $_ENV['PAYMONGO_API_KEY'] ?? '',
                'public_key' => $_ENV['PAYMONGO_PUBLIC_KEY'] ?? ''
            ]
        ];
        
        return $configs[$gateway] ?? [];
    }

    // ============================================
    // ADMIN BILLING ENDPOINTS
    // ============================================

    /**
     * Get all transactions (Admin only)
     * GET /api/admin/transactions
     */
    public function getAllTransactions(Request $request): Response
    {
        $user = $request->user();
        
        if (!$user || !in_array($user['role'], ['admin', 'staff'])) {
            return Response::error('Unauthorized access', 403);
        }

        try {
            $status = $request->query('status');
            $transactions = $this->paymentModel->getAllTransactions($status);

            return Response::success($transactions);
        } catch (\Exception $e) {
            error_log("Error fetching all transactions: " . $e->getMessage());
            return Response::error('Failed to retrieve transactions', 500);
        }
    }

    /**
     * Get billing statistics (Admin only)
     * GET /api/admin/billing/stats
     */
    public function getStats(Request $request): Response
    {
        $user = $request->user();
        
        if (!$user || !in_array($user['role'], ['admin', 'staff'])) {
            return Response::error('Unauthorized access', 403);
        }

        try {
            $stats = $this->paymentModel->getBillingStats();

            return Response::success($stats);
        } catch (\Exception $e) {
            error_log("Error fetching billing stats: " . $e->getMessage());
            return Response::error('Failed to retrieve statistics', 500);
        }
    }

    /**
     * Get all payments/invoices (Admin only)
     * GET /api/admin/billing/all-payments
     */
    public function getAllPayments(Request $request): Response
    {
        $user = $request->user();
        
        if (!$user || !in_array($user['role'], ['admin', 'staff'])) {
            return Response::error('Unauthorized access', 403);
        }

        try {
            $payments = $this->paymentModel->getAllPaymentsAdmin();
            return Response::success($payments);
        } catch (\Exception $e) {
            error_log("Error fetching all payments: " . $e->getMessage());
            return Response::error('Failed to retrieve payments', 500);
        }
    }

    /**
     * Create invoice manually (Admin only)
     * POST /api/admin/billing/create-invoice
     */
    public function createInvoice(Request $request): Response
    {
        $user = $request->user();
        
        if (!$user || !in_array($user['role'], ['admin', 'staff'])) {
            return Response::error('Unauthorized access', 403);
        }

        try {
            $data = [
                'user_id' => $request->input('user_id'),
                'bill_type' => $request->input('bill_type'),
                'amount' => (float) $request->input('amount'),
                'due_date' => $request->input('due_date'),
                'description' => $request->input('description'),
                'status' => 'unpaid',
                'created_by' => $user['id']
            ];

            // Validate required fields
            if (empty($data['user_id'])) {
                return Response::error('User ID is required', 400);
            }
            if (empty($data['bill_type'])) {
                return Response::error('Bill type is required', 400);
            }
            if (empty($data['amount']) || $data['amount'] <= 0) {
                return Response::error('Valid amount is required', 400);
            }
            if (empty($data['due_date'])) {
                return Response::error('Due date is required', 400);
            }

            $invoice = $this->paymentModel->createInvoice($data);

            if ($invoice) {
                return Response::success([
                    'message' => 'Invoice created successfully and user has been notified',
                    'invoice' => $invoice
                ]);
            } else {
                return Response::error('Failed to create invoice', 500);
            }
        } catch (\Exception $e) {
            error_log("Error creating invoice: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            return Response::error('Failed to create invoice: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Approve transaction (Admin only)
     * POST /api/admin/transactions/{id}/approve
     */
    public function approveTransaction(Request $request, array $params): Response
    {
        $user = $request->user();
        
        if (!$user || !in_array($user['role'], ['admin', 'staff'])) {
            return Response::error('Unauthorized access', 403);
        }

        try {
            $transactionId = $params['id'] ?? null;
            
            if (!$transactionId) {
                return Response::error('Transaction ID required', 400);
            }

            $result = $this->paymentModel->approveTransaction($transactionId, $user['id']);

            if ($result) {
                // TODO: Send notification to user
                return Response::success(['message' => 'Transaction approved successfully']);
            } else {
                return Response::error('Failed to approve transaction', 500);
            }
        } catch (\Exception $e) {
            error_log("Error approving transaction: " . $e->getMessage());
            return Response::error('Failed to approve transaction', 500);
        }
    }

    /**
     * Reject transaction (Admin only)
     * POST /api/admin/transactions/{id}/reject
     */
    public function rejectTransaction(Request $request, array $params): Response
    {
        $user = $request->user();
        
        if (!$user || !in_array($user['role'], ['admin', 'staff'])) {
            return Response::error('Unauthorized access', 403);
        }

        try {
            $transactionId = $params['id'] ?? null;
            $reason = $request->param('reason') ?? 'Transaction rejected by admin';
            
            if (!$transactionId) {
                return Response::error('Transaction ID required', 400);
            }

            $result = $this->paymentModel->rejectTransaction($transactionId, $user['id'], $reason);

            if ($result) {
                // TODO: Send notification to user
                return Response::success(['message' => 'Transaction rejected successfully']);
            } else {
                return Response::error('Failed to reject transaction', 500);
            }
        } catch (\Exception $e) {
            error_log("Error rejecting transaction: " . $e->getMessage());
            return Response::error('Failed to reject transaction', 500);
        }
    }

    /**
     * Get all users for invoice creation (Admin only)
     * GET /api/admin/users
     */
    public function getAllUsers(Request $request): Response
    {
        $user = $request->user();
        
        if (!$user || !in_array($user['role'], ['admin', 'staff'])) {
            return Response::error('Unauthorized access', 403);
        }

        try {
            require_once __DIR__ . '/../Models/User.php';
            $userModel = new \FixItMati\Models\User();
            $users = $userModel->getAllCitizens();

            return Response::success($users);
        } catch (\Exception $e) {
            error_log("Error fetching users: " . $e->getMessage());
            return Response::error('Failed to retrieve users', 500);
        }
    }

    /**
     * Export transactions as CSV (Admin only)
     * GET /api/admin/transactions/export
     */
    public function exportTransactions(Request $request): Response
    {
        $user = $request->user();
        
        if (!$user || !in_array($user['role'], ['admin', 'staff'])) {
            return Response::error('Unauthorized access', 403);
        }

        try {
            $transactions = $this->paymentModel->getAllTransactions();

            // Generate CSV
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="transactions-' . date('Y-m-d') . '.csv"');
            
            $output = fopen('php://output', 'w');
            
            // Add headers
            fputcsv($output, ['Transaction ID', 'User', 'Type', 'Amount', 'Method', 'Status', 'Date']);
            
            // Add data
            foreach ($transactions as $transaction) {
                fputcsv($output, [
                    $transaction['reference_number'] ?? $transaction['id'],
                    $transaction['user_name'] ?? 'Unknown',
                    $transaction['payment_type'] ?? 'General',
                    $transaction['amount'],
                    $transaction['payment_method'] ?? 'N/A',
                    $transaction['status'],
                    $transaction['created_at']
                ]);
            }
            
            fclose($output);
            exit;
        } catch (\Exception $e) {
            error_log("Error exporting transactions: " . $e->getMessage());
            return Response::error('Failed to export transactions', 500);
        }
    }
    
    /**
     * Handle PayPal payment return
     * GET /api/payments/paypal/return
     */
    public function handlePayPalReturn(Request $request): Response
    {
        $token = $request->query('token');
        $payerId = $request->query('PayerID');
        
        if (!$token) {
            header('Location: /pages/user/payments.php?error=missing_token');
            exit;
        }
        
        try {
            // Capture the payment
            $paymentConfig = require __DIR__ . '/../config/payment.php';
            $config = $paymentConfig['paypal'] ?? [];
            
            $adapter = PaymentAdapterFactory::createGateway('paypal', $config);
            $accessToken = $this->getPayPalAccessToken($config);
            
            // Capture order
            $apiUrl = $config['mode'] === 'live' 
                ? 'https://api-m.paypal.com' 
                : 'https://api-m.sandbox.paypal.com';
            
            $ch = curl_init("$apiUrl/v2/checkout/orders/$token/capture");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $accessToken
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            $result = json_decode($response, true);
            
            if ($httpCode === 201 && $result['status'] === 'COMPLETED') {
                // Payment successful - update database
                // TODO: Update payment status in database to 'paid'
                
                // Generate and send receipt
                try {
                    $receiptService = new ReceiptService();
                    $receiptService->generatePDFReceipt($token);
                    
                    // Get user email from session/token and send receipt
                    // $receiptService->sendReceiptEmail($token, $userEmail);
                } catch (\Exception $e) {
                    error_log("Receipt generation error: " . $e->getMessage());
                }
                
                header('Location: /pages/user/payment-success.php?ref=' . $token);
                exit;
            } else {
                header('Location: /pages/user/payments.php?error=payment_failed');
                exit;
            }
        } catch (\Exception $e) {
            error_log("PayPal return error: " . $e->getMessage());
            header('Location: /pages/user/payments.php?error=' . urlencode($e->getMessage()));
            exit;
        }
    }
    
    /**
     * Handle PayPal payment cancellation
     * GET /api/payments/paypal/cancel
     */
    public function handlePayPalCancel(Request $request): Response
    {
        header('Location: /pages/user/payments.php?cancelled=1');
        exit;
    }
    
    /**
     * Handle GCash payment return
     * GET /api/payments/gcash/return
     */
    public function handleGCashReturn(Request $request): Response
    {
        $reference = $request->query('ref');
        $status = $request->query('status');
        
        if ($status === 'success') {
            header('Location: /pages/user/payment-success.php?ref=' . $reference);
        } else {
            header('Location: /pages/user/payments.php?error=payment_failed');
        }
        exit;
    }
    
    /**
     * Handle webhook from PayPal
     * POST /api/webhooks/paypal
     */
    public function handlePayPalWebhook(Request $request): Response
    {
        $payload = $request->getBody();
        $data = json_decode($payload, true);
        
        error_log("PayPal webhook received: " . $payload);
        
        // Verify webhook signature here
        
        if ($data['event_type'] === 'PAYMENT.CAPTURE.COMPLETED') {
            // Update payment status in database
            $transactionId = $data['resource']['id'];
            // Update database...
        }
        
        return Response::json(['success' => true]);
    }
    
    /**
     * Handle webhook from GCash
     * POST /api/webhooks/gcash
     */
    public function handleGCashWebhook(Request $request): Response
    {
        $payload = $request->getBody();
        $data = json_decode($payload, true);
        
        error_log("GCash webhook received: " . $payload);
        
        // Verify webhook signature here
        
        return Response::json(['success' => true]);
    }
    
    /**
     * Download receipt for a transaction
     * GET /api/payments/receipt/{transactionId}
     */
    public function downloadReceipt(Request $request): Response
    {
        $transactionId = $request->param('transactionId');
        
        if (!$transactionId) {
            return Response::badRequest('Transaction ID is required');
        }
        
        try {
            $receiptService = new ReceiptService();
            $html = $receiptService->generateHTMLReceipt($transactionId);
            
            // Set headers for download
            header('Content-Type: text/html; charset=UTF-8');
            header('Content-Disposition: inline; filename="receipt_' . $transactionId . '.html"');
            echo $html;
            exit;
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 404);
        }
    }
    
    /**
     * Send receipt via email
     * POST /api/payments/receipt/send
     */
    public function sendReceipt(Request $request): Response
    {
        $transactionId = $request->input('transaction_id');
        $email = $request->input('email');
        
        if (!$transactionId || !$email) {
            return Response::badRequest('Transaction ID and email are required');
        }
        
        try {
            $receiptService = new ReceiptService();
            $sent = $receiptService->sendReceiptEmail($transactionId, $email);
            
            if ($sent) {
                return Response::success(['message' => 'Receipt sent successfully']);
            } else {
                return Response::error('Failed to send receipt', 500);
            }
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }
    
    /**
     * Get PayPal access token
     */
    private function getPayPalAccessToken(array $config): ?string
    {
        $clientId = $config['client_id'] ?? '';
        $clientSecret = $config['client_secret'] ?? '';
        
        $apiUrl = $config['mode'] === 'live' 
            ? 'https://api-m.paypal.com' 
            : 'https://api-m.sandbox.paypal.com';
        
        $ch = curl_init("$apiUrl/v1/oauth2/token");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, 'grant_type=client_credentials');
        curl_setopt($ch, CURLOPT_USERPWD, "$clientId:$clientSecret");
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        $result = json_decode($response, true);
        return $result['access_token'] ?? null;
    }
}
