<?php

namespace FixItMati\Services;

use FixItMati\Core\Database;

/**
 * Receipt Generation Service
 * Generates PDF receipts for payments
 */
class ReceiptService
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Generate receipt for a payment
     * 
     * @param string $transactionId Transaction ID
     * @return array Receipt data
     */
    public function generateReceipt(string $transactionId): array
    {
        // Get payment details
        $payment = $this->getPaymentDetails($transactionId);

        if (!$payment) {
            throw new \Exception('Payment not found');
        }

        // Get user details
        $user = $this->getUserDetails($payment['user_id']);

        // Generate receipt number
        $receiptNumber = $this->generateReceiptNumber($payment['id']);

        // Build receipt data
        $receiptData = [
            'receipt_number' => $receiptNumber,
            'transaction_id' => $transactionId,
            'date' => date('F d, Y', strtotime($payment['created_at'])),
            'time' => date('h:i A', strtotime($payment['created_at'])),
            'customer' => [
                'name' => $user['first_name'] . ' ' . $user['last_name'],
                'email' => $user['email'],
                'account_number' => $user['account_number'] ?? 'N/A'
            ],
            'payment' => [
                'amount' => $payment['amount'],
                'gateway' => $this->formatGateway($payment['gateway']),
                'status' => ucfirst($payment['status']),
                'description' => $payment['description'] ?? 'Water Bill Payment',
                'reference' => $payment['reference_number'] ?? $transactionId
            ],
            'company' => [
                'name' => 'FixItMati Water Services',
                'address' => 'Mati City, Davao Oriental',
                'phone' => '+63 123 456 7890',
                'email' => 'support@fixitmati.com'
            ]
        ];

        return $receiptData;
    }

    /**
     * Generate HTML receipt
     * 
     * @param string $transactionId Transaction ID
     * @return string HTML content
     */
    public function generateHTMLReceipt(string $transactionId): string
    {
        $data = $this->generateReceipt($transactionId);

        return $this->buildHTMLTemplate($data);
    }

    /**
     * Generate PDF receipt (simplified version)
     * Note: For production, use a library like TCPDF or DOMPDF
     * 
     * @param string $transactionId Transaction ID
     * @return string Path to generated PDF
     */
    public function generatePDFReceipt(string $transactionId): string
    {
        $html = $this->generateHTMLReceipt($transactionId);

        // For now, we'll save as HTML
        // In production, convert to PDF using TCPDF/DOMPDF
        $filename = "receipt_{$transactionId}.html";
        $filepath = __DIR__ . "/../../uploads/receipts/{$filename}";

        // Create receipts directory if not exists
        $dir = dirname($filepath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        file_put_contents($filepath, $html);

        return $filepath;
    }

    /**
     * Send receipt via email
     * 
     * @param string $transactionId Transaction ID
     * @param string $email Recipient email
     * @return bool Success status
     */
    public function sendReceiptEmail(string $transactionId, string $email): bool
    {
        $data = $this->generateReceipt($transactionId);
        $html = $this->buildHTMLTemplate($data);

        // Email headers
        $headers = [
            'MIME-Version: 1.0',
            'Content-Type: text/html; charset=UTF-8',
            'From: FixItMati <noreply@fixitmati.com>',
            'Reply-To: support@fixitmati.com'
        ];

        $subject = "Payment Receipt - {$data['receipt_number']}";

        // Send email
        $sent = mail($email, $subject, $html, implode("\r\n", $headers));

        // Log email attempt
        error_log("Receipt email " . ($sent ? "sent" : "failed") . " to {$email}");

        return $sent;
    }

    /**
     * Get payment details from database
     */
    private function getPaymentDetails(string $transactionId): ?array
    {
        $sql = "SELECT * FROM payments WHERE transaction_id = :transaction_id OR id::text = :id LIMIT 1";
        $result = $this->db->query($sql, [
            'transaction_id' => $transactionId,
            'id' => $transactionId
        ]);

        return $result[0] ?? null;
    }

    /**
     * Get user details from database
     */
    private function getUserDetails(string $userId): array
    {
        $sql = "SELECT * FROM users WHERE id = :user_id LIMIT 1";
        $result = $this->db->query($sql, ['user_id' => $userId]);

        return $result[0] ?? [
            'first_name' => 'Unknown',
            'last_name' => 'User',
            'email' => 'unknown@example.com'
        ];
    }

    /**
     * Generate unique receipt number
     */
    private function generateReceiptNumber(string $paymentId): string
    {
        $date = date('Ymd');
        $short = substr($paymentId, 0, 8);
        return "RCP-{$date}-{$short}";
    }

    /**
     * Format gateway name
     */
    private function formatGateway(string $gateway): string
    {
        $gateways = [
            'paypal' => 'PayPal',
            'gcash' => 'GCash',
            'card' => 'Credit/Debit Card',
            'stripe' => 'Stripe'
        ];

        return $gateways[$gateway] ?? ucfirst($gateway);
    }

    /**
     * Build HTML template for receipt
     */
    private function buildHTMLTemplate(array $data): string
    {
        $amount = number_format($data['payment']['amount'], 2);

        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Receipt - {$data['receipt_number']}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
            padding: 20px;
        }
        .receipt-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .receipt-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px;
            text-align: center;
        }
        .receipt-header h1 {
            font-size: 2rem;
            margin-bottom: 10px;
        }
        .receipt-number {
            font-size: 1.2rem;
            opacity: 0.9;
        }
        .receipt-body {
            padding: 40px;
        }
        .section {
            margin-bottom: 30px;
        }
        .section-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #667eea;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e2e8f0;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #f0f0f0;
        }
        .info-label {
            font-weight: 600;
            color: #4a5568;
        }
        .info-value {
            color: #1a202c;
        }
        .amount-section {
            background: #f7fafc;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            margin: 30px 0;
        }
        .amount-label {
            font-size: 0.9rem;
            color: #718096;
            margin-bottom: 10px;
        }
        .amount-value {
            font-size: 2.5rem;
            font-weight: 700;
            color: #667eea;
        }
        .status-badge {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 600;
            background: #48bb78;
            color: white;
        }
        .footer {
            background: #f7fafc;
            padding: 30px 40px;
            text-align: center;
            color: #718096;
            font-size: 0.9rem;
        }
        .footer p {
            margin: 5px 0;
        }
        @media print {
            body {
                background: white;
                padding: 0;
            }
            .receipt-container {
                box-shadow: none;
            }
        }
    </style>
