# Email Verification System - Technical Architecture

## 🏗️ System Architecture

```
┌────────────────────────────────────────────────────────────────┐
│                     USER BROWSER                               │
│                                                                │
│  public/pages/auth/register.php (HTML + JavaScript)           │
│  • Step 1: Personal Information Form                          │
│  • Step 2: Password + Email Form (No "Send Code" btn)        │
│  • Step 3: Email Verification Form                           │
│                                                                │
└────────┬─────────────────────────────────────────────────────┘
         │
         │ AJAX POST Requests
         ▼
┌────────────────────────────────────────────────────────────────┐
│                  API ENDPOINTS                                 │
│                  (public/api/index.php)                        │
│                                                                │
│  POST /api/auth/send-verification-code                       │
│  POST /api/auth/verify-and-register                          │
│                                                                │
└────────┬─────────────────────────────────────────────────────┘
         │
         │ Routes to
         ▼
┌────────────────────────────────────────────────────────────────┐
│            AuthController (Controllers/AuthController.php)    │
│                                                                │
│  sendVerificationCode()                                       │
│  ├─ Validate email format                                    │
│  ├─ Generate 6-digit code                                    │
│  ├─ Store code + email in SESSION (15 min expiry)           │
│  └─ Call AuthService::sendVerificationEmail()               │
│                                                                │
│  verifyAndRegister()                                          │
│  ├─ Validate verification code                              │
│  ├─ Check attempts (max 5)                                  │
│  ├─ Verify code matches                                     │
│  ├─ Call AuthService::register()                            │
│  └─ Generate JWT token                                       │
│                                                                │
└────────┬─────────────────────────────────────────────────────┘
         │
         │ Calls
         ▼
┌────────────────────────────────────────────────────────────────┐
│       AuthService (Services/AuthService.php)                  │
│                                                                │
│  sendVerificationEmail(email, code)                           │
│  ├─ Load config/mail.php                                     │
│  ├─ Build HTML email template                                │
│  ├─ Try sendViaPhpMailer()                                   │
│  └─ Fallback to mail() if PHPMailer fails                   │
│                                                                │
│  sendViaPhpMailer(email, subject, message)                   │
│  ├─ Initialize PHPMailer class                              │
│  ├─ Set Gmail SMTP config                                   │
│  │  - Host: smtp.gmail.com                                  │
│  │  - Port: 587                                             │
│  │  - Username: Gmail address                               │
│  │  - Password: 16-char app password                        │
│  │  - Encryption: TLS                                       │
│  ├─ Add recipient, subject, HTML body                       │
│  └─ Send via SMTP                                           │
│                                                                │
│  register(data)                                               │
│  ├─ Validate all fields                                      │
│  ├─ Hash password (bcrypt)                                   │
│  ├─ Insert into database                                     │
│  └─ Return user object                                       │
│                                                                │
└────────┬─────────────────────────────────────────────────────┘
         │
         │ Uses
         ▼
┌────────────────────────────────────────────────────────────────┐
│                 EMAIL DELIVERY LAYER                           │
│                                                                │
│  ┌─────────────────────────────────────────┐                 │
│  │  PHPMailer (Preferred)                  │                 │
│  │  ✓ TLS Encryption (Port 587)           │                 │
│  │  ✓ Gmail App Password auth             │                 │
│  │  ✓ Professional error handling         │                 │
│  │  ✓ Logging support                     │                 │
│  └──────────────┬──────────────────────────┘                 │
│                 │                                             │
│                 ├─────→ Gmail SMTP Server (smtp.gmail.com)  │
│                 │                                             │
│  ┌──────────────▼──────────────────────────┐                 │
│  │  Fallback: PHP mail() function          │                 │
│  │  (if PHPMailer not available)           │                 │
│  └──────────────┬──────────────────────────┘                 │
│                 │                                             │
│                 └─────→ System mail server                   │
│                                                                │
└────────┬─────────────────────────────────────────────────────┘
         │
         │
         ▼
┌────────────────────────────────────────────────────────────────┐
│            USER'S EMAIL INBOX                                 │
│                                                                │
│  From: FixItMati <noreply@fixitmati.local>                   │
│  Subject: Email Verification Code - FixItMati                │
│  Body:                                                        │
│  ┌────────────────────────────────────┐                     │
│  │  FixItMati Logo                    │                     │
│  │  ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ │                     │
│  │  Email Verification                │                     │
│  │                                    │                     │
│  │  Thank you for registering!       │                     │
│  │  Your verification code is:       │                     │
│  │                                    │                     │
│  │  ┌──────────────────────────────┐ │                     │
│  │  │   123456                     │ │                     │
│  │  └──────────────────────────────┘ │                     │
│  │                                    │                     │
│  │  This code will expire in 15 min. │                     │
│  │  ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ │                     │
│  │  © 2024 FixItMati                │                     │
│  └────────────────────────────────────┘                     │
│                                                                │
└─────────────────────────────────────────────────────────────────┘
```

