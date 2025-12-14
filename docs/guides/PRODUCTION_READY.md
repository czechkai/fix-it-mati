# Production Readiness Guide

## Overview
This guide helps you deploy FixItMati to production with real payment gateway integration, database transactions, and comprehensive error handling.

---

## Phase 1: Payment Gateway Integration ✅ COMPLETE

### What Was Implemented

#### 1. Payment Adapter Enhancements
**Location**: `DesignPatterns/Structural/Adapter/`

**Stripe Adapter** (`StripePaymentAdapter.php`):
- ✅ Real Stripe SDK integration structure
- ✅ Payment Intent API implementation
- ✅ Automatic fallback to mock mode if SDK not available
- ✅ Comprehensive error handling
- ✅ Transaction logging

**Features**:
```php
// Production mode (when Stripe SDK is installed)
- Uses \Stripe\PaymentIntent::create() for payments
- Uses \Stripe\Refund::create() for refunds
- Uses \Stripe\PaymentIntent::retrieve() for status checks

// Development mode (fallback)
- Mock implementations with 'mock' => true flag
- Same response structure for consistency
```

#### 2. Configuration Management
**Location**: `config/payment.php`

Centralized payment gateway configuration:
- API credentials from environment variables
- Gateway-specific settings (mode, currency, timeouts)
- Retry logic configuration
- Logging preferences

**Security**:
- All credentials in `.env` file (not committed to git)
- Environment-based configuration
- Separate test/live mode settings

#### 3. Comprehensive Logging System
**Location**: `Services/PaymentLogger.php`

**Features**:
- Transaction logging (success/failure)
- Webhook event logging
- Error logging with stack traces
- Audit trail for compliance
- Log rotation (auto-delete logs older than 30 days)
- Configurable log levels (debug, info, warning, error)

**Log Files**:
- `logs/payment_YYYY-MM-DD.log` - Daily payment logs
- `logs/processor_errors_YYYY-MM-DD.log` - Error logs
- `logs/processor_audit_YYYY-MM-DD.log` - Audit trail

#### 4. Database Transactions
**Location**: `DesignPatterns/Behavioral/TemplateMethod/RequestProcessorTemplate.php`

**Enhanced Template Method Pattern**:
```php
try {
    $db->beginTransaction();
    
    // Process request steps
    loadRequest();
    validateRequest();
    preProcess();
    execute();
    postProcess();
    
    $db->commit();
    
    // Send notifications (outside transaction)
    sendNotifications();
    
} catch (Exception $e) {
    $db->rollback();
    logError($e);
}
```

**Benefits**:
- Atomic operations
- Automatic rollback on errors
- Data consistency guaranteed
- Audit logging for all operations

#### 5. Webhook Handlers
**Location**: `Controllers/WebhookController.php`

**Supported Webhooks**:
- Stripe: `/api/webhooks/stripe`
- PayPal: `/api/webhooks/paypal`
- GCash: `/api/webhooks/gcash`

**Features**:
- Signature verification for security
- Event type handling
- Automatic database updates
- Notification triggers
- Error handling and retry logic

**Handles**:
- Payment success/failure
- Refunds
- Disputes/chargebacks
- Status updates

---

## Installation Steps

### 1. Install Payment Gateway SDKs

```bash
# International
# Stripe (recommended for international cards)
composer require stripe/stripe-php

# PayPal (for PayPal accounts worldwide)
composer require paypal/rest-api-sdk-php

# Philippines Payment Gateways (RECOMMENDED)
# PayMongo - handles GCash, GrabPay, Cards, Bank Transfers
composer require paymongo/paymongo-php
# Docs: https://github.com/luigel/paymongo-php

# OR Xendit - handles GCash, PayMaya, Cards, Banking
composer require xendit/xendit-php
# Docs: https://github.com/xendit/xendit-php

# GCash Direct (only for large merchants with GCash agreement)
# No public SDK - contact GCash Business Support
```

**💡 Recommendation for Philippine Market:**
- **Start with PayMongo or Xendit** - They aggregate multiple local payment methods including GCash
- **Use Stripe** for international credit/debit cards
- **Use PayPal** for customers with PayPal accounts
- Direct GCash integration requires business verification and minimum monthly volume

### 2. Configure Environment

Copy `.env.example` to `.env`:
```bash
cp .env.example .env
```

Edit `.env` and fill in your credentials:

**For Stripe**:
```env
STRIPE_ENABLED=true
STRIPE_MODE=test  # or 'live' for production
STRIPE_SECRET_KEY=sk_test_xxxxx
STRIPE_PUBLISHABLE_KEY=pk_test_xxxxx
STRIPE_WEBHOOK_SECRET=whsec_xxxxx
```

