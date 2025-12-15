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
            // Get access token
            $accessToken = $this->getAccessToken();

            if (!$accessToken) {
                return [
                    'success' => false,
                    'message' => 'Failed to authenticate with PayPal'
                ];
            }

            // Create PayPal order
            $apiUrl = $this->config['mode'] === 'live'
                ? 'https://api-m.paypal.com'
                : 'https://api-m.sandbox.paypal.com';

            $returnUrl = $paymentDetails['return_url'] ?? 'http://localhost:8000/api/payments/paypal/return';
            $cancelUrl = $paymentDetails['cancel_url'] ?? 'http://localhost:8000/api/payments/paypal/cancel';

            $orderData = [
                'intent' => 'CAPTURE',
                'purchase_units' => [[
                    'amount' => [
                        'currency_code' => 'PHP',
                        'value' => number_format($amount, 2, '.', '')
                    ],
                    'description' => $paymentDetails['description'] ?? 'Water and Electricity Bill Payment'
                ]],
                'application_context' => [
                    'return_url' => $returnUrl,
                    'cancel_url' => $cancelUrl,
                    'brand_name' => 'FixItMati',
                    'user_action' => 'PAY_NOW'
                ]
            ];

            $ch = curl_init("$apiUrl/v2/checkout/orders");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($orderData));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $accessToken
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode !== 201) {
                error_log("PayPal API Error: $response");
                return [
                    'success' => false,
                    'message' => 'Failed to create PayPal order'
                ];
            }

            $result = json_decode($response, true);

            // Extract approval URL
            $approvalUrl = null;
            foreach ($result['links'] ?? [] as $link) {
                if ($link['rel'] === 'approve') {
                    $approvalUrl = $link['href'];
                    break;
                }
            }

            return [
                'success' => true,
                'transaction_id' => $result['id'],
                'payment_url' => $approvalUrl,
                'message' => 'PayPal order created successfully',
                'amount' => $amount,
                'currency' => 'PHP',
                'gateway' => 'paypal'
            ];
        } catch (\Exception $e) {
            error_log("PayPal payment error: " . $e->getMessage());
            return [
                'success' => false,
                'transaction_id' => null,
                'message' => 'PayPal payment failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get PayPal access token
     */
    private function getAccessToken(): ?string
    {
        $clientId = $this->config['client_id'] ?? '';
        $clientSecret = $this->config['client_secret'] ?? '';

        if (!$clientId || !$clientSecret) {
            error_log("PayPal credentials not configured");
            error_log("Client ID: " . ($clientId ? 'Present (' . strlen($clientId) . ' chars)' : 'MISSING'));
            error_log("Client Secret: " . ($clientSecret ? 'Present (' . strlen($clientSecret) . ' chars)' : 'MISSING'));
            return null;
        }

        $apiUrl = $this->config['mode'] === 'live'
            ? 'https://api-m.paypal.com'
            : 'https://api-m.sandbox.paypal.com';

        error_log("PayPal Auth Request to: $apiUrl/v1/oauth2/token");
        error_log("Using Client ID (first 10 chars): " . substr($clientId, 0, 10) . "...");

        $ch = curl_init("$apiUrl/v1/oauth2/token");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, 'grant_type=client_credentials');
        curl_setopt($ch, CURLOPT_USERPWD, "$clientId:$clientSecret");
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/x-www-form-urlencoded'
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            error_log("PayPal CURL Error: $curlError");
            return null;
        }

        if ($httpCode !== 200) {
            error_log("PayPal auth failed with HTTP $httpCode");
            error_log("PayPal response: $response");
            return null;
        }

        $result = json_decode($response, true);

        if (isset($result['access_token'])) {
            error_log("PayPal authentication successful!");
            return $result['access_token'];
        }

        error_log("No access token in response");
        return null;
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
