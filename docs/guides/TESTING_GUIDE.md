# 🎯 Step-by-Step Testing Guide

## Prerequisites

**YOU MUST RUN THE DATABASE MIGRATION FIRST!**

The migration file you have open (`001_add_auth_columns.sql`) needs to be run in Supabase.

---

## Step 1: Run Database Migration

1. **Open Supabase Dashboard**
   - Go to: https://supabase.com/dashboard
   - Select your project: `qyuwbrougimcexrjvrcm`

2. **Open SQL Editor**
   - Click on "SQL Editor" in the left sidebar
   - Click "New Query"

3. **Copy and paste this SQL:**
   ```sql
   -- Add password_hash column
   ALTER TABLE users 
   ADD COLUMN IF NOT EXISTS password_hash VARCHAR(255);

   -- Add role column with default value
   ALTER TABLE users 
   ADD COLUMN IF NOT EXISTS role VARCHAR(50) DEFAULT 'customer';

   -- Create index on role for faster queries
   CREATE INDEX IF NOT EXISTS idx_users_role ON users(role);

   -- Add check constraint for valid roles
   ALTER TABLE users 
   ADD CONSTRAINT check_user_role 
   CHECK (role IN ('customer', 'admin', 'technician'));

   -- Update existing users to have customer role if NULL
   UPDATE users SET role = 'customer' WHERE role IS NULL;
   ```

4. **Run the query**
   - Click "Run" button
   - You should see "Success. No rows returned"

---

## Step 2: Make Sure PHP Server is Running

```powershell
cd c:\tools_\fix-it-mati\public
php -S localhost:8000 router.php
```

**Keep this window open!**

---

## Step 3: Run the Test Script

Open a **NEW PowerShell window** and run:

```powershell
cd c:\tools_\fix-it-mati
php test-api.php
```

This will automatically test:
- ✅ API health check
- ✅ User registration
- ✅ User login (gets JWT token)
- ✅ Protected endpoint access

---

## Step 4: Manual Testing (Alternative)

If you prefer to test manually, open a **NEW PowerShell window** and run:

### Test 1: Register a User
```powershell
$body = @{
    email = "johndoe@fixitmati.com"
    full_name = "John Doe"
    password = "john123456"
    password_confirmation = "john123456"
    phone = "09123456789"
    address = "Mati City"
} | ConvertTo-Json

Invoke-RestMethod -Uri "http://localhost:8000/api/auth/register" `
    -Method POST `
    -Body $body `
    -ContentType "application/json"
```

**Expected Output:**
```
success : True
message : Registration successful
data    : @{id=...; email=johndoe@fixitmati.com; full_name=John Doe; ...}
```

---

### Test 2: Login and Get Token
```powershell
$body = @{
    email = "johndoe@fixitmati.com"
    password = "john123456"
} | ConvertTo-Json

$response = Invoke-RestMethod -Uri "http://localhost:8000/api/auth/login" `
    -Method POST `
    -Body $body `
    -ContentType "application/json"

# Save the token
$token = $response.data.token
Write-Host "Token received: $token" -ForegroundColor Green
```

**Expected Output:**
```
success : True
message : Login successful
data    : @{user=...; token=eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...}
Token received: eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...
```

---

### Test 3: Access Protected Endpoint
```powershell
Invoke-RestMethod -Uri "http://localhost:8000/api/auth/me" `
    -Method GET `
    -Headers @{
        "Authorization" = "Bearer $token"
    }
```

**Expected Output:**
```
success : True
message : Success
data    : @{id=...; email=johndoe@fixitmati.com; full_name=John Doe; role=customer; ...}
```

---

## Quick Copy-Paste Test (All in One)

Run this entire block after the database migration:

```powershell
# Register
Write-Host "Registering user..." -ForegroundColor Cyan
$registerBody = @{
    email = "demo$(Get-Random)@fixitmati.com"
    full_name = "Demo User"
    password = "demo123456"
    password_confirmation = "demo123456"
    phone = "09123456789"
} | ConvertTo-Json

$registerResponse = Invoke-RestMethod -Uri "http://localhost:8000/api/auth/register" -Method POST -Body $registerBody -ContentType "application/json"
Write-Host "✅ User registered: $($registerResponse.data.email)" -ForegroundColor Green

# Login
Write-Host "`nLogging in..." -ForegroundColor Cyan
$loginBody = @{
    email = $registerResponse.data.email
    password = "demo123456"
} | ConvertTo-Json

$loginResponse = Invoke-RestMethod -Uri "http://localhost:8000/api/auth/login" -Method POST -Body $loginBody -ContentType "application/json"
$token = $loginResponse.data.token
Write-Host "✅ Login successful, token received" -ForegroundColor Green

# Access protected endpoint
Write-Host "`nAccessing protected endpoint..." -ForegroundColor Cyan
$meResponse = Invoke-RestMethod -Uri "http://localhost:8000/api/auth/me" -Headers @{"Authorization" = "Bearer $token"}
Write-Host "✅ Protected endpoint accessed!" -ForegroundColor Green
Write-Host "   User: $($meResponse.data.full_name)" -ForegroundColor White
Write-Host "   Email: $($meResponse.data.email)" -ForegroundColor White
Write-Host "   Role: $($meResponse.data.role)" -ForegroundColor White

Write-Host "`n🎉 ALL TESTS PASSED!" -ForegroundColor Green
```

---

## Troubleshooting

### "FAILED - Check if database migration was run"
**Solution:** Run the SQL migration in Supabase (Step 1 above)

### "Unable to connect to the remote server"
**Solution:** Make sure PHP server is running with `php -S localhost:8000 router.php`

### "500 Internal Server Error"
**Solution:** Check that .env file has correct database credentials

### "Invalid credentials"
**Solution:** Make sure you're using the same email/password you registered with

---

## What Should Happen

After running the database migration:

1. ✅ **Register** creates a user in the database
2. ✅ **Login** verifies password and returns JWT token
3. ✅ **Protected endpoint** validates token and returns user data

---

## Ready for Phase 2?

Once all three tests pass, you're ready to implement:
- Service Request system (State Pattern)
- Notification system (Observer Pattern)
- Request operations (Facade Pattern)
- More design patterns...

🚀 **Let me know when tests pass and we'll continue!**
