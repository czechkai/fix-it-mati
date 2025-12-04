<?php
/**
 * Adapter Pattern - GCash Adapter
 * 
 * Adapts GCash API to our PaymentGatewayInterface
 */

namespace FixItMati\DesignPatterns\Structural\Adapter;

class GCashPaymentAdapter implements PaymentGatewayInterface
{
    private array $config;
    
    public function __construct(array $config = [])
    {
        $this->config = $config;
        // In production: Initialize GCash API client
    }
    
    /**
     * Process payment via GCash
     */
    public function processPayment(float $amount, array $paymentDetails): array
    {
        try {
            // GCash uses PHP amount directly
            // In production: Call GCash API
            // POST to https://api.gcash.com/v1/payments
            
            $transactionId = 'GCASH-' . uniqid();
            
            return [
                'success' => true,
                'transaction_id' => $transactionId,
                'message' => 'Payment processed successfully via GCash',
                'amount' => $amount,
                'currency' => 'PHP',
                'gateway' => 'gcash',
                'mobile_number' => $paymentDetails['mobile_number'] ?? null
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'transaction_id' => null,
                'message' => 'GCash payment failed: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Refund payment via GCash
     */
    public function refundPayment(string $transactionId, float $amount): array
    {
        try {
            // In production: Call GCash refund API
            
            $refundId = 'GCASH-REF-' . uniqid();
            
            return [
                'success' => true,
                'refund_id' => $refundId,
                'message' => 'Refund processed successfully via GCash',
                'amount' => $amount,
                'original_transaction' => $transactionId
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'refund_id' => null,
                'message' => 'GCash refund failed: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Get transaction status from GCash
     */
    public function getTransactionStatus(string $transactionId): array
    {
        try {
            // In production: Query GCash API
            // GET https://api.gcash.com/v1/payments/{transactionId}
            
            return [
                'success' => true,
                'status' => 'success',
                'details' => [
                    'transaction_id' => $transactionId,
                    'gateway' => 'gcash',
                    'payment_status' => 'completed',
                    'refunded' => false
                ]
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'status' => 'error',
                'details' => ['message' => $e->getMessage()]
            ];
        }
    }
    
    /**
     * Get gateway name
     */
    public function getGatewayName(): string
    {
        return 'GCash';
    }
}
