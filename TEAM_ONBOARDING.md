# 🚀 FixItMati - Team Onboarding Guide

## Welcome to the FixItMati Team! 👋

This guide will help you set up the project on your local machine quickly and correctly.

## ⚡ Quick Setup (5 minutes)

### Step 1: Clone the Repository
```bash
git clone https://github.com/czechkai/fix-it-mati.git
cd fix-it-mati
```

### Step 2: Check Requirements
```bash
php check-requirements.php
```

**If you see errors, follow the instructions it provides. Common issue:**

### ❌ Error: "PostgreSQL PDO driver not installed"

#### Windows Fix:
1. Find your PHP configuration file:
   ```bash
   php --ini
   ```
   Look for "Loaded Configuration File" path

2. Open `php.ini` in a text editor (as Administrator)

3. Find these lines (use Ctrl+F to search):
   ```ini
   ;extension=pdo_pgsql
   ;extension=pgsql
   ```

4. Remove the semicolons:
   ```ini
   extension=pdo_pgsql
   extension=pgsql
   ```

5. Save the file and close your terminal completely

6. Open a NEW terminal and verify:
   ```bash
   php -m | findstr pdo_pgsql
   ```
   You should see `pdo_pgsql` in the output

#### Linux/Mac Fix:
```bash
# Ubuntu/Debian
sudo apt-get install php-pgsql

# Mac with Homebrew
brew install php-pgsql
```

### Step 3: Configure Database

1. **Get Supabase credentials from your team lead** or from Supabase dashboard

   Don't have them? See: **[SUPABASE_SETUP.md](SUPABASE_SETUP.md)** for detailed instructions

2. Copy the template:
   ```bash
   copy config\database.template.php config\database.php
   ```

3. Edit `config/database.php` - credentials are already configured:
   ```php
   define('DB_HOST', 'db.qyuwbrougimcexrjvrcm.supabase.co');
   define('DB_NAME', 'postgres');
   define('DB_USER', 'postgres');
   define('DB_PASSWORD', 'fIxITmAtI123');
   define('DB_PORT', '5432');
   ```

   **✅ Note:** These credentials are pre-configured in `database.template.php`
   - Just copy the template and it will work
   - No changes needed unless using a different database

### Step 4: Run Automated Setup
```bash
quick-setup.bat
```

This will:
- ✅ Verify all requirements
- ✅ Test database connection
- ✅ Setup database schema
- ✅ Seed test data

### Step 5: Start the Server
```bash
start.bat
```

Or manually:
```bash
php -S localhost:8000
```

### Step 6: Verify Everything Works
Open your browser to: http://localhost:8000/setup-check.html

This page will verify:
- ✅ Server is running
- ✅ API is accessible
- ✅ Database connection works
- ✅ Assets are loading

## 🧪 Test Login Accounts

After setup, you can login with these test accounts:

| Email | Password | Role |
|-------|----------|------|
| test.customer@example.com | customer123 | Customer |
| jaysonB354@gmail.com | customer123 | Customer |
| testuser99@mati.gov.ph | customer123 | Customer |

## 🔧 Common Issues & Solutions

### Issue: "An error occurred. Please try again later"
**Cause:** Database connection problem
**Fix:**
1. Open browser console (F12)
2. Check the actual error message
3. Run `php check-requirements.php`
4. Verify database credentials in `config/database.php`

### Issue: "Failed to load resource: 500 Internal Server Error"
**Cause:** Backend API error (usually database-related)
**Fix:**
1. Check if PostgreSQL PDO is enabled: `php -m | findstr pdo_pgsql`
2. Test database connection: `php check-requirements.php`
3. Check server logs in the terminal where you ran `php -S`

### Issue: "could not find driver"
**Cause:** PostgreSQL PDO extension not enabled
**Fix:** Follow "Windows Fix" or "Linux/Mac Fix" above in Step 2

### Issue: Port 8000 already in use
**Fix:** Use a different port:
```bash
php -S localhost:8080
```

### Issue: Register page redirects after logout
**Cause:** Browser cache or old session
**Fix:**
1. Close all browser tabs
2. Open a new incognito/private window
3. Try again, or clear browser cache (Ctrl+Shift+Delete)

## 📁 Project Structure

```
fix-it-mati/
├── public/              # Frontend pages
│   ├── login.php       # Login page
│   ├── register.php    # Registration page
│   ├── user-dashboard.php
│   ├── payments.php
│   └── api/            # Backend API
├── assets/             # JavaScript & CSS
│   ├── api-client.js   # API communication
│   ├── dashboard.js    # Dashboard logic
│   └── style.css
├── Controllers/        # API controllers
├── Models/            # Database models
├── Services/          # Business logic
├── Core/              # Framework core
└── config/            # Configuration files
    └── database.php   # Database config (YOU CREATE THIS)
```

## 🔄 Daily Development Workflow

1. **Pull latest changes**
   ```bash
   git pull origin main
   ```

2. **Start the server**
   ```bash
   start.bat
   ```

3. **Make your changes**
   - Edit files in your code editor
   - Browser will auto-reload (refresh manually if needed)

4. **Test your changes**
   - Open http://localhost:8000
   - Test the feature you're working on

5. **Commit and push**
   ```bash
   git add .
   git commit -m "Description of changes"
   git push origin main
   ```

## 🆘 Getting Help

If you're stuck:

1. **Check Requirements**
   ```bash
   php check-requirements.php
   ```

2. **Check Setup**
   - Visit: http://localhost:8000/setup-check.html

3. **Check Logs**
   - Look at the terminal where `php -S` is running
   - Check browser console (F12)

4. **Ask Team**
   - Share the exact error message
   - Share what you've already tried
   - Include screenshots if helpful

## ✅ Verification Checklist

Before you start developing, verify:

- [ ] `php check-requirements.php` passes all checks
- [ ] `config/database.php` exists and has correct credentials
- [ ] Server starts without errors: `php -S localhost:8000`
- [ ] http://localhost:8000/login.php loads correctly
- [ ] http://localhost:8000/setup-check.html shows all green checks
- [ ] You can login with test.customer@example.com / customer123

## 🎯 You're Ready!

Once all the above checks pass, you're ready to start developing! 🎉

For more details, check:
- `README.md` - General project documentation
- `QUICK_START_BACKEND.md` - Backend development guide
- `TESTING_GUIDE.md` - Testing instructions

Welcome aboard! 🚢
