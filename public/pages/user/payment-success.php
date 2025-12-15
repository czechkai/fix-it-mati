<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Successful - FixItMati</title>
    <link rel="stylesheet" href="/assets/style.css">
    <style>
        .success-container {
            max-width: 600px;
            margin: 50px auto;
            padding: 40px;
            text-align: center;
        }

        .success-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            animation: scaleIn 0.5s ease-out;
        }

        .success-icon svg {
            width: 50px;
            height: 50px;
            color: white;
        }

        @keyframes scaleIn {
            from {
                transform: scale(0);
                opacity: 0;
            }

            to {
                transform: scale(1);
                opacity: 1;
            }
        }

        .success-title {
            font-size: 2rem;
            font-weight: 700;
            color: #1a202c;
            margin-bottom: 10px;
        }

        .success-subtitle {
            font-size: 1.1rem;
            color: #718096;
            margin-bottom: 30px;
        }

        .payment-details {
            background: #f7fafc;
            border-radius: 12px;
            padding: 30px;
            margin: 30px 0;
            text-align: left;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 15px 0;
            border-bottom: 1px solid #e2e8f0;
        }

        .detail-row:last-child {
            border-bottom: none;
        }

        .detail-label {
            font-weight: 600;
            color: #4a5568;
        }

        .detail-value {
            color: #1a202c;
            font-weight: 500;
        }

        .amount-value {
            font-size: 1.3rem;
            color: #667eea;
            font-weight: 700;
        }

        .action-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 30px;
        }

        .btn {
            padding: 12px 30px;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }

        .btn-secondary {
            background: white;
            color: #4a5568;
            border: 2px solid #e2e8f0;
        }

        .btn-secondary:hover {
            background: #f7fafc;
            border-color: #cbd5e0;
        }

        .loading {
            text-align: center;
            padding: 40px;
        }

        .spinner {
            border: 3px solid #f3f3f3;
            border-top: 3px solid #667eea;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }
    </style>
</head>

<body>
    <div class="success-container">
        <div id="loadingState" class="loading">
            <div class="spinner"></div>
            <p>Processing your payment...</p>
        </div>

        <div id="successState" style="display: none;">
            <div class="success-icon">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
            </div>

            <h1 class="success-title">Payment Successful!</h1>
            <p class="success-subtitle">Your payment has been processed successfully</p>

            <div class="payment-details">
                <div class="detail-row">
                    <span class="detail-label">Transaction ID</span>
                    <span class="detail-value" id="transactionId">-</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Payment Method</span>
                    <span class="detail-value" id="paymentMethod">-</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Amount Paid</span>
                    <span class="detail-value amount-value" id="amount">-</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Date & Time</span>
                    <span class="detail-value" id="timestamp">-</span>
                </div>
            </div>

            <div class="action-buttons">
                <button onclick="downloadReceipt()" class="btn btn-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Download Receipt
                </button>
                <a href="/pages/user/payments.php" class="btn btn-secondary">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                    </svg>
                    Back to Payments
                </a>
                <a href="/pages/user/dashboard.php" class="btn btn-secondary">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    Go to Dashboard
                </a>
            </div>
        </div>
    </div>

    <script>
        // Get payment info from URL parameters or sessionStorage
        const urlParams = new URLSearchParams(window.location.search);
        const paymentRef = urlParams.get('ref');

        // Try to get pending payment from sessionStorage
        let pendingPayment = null;
        try {
            const stored = sessionStorage.getItem('pending_payment');
            if (stored) {
                pendingPayment = JSON.parse(stored);
                // Clear it after retrieving
                sessionStorage.removeItem('pending_payment');
            }
        } catch (e) {
            console.error('Error reading sessionStorage:', e);
        }

        // Show success state after brief delay
        setTimeout(() => {
            document.getElementById('loadingState').style.display = 'none';
            document.getElementById('successState').style.display = 'block';

            // Populate payment details
            if (pendingPayment) {
                document.getElementById('transactionId').textContent = pendingPayment.transaction_id || paymentRef || 'N/A';
                document.getElementById('paymentMethod').textContent = formatGateway(pendingPayment.gateway);
                document.getElementById('amount').textContent = '₱' + parseFloat(pendingPayment.amount).toFixed(2);
                document.getElementById('timestamp').textContent = new Date(pendingPayment.timestamp || Date.now()).toLocaleString();
            } else if (paymentRef) {
                // Fallback if no sessionStorage data
                document.getElementById('transactionId').textContent = paymentRef;
                document.getElementById('paymentMethod').textContent = 'Online Payment';
                document.getElementById('timestamp').textContent = new Date().toLocaleString();
            }
        }, 1000);

        function formatGateway(gateway) {
            const gateways = {
                'paypal': 'PayPal',
                'gcash': 'GCash',
                'card': 'Credit/Debit Card',
                'stripe': 'Stripe'
            };
            return gateways[gateway] || gateway;
        }

        function downloadReceipt() {
            const transactionId = document.getElementById('transactionId').textContent;
            if (transactionId && transactionId !== 'N/A' && transactionId !== '-') {
                // Open receipt in new window
                window.open(`/api/payments/receipt/${transactionId}`, '_blank');
            } else {
                alert('Transaction ID not available');
            }
        }
    </script>
</body>

</html>