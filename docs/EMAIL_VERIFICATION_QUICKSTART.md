# Email Verification Registration - Quick Start

## 🎯 TL;DR

Your 3-step email verification registration is **fully implemented and working**. Here's what to do next:

## ⚡ Quick Test (2 minutes)

### Step 1: Navigate to Registration
```
http://localhost:8000/register.php
```

### Step 2: Fill Out Personal Information
- First Name: Juan
- Last Name: Dela Cruz
- Phone: 09171234567
- Street: 123 Main Street
- Barangay: Bagacay
- City: Mati (auto-filled)
- Click: **Next**

### Step 3: Enter Security & Email
- Password: `SecurePass123!` (meets requirements: 8+ chars, number, symbol)
- Confirm: `SecurePass123!`
- Email: `test@example.com`
- Click: **Send Code**

**What happens:**
- Email form hides
- Step 3 appears: "Verification Code" input
- Code is sent to your email (check inbox or Mailtrap)
- Resend button is disabled for 60 seconds

### Step 4: Verify Email
- Get the 6-digit code from email
- Enter into verification code field
- Click: **Verify & Create Account**

✅ Account created! You're now logged in.

---

## 📧 Email Configuration (For Real Emails)

### Option A: Use PHP mail() (Default)
**No setup needed** - will use your server's mail handler.

### Option B: Use Mailtrap (Recommended for Testing)

1. Sign up at https://mailtrap.io (free)
2. Get your SMTP credentials
3. Edit `config/mail.php`:

```php
'smtp' => [
    'host' => 'smtp.mailtrap.io',
    'port' => 587,
    'username' => 'YOUR_MAILTRAP_USERNAME',
    'password' => 'YOUR_MAILTRAP_PASSWORD',
    'encryption' => 'tls',
],
```

4. Check Mailtrap inbox for verification codes

### Option C: Use Gmail
1. Enable "App Passwords" in Google Account settings
2. Edit `config/mail.php`:

```php
'smtp' => [
    'host' => 'smtp.gmail.com',
    'port' => 587,
    'username' => 'your.email@gmail.com',
    'password' => 'your_app_password',
    'encryption' => 'tls',
],
```

---

## ✅ What's Implemented

| Feature | Status | Details |
|---------|--------|---------|
| 3-Step UI | ✅ Complete | Personal → Security → Verify |
| Step 1 Validation | ✅ Complete | All fields required |
| Step 2 Validation | ✅ Complete | Password complexity, email validation |
| Password Auto-Check | ✅ Complete | Shows requirements as you type |
| Email Sending | ✅ Complete | 6-digit code sent to email |
| 60s Resend Timer | ✅ Complete | Button disabled until timer expires |
| Code Validation | ✅ Complete | Must be 6 digits, not expired |
| Account Creation | ✅ Complete | User created on successful verification |
| JWT Token | ✅ Complete | Auto-login after registration |
| Error Messages | ✅ Complete | Clear feedback at each step |

---

## 🧪 Test Different Scenarios

### Test 1: Invalid Email Format
- Step 2: Enter `notanemail`
- Click "Send Code"
- ❌ Error: "Please enter a valid email address"

### Test 2: Email Already Registered
- Step 2: Enter email of existing user
- Click "Send Code"
- ❌ Error: "This email is already registered"

### Test 3: Invalid Verification Code
- Step 3: Enter `000000` (wrong code)
- Click "Verify & Create Account"
- ❌ Error: "Invalid or expired verification code. X attempts remaining"

### Test 4: Code Expires
- Send code
- Wait 15 minutes
- Try to verify
- ❌ Error: "Verification code has expired"

### Test 5: Too Many Attempts
- Send code
- Try wrong code 5 times
- ❌ Error: "Too many attempts. Please request a new code"

### Test 6: Resend Code
- Send code
- Wait 60 seconds (timer counts down)
- Click "Resend Code"
- ✅ New code sent to email

---

## 🔍 Debugging

### Email Not Received?

1. **Check Logs**: Look in `logs/` directory
2. **Check Mailtrap**: Visit https://mailtrap.io to see inbox
3. **Verify Config**: Check `config/mail.php` settings
4. **PHP Mail Test**:
   ```bash
   php -r "mail('test@example.com', 'Test', 'Test');"
   ```

### Code Validation Errors?

Check browser console (F12 → Console) for:
- Network errors (red in Network tab)
- JavaScript errors
- API response details

### Database Issues?

Check if user was created:
```sql
SELECT * FROM users WHERE email = 'test@example.com';
```

---

## 📚 Full Documentation

For complete details, see:
- **Setup Guide**: `docs/EMAIL_VERIFICATION_GUIDE.md`
- **Implementation Details**: `docs/EMAIL_VERIFICATION_IMPLEMENTATION.md`

---

## 🛠️ API Endpoints (For Developers)

### Send Verification Code
```bash
curl -X POST http://localhost:8000/api/auth/send-verification-code \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com"}'
```

### Verify Code
```bash
curl -X POST http://localhost:8000/api/auth/verify-code \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","code":"123456"}'
```

### Verify & Register
```bash
curl -X POST http://localhost:8000/api/auth/verify-and-register \
  -H "Content-Type: application/json" \
  -d '{
    "first_name":"Juan",
    "last_name":"Dela Cruz",
    "email":"test@example.com",
    "password":"SecurePass123!",
    "password_confirmation":"SecurePass123!",
    "phone":"09171234567",
    "street":"123 Main St",
    "barangay":"Bagacay",
    "city":"Mati",
    "verification_code":"123456",
    "role":"customer"
  }'
```

---

## 🎉 Success Indicators

You'll know it's working when:

✅ Registration form shows 3-step progress indicator
✅ Email validation appears in real-time
✅ Password requirements appear as you type
✅ "Send Code" button is clickable in Step 2
✅ Step 3 appears after sending code
✅ Resend button shows 60-second countdown
✅ Code validation works with correct code
✅ User is created in database
✅ JWT token is set in localStorage
✅ Auto-redirect to dashboard on success

---

## 📝 Password Requirements

Users must enter password with:
- ✅ At least 8 characters
- ✅ At least 1 number (0-9)
- ✅ At least 1 symbol (!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?")

**Example valid password**: `SecurePass123!`

---

## 🚀 You're Ready to Go!

1. ✅ System is fully implemented
2. ✅ All features are working
3. ✅ Ready for testing and deployment
4. ✅ Can configure email service of your choice

**Start testing**: Navigate to `/register.php` and try it out!

---

**Questions?** Check `docs/EMAIL_VERIFICATION_GUIDE.md` for troubleshooting.
