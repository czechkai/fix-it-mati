<?php
/**
 * Adapter Pattern - PayPal Adapter
 * 
 * Adapts PayPal API to our PaymentGatewayInterface
 */

namespace FixItMati\DesignPatterns\Structural\Adapter;

class PayPalPaymentAdapter implements PaymentGatewayInterface
{
    private array $config;
    
    public function __construct(array $config = [])
    {
        $this->config = $config;
        // In production: Initialize PayPal SDK
    }
    
    /**
     * Process payment via PayPal
     */
    public function processPayment(float $amount, array $paymentDetails): array
    {
        try {
            // PayPal has different API structure
            // In production: Use PayPal REST API
            // $payment = new \PayPal\Api\Payment();
            // $payment->setIntent('sale')
            //         ->setPayer($payer)
            //         ->setTransactions([$transaction]);
            
            $transactionId = 'PAYPAL-' . uniqid();
            
            return [
                'success' => true,
                'transaction_id' => $transactionId,
                'message' => 'Payment processed successfully via PayPal',
                'amount' => $amount,
                'currency' => 'PHP',
                'gateway' => 'paypal'
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'transaction_id' => null,
                'message' => 'PayPal payment failed: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Refund payment via PayPal
     */
    public function refundPayment(string $transactionId, float $amount): array
    {
        try {
            // In production: Use PayPal REST API
            // $sale = \PayPal\Api\Sale::get($transactionId, $apiContext);
            // $refund = new \PayPal\Api\Refund();
            // $refund->setAmount($amt);
            
            $refundId = 'REFUND-' . uniqid();
            
            return [
                'success' => true,
                'refund_id' => $refundId,
                'message' => 'Refund processed successfully via PayPal',
                'amount' => $amount,
                'original_transaction' => $transactionId
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'refund_id' => null,
                'message' => 'PayPal refund failed: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Get transaction status from PayPal
     */
    public function getTransactionStatus(string $transactionId): array
    {
        try {
            // In production: Query PayPal API
            // $payment = \PayPal\Api\Payment::get($transactionId, $apiContext);
            
            return [
                'success' => true,
                'status' => 'approved',
                'details' => [
                    'transaction_id' => $transactionId,
                    'gateway' => 'paypal',
                    'state' => 'approved',
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
        return 'PayPal';
    }
}
