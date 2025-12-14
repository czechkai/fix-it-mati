# FixItMati - Municipal Water & Electricity Services Platform

A web-based platform for managing municipal water and electricity service requests, announcements, and payments with cloud-hosted PostgreSQL database.

## 🚀 ONE-CLICK Setup (No PostgreSQL Install Needed!)

**Database is cloud-hosted on Supabase** - no local installation required!

### Windows Team Members:
```bash
git clone https://github.com/czechkai/fix-it-mati.git
cd fix-it-mati
setup.bat          # ONE-CLICK setup
start.bat          # Start server
```

Then open: http://localhost:8000/login.php

### Linux/Mac Team Members:
```bash
git clone https://github.com/czechkai/fix-it-mati.git
cd fix-it-mati
cp .env.example .env
php -S localhost:8000 router.php
```

Then open: http://localhost:8000/login.php

> **Note:** Routing system updated December 2025 - all pages now use consistent URLs. See [QUICK_START.md](QUICK_START.md) for details.

## 🔑 Test Login

- **Email:** `test.customer@example.com`
- **Password:** `customer123`

## 📚 Documentation

### Getting Started
- **[QUICK_START.md](QUICK_START.md)** - ⭐ Quick start guide (start here!)
- **[TEAM_CONNECTION_READY.md](TEAM_CONNECTION_READY.md)** - Complete team setup guide
- **[TROUBLESHOOTING.md](TROUBLESHOOTING.md)** - Common issues and fixes

### Technical Documentation
- **[docs/ROUTING.md](docs/ROUTING.md)** - Routing and path configuration
- **[docs/PATH_FIX_SUMMARY.md](docs/PATH_FIX_SUMMARY.md)** - Recent routing fixes (Dec 2025)
- **[API_WORKING.md](API_WORKING.md)** - API endpoints reference

## ✅ What's Included

- ✅ Cloud database (no local PostgreSQL needed!)
- ✅ Pre-configured credentials in `.env.example`
- ✅ Automated setup script (`setup.bat`)
- ✅ 5 test users already in database
- ✅ Real-time data loading
- ✅ Complete API documentation

---

## 📋 Original Documentation Below
   - Default login: `test.customer@example.com` / `customer123`

## 📦 Requirements

### Required Software
- **PHP 7.4+** with the following extensions:
  - ✅ `pdo` - Database abstraction
  - ✅ `pdo_pgsql` - PostgreSQL driver ⚠️ **REQUIRED**
  - ✅ `json` - JSON support
  - ✅ `mbstring` - Multibyte strings
  - ✅ `openssl` - Encryption
- **PostgreSQL 12+** database server
- **Git** for version control

### Quick Check
Run this to verify your setup:
```bash
php check-requirements.php
```

This will show you exactly what's missing and how to fix it.

## 👥 Team Setup Guide

### First Time Setup

1. **Check Requirements**
   ```bash
   php check-requirements.php
   ```
   
   **If you see "PostgreSQL PDO driver not installed":**
   
   **Windows:**
   - Find your `php.ini` file: `php --ini`
   - Open `php.ini` in a text editor
   - Find these lines and remove the semicolon:
     ```ini
     ;extension=pdo_pgsql
     ;extension=pgsql
     ```
   - Change to:
     ```ini
     extension=pdo_pgsql
     extension=pgsql
     ```
   - Save and restart your terminal
   
   **Linux/Ubuntu:**
   ```bash
   sudo apt-get install php-pgsql
   ```
   
   **Mac (with Homebrew):**
   ```bash
   brew install php-pgsql
   ```

2. **Configure Database**
   ```bash
   copy config\database_examples.php config\database.php
   ```
   
   Edit `config/database.php` with your PostgreSQL credentials:
   ```php
   define('DB_HOST', 'your-database-host');
   define('DB_NAME', 'your-database-name');
   define('DB_USER', 'your-username');
   define('DB_PASSWORD', 'your-password');
   ```

3. **Setup Database**
   ```bash
   php run-migration.php
   php seed-all-data.php
   ```

4. **Start Server**
   ```bash
   php -S localhost:8000
   ```

## 🔧 Troubleshooting

### Error: "could not find driver"
**Problem:** PostgreSQL PDO extension not enabled
**Solution:** Follow the "Check Requirements" section above to enable `pdo_pgsql`

### Error: "Database connection failed"
**Problem:** Wrong database credentials or PostgreSQL not running
**Solution:** 
1. Verify PostgreSQL is running
2. Check credentials in `config/database.php`
3. Test connection: `php check-requirements.php`

### Error: "An error occurred. Please try again later"
**Problem:** Backend API error (usually database-related)
**Solution:**
1. Open browser console (F12)
2. Check the error message
3. Run `php check-requirements.php`
4. Verify database connection

### Port 8000 Already in Use
**Solution:**
```bash
php -S localhost:8080  # Use different port
```

## ✨ Features

### Service Categories
- **Water Supply** - Report water interruptions, leaks, low pressure, pipe bursts
- **Electricity** - Report power outages, faulty meters, streetlight issues

### User Features
- Submit and track service requests
- View announcements from utility providers
- Manage payment history
- Real-time request status updates
- Role-based dashboards (Customer, Technician, Admin)

### Technical Features
- JWT authentication with role-based access control
- PostgreSQL database via Supabase
- RESTful API architecture
- Design patterns implementation (Facade, Adapter, State, Template Method)

## 📁 Project Structure

