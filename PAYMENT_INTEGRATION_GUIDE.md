# Payment Gateway Integration Guide

## GCash and PayPal Setup for FixItMati

This guide explains how to integrate GCash and PayPal payment gateways into your FixItMati application.

---

## 🚀 Overview

Your application now supports three payment methods:

1. **GCash** - Popular mobile wallet in the Philippines
2. **PayPal** - Global digital payment platform
3. **Credit/Debit Cards** - Via Stripe integration

---

## 📋 Prerequisites

Before you begin, you'll need:

- Active accounts on GCash and PayPal merchant platforms
- API credentials for each service
- SSL certificate for your domain (required for production)
- Basic understanding of webhook handling

---

## 💳 GCash Integration

### Step 1: Create GCash Merchant Account

1. **Apply for GCash Business Account**

   - Visit: https://www.gcash.com/business/
   - Submit business registration documents
   - Wait for approval (typically 5-7 business days)

2. **Access Developer Portal**
   - Once approved, log in to GCash Developer Portal
   - Navigate to API Credentials section

### Step 2: Get API Credentials

You'll need the following credentials:

- **Merchant ID** - Your unique merchant identifier
- **API Key** - Public key for authentication
- **API Secret** - Private key (keep secure!)
- **Webhook Secret** - For verifying webhook callbacks

### Step 3: Configure Environment Variables

