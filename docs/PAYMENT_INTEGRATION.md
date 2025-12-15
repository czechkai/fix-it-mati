# Payment Gateway Integration - Complete Guide

## Overview

This document describes the full payment integration with PayPal and GCash, including the redirect flow, return handlers, and webhook processing.

## Payment Flow

### 1. User Initiates Payment

- User selects payment gateway (PayPal or GCash) on `payments.php`
- JavaScript calls `/api/payments/process` with:
  - `payment_id`: UUID of the payment
  - `gateway`: 'paypal' or 'gcash'
  - `amount`: Payment amount
  - `return_url`: Where to redirect after success
  - `cancel_url`: Where to redirect if cancelled

### 2. Backend Creates Payment

**File**: `Controllers/PaymentController.php` - `processPayment()`

```php
// Creates payment adapter
$adapter = PaymentAdapterFactory::createGateway($gateway, $config);

// Calls adapter to create payment and get approval URL
$result = $adapter->processPayment($paymentData);

// Returns payment_url to frontend
return Response::success([
    'payment_url' => $result['payment_url'],
    'transaction_id' => $result['transaction_id'],
    'gateway' => $gateway,
    'amount' => $amount
]);
```

### 3. Frontend Redirects to Gateway

**File**: `assets/payments.js` - `processPayment()`

```javascript
// Store pending payment info
sessionStorage.setItem(
  "pending_payment",
  JSON.stringify({
    transaction_id: result.data.transaction_id,
    gateway: gateway,
    amount: amount,
    timestamp: Date.now(),
  })
);

// Redirect to payment gateway
window.location.href = result.data.payment_url;
```

### 4. User Completes Payment on Gateway

- User is redirected to PayPal or GCash website
- User logs in and approves payment
- Gateway redirects back to our return URL

### 5. Return Handler Captures Payment

**File**: `Controllers/PaymentController.php` - `handlePayPalReturn()` / `handleGCashReturn()`

**PayPal Return Handler**:

```php
// Get token from query string
$token = $request->query('token');

// Capture the payment via PayPal API
$ch = curl_init("$apiUrl/v2/checkout/orders/$token/capture");
// ... OAuth and capture logic

// Redirect to success page
header('Location: /pages/user/payment-success.php?ref=' . $token);
```

**GCash Return Handler**:

```php
$reference = $request->query('ref');
$status = $request->query('status');

if ($status === 'success') {
    header('Location: /pages/user/payment-success.php?ref=' . $reference);
}
```

### 6. Success Page Displays Confirmation

**File**: `public/pages/user/payment-success.php`

- Retrieves payment info from `sessionStorage` (pending_payment)
- Displays transaction details (ID, method, amount, timestamp)
- Provides buttons to return to payments or dashboard

## Payment Adapters

### PayPal Adapter

**File**: `DesignPatterns/Structural/Adapter/PayPalPaymentAdapter.php`

**Configuration** (in `.env`):

```env
PAYPAL_CLIENT_ID=your_client_id
PAYPAL_CLIENT_SECRET=your_client_secret
PAYPAL_MODE=sandbox # or 'live'
```

**Key Methods**:

1. `getAccessToken()`: Authenticates with PayPal OAuth

   - Endpoint: `/v1/oauth2/token`
   - Auth: Basic (client_id:client_secret)
   - Returns: Access token

2. `processPayment()`: Creates PayPal order
   - Endpoint: `/v2/checkout/orders`
   - Creates order with purchase_units
   - Returns approval URL from response links

**API Endpoints**:

- Sandbox: `https://api-m.sandbox.paypal.com`
- Live: `https://api-m.paypal.com`

### GCash Adapter

**File**: `DesignPatterns/Structural/Adapter/GCashPaymentAdapter.php`

**Configuration** (in `.env`):

```env
GCASH_MERCHANT_ID=your_merchant_id
GCASH_API_KEY=your_api_key
GCASH_API_SECRET=your_api_secret
```

**Key Methods**:

1. `processPayment()`: Creates GCash payment
   - Generates HMAC-SHA256 signature
   - Signature string: `merchant_id|transaction_id|amount|timestamp`
   - Posts to GCash API
   - Returns payment URL

