# ✅ TEAM CONNECTION - READY TO USE!

## 🚀 Quick Start (3 Steps)

Your database is **already configured and tested**. Just follow these steps:

### Step 1: Get Latest Code
```powershell
git pull origin main
```

### Step 2: Copy Environment File
```powershell
# Windows
copy .env.example .env

# Linux/Mac
cp .env.example .env
```

### Step 3: Start Server
```powershell
# Windows
.\start.bat

# Linux/Mac
php -S localhost:8000
```

### Step 4: Open Browser
Go to: **http://localhost:8000/login.php**

**Test Login:**
- Email: `test.customer@example.com`
- Password: `customer123`

---

## ✅ What's Already Done

✓ Database connection configured with **Transaction Pooler**  
✓ Tested and working (5 users found in database)  
✓ All credentials pre-configured in `.env.example`  
✓ No manual setup needed!

---

## 🔧 Connection Details

The `.env.example` file contains:

```env
DB_HOST=aws-1-ap-southeast-2.pooler.supabase.com
DB_PORT=6543
DB_NAME=postgres
DB_USER=postgres.qyuwbrougimcexrjvrcm
DB_PASSWORD=fIxITmAtI123
```

**Why Transaction Pooler?**
- ✅ Better performance
- ✅ Works with IPv4 networks
- ✅ No DNS issues
- ✅ Optimized for multiple connections

---

## 🧪 Test Your Setup

After copying `.env`, test the connection:

```powershell
php test-pooler.php
```

You should see:
```
✓ Connected successfully!
✓ Found 5 users in database
✅ Transaction Pooler is working!
```

Or test the full application:
```powershell
php test-app-connection.php
```

---

## ⚠️ Troubleshooting

### "Extension pdo_pgsql not found"
1. Find your `php.ini`: `php --ini`
2. Open `php.ini` and find: `;extension=pdo_pgsql`
3. Remove the semicolon: `extension=pdo_pgsql`
4. Save and restart terminal

### "Server already running on port 8000"
```powershell
# Kill existing PHP processes
Get-Process -Name php | Stop-Process -Force

# Try starting again
.\start.bat
```

### ".env file not found" Error
Make sure you copied the file:
```powershell
copy .env.example .env
```

### Still Having Issues?
1. Run: `php check-requirements.php`
2. Make sure you have PHP 7.4+
3. Check that `pdo_pgsql` extension is enabled
4. Contact team lead

---

## 📁 Important Files

- **`.env`** - Your local configuration (DON'T COMMIT!)
- **`.env.example`** - Template with team credentials (already configured)
- **`start.bat`** - Starts the development server
- **`test-pooler.php`** - Tests database connection
- **`test-app-connection.php`** - Tests full application

---

## 👥 Test Accounts

| Role | Email | Password |
|------|-------|----------|
| Customer | test.customer@example.com | customer123 |
| Technician | test.technician@example.com | tech123 |
| Admin | test.admin@example.com | admin123 |

---

## 📚 More Documentation

- **API_WORKING.md** - API endpoints reference
- **TROUBLESHOOTING.md** - Detailed problem solutions
- **QUICK_START_BACKEND.md** - Backend development guide

---

**Connection Status:** ✅ TESTED AND WORKING  
**Last Verified:** December 11, 2025  
**Database:** Supabase PostgreSQL (Transaction Pooler)