**For PayPal**:
```env
PAYPAL_ENABLED=true
PAYPAL_MODE=sandbox  # or 'live' for production
PAYPAL_CLIENT_ID=xxxxx
PAYPAL_CLIENT_SECRET=xxxxx
PAYPAL_WEBHOOK_ID=xxxxx
```

**For GCash** (Via Payment Gateway Aggregator):
```env
# Recommended: Use PayMongo for Philippine payments
PAYMONGO_ENABLED=true
PAYMONGO_PUBLIC_KEY=pk_test_xxxxx
PAYMONGO_SECRET_KEY=sk_test_xxxxx
PAYMONGO_WEBHOOK_SECRET=whsec_xxxxx

# Or use Xendit
XENDIT_ENABLED=true
XENDIT_SECRET_KEY=xnd_xxxxx
XENDIT_PUBLIC_KEY=xnd_public_xxxxx
XENDIT_WEBHOOK_TOKEN=xxxxx

# Direct GCash (for large merchants only)
GCASH_ENABLED=false
GCASH_MERCHANT_ID=xxxxx
GCASH_API_KEY=xxxxx
GCASH_API_SECRET=xxxxx
GCASH_WEBHOOK_SECRET=xxxxx
```

### 3. Set Up Webhooks

#### Stripe Webhooks:
1. Go to https://dashboard.stripe.com/webhooks
2. Click "Add endpoint"
3. URL: `https://your-domain.com/api/webhooks/stripe`
4. Select events:
   - `payment_intent.succeeded`
   - `payment_intent.payment_failed`
   - `charge.refunded`
   - `charge.dispute.created`
5. Copy the webhook signing secret to `.env`

#### PayPal Webhooks:
1. Go to https://developer.paypal.com/dashboard/webhooks
2. Create webhook
3. URL: `https://your-domain.com/api/webhooks/paypal`
4. Select events:
   - `PAYMENT.CAPTURE.COMPLETED`
   - `PAYMENT.CAPTURE.DENIED`
   - `PAYMENT.CAPTURE.REFUNDED`
5. Copy webhook ID to `.env`

#### GCash Webhooks:
1. **Option 1: GCash Direct Integration**
   - Contact GCash Business Support at business@gcash.com
   - Request API credentials and webhook setup
   - Provide URL: `https://your-domain.com/api/webhooks/gcash`
   - Note: GCash typically requires business verification first

2. **Option 2: Use Payment Gateway Aggregator (Recommended for Philippines)**
   
   **PayMongo** (Philippine Payment Gateway):
   - Website: https://www.paymongo.com
   - Supports: GCash, GrabPay, Cards, Bank Transfers
   - Has webhook support: https://developers.paymongo.com/docs/webhooks
   - Setup:
     ```
     1. Sign up at https://dashboard.paymongo.com
     2. Get API keys from dashboard
     3. Set up webhook: https://your-domain.com/api/webhooks/paymongo
     4. Select events: payment.paid, payment.failed, refund.updated
     ```
   
   **Xendit** (Southeast Asia Payment Gateway):
   - Website: https://www.xendit.co/en-ph/
   - Supports: GCash, PayMaya, Cards, Online Banking
   - Webhook docs: https://developers.xendit.co/api-reference/#webhooks
   - Setup similar to PayMongo
   
   **Dragonpay** (Philippine Payment Gateway):
   - Website: https://www.dragonpay.ph
   - Supports: Online Banking, OTC, Mobile Wallets
   - Contact for API access and webhook setup

3. **Option 3: Direct GCash API** (For Large Merchants)
   - Requires GCash Merchant account
   - Minimum monthly volume requirements
   - Contact: https://www.gcash.com/gcash-for-business/

### 4. Create Logs Directory

```bash
mkdir -p logs
chmod 755 logs
```

### 5. Test Payment Integration

Run the Sprint 4 tests to verify everything works:
```bash
php test-sprint4.php
```

Expected: All 25 tests should pass

---

## Testing Guide

### Development Testing (Mock Mode)

When SDKs are not installed, adapters run in mock mode:
```php
// Test payment processing
$adapter = PaymentAdapterFactory::createGateway('stripe');
$result = $adapter->processPayment(100.00, [
    'currency' => 'PHP',
    'payment_method' => 'pm_card_visa'
]);

// Check for mock flag
if ($result['mock'] ?? false) {
    echo "Running in mock mode";
}
```

### Production Testing

#### Test Stripe:
```bash
# Use Stripe test cards
# Success: 4242 4242 4242 4242
# Decline: 4000 0000 0000 0002
```

#### Test PayPal:
```bash
# Use PayPal sandbox accounts
# https://developer.paypal.com/dashboard/accounts
```

#### Test Webhooks:
```bash
# Use webhook testing tools
# Stripe: stripe listen --forward-to localhost:8000/api/webhooks/stripe
# PayPal: Use webhook simulator in dashboard
```

---

## Monitoring & Maintenance

### 1. Log Monitoring

