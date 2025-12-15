# PHPMailer Setup for Email Verification - FixItMati

This guide will help you set up PHPMailer to send email verification codes using Gmail's SMTP server.

## 📋 Requirements

- PHP 5.5.0 or higher
- Composer (for easy installation)
- Gmail account with 2-Factor Authentication enabled
- Application-specific password for Gmail

---

## 🚀 Step 1: Install PHPMailer via Composer

### Option A: Using Composer (Recommended)

Open terminal/command prompt in your project root and run:

```bash
cd d:\wamp64\www\fix-it-mati
composer require phpmailer/phpmailer
```

### Option B: Manual Installation

If Composer is not available:

1. Download PHPMailer from: https://github.com/PHPMailer/PHPMailer/releases
2. Extract the ZIP file
3. Copy the `src` folder to: `vendor/phpmailer/phpmailer/src`
4. Ensure autoload.php includes PHPMailer

---

## 🔐 Step 2: Set Up Gmail App Password

### Why App Passwords?
Gmail blocks standard password authentication for security reasons. You need to create an **App Password** instead.

### How to Generate:

1. **Enable 2-Factor Authentication** on your Google Account:
   - Go to: https://myaccount.google.com/security
   - Click "2-Step Verification"
   - Follow the setup process

2. **Generate App Password**:
   - Go to: https://myaccount.google.com/apppasswords
   - Select: **Mail** and **Windows Computer** (or your device)
   - Google will generate a 16-character password
   - **Copy this password** (you'll need it next)

### Example Generated Password:
```
abcd efgh ijkl mnop
```
(Remove spaces when using)

---

## ⚙️ Step 3: Configure PHPMailer in FixItMati

### Edit `config/mail.php`

Open the file: `d:\wamp64\www\fix-it-mati\config\mail.php`

Replace the SMTP configuration with:

```php
<?php
return [
    // Email from address
    'from_email' => 'your-email@gmail.com',
    'from_name' => 'FixItMati',

    // Gmail SMTP Configuration
    'smtp' => [
        'host' => 'smtp.gmail.com',
        'port' => 587,
        'username' => 'your-email@gmail.com',
        'password' => 'your-app-password',  // 16-character password from Step 2
        'encryption' => 'tls',
    ],
];
```

### Example:
```php
<?php
return [
    'from_email' => 'juan.delacruz@gmail.com',
    'from_name' => 'FixItMati',

    'smtp' => [
        'host' => 'smtp.gmail.com',
        'port' => 587,
        'username' => 'juan.delacruz@gmail.com',
        'password' => 'abcdefghijklmnop',  // 16-char password without spaces
        'encryption' => 'tls',
    ],
];
```

---

## ✅ Step 4: Test Email Sending

### Create a test file: `test-email.php`

```php
<?php
// Test Email Sending
require_once __DIR__ . '/autoload.php';

use FixItMati\Services\AuthService;

$authService = AuthService::getInstance();
$testEmail = 'your-test-email@gmail.com';
$testCode = '123456';

$result = $authService->sendVerificationEmail($testEmail, $testCode);

if ($result) {
    echo "✅ Email sent successfully!";
} else {
    echo "❌ Failed to send email. Check error logs.";
}
?>
```

Run in browser:
```
http://localhost/fix-it-mati/test-email.php
```

---

## 🔑 Registration Flow with Email Verification

### Now when users register:

1. **Fill Registration Form**
   - Step 1: Personal Information (Name, Phone, Address)
   - Step 2: Password & Email

2. **Click "Create Account"**
   - System validates all fields
   - **PHPMailer sends 6-digit code to email**
   - Moves to Step 3

3. **Step 3: Verify Email**
   - User enters 6-digit code from email
   - Code verified against database
   - Account created with verification complete

4. **Login**
   - Redirected to login page
   - Can now log in with email + password

---

## 🚨 Common Issues & Fixes

### ❌ Error: "Could not instantiate mail function"
**Solution**: PHPMailer not installed
```bash
composer require phpmailer/phpmailer
```

### ❌ Error: "SMTP Connect failed"
**Solutions**:
- Check credentials in `config/mail.php`
- Verify port is 587 (not 25 or 465)
- Ensure Gmail App Password is correct
- Check firewall isn't blocking SMTP

### ❌ Error: "Username and Password not accepted"
**Solutions**:
- Ensure you're using **App Password** (not regular Gmail password)
- Go to https://myaccount.google.com/apppasswords again
- Remove spaces from the password
- Both username and password must be exactly right

### ❌ Error: "Not authenticated"
**Solution**: Enable "Less secure app access"
- Go to: https://myaccount.google.com/lesssecureapps
- Turn ON "Less secure app access"
- (Only if App Password method fails)

### ❌ Email not received
**Check**:
1. Spam folder
2. `from_email` is correct
3. Gmail inbox filters
4. Check logs: `logs/` directory

---

## 📧 Alternative Email Providers

### If you don't want to use Gmail:

#### **Mailtrap** (Development/Testing)
```php
'smtp' => [
    'host' => 'smtp.mailtrap.io',
    'port' => 587,
    'username' => 'your_inbox_username',
    'password' => 'your_inbox_password',
    'encryption' => 'tls',
],
```

#### **SendGrid** (Production)
```php
'smtp' => [
    'host' => 'smtp.sendgrid.net',
    'port' => 587,
    'username' => 'apikey',
    'password' => 'SG.your_api_key_here',
    'encryption' => 'tls',
],
```

#### **AWS SES**
```php
'smtp' => [
    'host' => 'email-smtp.us-east-1.amazonaws.com',
    'port' => 587,
    'username' => 'your_ses_username',
    'password' => 'your_ses_password',
    'encryption' => 'tls',
],
```

---

## 🔐 Security Best Practices

1. **Never commit credentials to Git**
   - Add `config/mail.php` to `.gitignore`
   - Use environment variables in production

2. **Use App Passwords, not Gmail passwords**
   - More secure
   - Can be revoked without changing Gmail password

3. **Verify SSL/TLS**
   - Always use `encryption: 'tls'` with port 587
   - Or `encryption: 'ssl'` with port 465

4. **Log emails for audit trail**
   - Check `/logs/` directory
   - Review error logs regularly

---

## 🎯 Quick Checklist

- [ ] PHPMailer installed (`composer require phpmailer/phpmailer`)
- [ ] Gmail 2-Factor Authentication enabled
- [ ] App Password generated from Google Account
- [ ] `config/mail.php` updated with credentials
- [ ] `from_email` is a real Gmail address
- [ ] Port is 587 and encryption is 'tls'
- [ ] Test email sent successfully
- [ ] Users can register and receive codes
- [ ] Users can verify and create accounts
- [ ] Users can login after verification

---

## 📞 Need Help?

### Common Resources:
- PHPMailer Documentation: https://github.com/PHPMailer/PHPMailer
- Gmail SMTP Setup: https://support.google.com/accounts/answer/185833
- Troubleshooting: Check `/logs/` directory in project root

### Debug Mode:
Add this to `Services/AuthService.php` `sendViaPhpMailer()` method:

```php
$mail->SMTPDebug = 2; // Enable debug output
```

This will show detailed SMTP conversation in error logs.

---

## ✨ What's Next?

After email verification is working:

1. **Test Full Registration Flow**
   - Go to http://localhost/fix-it-mati/register.php
   - Fill form and submit
   - Check email for code
   - Enter code to verify

2. **Monitor Email Logs**
   - Check `logs/` directory
   - Review error messages
   - Monitor SMTP responses

3. **Production Deployment**
   - Use environment variables for credentials
   - Enable SSL/TLS on server
   - Set up error logging
   - Monitor email delivery

---

**Version**: 1.0  
**Last Updated**: December 2025  
**Status**: ✅ Ready for Production

