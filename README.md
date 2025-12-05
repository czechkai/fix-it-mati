# FixItMati - Municipal Services Management Platform

A web-based platform for managing municipal service requests, announcements, and payments with Supabase integration.

## 📋 Table of Contents
- [Quick Start](#quick-start)
- [Project Structure](#project-structure)
- [Database Setup](#database-setup)
- [Team Collaboration](#team-collaboration)
- [Requirements](#requirements)
- [Running Locally](#running-locally)

## 🚀 Quick Start

### For New Team Members

1. **Clone the repository**
   ```bash
   git clone https://github.com/czechkai/fix-it-mati.git
   cd fix-it-mati
   ```

2. **Run the setup script**
   ```bash
   setup.bat
   ```
   This will:
   - Create your `.env` file
   - Prompt for database password
   - Verify PHP installation
   - Test database connection

3. **Start the development server**
   
   **Option 1: Using the start script (Recommended)**
   ```bash
   start.bat
   ```
   
   **Option 2: Manual start**
   ```bash
   php -S localhost:8000
   ```

4. **Open your browser**
   - Navigate to `http://localhost:8000` (redirects to login)
   - Or go directly to `http://localhost:8000/login.php`

## 📁 Project Structure

```
fix-it-mati/
├── index.php               # Root router (handles all requests)
├── start.bat               # Quick server start script
├── public/                 # Web-accessible files
│   ├── login.php           # Login page
│   ├── register.php        # Registration page
│   ├── user-dashboard.php  # Main dashboard
│   ├── active-requests.php # Service requests page
│   ├── announcements.php   # Announcements feed
│   └── payments.php        # Billing and payments
├── assets/                 # CSS and JavaScript files
│   ├── style.css           # Dashboard styles
│   ├── app.js              # Dashboard interactions
│   ├── active-requests.css
│   ├── active-requests.js
│   ├── announcements.css
│   ├── announcements.js
│   ├── payments.css
│   └── payments.js
├── config/                 # Configuration files
│   └── database.php        # Database connection class
├── .env                    # Environment variables (NOT in git)
├── .env.example            # Environment template (committed)
├── .gitignore              # Git ignore rules
└── setup.bat               # Automated setup script
```

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
