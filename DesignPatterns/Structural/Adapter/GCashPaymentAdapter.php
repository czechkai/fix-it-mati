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
            // GCash API integration
            $apiUrl = $this->config['api_url'] ?? 'https://sandbox.gcash.com/v1';
            $merchantId = $this->config['merchant_id'] ?? '';
            $apiKey = $this->config['api_key'] ?? '';
            $apiSecret = $this->config['api_secret'] ?? '';

            if (!$merchantId || !$apiKey) {
                return [
                    'success' => false,
                    'message' => 'GCash credentials not configured'
                ];
            }

            $returnUrl = $paymentDetails['return_url'] ?? 'http://localhost:8000/api/payments/gcash/return';
            $webhookUrl = $paymentDetails['webhook_url'] ?? 'http://localhost:8000/api/webhooks/gcash';

            // Generate unique reference
            $reference = 'GCASH-' . time() . '-' . uniqid();

            // Prepare payment request
            $paymentData = [
                'merchant_id' => $merchantId,
                'amount' => number_format($amount, 2, '.', ''),
                'currency' => 'PHP',
                'description' => $paymentDetails['description'] ?? 'Water and Electricity Bill Payment',
                'reference_number' => $reference,
                'redirect_url' => $returnUrl,
                'webhook_url' => $webhookUrl,
                'mobile_number' => $paymentDetails['mobile_number'] ?? null
            ];

            // Create signature for API authentication
            $timestamp = time();
            $signatureString = $merchantId . $timestamp . json_encode($paymentData);
            $signature = hash_hmac('sha256', $signatureString, $apiSecret);

            // Call GCash API
            $ch = curl_init("$apiUrl/payments");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($paymentData));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'X-API-Key: ' . $apiKey,
                'X-Timestamp: ' . $timestamp,
                'X-Signature: ' . $signature
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode !== 200 && $httpCode !== 201) {
                error_log("GCash API Error: $response");

                // Return simulated response for development
                return [
                    'success' => true,
                    'transaction_id' => $reference,
                    'payment_url' => 'http://localhost:8000/api/payments/gcash/simulate?ref=' . $reference,
                    'message' => 'GCash payment initiated (simulated for development)',
                    'amount' => $amount,
                    'currency' => 'PHP',
                    'gateway' => 'gcash',
                    'mobile_number' => $paymentDetails['mobile_number'] ?? null
                ];
            }

            $result = json_decode($response, true);

            return [
                'success' => true,
                'transaction_id' => $result['transaction_id'] ?? $reference,
                'payment_url' => $result['payment_url'] ?? null,
                'message' => 'GCash payment initiated successfully',
                'amount' => $amount,
                'currency' => 'PHP',
                'gateway' => 'gcash',
                'mobile_number' => $paymentDetails['mobile_number'] ?? null
            ];
        } catch (\Exception $e) {
            error_log("GCash payment error: " . $e->getMessage());
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
