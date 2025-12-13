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

        $gateway = $request->param('gateway');
        $paymentId = $request->param('payment_id');
        $amount = (float) $request->param('amount');
        
        if (!$gateway || !$paymentId || !$amount) {
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
            // Generate reference number
            $referenceNumber = 'TRX-' . strtoupper(substr(uniqid(), -8));
            
            // Process payment in database
            $result = $this->paymentModel->processPayment($paymentId, $userId, [
                'payment_method' => $gateway,
                'reference_number' => $referenceNumber,
                'notes' => "Payment via {$gateway}"
            ]);
            
            if ($result) {
                return Response::json([
                    'success' => true,
                    'message' => 'Payment processed successfully',
                    'data' => [
                        'payment' => $result['payment'],
                        'transaction' => $result['transaction'],
                        'reference_number' => $referenceNumber
                    ]
                ]);
            } else {
                return Response::json([
                    'success' => false,
                    'message' => 'Payment processing failed'
                ], 400);
            }
            
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
                        'id' => 'maya',
                        'name' => 'Maya',
                        'description' => 'Maya (PayMaya) mobile wallet',
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
}
