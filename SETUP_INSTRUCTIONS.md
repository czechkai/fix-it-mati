# 🚀 QUICK START - Email Verification Setup

## ✅ What's Done

Your registration system is **100% ready**. The only thing left is:

1. Install PHPMailer
2. Configure Gmail credentials
3. Test it!

---

## 📝 Changes Made to Register Form

### **Before**:
```
Step 2:
- Password field
- Confirm Password field
- Email field with "Send Code" button ← REMOVED
- Create Account button (only validated, didn't send email)
```

### **After**:
```
Step 2:
- Password field  
- Confirm Password field
- Email field (no button) ← "Send Code" removed
- Create Account button (now sends verification code via email!)

Step 3:
- Enter 6-digit code from email
- Verify button (creates account if code matches)
- Redirect to login
```

---

## 🔧 What You Need to Do (3 Easy Steps)

### **Step 1: Install PHPMailer** (2 minutes)

Open Command Prompt/PowerShell and run:

```bash
cd d:\wamp64\www\fix-it-mati
composer require phpmailer/phpmailer
```

If you don't have Composer:
- Download from: https://getcomposer.org
- Install it
- Then run the command above

---

### **Step 2: Get Gmail App Password** (5 minutes)

1. **Go to**: https://myaccount.google.com
2. **Enable 2-Factor Authentication** (if not already):
   - Click "Security" on left
   - Find "2-Step Verification"
   - Follow setup
3. **Generate App Password**:
   - Go to: https://myaccount.google.com/apppasswords
   - Select: **Mail** and **Windows Computer**
   - Copy the 16-character password (e.g., `abcd efgh ijkl mnop`)
   - Remove spaces: `abcdefghijklmnop`

---

### **Step 3: Update Configuration** (1 minute)

Edit file: `d:\wamp64\www\fix-it-mati\config\mail.php`

```php
<?php
return [
    'from_email' => 'your-email@gmail.com',  // Your Gmail address
    'from_name' => 'FixItMati',

    'smtp' => [
        'host' => 'smtp.gmail.com',
        'port' => 587,
        'username' => 'your-email@gmail.com',  // Your Gmail address
        'password' => 'abcdefghijklmnop',      // 16-char app password (no spaces!)
        'encryption' => 'tls',
    ],
];
```

**Example**:
```php
<?php
return [
    'from_email' => 'mati.contact@gmail.com',
    'from_name' => 'FixItMati',

    'smtp' => [
        'host' => 'smtp.gmail.com',
        'port' => 587,
        'username' => 'mati.contact@gmail.com',
        'password' => 'zyxwvutsrqponmlk',  // 16-char app password
        'encryption' => 'tls',
    ],
];
```

---

## ✅ Test It

1. **Go to registration page**:
   ```
   http://localhost/fix-it-mati/public/pages/auth/register.php
   ```

2. **Fill the form**:
   - Step 1: Any name, phone, address
   - Step 2: 
     - Password: `Test@1234` (or similar with number + symbol)
     - Email: Your actual email
     - Check Terms
   
3. **Click "Create Account"**:
   - Should show "Sending Code..." 
   - After 2-3 seconds, goes to Step 3
   
4. **Check your email**:
   - Look in inbox for FixItMati email
   - Copy the 6-digit code

5. **Enter code in Step 3**:
   - Paste the code
   - Click "Verify"
   - Should say "Account Created!" and redirect to login
   
6. **Login**:
   - Email + Password you just created
   - Should work! ✅

---

## 🎯 Expected User Flow

```
User Registration:
1. Fill personal info (Step 1)
2. Fill password + email (Step 2)
3. Click "Create Account"
   ↓
   [System sends code to email via Gmail SMTP]
   ↓
4. Enter 6-digit code (Step 3)
5. Click "Verify"
   ↓
   [System creates account]
   ↓
6. Redirect to login page
7. Can now login with email + password ✅
```

---

## 📧 What the User Gets

### Email from FixItMati:

```
From: noreply@fixitmati.local
Subject: Email Verification Code - FixItMati

---

Hello!

Thank you for registering with FixItMati! 
Your verification code is:

     123456

This code will expire in 15 minutes.

If you didn't request this code, please ignore this email.

© 2024 FixItMati. All rights reserved.
```

---

## 🔐 Security Features Built-In

✅ **Password Validation**
- Shows error while typing if missing requirements
- 8+ characters, 1 number, 1 symbol required

✅ **Email Verification**
- 6-digit code (random)
- 15-minute expiration
- Max 5 wrong attempts
- Prevents fake accounts

✅ **Encrypted Communication**
- Gmail TLS encryption
- Passwords hashed with bcrypt
- Email hashed in database

---

## ⚡ If It Doesn't Work

### Email not sending?

**Check 1**: PHPMailer installed?
```bash
cd d:\wamp64\www\fix-it-mati
composer show phpmailer/phpmailer
```
If error: run `composer require phpmailer/phpmailer`

**Check 2**: Gmail credentials correct?
- Open `config/mail.php`
- Verify email and app password (16 chars, no spaces)
- Make sure it's app password, not Gmail password

**Check 3**: Gmail settings?
- Go to: https://myaccount.google.com/apppasswords
- Regenerate password if needed
- Copy exactly (remove spaces)

**Check 4**: Look at error logs
- Check `/logs/` directory
- Look for "phpmailer" or "SMTP" errors
- Share error message

---

## 📚 Helpful Documents

Inside your project:

1. **`PHPMAILER_GMAIL_SETUP.md`**
   - Detailed setup guide
   - Troubleshooting section
   - Alternative providers (SendGrid, AWS SES, etc)

2. **`EMAIL_VERIFICATION_COMPLETE.md`**
   - Complete implementation details
   - API documentation
   - Testing procedures

3. **`PHPMAILER_SETUP.md`**
   - General PHPMailer info
   - Multiple provider configs

---

## ✨ That's It!

You're done with the setup. The system is ready to:
- ✅ Accept registrations
- ✅ Send verification emails
- ✅ Create verified accounts
- ✅ Allow users to login

Just:
1. Install PHPMailer
2. Configure Gmail
3. Test it!

**Need help?** Check the detailed docs mentioned above.

---

**Status**: ✅ Implementation Complete  
**Ready for**: Testing & Deployment  
**Last Updated**: December 15, 2025
