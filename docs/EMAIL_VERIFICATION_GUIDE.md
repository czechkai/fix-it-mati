# Email Verification Registration System

## Overview

The registration form has been upgraded to a **3-step email verification system** to ensure email validity before account creation. This guide explains how the system works and how to configure it.

## Registration Flow

### Step 1: Personal Information
- First Name
- Last Name  
- Phone Number
- Street Address
- Barangay (dropdown with all 26 Mati City barangays)
- City (pre-filled with Mati)

**Validation:** All fields required. Proceeds to Step 2 when "Next" is clicked.

### Step 2: Security & Email
- Password (with automatic complexity validation)
  - Minimum 8 characters
  - At least 1 number
  - At least 1 symbol (!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?")
  - Requirements auto-hide when all criteria met
- Confirm Password (must match)
- Email Address
- **Send Verification Code** button

**Validation:** 
- Password meets complexity requirements
- Confirm password matches
- Email format valid
- Email not already registered

Once valid, clicking "Send Code" triggers email sending and moves to Step 3.

### Step 3: Email Verification
- 6-digit verification code input (auto-formats digits only)
- Displays email address where code was sent
- **Resend Code** button (disabled for 60 seconds after initial send)
- **Verify & Create Account** button

**Validation:**
- Code must be exactly 6 digits
- Code must match what was sent (valid for 15 minutes)
- Maximum 5 incorrect attempts before requiring new code
- If code expires or attempts exceeded, user can resend

## Email Configuration

### Default Configuration (PHP mail())

By default, the system uses PHP's `mail()` function, which uses your server's configured mail handler. No additional setup required.

### SMTP Configuration (Recommended)

For production, configure SMTP in `config/mail.php`:

#### Option 1: Environment Variables

Set these in your `.env` or server environment:

```
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=587
MAIL_USERNAME=your_mailtrap_username
MAIL_PASSWORD=your_mailtrap_password
MAIL_ENCRYPTION=tls
MAIL_FROM=noreply@fixitmati.local
```

#### Option 2: Direct Configuration

Edit `config/mail.php`:

```php
return [
    'from_email' => 'noreply@fixitmati.local',
    'from_name' => 'FixItMati',
    
    'smtp' => [
        'host' => 'smtp.mailtrap.io',
        'port' => 587,
        'username' => 'your_username',
        'password' => 'your_password',
        'encryption' => 'tls',
    ],
];
```

### Email Services Tested

**Mailtrap** (Development):
- Website: https://mailtrap.io
- Recommended for testing/development
- Free tier available
- SMTP settings found in Account Settings

**Gmail/Google Workspace**:
- Use `smtp.gmail.com` on port 587
- Enable "Less secure app access" or use App Passwords
- Encryption: `tls`

**SendGrid**:
- Host: `smtp.sendgrid.net`
- Port: 587
- Username: `apikey`
- Password: Your SendGrid API key
- Encryption: `tls`

**AWS SES**:
- Host: `email-smtp.us-east-1.amazonaws.com` (replace region)
- Port: 587
- Username & Password: SMTP credentials from AWS Console
- Encryption: `tls`

## API Endpoints

### 1. Send Verification Code
```
POST /api/auth/send-verification-code
Content-Type: application/json

{
  "email": "user@example.com"
}
```

**Response (Success):**
```json
{
  "success": true,
  "data": {
    "email": "user@example.com",
    "message": "Verification code sent successfully"
  },
  "message": "Verification code sent to user@example.com"
}
```

**Response (Error):**
```json
{
  "success": false,
  "message": "This email is already registered",
  "status": 400
}
```

**Backend Logic:**
- Generates 6-digit code
- Stores in `$_SESSION['verification']` with 15-minute expiration
- Sends email with HTML template
- Returns 400 if email already registered

### 2. Verify Code
```
POST /api/auth/verify-code
Content-Type: application/json

{
  "email": "user@example.com",
  "code": "123456"
}
```

**Response (Success):**
```json
{
  "success": true,
  "data": {
    "email": "user@example.com"
  },
  "message": "Email verified successfully"
}
```

**Response (Error):**
```json
{
  "success": false,
  "message": "Invalid verification code. 4 attempts remaining.",
  "status": 400
}
```

**Validations:**
- Code must match (case-sensitive)
- Code must not be expired (15 minutes)
- Email must match the one code was sent to
- Maximum 5 attempts

### 3. Verify & Register
```
POST /api/auth/verify-and-register
Content-Type: application/json

{
  "first_name": "Juan",
  "last_name": "Dela Cruz",
  "email": "juan@example.com",
  "password": "SecurePass123!",
  "password_confirmation": "SecurePass123!",
  "phone": "09XX-XXX-XXXX",
  "street": "123 Main St",
  "barangay": "Bagacay",
  "city": "Mati",
  "verification_code": "123456",
  "role": "customer"
}
```

**Response (Success):**
```json
{
  "success": true,
  "data": {
    "user": {
      "id": 123,
      "first_name": "Juan",
      "email": "juan@example.com",
      "role": "customer"
    },
    "token": "eyJhbGc..."
  },
  "message": "Account created and verified successfully"
}
```

**Response (Error):**
```json
{
  "success": false,
  "message": "Invalid verification code. 3 attempts remaining.",
  "status": 400
}
```

**Process:**
1. Validates verification code
2. Creates user account
3. Returns JWT token
4. Clears verification session

## Database Changes

No database changes required. Verification codes are stored in PHP sessions (`$_SESSION['verification']`).

For production persistence (across server restarts), you can modify `AuthController::sendVerificationCode()` to store codes in database:

```php
// Example: Store in database
$db = Database::getInstance();
$db->insert('verification_codes', [
    'email' => $email,
    'code' => $verificationCode,
    'expires_at' => date('Y-m-d H:i:s', time() + (15 * 60)),
    'attempts' => 0
]);
```

## Frontend Implementation

### JavaScript Event Listeners

The registration form includes the following functionality:

1. **Send Code Button** - Validates email, calls API, moves to Step 3
2. **Resend Timer** - 60-second countdown before resend enabled
3. **Code Input** - Auto-formats to digits only, max 6 characters
4. **Verify Button** - Validates code format, submits with form data
5. **Back Button** - Returns to Step 2 to edit email

### API Client Methods

```javascript
// Send verification code
await ApiClient.auth.sendVerificationCode({ email: 'user@example.com' });

// Verify code only (if needed)
await ApiClient.auth.verifyCode({ 
  email: 'user@example.com', 
  code: '123456' 
});

// Verify and register in one call
await ApiClient.auth.verifyAndRegister({
  first_name: 'Juan',
  last_name: 'Dela Cruz',
  email: 'juan@example.com',
  password: 'SecurePass123!',
  password_confirmation: 'SecurePass123!',
  phone: '09XX-XXX-XXXX',
  street: '123 Main St',
  barangay: 'Bagacay',
  city: 'Mati',
  verification_code: '123456',
  role: 'customer'
});
```

## Testing

### Test Registration Flow

1. Navigate to `/register.php`
2. Enter Step 1 details
3. Click "Next" 
4. Enter password (watch auto-validation)
5. Enter email and click "Send Code"
6. Check terminal/mailbox for 6-digit code
7. Enter code in Step 3
8. Click "Verify & Create Account"

### Test Email Sending

Using Mailtrap for testing:
```bash
# Check that emails appear in Mailtrap inbox
# Visit https://mailtrap.io and check your inbox
```

### Test Error Cases

**Case 1: Code expires**
- Send code
- Wait 15 minutes
- Try to verify → Should show "code expired" message

**Case 2: Too many attempts**
- Send code
- Enter wrong code 5 times
- Try again → Should show "too many attempts" message

**Case 3: Email already registered**
- Try to send code to existing user email
- Should show error: "This email is already registered"

## Troubleshooting

### "Verification code sent to email..." but no email received

1. Check SMTP configuration in `config/mail.php`
2. Check server error logs: `logs/`
3. If using Mailtrap, verify credentials
4. Test with direct PHP mail: `php -r "mail('test@example.com', 'Test', 'Test');"`

### Code not working despite being correct

1. Verify code within 15 minutes of sending
2. Check that email matches the one code was sent to
3. Ensure no extra spaces in code input
4. Maximum 5 attempts before resend required

### "Email already registered" for new email

1. Check if email exists in database: `SELECT * FROM users WHERE email='user@example.com'`
2. Clear any cached user data
3. Use different email address

## Security Considerations

✅ **Implemented:**
- 6-digit verification codes (1 in 1,000,000)
- 15-minute expiration on codes
- 5-attempt maximum before requiring resend
- Email format validation
- Password complexity requirements (8+ chars, 1 number, 1 symbol)
- Session-based code storage

🔒 **Recommendations for Production:**
- Enable HTTPS/SSL for all auth endpoints
- Use database storage instead of session for codes (survives restarts)
- Add rate limiting to prevent brute force
- Log all verification attempts
- Consider CAPTCHA for repeated failures
- Set secure session cookies (HttpOnly, Secure flags)

## File Changes

### Modified Files:
- `public/pages/auth/register.php` - Added Step 3 UI and event handlers
- `assets/api-client.js` - Added verification methods
- `Controllers/AuthController.php` - Added verification endpoints
- `public/api/index.php` - Added route definitions
- `Services/AuthService.php` - Added email sending methods

### New Files:
- `config/mail.php` - Email configuration

## Future Enhancements

1. **Database Persistence** - Store codes in DB for multi-server deployments
2. **Email Templates** - Create customizable HTML email templates
3. **Two-Factor Auth** - Extend to post-login verification
4. **SMS Codes** - Add SMS verification as alternative
5. **Code Resend Limits** - Track resend attempts per email
6. **User Registration Events** - Trigger webhooks on successful registration

## References

- [PHPMailer](https://github.com/PHPMailer/PHPMailer) - Professional email library
- [Mailtrap](https://mailtrap.io) - Email testing service
- [OWASP Authentication Cheat Sheet](https://cheatsheetseries.owasp.org/cheatsheets/Authentication_Cheat_Sheet.html)
