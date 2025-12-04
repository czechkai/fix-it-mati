<?php
/**
 * Payment Controller
 * 
 * Handles payment processing through various gateways (Adapter Pattern)
 */

namespace FixItMati\Controllers;

use FixItMati\Core\Request;
use FixItMati\Core\Response;
use FixItMati\DesignPatterns\Structural\Adapter\PaymentAdapterFactory;

class PaymentController
{
    private PaymentAdapterFactory $factory;
    
    public function __construct()
    {
        // Configure payment gateways
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
            ]
        ];
        
        $this->factory = new PaymentAdapterFactory($configs);
    }
    
    /**
     * Process payment through specified gateway
     */
    public function processPayment(Request $request): Response
    {
        $gateway = $request->param('gateway');
        $amount = (float) $request->param('amount');
        $paymentDetails = $request->param('payment_details', []);
        
        if (!$gateway || !$amount) {
            return Response::json([
                'success' => false,
                'message' => 'Gateway and amount are required'
            ], 400);
        }
        
        try {
            // Check if gateway is supported
            if (!$this->factory->isGatewaySupported($gateway)) {
                return Response::json([
                    'success' => false,
                    'message' => 'Unsupported payment gateway',
                    'supported_gateways' => $this->factory->getSupportedGateways()
                ], 400);
            }
            
            // Get payment adapter
            $paymentGateway = $this->factory->createGateway($gateway);
            
            // Process payment
            $result = $paymentGateway->processPayment($amount, $paymentDetails);
            
            if ($result['success']) {
                return Response::json([
                    'success' => true,
                    'message' => 'Payment processed successfully',
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
            $paymentGateway = $this->factory->createGateway($gateway);
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
            $paymentGateway = $this->factory->createGateway($gateway);
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
     * Get supported payment gateways
     */
    public function getSupportedGateways(Request $request): Response
    {
        return Response::json([
            'success' => true,
            'data' => [
                'gateways' => [
                    [
                        'id' => 'stripe',
                        'name' => 'Stripe',
                        'description' => 'Credit/Debit card payments',
                        'supported' => true
                    ],
                    [
                        'id' => 'paypal',
                        'name' => 'PayPal',
                        'description' => 'PayPal account payments',
                        'supported' => true
                    ],
                    [
                        'id' => 'gcash',
                        'name' => 'GCash',
                        'description' => 'GCash mobile wallet',
                        'supported' => true
                    ]
                ]
            ]
        ]);
    }
}