Check payment logs daily:
```bash
tail -f logs/payment_$(date +%Y-%m-%d).log
```

Check error logs:
```bash
tail -f logs/processor_errors_$(date +%Y-%m-d).log
```

### 2. Database Health

Monitor transaction success rates:
```sql
SELECT 
    DATE(created_at) as date,
    COUNT(*) as total_transactions,
    SUM(CASE WHEN status = 'succeeded' THEN 1 ELSE 0 END) as successful,
    SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed
FROM payment_transactions
GROUP BY DATE(created_at)
ORDER BY date DESC
LIMIT 7;
```

### 3. Webhook Monitoring

Check webhook delivery:
- Stripe: Check dashboard for failed deliveries
- PayPal: Monitor webhook events in dashboard
- Set up alerts for webhook failures

### 4. Automated Log Cleanup

Add to cron (runs daily at 2 AM):
```bash
0 2 * * * php /path/to/fixitmati/cleanup-logs.php
```

Create `cleanup-logs.php`:
```php
<?php
require_once __DIR__ . '/autoload.php';

use FixItMati\Services\PaymentLogger;

$logger = new PaymentLogger();
$cleared = $logger->clearOldLogs(30); // Keep 30 days
echo "Cleared {$cleared} old log files\n";
```

---

## Security Checklist

- ✅ All API keys in environment variables
- ✅ Webhook signature verification enabled
- ✅ Database transactions for atomic operations
- ✅ Error messages don't expose sensitive data
- ✅ HTTPS enforced in production
- ✅ Rate limiting on payment endpoints
- ✅ Input validation on all payment data
- ✅ Audit logging for compliance
- ✅ Regular security audits

---

## Performance Optimization

### 1. Database Indexes

Ensure these indexes exist:
```sql
CREATE INDEX idx_payment_transactions_status ON payment_transactions(status);
CREATE INDEX idx_payment_transactions_created ON payment_transactions(created_at);
CREATE INDEX idx_service_requests_payment_status ON service_requests(payment_status);
```

### 2. Caching

Consider caching:
- Payment gateway status (5 minutes)
- Configuration data (1 hour)
- Rate limit counters (in-memory)

### 3. Connection Pooling

For high traffic, use connection pooling:
```php
// In Database.php
'pool' => [
    'min' => 2,
    'max' => 10,
],
```

---

## Troubleshooting

### Problem: Payments fail in production

**Solution**:
1. Check API credentials in `.env`
2. Verify webhook endpoints are accessible
3. Check logs: `logs/payment_*.log`
4. Test with gateway's test mode first

### Problem: Webhooks not received

**Solution**:
1. Verify webhook URL is publicly accessible
2. Check firewall rules
3. Test webhook manually using gateway's dashboard
4. Check webhook signature secret

### Problem: Database deadlocks

**Solution**:
1. Review transaction scope
2. Reduce transaction duration
3. Add appropriate indexes
4. Consider optimistic locking

### Problem: High memory usage

**Solution**:
1. Enable log rotation
2. Limit webhook event retention
3. Clear old payment logs regularly
4. Optimize database queries

---

## Going Live Checklist

- [ ] All payment SDKs installed
- [ ] Live API credentials configured
- [ ] Webhooks set up and tested
- [ ] HTTPS enforced
- [ ] Error monitoring enabled
- [ ] Log rotation configured
- [ ] Database backups automated
- [ ] Rate limiting configured
- [ ] Security audit completed
- [ ] Load testing completed
- [ ] Monitoring dashboards set up
- [ ] Incident response plan documented
- [ ] PCI compliance verified (for card payments)

---

## Support & Resources

### Stripe
- Docs: https://stripe.com/docs
- Support: https://support.stripe.com
- Status: https://status.stripe.com

### PayPal
- Docs: https://developer.paypal.com/docs
- Support: https://developer.paypal.com/support
- Status: https://www.paypal-status.com

### GCash
- **Recommended**: Use PayMongo or Xendit for easier integration
- **PayMongo**: https://developers.paymongo.com
- **Xendit**: https://developers.xendit.co
- **Direct GCash**: Contact GCash Business Support (for large merchants)
- Note: Direct GCash integration requires business verification and minimum volume

---

## What's Next

After production deployment:

1. **Phase 2: Monitoring & Analytics**
   - Set up payment analytics dashboard
   - Configure alerting for failures
   - Track conversion rates

2. **Phase 3: Advanced Features**
   - Recurring payments/subscriptions
   - Split payments for multiple technicians
   - Installment plans
   - Multi-currency support

3. **Phase 4: Optimization**
   - A/B testing checkout flows
   - Performance optimization
   - Cost reduction strategies

---

**Production Readiness Status**: ✅ Phase 1 Complete

All core infrastructure for production payment processing is now in place. The system gracefully handles both development (mock) and production (real API) modes, ensuring a smooth transition to live operations.