Add these to your `.env` file (create one if it doesn't exist):

```env
# GCash Configuration
GCASH_MERCHANT_ID=your_merchant_id_here
GCASH_API_KEY=your_api_key_here
GCASH_API_SECRET=your_api_secret_here
GCASH_WEBHOOK_SECRET=your_webhook_secret_here
GCASH_API_URL=https://api.gcash.com/v1
GCASH_ENABLED=true
```

### Step 4: GCash Payment Flow

**How it works:**

1. User selects GCash as payment method
2. Your app calls GCash API to create payment
3. User is redirected to GCash app/web to authorize
4. GCash processes payment
5. User is redirected back to your app
6. Webhook confirms payment status

**API Endpoints:**

```php
// Create Payment Request
POST https://api.gcash.com/v1/payments
Headers:
  - Authorization: Bearer {API_KEY}
  - Content-Type: application/json

Body:
{
  "amount": 1500.00,
  "currency": "PHP",
  "description": "Water and Electricity Bill Payment",
  "merchant_id": "{MERCHANT_ID}",
  "redirect_url": "https://yoursite.com/api/webhooks/gcash/return",
  "webhook_url": "https://yoursite.com/api/webhooks/gcash"
}

Response:
{
  "transaction_id": "GCASH-123456789",
  "status": "pending",
  "payment_url": "https://gcash.com/checkout/abc123",
  "expires_at": "2024-12-15T10:30:00Z"
}
```

### Step 5: Test GCash Integration

**Test Mode:**

- Use GCash Sandbox environment
- Test cards provided in developer portal
- URL: https://sandbox.gcash.com/v1

**Production Mode:**

- Switch to production API URL
- Use real API credentials
- Enable in payment.php config

---

## 💰 PayPal Integration

### Step 1: Create PayPal Business Account

1. **Sign up for PayPal Business**

   - Visit: https://www.paypal.com/ph/business
   - Complete business verification
   - This is free but requires business documents

2. **Access Developer Dashboard**
   - Go to: https://developer.paypal.com/dashboard
   - Log in with your PayPal Business credentials

### Step 2: Create REST API App

1. Navigate to "My Apps & Credentials"
2. Click "Create App"
3. Name your app: "FixItMati Payment System"
4. Select "Merchant" as app type
5. Click "Create App"

### Step 3: Get API Credentials

You'll receive:

- **Client ID** - Public identifier for your app
- **Client Secret** - Private key (keep secure!)

You'll also need to:

- Create a **Webhook** for payment notifications
- Get the **Webhook ID**

### Step 4: Configure Environment Variables

Add these to your `.env` file:

```env
# PayPal Configuration
PAYPAL_CLIENT_ID=your_client_id_here
PAYPAL_CLIENT_SECRET=your_client_secret_here
PAYPAL_WEBHOOK_ID=your_webhook_id_here
PAYPAL_MODE=sandbox
PAYPAL_ENABLED=true

# For production, change to:
# PAYPAL_MODE=live
```

### Step 5: PayPal Payment Flow

**How it works:**

1. User selects PayPal as payment method
2. Your app creates PayPal order
3. User logs in to PayPal and approves payment
4. PayPal captures payment
5. Webhook confirms payment status
6. User is redirected back to your app

**API Integration:**

```php
// Get Access Token
POST https://api-m.sandbox.paypal.com/v1/oauth2/token
Headers:
  - Authorization: Basic {base64(CLIENT_ID:CLIENT_SECRET)}
  - Content-Type: application/x-www-form-urlencoded

Body: grant_type=client_credentials

Response:
{
  "access_token": "A21AAxxxxx",
  "expires_in": 32400
}

// Create Order
POST https://api-m.sandbox.paypal.com/v2/checkout/orders
Headers:
  - Authorization: Bearer {ACCESS_TOKEN}
  - Content-Type: application/json

Body:
{
  "intent": "CAPTURE",
  "purchase_units": [{
    "amount": {
      "currency_code": "PHP",
      "value": "1500.00"
    },
    "description": "Water and Electricity Bill Payment"
  }],
  "application_context": {
    "return_url": "https://yoursite.com/api/webhooks/paypal/return",
    "cancel_url": "https://yoursite.com/api/webhooks/paypal/cancel"
  }
}

Response:
{
  "id": "5O190127TN364715T",
  "status": "CREATED",
  "links": [
    {
      "rel": "approve",
      "href": "https://www.sandbox.paypal.com/checkoutnow?token=5O190127TN364715T"
    }
  ]
}
```

### Step 6: Set Up Webhooks

**Create Webhook:**

1. In PayPal Developer Dashboard, go to "Webhooks"
2. Click "Create Webhook"
3. Enter your webhook URL: `https://yoursite.com/api/webhooks/paypal`
4. Select events to listen for:
   - `PAYMENT.CAPTURE.COMPLETED`
   - `PAYMENT.CAPTURE.DENIED`
   - `PAYMENT.CAPTURE.REFUNDED`

**Handle Webhook:**

```php
// In your webhook endpoint
$payload = file_get_contents('php://input');
$signature = $_SERVER['HTTP_PAYPAL_TRANSMISSION_SIG'] ?? '';

// Verify webhook signature
$isValid = verifyPayPalWebhook($payload, $signature);

if ($isValid) {
    $data = json_decode($payload, true);

    if ($data['event_type'] === 'PAYMENT.CAPTURE.COMPLETED') {
        // Payment successful - update database
        $transactionId = $data['resource']['id'];
        $amount = $data['resource']['amount']['value'];
        // Update payment status in your database
    }
}
```

### Step 7: Test PayPal Integration

**Sandbox Testing:**

- Use sandbox.paypal.com for testing
- Create test buyer and seller accounts in Developer Dashboard
- Test cards provided by PayPal

**Test Credentials (Sandbox):**

- Buyer Account: sb-buyer@business.example.com / TestPassword123
- Seller Account: sb-seller@business.example.com / TestPassword123

**Production Mode:**

- Switch `PAYPAL_MODE=live` in .env
- Use live credentials
- Test with small real transactions first

---

## 🔧 Implementation in Your App

### Current Files Structure

```
fix-it-mati/
├── config/
│   └── payment.php                    # Payment gateway configuration
├── Controllers/
│   └── PaymentController.php          # Handles payment requests
├── DesignPatterns/Structural/Adapter/
│   ├── PaymentGatewayInterface.php    # Common interface
│   ├── GCashPaymentAdapter.php        # GCash implementation
│   ├── PayPalPaymentAdapter.php       # PayPal implementation
│   └── PaymentAdapterFactory.php      # Creates adapters
├── public/pages/user/
│   └── payments.php                   # Payment UI
└── assets/
    └── payments.js                    # Payment processing logic
```

### Code Implementation

**1. Payment Gateway Configuration** ([config/payment.php](config/payment.php))

All payment gateway settings are centralized here. The configuration reads from environment variables for security.

**2. Payment Controller** ([Controllers/PaymentController.php](Controllers/PaymentController.php))

- `getSupportedGateways()` - Returns available payment methods (GCash, PayPal, Card)
- `processPayment()` - Initiates payment through selected gateway
- `handleWebhook()` - Processes payment confirmation webhooks

**3. Payment Adapters** (DesignPatterns/Structural/Adapter/)

Each payment gateway has its own adapter implementing `PaymentGatewayInterface`:

```php
// Example usage
$gateway = PaymentAdapterFactory::createGateway('paypal', $config);
$result = $gateway->processPayment(1500.00, $paymentDetails);

if ($result['success']) {
    // Payment initiated successfully
    $transactionId = $result['transaction_id'];
}
```

**4. Frontend Integration** ([public/pages/user/payments.php](public/pages/user/payments.php))

The UI now shows:

- GCash button (blue)
- PayPal button (blue with white text)
- Card button (gray)

**5. JavaScript Processing** ([assets/payments.js](assets/payments.js))

When user clicks a payment button:

```javascript
async function processPayment(gateway) {
  // gateway can be: 'gcash', 'paypal', or 'stripe'

  const response = await ApiClient.post("/payments/process", {
    gateway: gateway,
    amount: totalAmount,
    payment_ids: selectedPayments,
  });

  if (response.success) {
    // Redirect to payment gateway
    window.location.href = response.payment_url;
  }
}
```

---

## 🔐 Security Best Practices

### 1. Environment Variables

**Never commit API keys to version control!**

Create `.env` file (add to .gitignore):

```env
# Never commit this file!
GCASH_API_SECRET=xxxxx
PAYPAL_CLIENT_SECRET=xxxxx
```

### 2. Webhook Verification

Always verify webhook signatures:

```php
// GCash
$signature = $_SERVER['HTTP_X_GCASH_SIGNATURE'] ?? '';
$isValid = hash_equals($signature, hash_hmac('sha256', $payload, $webhookSecret));

// PayPal
// Use PayPal SDK to verify webhook signature
$verification = PayPalWebhook::verify($payload, $headers, $webhookId);
```

### 3. SSL/HTTPS

- Use HTTPS for all payment pages
- Payment gateways will reject non-HTTPS webhooks in production

### 4. Amount Validation

- Always validate amounts on server-side
- Never trust client-side amount values
- Check against database before processing

---

## 🧪 Testing Guide

### Test Flow

1. **Start Local Server**

   ```bash
   php -S localhost:8000 -t public
   ```

2. **Access Payment Page**

   - Navigate to: http://localhost:8000/pages/user/payments.php
   - Log in with test account

3. **Test GCash Payment**

   - Click "GCash" button
   - Should redirect to GCash sandbox
   - Use test account to complete payment
   - Verify webhook received
   - Check payment status in database

4. **Test PayPal Payment**
   - Click "PayPal" button
   - Should redirect to PayPal sandbox
   - Log in with test buyer account
   - Approve payment
   - Verify webhook received
   - Check payment status in database

### Test Checklist

- [ ] GCash sandbox payment works
- [ ] PayPal sandbox payment works
- [ ] Webhooks are received and processed
- [ ] Payment status updates in database
- [ ] User sees confirmation message
- [ ] Transaction appears in payment history
- [ ] Failed payments are handled gracefully
- [ ] Amount calculations are correct

---

## 🐛 Troubleshooting

### Common Issues

**1. "Gateway not supported" error**

- Check if gateway is enabled in `config/payment.php`
- Verify `GCASH_ENABLED=true` or `PAYPAL_ENABLED=true` in .env

**2. "Invalid credentials" error**

- Double-check API keys in .env file
- Ensure no extra spaces in credentials
- Verify using correct sandbox vs live credentials

**3. Webhook not received**

- Check webhook URL is publicly accessible
- Verify webhook URL in gateway dashboard
- Check server logs for incoming requests
- Ensure firewall allows incoming connections

**4. SSL certificate errors**

- For local testing, use ngrok: `ngrok http 8000`
- In production, install valid SSL certificate
- Payment gateways require HTTPS

**5. Payment fails immediately**

- Check error logs in `logs/` directory
- Verify API endpoints are correct
- Test with small amount first
- Check account has sufficient balance (sandbox)

---

## 📊 Monitoring & Logs

### Enable Logging

In [config/payment.php](config/payment.php):

```php
'log_transactions' => true,
'log_level' => 'debug'  // debug, info, warning, error
```

### Check Logs

```bash
# View payment logs
tail -f logs/payment.log

# View error logs
tail -f logs/error.log
```

### Database Monitoring

```sql
-- Check recent transactions
SELECT * FROM transactions
WHERE gateway IN ('gcash', 'paypal')
ORDER BY created_at DESC
LIMIT 10;

-- Check payment success rate
SELECT
    gateway,
    status,
    COUNT(*) as count
FROM transactions
WHERE created_at > NOW() - INTERVAL '1 day'
GROUP BY gateway, status;
```

---

## 🚀 Going to Production

### Pre-Launch Checklist

- [ ] Obtain production API credentials from GCash
- [ ] Obtain production API credentials from PayPal
- [ ] Update `.env` with production credentials
- [ ] Set `GCASH_API_URL` to production endpoint
- [ ] Set `PAYPAL_MODE=live`
- [ ] Install SSL certificate on domain
- [ ] Update webhook URLs to production domain
- [ ] Test with small real transactions
- [ ] Set up monitoring and alerts
- [ ] Document payment reconciliation process
- [ ] Train support team on handling payment issues

### Production Environment Variables

```env
# Production Settings
GCASH_API_URL=https://api.gcash.com/v1
GCASH_ENABLED=true

PAYPAL_MODE=live
PAYPAL_ENABLED=true

# Update all credentials to production values
GCASH_MERCHANT_ID=prod_merchant_id
PAYPAL_CLIENT_ID=prod_client_id
# etc...
```

---

## 📞 Support & Resources

### GCash

- Developer Portal: https://developer.gcash.com
- Documentation: https://developer.gcash.com/docs
- Support: developer-support@gcash.com

### PayPal

- Developer Portal: https://developer.paypal.com
- Documentation: https://developer.paypal.com/docs
- Support: https://developer.paypal.com/support

### FixItMati Support

- Issues: Create ticket in GitHub repository
- Documentation: Check `/docs` folder
- Community: Contact development team

---

## ✅ Summary

You've successfully configured your application to accept:

1. ✅ **GCash** payments via direct API integration
2. ✅ **PayPal** payments via REST API
3. ✅ **Credit/Debit Cards** via existing Stripe integration

Your users can now pay their water and electricity bills using their preferred payment method!

**Next Steps:**

1. Complete API credential setup for both gateways
2. Test in sandbox/test mode thoroughly
3. Deploy to production with proper SSL
4. Monitor transactions and handle any issues
5. Consider adding more payment options based on user feedback

---

## 📝 Notes

- Always test thoroughly in sandbox before production
- Keep API credentials secure and never commit to git
- Monitor webhook deliveries to catch issues early
- Have a rollback plan if issues occur
- Document any custom modifications for your team

Good luck with your payment integration! 🎉
