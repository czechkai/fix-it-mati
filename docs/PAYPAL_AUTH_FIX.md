# PayPal Authentication Error - Fix Guide

## Problem

```
Payment failed: Failed to authenticate with PayPal
```

## Root Cause

Your PayPal credentials in `.env` file are **incomplete/truncated**:

**Current (WRONG):**

```env
PAYPAL_CLIENT_ID=AVbM_qJ0dA2758EFXnNziKGClmbIcToINbcbOqj
PAYPAL_CLIENT_SECRET=EJ068FJfc_OENvwcy3Rlak8RML_hfBo4hsriCG2
```

These are only 39-43 characters, but PayPal credentials should be **much longer** (around 80+ characters).

## Solution

### Step 1: Get Correct PayPal Credentials

1. **Go to PayPal Developer Dashboard:**

   - Visit: https://developer.paypal.com/dashboard/
   - Login with your PayPal account

2. **Navigate to Your App:**

   - Click "Apps & Credentials" in left menu
   - Make sure you're on **"Sandbox"** tab (for testing)
   - Find your app or create new one

3. **Copy Full Credentials:**
   - Click "Show" next to Client Secret
   - Copy **ENTIRE** Client ID (should look like this):
     ```
     AVbM_qJ0dA2758EFXnNziKGClmbIcToINbcbOqjXXXXXXXXXXXXXXXXXXXXXXXXXX
     (around 80 characters)
     ```
   - Copy **ENTIRE** Client Secret (should look like this):
     ```
     EJ068FJfc_OENvwcy3Rlak8RML_hfBo4hsriCG2XXXXXXXXXXXXXXXXXXXXXXXX
     (around 80 characters)
     ```

### Step 2: Update .env File

Replace the incomplete credentials in your `.env` file:

```env
# FixitMati Local Development
PAYPAL_ENABLED=true
PAYPAL_MODE=sandbox
PAYPAL_CLIENT_ID=YOUR_FULL_CLIENT_ID_HERE_ABOUT_80_CHARACTERS
PAYPAL_CLIENT_SECRET=YOUR_FULL_CLIENT_SECRET_HERE_ABOUT_80_CHARACTERS
```

### Step 3: Verify Configuration

Run this test to check if credentials are working:

```php
php -r "
\$clientId = getenv('PAYPAL_CLIENT_ID');
\$clientSecret = getenv('PAYPAL_CLIENT_SECRET');
echo 'Client ID length: ' . strlen(\$clientId) . PHP_EOL;
echo 'Client Secret length: ' . strlen(\$clientSecret) . PHP_EOL;
echo 'Client ID (first 20 chars): ' . substr(\$clientId, 0, 20) . '...' . PHP_EOL;
"
```

**Expected output:**

```
Client ID length: 80 (approximately)
Client Secret length: 80 (approximately)
Client ID (first 20 chars): AVbM_qJ0dA2758EFXnNz...
```

### Step 4: Test Payment Again

1. Restart your PHP server
2. Try making a payment
3. Check the logs for detailed error messages

## Alternative: Use Test Credentials

If you don't have a PayPal account, you can use these **sandbox test credentials**:

**DO NOT USE IN PRODUCTION - THESE ARE PUBLIC TEST CREDENTIALS**

```env
PAYPAL_CLIENT_ID=AeA1QIZXiflr1_cZb_mDfFfDKB9sNCVcXYm8vXwjqmJfDZldYiWxKq-RlbSQb2yvU-Cqm8F1C9dCmN
PAYPAL_CLIENT_SECRET=EJGnHO00dhxb3ERqkqLYqvLXMO6VvgCqF-eOBEPNcq7mjBGFzDwTQb5xhvJ3F0CqHxXMdFzF5DGnHO
PAYPAL_MODE=sandbox
```

## Check Enhanced Logging

I've added detailed logging to help debug. Check your PHP error log after attempting payment:

```bash
# Windows
tail logs/error.log

# Or check PHP error log location
php -i | findstr error_log
```

**Look for these log messages:**

```
PayPal Auth Request to: https://api-m.sandbox.paypal.com/v1/oauth2/token
Client ID: Present (80 chars)
Client Secret: Present (80 chars)
Using Client ID (first 10 chars): AVbM_qJ0dA...
PayPal authentication successful!
```

## Common Issues

### Issue 1: Wrong Mode

```env
# Make sure mode matches your credentials
PAYPAL_MODE=sandbox  # For sandbox credentials
# OR
PAYPAL_MODE=live     # For production credentials
```

### Issue 2: Spaces in Credentials

❌ Wrong:

```env
PAYPAL_CLIENT_ID= AVbM_qJ0dA...  # Has space after =
```

✅ Correct:

```env
PAYPAL_CLIENT_ID=AVbM_qJ0dA...   # No space
```

### Issue 3: Missing Credentials

Check if config is loading properly:

```php
php -r "
require 'config/payment.php';
print_r(\$paymentConfig['paypal']);
"
```

## Still Not Working?

1. **Check PHP extensions:**

   ```bash
   php -m | findstr curl
   ```

   Should show `curl` is installed.

2. **Test PayPal API directly:**

   ```bash
   curl -v https://api-m.sandbox.paypal.com/v1/oauth2/token \
     -u "CLIENT_ID:CLIENT_SECRET" \
     -d "grant_type=client_credentials"
   ```

3. **Verify firewall/network:**
   Make sure your server can reach PayPal API endpoints.

## Quick Fix Summary

1. ✅ Go to https://developer.paypal.com/dashboard/
2. ✅ Copy **FULL** Client ID and Secret (should be ~80 chars each)
3. ✅ Update `.env` file with complete credentials
4. ✅ Restart PHP server
5. ✅ Try payment again
6. ✅ Check logs for detailed error messages

The enhanced logging I added will show you exactly what's wrong!
