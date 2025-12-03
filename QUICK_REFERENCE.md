# FixItMati - Quick Reference Guide

## 🚀 Getting Started (New Team Member)

```bash
# 1. Clone the repo
git clone https://github.com/czechkai/fix-it-mati.git
cd fix-it-mati

# 2. Run setup
setup.bat

# 3. Start server
cd public
php -S localhost:8000

# 4. Open browser
# http://localhost:8000/user-dashboard.php
```

## 📋 Daily Workflow

### Before You Start Working
```bash
git pull origin main
```

### Making Changes
```bash
# Create a new branch
git checkout -b feature/your-feature-name

# Make your changes...

# Commit
git add .
git commit -m "Add: description of changes"

# Push
git push origin feature/your-feature-name

# Create Pull Request on GitHub
```

## 🗄️ Database Usage

### Connect to Database
```php
<?php
require_once __DIR__ . '/../config/database.php';

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    // Your queries here
    
} catch(Exception $e) {
    error_log($e->getMessage());
}
?>
```

### Example Queries
```php
// SELECT
$stmt = $conn->prepare("SELECT * FROM users WHERE id = :id");
$stmt->execute(['id' => 1]);
$user = $stmt->fetch();

// INSERT
$stmt = $conn->prepare("INSERT INTO requests (title, status) VALUES (:title, :status)");
$stmt->execute(['title' => 'Fix water leak', 'status' => 'pending']);

// UPDATE
$stmt = $conn->prepare("UPDATE requests SET status = :status WHERE id = :id");
$stmt->execute(['status' => 'completed', 'id' => 123]);
```

## 🧪 Testing Database Connection

Visit: `http://localhost:8000/test-db.php`

Or via command line:
```bash
php -r "require 'config/database.php'; $db = Database::getInstance(); print_r($db->testConnection());"
```

## 🔧 Troubleshooting

### Problem: "pdo_pgsql extension not found"
**Solution:**
1. Find your `php.ini` file: `php --ini`
2. Open it and uncomment: `extension=pdo_pgsql`
3. Restart PHP server

### Problem: "Database connection failed"
**Solution:**
1. Check `.env` file exists
2. Verify database password is set
3. Run `setup.bat` again

### Problem: ".env file not found"
**Solution:**
```bash
copy .env.example .env
# Then edit .env and add database password
```

## 📁 File Locations

| Purpose | Location |
|---------|----------|
| Web pages | `public/*.php` |
| Styles | `assets/*.css` |
| Scripts | `assets/*.js` |
| Database config | `config/database.php` |
| Environment vars | `.env` (not in git) |
| Setup script | `setup.bat` |

## ⚠️ Important Rules

### ✅ DO
- Run `git pull` before working
- Test locally before pushing
- Use feature branches
- Write descriptive commits
- Keep `.env.example` updated

### ❌ DON'T
- Never commit `.env` file
- Never commit passwords/secrets
- Don't push to `main` directly
- Don't modify others' branches

## 🔑 Environment Variables

Located in `.env` file (create from `.env.example`):

```env
DB_HOST=db.qyuwbrougimcexrjvrcm.supabase.co
DB_PORT=5432
DB_NAME=postgres
DB_USER=postgres
DB_PASSWORD=your-password-here
SUPABASE_URL=https://qyuwbrougimcexrjvrcm.supabase.co
SUPABASE_ANON_KEY=your-anon-key
SUPABASE_SERVICE_KEY=your-service-key
```

## 🌐 Pages

- Dashboard: `/user-dashboard.php`
- Requests: `/active-requests.php`
- Announcements: `/announcements.php`
- Payments: `/payments.php`
- DB Test: `/test-db.php`

## 💡 Useful Commands

```bash
# Check PHP version
php --version

# Check PHP extensions
php -m

# Check if pdo_pgsql is enabled
php -m | findstr pdo_pgsql

# Test database connection
php -r "require 'config/database.php'; $db = Database::getInstance(); print_r($db->testConnection());"

# Start dev server
cd public; php -S localhost:8000

# Git status
git status

# Git pull latest
git pull origin main

# Create new branch
git checkout -b feature/my-feature
```

## 📞 Getting Help

1. Check this guide
2. Check README.md
3. Run `setup.bat` to reset config
4. Visit `/test-db.php` to diagnose issues
5. Contact team lead

---

**Last Updated:** December 2025