**Signature Generation**:

```php
$signatureString = "{$merchantId}|{$transactionId}|{$amount}|{$timestamp}";
$signature = hash_hmac('sha256', $signatureString, $apiSecret);
```

## API Routes

### Payment Processing Routes

```php
POST   /api/payments/process         // Create payment, get redirect URL
GET    /api/payments/current         // Get current bills
GET    /api/payments/history         // Get transaction history
GET    /api/payments/gateways        // Get supported gateways
POST   /api/payments/refund          // Refund a payment
```

### Return/Callback Routes (No Auth Required)

```php
GET    /api/payments/paypal/return   // PayPal success callback
GET    /api/payments/paypal/cancel   // PayPal cancellation callback
GET    /api/payments/gcash/return    // GCash callback
```

### Webhook Routes (Public, Signature Verified)

```php
POST   /api/webhooks/paypal          // PayPal webhook notifications
POST   /api/webhooks/gcash           // GCash webhook notifications
```

## Webhook Handlers

### PayPal Webhook

**File**: `Controllers/PaymentController.php` - `handlePayPalWebhook()`

**Purpose**: Receive async payment notifications from PayPal

**Events Handled**:

- `PAYMENT.CAPTURE.COMPLETED`: Payment captured successfully
- Update payment status in database

**Security**: Should verify webhook signature (TODO)

### GCash Webhook

**File**: `Controllers/PaymentController.php` - `handleGCashWebhook()`

**Purpose**: Receive async payment notifications from GCash

**Security**: Should verify webhook signature using HMAC (TODO)

## Database Schema

### Payments Table

```sql
CREATE TABLE payments (
    id UUID PRIMARY KEY,
    user_id UUID REFERENCES users(id),
    amount DECIMAL(10, 2),
    gateway VARCHAR(50),
    status VARCHAR(20),
    transaction_id VARCHAR(255),
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

**Status Values**:

- `pending`: Payment initiated, awaiting completion
- `paid`: Payment completed successfully
- `failed`: Payment failed
- `cancelled`: Payment cancelled by user
- `refunded`: Payment refunded

## Frontend Files

### Payment UI

**File**: `public/pages/user/payments.php`

- Displays current bills and due amounts
- Quick payment buttons for GCash, PayPal, Card
- Payment modal for selecting gateway

### Payment JavaScript

**File**: `assets/payments.js`

- `processPayment(gateway)`: Initiates payment flow
- `checkPaymentReturn()`: Handles return from gateway
- Stores pending payment in sessionStorage
- Redirects to payment gateway URL

### Success Page

**File**: `public/pages/user/payment-success.php`

- Shows success confirmation
- Displays transaction details
- Retrieved from sessionStorage
- Links back to payments/dashboard

## Configuration Setup

### 1. Environment Variables

Create or update `.env` file:

```env
# PayPal Configuration
PAYPAL_CLIENT_ID=your_sandbox_client_id
PAYPAL_CLIENT_SECRET=your_sandbox_client_secret
PAYPAL_MODE=sandbox

# GCash Configuration
GCASH_MERCHANT_ID=your_merchant_id
GCASH_API_KEY=your_api_key
GCASH_API_SECRET=your_api_secret

# Application
APP_URL=http://localhost:8000
```

### 2. PayPal Configuration File

**File**: `config/payment.php`

```php
return [
    'paypal' => [
        'client_id' => $_ENV['PAYPAL_CLIENT_ID'] ?? '',
        'client_secret' => $_ENV['PAYPAL_CLIENT_SECRET'] ?? '',
        'mode' => $_ENV['PAYPAL_MODE'] ?? 'sandbox',
        'currency' => 'PHP'
    ],
    'gcash' => [
        'merchant_id' => $_ENV['GCASH_MERCHANT_ID'] ?? '',
        'api_key' => $_ENV['GCASH_API_KEY'] ?? '',
        'api_secret' => $_ENV['GCASH_API_SECRET'] ?? '',
        'currency' => 'PHP'
    ]
];
```

### 3. PayPal Sandbox Setup

1. Go to: https://developer.paypal.com/
2. Login and create app
3. Get Client ID and Secret from app credentials
4. Add return URL: `http://localhost:8000/api/payments/paypal/return`
5. Add webhook URL: `http://localhost:8000/api/webhooks/paypal`

