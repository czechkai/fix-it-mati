# 🚀 Quick Start for Teams - Visual Guide

## Step 1: Clone Repository
```bash
git clone https://github.com/czechkai/fix-it-mati.git
cd fix-it-mati
```

## Step 2: Run Requirements Check
```bash
php check-requirements.php
```

### ✅ If you see this:
```
====================================
✓ ALL REQUIREMENTS MET!
====================================
```
**→ Skip to Step 4**

### ❌ If you see errors:

#### Common Error: "Extension 'pdo_pgsql': MISSING"

**Windows Fix:**
1. Find php.ini location:
   ```bash
   php --ini
   ```

2. Open php.ini (as Administrator)

3. Find and uncomment these lines:
   ```ini
   ;extension=pdo_pgsql  ← Remove semicolon
   ;extension=pgsql      ← Remove semicolon
   ```
   
   Change to:
   ```ini
   extension=pdo_pgsql
   extension=pgsql
   ```

4. Save, close terminal, open NEW terminal

5. Verify:
   ```bash
   php -m | findstr pdo_pgsql
   ```

**Linux:**
```bash
sudo apt-get install php-pgsql
```

**Mac:**
```bash
brew install php-pgsql
```

## Step 3: Configure Database
```bash
copy config\database_examples.php config\database.php
```

Edit `config/database.php`:
```php
define('DB_HOST', 'your-host');      // Ask team lead
define('DB_NAME', 'postgres');        
define('DB_USER', 'your-username');   // Ask team lead
define('DB_PASSWORD', 'your-pass');   // Ask team lead
define('DB_PORT', '6543');
```

## Step 4: Automated Setup
```bash
quick-setup.bat
```

This does everything automatically:
- ✓ Checks requirements again
- ✓ Tests database connection
- ✓ Creates database schema
- ✓ Seeds test data

## Step 5: Start Server
```bash
start.bat
```

Or:
```bash
php -S localhost:8000
```

## Step 6: Verify Setup

### Browser Check:
Open: **http://localhost:8000/setup-check.html**

You should see all green checkmarks:
- ✅ Server Connection
- ✅ API Availability  
- ✅ Database Connection
- ✅ Assets Loading
- ✅ Login Page

### Test Login:
Open: **http://localhost:8000/login.php**

Login with:
- **Email:** test.customer@example.com
- **Password:** customer123

## 🎉 You're Ready!

If all checks pass and you can login, you're all set!

---

## 🆘 Still Having Issues?

### Error: "An error occurred. Please try again later"
1. Open browser console (F12)
2. Check the actual error
3. Run: `php check-requirements.php`

### Error: "could not find driver"
→ PostgreSQL PDO not enabled. Follow Step 2 above.

### Error: "Database connection failed"
→ Wrong credentials in `config/database.php`

### Error: Port 8000 in use
→ Use different port: `php -S localhost:8080`

---

## 📚 Need More Help?

- **Full Guide:** Read `TEAM_ONBOARDING.md`
- **Troubleshooting:** Check `README.md`
- **Setup Details:** See `SETUP_SOLUTIONS.md`

---

## ⏱️ Expected Time: 5-10 minutes
Most issues are just enabling the PostgreSQL extension!