</head>
<body>
    <div class="receipt-container">
        <div class="receipt-header">
            <h1>Payment Receipt</h1>
            <div class="receipt-number">{$data['receipt_number']}</div>
        </div>
        
        <div class="receipt-body">
            <!-- Payment Status -->
            <div style="text-align: center; margin-bottom: 20px;">
                <span class="status-badge">{$data['payment']['status']}</span>
            </div>
            
            <!-- Date & Time -->
            <div class="section">
                <div class="info-row">
                    <span class="info-label">Date</span>
                    <span class="info-value">{$data['date']}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Time</span>
                    <span class="info-value">{$data['time']}</span>
                </div>
            </div>
            
            <!-- Customer Information -->
            <div class="section">
                <div class="section-title">Customer Information</div>
                <div class="info-row">
                    <span class="info-label">Name</span>
                    <span class="info-value">{$data['customer']['name']}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Email</span>
                    <span class="info-value">{$data['customer']['email']}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Account Number</span>
                    <span class="info-value">{$data['customer']['account_number']}</span>
                </div>
            </div>
            
            <!-- Payment Details -->
            <div class="section">
                <div class="section-title">Payment Details</div>
                <div class="info-row">
                    <span class="info-label">Description</span>
                    <span class="info-value">{$data['payment']['description']}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Payment Method</span>
                    <span class="info-value">{$data['payment']['gateway']}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Transaction ID</span>
                    <span class="info-value">{$data['transaction_id']}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Reference Number</span>
                    <span class="info-value">{$data['payment']['reference']}</span>
                </div>
            </div>
            
            <!-- Amount -->
            <div class="amount-section">
                <div class="amount-label">TOTAL AMOUNT PAID</div>
                <div class="amount-value">₱{$amount}</div>
            </div>
        </div>
        
        <div class="footer">
            <p><strong>{$data['company']['name']}</strong></p>
            <p>{$data['company']['address']}</p>
            <p>Phone: {$data['company']['phone']} | Email: {$data['company']['email']}</p>
            <p style="margin-top: 15px; font-size: 0.85rem;">
                This is an official receipt. Please keep for your records.
            </p>
        </div>
    </div>
</body>
</html>
HTML;
    }
}