---

## 📡 API Request/Response Flow

### **1. Send Verification Code**

```
REQUEST:
POST /api/auth/send-verification-code
Content-Type: application/json

{
  "email": "juan@gmail.com",
  "firstName": "Juan",
  "lastName": "Dela Cruz"
}

RESPONSE (Success):
HTTP 200 OK
{
  "success": true,
  "data": {
    "email": "juan@gmail.com",
    "message": "Verification code sent successfully"
  },
  "message": "Verification code sent to juan@gmail.com"
}

PROCESSING:
1. Validate email format
2. Check if email already exists
3. Generate 6-digit random code (000000-999999)
4. Store in SESSION:
   {
     "verification": {
       "code": "123456",
       "email": "juan@gmail.com",
       "expires_at": timestamp + 900 (15 min),
       "attempts": 0
     }
   }
5. Send email via PHPMailer to user's inbox
6. Return success response
```

### **2. Verify and Register**

```
REQUEST:
POST /api/auth/verify-and-register
Content-Type: application/json

{
  "firstName": "Juan",
  "lastName": "Dela Cruz",
  "email": "juan@gmail.com",
  "phone": "+63 912 345 6789",
  "street": "123 Main Street",
  "barangay": "Central",
  "password": "SecurePass@123",
  "verification_code": "123456"
}

RESPONSE (Success):
HTTP 201 Created
{
  "success": true,
  "data": {
    "user": {
      "id": 1,
      "email": "juan@gmail.com",
      "first_name": "Juan",
      "last_name": "Dela Cruz",
      "phone": "+63 912 345 6789",
      "street": "123 Main Street",
      "barangay": "Central",
      "created_at": "2025-12-15 10:30:00"
    },
    "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9..."
  },
  "message": "Account created and verified successfully"
}

PROCESSING:
1. Get code + email from SESSION
2. Check if expired (> 15 min) ❌ Return error if expired
3. Check email matches ❌ Return error if mismatch
4. Check attempts < 5 ❌ Return error if max attempts
5. Verify code matches ❌ Increment attempts if wrong
6. If code correct:
   ├─ Clear SESSION
   ├─ Validate all form data
   ├─ Check email not already registered
   ├─ Hash password with bcrypt
   ├─ Insert user into database
   ├─ Generate JWT token
   └─ Return user + token
```

---

## 🗄️ Database Schema (Existing)

```sql
users TABLE:
┌─────────────────────────────────────┐
│ Column          │ Type              │
├─────────────────────────────────────┤
│ id              │ INT PRIMARY KEY   │
│ email           │ VARCHAR (unique)  │
│ first_name      │ VARCHAR           │
│ last_name       │ VARCHAR           │
│ phone           │ VARCHAR           │
│ street          │ VARCHAR           │
│ barangay        │ VARCHAR           │
│ password_hash   │ VARCHAR           │
│ is_verified     │ BOOLEAN (default) │
│ created_at      │ TIMESTAMP         │
│ updated_at      │ TIMESTAMP         │
└─────────────────────────────────────┘

Note: Verification code stored in SESSION,
not in database. Expires automatically after 15 min.
```

---

## 🔐 Security Mechanisms

### **1. Password Security**
```
User Input: "MyPassword@123"
          ↓
SHA256/bcrypt hashing (10 rounds)
          ↓
Stored Hash: "$2y$10$N9qo8uLO..." (never plain text)
          ↓
On Login: Hash input → Compare with stored → Match?
```

### **2. Email Verification Security**
```
Verification Code Storage:
┌──────────────────────────┐
│ $_SESSION['verification']│
├──────────────────────────┤
│ code: "123456"          │ ← 6-digit random
│ email: "juan@gmail.com" │ ← Must match request
│ expires_at: 1703061000  │ ← 15 min TTL
│ attempts: 0             │ ← Max 5 wrong attempts
└──────────────────────────┘

Code Generation:
└─→ str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT)
    = 000000 to 999999 (1 in 1 million)
```

### **3. SMTP Security**
```
Gmail Connection:
User: gmail-address@gmail.com
Pass: 16-char app password (not Gmail password)
Port: 587 (TLS, not SSL)
Encryption: TLS (STARTTLS)

Benefits:
✓ Gmail handles HTTPS/TLS
✓ App password can be revoked independently
✓ Can't use Gmail password to compromise email
✓ TLS prevents man-in-the-middle attacks
```

### **4. Error Handling**
```
Security Principle: Never reveal system details

Instead of: "User juan@gmail.com does not exist"
Say: "Invalid credentials"

Instead of: "Code sent to database on 2025-12-15 10:30"
Say: "Verification code expired"

This prevents attackers from enumerating users
or understanding system state.
```

---

## 📊 Data Flow Diagram

