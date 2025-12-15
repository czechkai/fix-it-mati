# PHPMailer Setup Guide

Your registration system is configured to send verification emails using **PHPMailer** (with fallback to PHP's `mail()` function).

## 🚀 Quick Setup (5 minutes)

### Step 1: Install PHPMailer via Composer

```bash
composer require phpmailer/phpmailer
```

**Don't have Composer?** Download it from [getcomposer.org](https://getcomposer.org)

### Step 2: Choose Your Email Provider

#### Option A: **Mailtrap** (Recommended for Testing) ✅
- **Free tier available**: Perfect for development
- **No production emails**: Captures emails in a virtual inbox
- **Easy setup**: No domain verification

1. Sign up at [mailtrap.io](https://mailtrap.io)
2. Create an inbox
3. Copy SMTP credentials (Host, Port, Username, Password)
4. Update `config/mail.php` or set environment variables

```php
'smtp' => [
    'host' => 'smtp.mailtrap.io',
    'port' => 587,
    'username' => 'YOUR_INBOX_USERNAME',
    'password' => 'YOUR_INBOX_PASSWORD',
    'encryption' => 'tls',
],
```

#### Option B: **Gmail** (Easy for Small Apps)
- **Requires 2FA enabled** on your Google account
- **Generate App Password** instead of using your regular password

1. Enable 2-factor authentication on your Google account
2. Go to [myaccount.google.com/apppasswords](https://myaccount.google.com/apppasswords)
3. Generate a password for "Mail"
4. Use it in `config/mail.php`:

```php
'smtp' => [
    'host' => 'smtp.gmail.com',
    'port' => 587,
    'username' => 'your.email@gmail.com',
    'password' => 'your_generated_app_password',
    'encryption' => 'tls',
],
```

#### Option C: **SendGrid** (For Production)
- **Production-ready**: Built for high volume
- **Free tier**: 100 emails/day

1. Sign up at [sendgrid.com](https://sendgrid.com)
2. Create an API key
3. Update `config/mail.php`:

```php
'smtp' => [
    'host' => 'smtp.sendgrid.net',
    'port' => 587,
    'username' => 'apikey',  // Always 'apikey'
    'password' => 'SG.YOUR_SENDGRID_API_KEY',
    'encryption' => 'tls',
],
```

#### Option D: **AWS SES** (For High Volume)
- **Scalable**: Best for thousands of emails
- **Requires AWS account** and SMTP credentials

```php
'smtp' => [
    'host' => 'email-smtp.us-east-1.amazonaws.com', // Change region if needed
    'port' => 587,
    'username' => 'YOUR_SES_SMTP_USERNAME',
    'password' => 'YOUR_SES_SMTP_PASSWORD',
    'encryption' => 'tls',
],
```

### Step 3: Update Configuration

Edit `config/mail.php` and update the SMTP section with your chosen provider's credentials:

```php
'smtp' => [
    'host' => 'smtp.your-provider.com',
    'port' => 587,
    'username' => 'your_username',
    'password' => 'your_password',
    'encryption' => 'tls',
],
```

**OR** Use environment variables (recommended for security):

Create a `.env` file in your project root:
```
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=587
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM=noreply@fixitmati.local
```

Then load it in your code. Your `config/mail.php` already uses `getenv()` to read these.

### Step 4: Test Email Sending

1. Go to `/register.php`
2. Fill in the registration form:
   - Name, Email, Phone, Address
   - Password (8+ chars, 1 number, 1 symbol)
3. Click "Send Verification Code"
4. Check your email for the 6-digit code
5. Enter the code in the modal and click "Verify"
6. Account created! ✅

## 📧 Email Configuration Details

### How It Works:

```
User fills form → Clicks "Send Verification Code"
                      ↓
         AuthController → sendVerificationCode()
                      ↓
         Services\AuthService → sendVerificationEmail()
                      ↓
         Try PHPMailer → If not installed, use mail()
                      ↓
         Email sent with 6-digit code
                      ↓
         User enters code in modal → Clicks "Verify"
                      ↓
         Account created successfully!
```

### Email Template:

The verification email includes:
- Professional HTML design
- 6-digit code prominently displayed
- 15-minute expiration notice
- Company branding (FixItMati)

## 🔧 Advanced Configuration

### Using Environment Variables with .env

Install `vlucas/phpdotenv` via Composer:
```bash
composer require vlucas/phpdotenv
```

Load in your bootstrap/index file:
```php
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
```

### Custom Email Template

Edit `Services/AuthService.php` - `sendVerificationEmail()` method:

```php
$html = '<div style="...">
    Your custom HTML here
    <h2>' . $verificationCode . '</h2>
</div>';
```

### Using Queue System (Advanced)

For high-volume apps, queue emails instead of sending synchronously:

```php
// In your mail config
'queue' => true,

// Then send emails asynchronously (requires job queue setup)
```

## 🐛 Troubleshooting

### "PHPMailer not found" error?
- Run: `composer require phpmailer/phpmailer`
- Check: `vendor/autoload.php` is included in your code

### "SMTP connection failed"?
- Verify SMTP credentials are correct
- Check port is 587 (not 25 or 465 unless using SSL)
- Ensure your host allows SMTP connections
- Test with Mailtrap first (most reliable)

### "Email not received"?
- Check spam folder
- Verify `from_email` in `config/mail.php`
- Check email provider's logs
- Try Mailtrap to capture and debug

### "SSL certificate error"?
- Use port 587 with TLS (not 465)
- Or update config to allow self-signed certs (not recommended for production)

## 📋 Checklist

- [ ] Installed PHPMailer: `composer require phpmailer/phpmailer`
- [ ] Chose email provider (Mailtrap/Gmail/SendGrid/AWS SES)
- [ ] Got SMTP credentials from provider
- [ ] Updated `config/mail.php` with credentials
- [ ] Tested registration flow
- [ ] Received verification email
- [ ] Verified code works
- [ ] Account creation successful

## 🎉 You're Done!

Your email verification system is now fully functional. Users can:
1. Create accounts with strong passwords
2. Receive verification codes via email
3. Verify their email with a 6-digit code in the modal
4. Complete registration

For questions or issues, check the error logs in `/logs/` directory.

---

**Need help?**
- Check [PHPMailer documentation](https://github.com/PHPMailer/PHPMailer/wiki)
- Test SMTP with [Mailtrap](https://mailtrap.io)
- Review email sending code in `Services/AuthService.php`
