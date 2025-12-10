# ⚡ FixItMati - Super Quick Setup (1 Command!)

## For Team Members - Fastest Way to Get Started

### Prerequisites
- PHP 7.4+ installed
- PostgreSQL PDO extension enabled (see below if not)

### One Command Setup

**Windows:**
```bash
quick-setup.bat
```

**Linux/Mac:**
```bash
chmod +x quick-setup.sh
./quick-setup.sh
```

That's it! The script will:
1. ✅ Check all requirements
2. ✅ Create database config (with team credentials already set)
3. ✅ Test database connection
4. ✅ Setup database schema
5. ✅ Seed test data

### If You Get "PostgreSQL PDO driver not installed"

**Windows:**
1. Find php.ini:
   ```bash
   php --ini
   ```
2. Open `php.ini` as Administrator
3. Find and uncomment (remove `;`):
   ```ini
   extension=pdo_pgsql
   extension=pgsql
   ```
4. Save, close terminal, open new terminal
5. Run setup again

**Linux:**
```bash
sudo apt-get install php-pgsql
```

**Mac:**
```bash
brew install php-pgsql
```

### Start the Server

After setup completes:
```bash
start.bat
```

Or:
```bash
php -S localhost:8000
```

### Open the App

Go to: **http://localhost:8000**

Login with:
- **Email:** test.customer@example.com
- **Password:** customer123

## ✅ That's All!

Total time: **5 minutes** (most of it is just enabling PDO extension if needed)

---

## Need More Details?

- **Full Guide:** See `TEAM_ONBOARDING.md`
- **Troubleshooting:** See `TROUBLESHOOTING.md`
- **Supabase Info:** See `SUPABASE_SETUP.md`

## Database Credentials (Pre-configured)

The setup uses these credentials automatically:
- **Host:** db.qyuwbrougimcexrjvrcm.supabase.co
- **Port:** 5432
- **Database:** postgres
- **User:** postgres
- **Password:** fIxITmAtI123

These are already in `config/database.template.php` - no manual entry needed!

---

**Having issues?** Run this for diagnostics:
```bash
php check-requirements.php
```
