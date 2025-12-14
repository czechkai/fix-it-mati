# FixItMati - Project Structure

## 📁 Organized Folder Structure

The project has been reorganized for better maintainability and clarity. All files are now logically grouped by their purpose.

## Root Directory Structure

```
fix-it-mati/
├── config/              # Configuration files (database, payment)
├── Controllers/         # MVC Controllers
├── Core/               # Core application classes (Database, Router, Request, Response)
├── database/           # Database schema and migrations
├── DesignPatterns/     # Design pattern implementations
├── docs/               # All documentation
│   ├── api/           # API documentation
│   ├── guides/        # Setup guides, testing guides, troubleshooting
│   └── sprints/       # Sprint completion reports
├── logs/              # Application logs
├── Middleware/        # Authentication and role middleware
├── Models/            # Data models
├── public/            # Publicly accessible files
│   ├── admin/        # Admin dashboard and components
│   │   ├── dashboard.php
│   │   └── tabs/     # Admin dashboard tabs
│   ├── api/          # API endpoints
│   ├── assets/       # CSS, JavaScript, images
│   ├── pages/        # Application pages
│   │   ├── auth/    # Authentication pages (login, register, logout)
│   │   └── user/    # User dashboard pages
│   └── uploads/      # User uploaded files
├── scripts/           # Utility scripts
│   ├── database/     # Database check and fix scripts
│   ├── migrations/   # Migration runner scripts
│   ├── seeds/        # Database seed scripts
│   └── setup/        # Setup and initialization scripts
├── Services/          # Business logic services
├── tests/            # Test files
│   ├── api/         # API tests
│   ├── debug/       # Debug and diagnostic tools
│   └── integration/ # Integration tests
├── uploads/          # File uploads storage
└── z_ref/           # Reference/backup files

## Key Files

### Root Level
- `index.php` - Entry point
- `router.php` - Request routing with path mappings
- `autoload.php` - Class autoloader
- `README.md` - Main project documentation
- `.env` - Environment configuration

### Public Pages

#### Authentication (`public/pages/auth/`)
- `login.php` - User login
- `register.php` - New user registration
- `logout.php` - Session logout

#### User Dashboard (`public/pages/user/`)
- `user-dashboard.php` - Main user dashboard
- `active-requests.php` - View service requests
- `create-request.php` - Create new service request
- `discussions.php` - Community discussions
- `discussion-detail.php` - Discussion thread view
- `notifications.php` - User notifications
- `payments.php` - Payment processing
- `payment-history.php` - Payment records
- `service-addresses.php` - Manage service addresses
- `service-history.php` - Service request history
- `linked-meters.php` - Linked utility meters
- `announcements.php` - System announcements
- `help-support.php` - Help and support
- `settings.php` - User settings
- `edit-profile.php` - Edit user profile

#### Admin (`public/admin/`)
- `dashboard.php` - Admin dashboard
- `tabs/` - Dashboard tab components
  - `service-requests-tab.php`
  - `billing-tab.php`
  - `users-tab.php`
  - `technicians-tab.php`
  - `announcements-tab.php`
  - `analytics-tab.php`

### Scripts

#### Database Scripts (`scripts/database/`)
- `check-*.php` - Database validation scripts
- `fix-*.php` - Database repair scripts
- `update-*.php` - Database update utilities
- `verify-*.php` - Data verification scripts

#### Migration Scripts (`scripts/migrations/`)
- `run-migration.php` - Main migration runner
- `run-migration-*.php` - Specific feature migrations

#### Seed Scripts (`scripts/seeds/`)
- `seed-*.php` - Database seeding scripts for test data

#### Setup Scripts (`scripts/setup/`)
- `create-admin-account.php` - Create admin user
- `create-sample-requests.php` - Generate sample data
- `test-database-connection.php` - Test DB connectivity

### Tests

#### Debug Tools (`tests/debug/`)
- `debug-*.php` - Debugging utilities
- `test-*.php` - Manual test files
- `diagnostic-*.php` - System diagnostics

#### Integration Tests (`tests/integration/`)
- Complete system integration tests

## Path Routing

The `router.php` handles path mapping for backward compatibility:

### Legacy Paths → New Paths
- `/login.php` → `/pages/auth/login.php`
- `/user-dashboard.php` → `/pages/user/user-dashboard.php`
- `/admin-dashboard.php` → `/admin/dashboard.php`
- etc.

All old URLs continue to work through the router!

## Access URLs

When running the development server:

```bash
php -S localhost:8000
```

### Main URLs:
- **Login**: http://localhost:8000/login.php (auto-redirects)
- **User Dashboard**: http://localhost:8000/user-dashboard.php
- **Admin Dashboard**: http://localhost:8000/admin-dashboard.php
- **API**: http://localhost:8000/api/*

### Direct New Paths:
- **Login**: http://localhost:8000/pages/auth/login.php
- **User Dashboard**: http://localhost:8000/pages/user/user-dashboard.php
- **Admin Dashboard**: http://localhost:8000/admin/dashboard.php

## Benefits of New Structure

✅ **Organized by Purpose**: Files grouped logically
✅ **Easy Navigation**: Clear folder hierarchy
✅ **Better Maintainability**: Related files together
✅ **Scalable**: Easy to add new features
✅ **Clean Separation**: Auth, user, admin clearly separated
✅ **Backward Compatible**: Old URLs still work via router

## Development Workflow

1. **User Pages**: Work in `public/pages/user/`
2. **Admin Features**: Work in `public/admin/`
3. **Auth Changes**: Work in `public/pages/auth/`
4. **Database Scripts**: Use scripts in `scripts/database/`
5. **Tests**: Add to `tests/` subfolders
6. **Documentation**: Update in `docs/guides/`

## Assets Loading

All assets (CSS/JS) remain in `public/assets/` and are accessible from any page using relative paths or absolute paths from root.

Example:
```html
<link rel="stylesheet" href="/assets/style.css">
<script src="/assets/dashboard.js"></script>
```

## Notes

- All references in JavaScript and PHP files have been updated to reflect the new structure
- The router handles both old and new paths for seamless transition
- Test files moved to `tests/debug/` for cleaner public folder
- Documentation consolidated in `docs/` with subcategories
