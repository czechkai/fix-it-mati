<?php
/**
 * Adapter Pattern - Target Interface
 * 
 * Common interface for payment processing
 */

namespace FixItMati\DesignPatterns\Structural\Adapter;

interface PaymentGatewayInterface
{
    /**
     * Process a payment
     * 
     * @param float $amount Amount in PHP
     * @param array $paymentDetails Payment details (card, account, etc.)
     * @return array ['success' => bool, 'transaction_id' => string, 'message' => string]
     */
    public function processPayment(float $amount, array $paymentDetails): array;
    
    /**
     * Refund a payment
     * 
     * @param string $transactionId Original transaction ID
     * @param float $amount Amount to refund
     * @return array ['success' => bool, 'refund_id' => string, 'message' => string]
     */
    public function refundPayment(string $transactionId, float $amount): array;
    
    /**
     * Get transaction status
     * 
     * @param string $transactionId Transaction ID
     * @return array ['status' => string, 'details' => array]
     */
    public function getTransactionStatus(string $transactionId): array;
    
    /**
     * Get gateway name
     * 
     * @return string
     */
    public function getGatewayName(): string;
}