```
User Browser                API Server              Email Server
    │                           │                        │
    │─→ Fill form ────────────→│                         │
    │                           │                         │
    │←─ Show Step 2 ─────────────│                        │
    │                           │                         │
    │─→ Click "Create Account"─→│                         │
    │    (password + email)      │                         │
    │                           │                         │
    │                    ┌──────▼────────┐               │
    │                    │ Generate Code │               │
    │                    │ "123456"      │               │
    │                    │ TTL: 15 min   │               │
    │                    └──────┬────────┘               │
    │                           │                         │
    │                    ┌──────▼────────┐               │
    │                    │ Send via      │               │
    │                    │ PHPMailer     │───────────────→│
    │                    │ (Gmail SMTP)  │               │
    │                    └──────┬────────┘               │
    │                           │               ┌────────▼─────┐
    │←─ Move to Step 3 ─────────│               │ Queue email  │
    │   (Verify Email)          │               │ Send to SMTP │
    │   Show success msg        │               │ Server       │
    │                           │               └────────┬─────┘
    │                           │                        │
    │ 📧 User checks email ◀────────────────────────────│
    │                           │                 Deliver to
    │ 🔖 Copy code: 123456      │               user inbox
    │                           │                        │
    │─→ Enter code + Click  ──→ │                        │
    │    "Verify"               │                        │
    │                    ┌──────▼────────┐               │
    │                    │ Verify code   │               │
    │                    │ Check: matches│               │
    │                    │ Check: not exp│               │
    │                    │ Check: < 5 att│               │
    │                    └──────┬────────┘               │
    │                           │                        │
    │                    ┌──────▼────────┐               │
    │                    │ Create account│               │
    │                    │ Hash password │               │
    │                    │ Insert in DB  │               │
    │                    │ Gen JWT token │               │
    │                    └──────┬────────┘               │
    │                           │                        │
    │←─ Success + Token ────────│                        │
    │←─ Redirect to login ──────│                        │
    │                           │                        │
    │─→ Login page ──────────→ │                        │
    │   (email + password)      │                        │
    │                           │                        │
    │←─ JWT token + Dashboard ──│                        │
    │   ✅ Logged in!           │                        │
    │                           │                        │
```

---

## 🔄 Session Flow

```
SESSION Storage:
┌─────────────────────────────────────────┐
│ $_SESSION (Server-side)                 │
├─────────────────────────────────────────┤
│ [Start: User begins registration]      │
│                                         │
│ [After sending code]                   │
│ $_SESSION['verification'] = [          │
│   'code' => '123456',                   │
│   'email' => 'juan@gmail.com',          │
│   'expires_at' => 1703061000,           │
│   'attempts' => 0                       │
│ ]                                       │
│                                         │
│ [User verifies code]                   │
│ If code correct:                        │
│   unset($_SESSION['verification'])     │
│   // Create account in database        │
│                                         │
│ [User registers again]                 │
│ If no session verification:             │
│   Return error: "Send code first"      │
└─────────────────────────────────────────┘
```

---

## ⚙️ Configuration

### Files Involved:

```
config/mail.php
├─ from_email: "noreply@fixitmati.local"
├─ from_name: "FixItMati"
└─ smtp:
   ├─ host: "smtp.gmail.com"
   ├─ port: 587
   ├─ username: "user@gmail.com"
   ├─ password: "16-char-app-password"
   └─ encryption: "tls"

Services/AuthService.php
├─ sendVerificationEmail()
│  ├─ Loads mail.php config
│  ├─ Creates HTML template
│  ├─ Tries PHPMailer
│  └─ Falls back to mail()
└─ sendViaPhpMailer()
   ├─ Initializes PHPMailer
   ├─ Sets Gmail SMTP config
   ├─ Sends via SMTP
   └─ Returns success/failure

public/pages/auth/register.php
├─ Step 1 validation
├─ Step 2 validation + API call
└─ Step 3 verification + API call
```

---

## 🧪 Test Scenarios

```
HAPPY PATH:
1. Fill form with valid data
2. Click Create Account
3. Email sent to inbox ✓
4. Enter correct code
5. Account created ✓
6. Login works ✓

ERROR: Invalid Code
1. Fill form
2. Create Account
3. Email sent
4. Enter wrong code (123456 → 654321)
5. Show error: "Invalid code. 4 attempts remaining"
6. Try again (max 5 attempts)

ERROR: Code Expired
1. Fill form
2. Create Account (get code)
3. Wait > 15 minutes
4. Enter code
5. Show error: "Verification code expired. Send new code"

ERROR: Too Many Attempts
1. Fill form
2. Create Account
3. Enter wrong code 5 times
4. Show error: "Too many attempts. Request new code"
5. User must click "Send Code" again
```

---

This architecture ensures:
✅ Security (encryption, validation, rate limiting)  
✅ Reliability (fallback to mail(), error handling)  
✅ Scalability (stateless API, simple database)  
✅ Usability (clear errors, smooth flow)  