### 4. GCash Setup

1. Contact GCash merchant support
2. Request API credentials
3. Register webhook URL with GCash

## Testing

### Testing PayPal (Sandbox)

1. Use sandbox credentials in `.env`
2. Test with PayPal sandbox account:
   - Email: sb-xxxxx@personal.example.com
   - Password: (provided in sandbox)
3. Complete test payment
4. Verify return to success page

### Testing Return URLs

```
Success: http://localhost:8000/api/payments/paypal/return?token=ABC123&PayerID=XYZ
Cancel:  http://localhost:8000/api/payments/paypal/cancel?token=ABC123
Error:   http://localhost:8000/pages/user/payments.php?error=payment_failed
```

### Testing Webhooks (Local Development)

Use ngrok or similar tool to expose localhost:

```bash
ngrok http 8000
```

Then register webhook URL: `https://xxxxx.ngrok.io/api/webhooks/paypal`

## Security Considerations

### 1. Webhook Signature Verification

**TODO**: Implement PayPal webhook signature verification

```php
// Verify PayPal webhook signature
$headers = getallheaders();
$signature = $headers['PAYPAL-TRANSMISSION-SIG'] ?? '';
// Verify signature using PayPal SDK
```

### 2. Return URL Validation

- Verify payment status with gateway API (already implemented)
- Don't trust query parameters alone
- Always capture/verify with gateway before marking as paid

### 3. HTTPS in Production

- All payment URLs must use HTTPS
- Update `APP_URL` to use https://
- Ensure SSL certificate is valid

### 4. Payment Amount Validation

- Always verify amount on backend
- Don't trust frontend-submitted amounts
- Match against expected bill amount

## Troubleshooting

### Payment URL Not Returned

**Check**:

- Payment adapter is correctly instantiated
- OAuth token is obtained (PayPal)
- API credentials are correct
- API response is parsed correctly

### User Not Redirected Back

**Check**:

- Return URL is correctly set in payment creation
- Return URL is accessible (not behind auth)
- Gateway has correct return URL registered

### Payment Status Not Updated

**Check**:

- Webhook URL is registered with gateway
- Webhook handler is working
- Database update logic is correct
- Check application logs for errors

## Future Enhancements

1. **Payment Status Tracking**: Add real-time payment status updates
2. **Email Notifications**: Send payment receipt via email
3. **Refund UI**: Add admin interface for processing refunds
4. **Multi-currency**: Support payments in different currencies
5. **Recurring Payments**: Add subscription/recurring payment support
6. **Payment Analytics**: Dashboard showing payment metrics
7. **Failed Payment Retry**: Automatic retry for failed payments
8. **Payment Installments**: Support for installment payments

## Related Files

### Controllers

- `Controllers/PaymentController.php` - Main payment controller
- `Controllers/WebhookController.php` - Webhook handlers (old)

### Design Patterns

- `DesignPatterns/Structural/Adapter/PaymentGatewayInterface.php` - Interface
- `DesignPatterns/Structural/Adapter/PayPalPaymentAdapter.php` - PayPal adapter
- `DesignPatterns/Structural/Adapter/GCashPaymentAdapter.php` - GCash adapter
- `DesignPatterns/Structural/Adapter/PaymentAdapterFactory.php` - Factory

### Frontend

- `public/pages/user/payments.php` - Payment UI
- `public/pages/user/payment-success.php` - Success page
- `assets/payments.js` - Payment JavaScript
- `assets/payments.css` - Payment styles

### Configuration

- `config/payment.php` - Payment gateway configuration
- `.env` - Environment variables

### Routes

- `public/api/index.php` - API route registration

## Support

For questions or issues:

- Check application logs: `logs/`
- Review PayPal developer docs: https://developer.paypal.com/docs/
- Contact GCash merchant support for GCash issues