```
fix-it-mati/
├── index.php               # Root router (handles all requests)
├── start.bat               # Quick server start script
├── assets/                 # CSS and JavaScript files (root level)
│   ├── style.css           # Dashboard styles
│   ├── api-client.js       # API client library
│   ├── dashboard.js        # Dashboard interactions
│   ├── active-requests.js  # Active requests page
│   ├── active-requests.css
│   ├── announcements.js
│   ├── announcements.css
│   ├── payments.js
│   └── payments.css
├── public/                 # Web-accessible files
│   ├── login.php           # Login page
│   ├── register.php        # Registration page
│   ├── user-dashboard.php  # Main dashboard
│   ├── active-requests.php # Service requests page
│   ├── announcements.php   # Announcements feed
│   ├── payments.php        # Billing and payments
│   └── create-request.php  # Create new request form
├── config/                 # Configuration files
│   └── database.php        # Database connection class
├── Controllers/            # MVC Controllers
├── Models/                 # Database models
├── Services/               # Business logic services
├── Middleware/             # Authentication middleware
├── DesignPatterns/         # Design pattern implementations
├── .env                    # Environment variables (NOT in git)
├── .env.example            # Environment template (committed)
├── .gitignore              # Git ignore rules
└── setup.bat               # Automated setup script
```

**Note:** Assets are served from the root `assets/` folder when using `php -S localhost:8000`

## 🗄️ Database Setup

### Supabase Configuration

This project uses **Supabase** (PostgreSQL) as the database backend.

#### Environment Variables

All sensitive credentials are stored in `.env` file (excluded from git):

```env
# Database Connection
DB_HOST=db.qyuwbrougimcexrjvrcm.supabase.co
DB_PORT=5432
DB_NAME=postgres
DB_USER=postgres
DB_PASSWORD=your-password-here

# Supabase API
SUPABASE_URL=https://qyuwbrougimcexrjvrcm.supabase.co
SUPABASE_ANON_KEY=your-anon-key
SUPABASE_SERVICE_KEY=your-service-key
```

#### Manual Setup (Alternative)

If you can't run `setup.bat`:

1. Copy `.env.example` to `.env`
   ```bash
   copy .env.example .env
   ```

2. Edit `.env` and add your database password

3. Verify PHP has PostgreSQL extension:
   ```bash
   php -m | findstr pdo_pgsql
   ```

4. Test connection:
   ```php
   php -r "require 'config/database.php'; $db = Database::getInstance(); print_r($db->testConnection());"
   ```

### Using the Database in PHP

```php
<?php
// Include database configuration
require_once __DIR__ . '/../config/database.php';

try {
    // Get database connection
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    // Execute queries
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = :id");
    $stmt->execute(['id' => 1]);
    $user = $stmt->fetch();
    
    // Get Supabase config (for API calls)
    $supabase = Database::getSupabaseConfig();
    
} catch(Exception $e) {
    error_log("Database error: " . $e->getMessage());
}
?>
```

## 👥 Team Collaboration

### Git Workflow

1. **Before starting work**
   ```bash
   git pull origin main
   ```

2. **Create a feature branch**
   ```bash
   git checkout -b feature/your-feature-name
   ```

3. **Make your changes and commit**
   ```bash
   git add .
   git commit -m "Description of changes"
   ```

4. **Push to remote**
   ```bash
   git push origin feature/your-feature-name
   ```

5. **Create a Pull Request** on GitHub

### Important Rules

✅ **DO:**
- Always run `git pull` before starting work
- Test your changes locally before pushing
- Use descriptive commit messages
- Keep `.env.example` updated if adding new variables

❌ **DON'T:**
- Never commit `.env` file (it's in `.gitignore`)
- Never commit sensitive credentials
- Don't push directly to `main` branch

### Database Best Practices

1. **Each team member has their own `.env` file** (not shared via git)
2. **Everyone connects to the same Supabase database**
3. **Database password is shared securely** (not via git)
4. **Schema changes should be documented** and coordinated with team

## 💻 Requirements

### Software Requirements

- **PHP 7.4+** with extensions:
  - `pdo_pgsql` (PostgreSQL)
  - `mbstring`
  - `json`
- **Git** for version control
- **Modern web browser**
- **Internet connection** (for CDN resources)

### PHP Installation (Windows)

1. Download PHP from [windows.php.net](https://windows.php.net/download/)
2. Extract to `C:\php`
3. Add to PATH environment variable
4. Enable `pdo_pgsql` extension in `php.ini`:
   ```ini
   extension=pdo_pgsql
   ```

## 🏃 Running Locally

### Method 1: PHP Built-in Server (Recommended)

```bash
cd public
php -S localhost:8000
```

Then open: `http://localhost:8000/user-dashboard.php`

### Method 2: Using Apache/Nginx

Configure document root to `public/` directory.

## 🛠️ Troubleshooting

### Database Connection Issues

1. **Check PHP extensions**
   ```bash
   php -m | findstr pdo_pgsql
   ```

2. **Verify .env file exists and has correct values**
   ```bash
   type .env
   ```

3. **Test connection manually**
   ```bash
   php -r "require 'config/database.php'; $db = Database::getInstance(); print_r($db->testConnection());"
   ```

### Common Errors

**Error: "pdo_pgsql extension not found"**
- Solution: Enable `extension=pdo_pgsql` in `php.ini`

**Error: ".env file not found"**
- Solution: Run `setup.bat` or copy `.env.example` to `.env`

**Error: "Database connection failed"**
- Solution: Verify database password in `.env` file

## 📚 Additional Resources

- [Supabase Documentation](https://supabase.com/docs)
- [PHP PDO Documentation](https://www.php.net/manual/en/book.pdo.php)
- [Tailwind CSS](https://tailwindcss.com/docs)
- [Lucide Icons](https://lucide.dev/)

## 🤝 Contributing

1. Fork the repository
2. Create your feature branch
3. Commit your changes
4. Push to the branch
5. Create a Pull Request

## 📄 License

This project is part of the FixItMati municipal services platform.

---

**Need Help?** Contact the development team or check the troubleshooting section above.
