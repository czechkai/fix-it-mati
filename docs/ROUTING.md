# Routing and Path Configuration Guide

## Overview

This project uses a flexible routing system that works with both Apache web server and PHP's built-in development server.

## Server Compatibility

### PHP Built-in Server (Development)
```bash
# Start the server using:
php -S localhost:8000 router.php

# Or use the convenience script:
start.bat
```

### Apache Web Server (Production)
The project includes a `.htaccess` file at the root that handles URL rewriting for Apache.

**Requirements:**
- `mod_rewrite` must be enabled
- `AllowOverride All` must be set in your Apache configuration

## URL Structure

All pages are accessed using clean URLs from the root:

### Authentication Pages
- `/login.php` → `public/pages/auth/login.php`
- `/register.php` → `public/pages/auth/register.php`
- `/logout.php` → `public/pages/auth/logout.php`

### User Pages
- `/user-dashboard.php` → `public/pages/user/user-dashboard.php`
- `/active-requests.php` → `public/pages/user/active-requests.php`
- `/announcements.php` → `public/pages/user/announcements.php`
- `/notifications.php` → `public/pages/user/notifications.php`
- `/payments.php` → `public/pages/user/payments.php`
- `/payment-history.php` → `public/pages/user/payment-history.php`
- `/create-request.php` → `public/pages/user/create-request.php`
- `/service-addresses.php` → `public/pages/user/service-addresses.php`
- `/linked-meters.php` → `public/pages/user/linked-meters.php`
- `/help-support.php` → `public/pages/user/help-support.php`
- `/edit-profile.php` → `public/pages/user/edit-profile.php`
- `/service-history.php` → `public/pages/user/service-history.php`
- `/discussions.php` → `public/pages/user/discussions.php`
- `/discussion-detail.php` → `public/pages/user/discussion-detail.php`
- `/settings.php` → `public/pages/user/settings.php`

### Admin Pages
- `/admin-dashboard.php` → `public/admin/dashboard.php`
- `/admin/service-requests.php` → `public/admin/service-requests.php`
- `/admin/billing.php` → `public/admin/billing.php`
- `/admin/users.php` → `public/admin/users.php`
- `/admin/technicians.php` → `public/admin/technicians.php`
- `/admin/announcements.php` → `public/admin/announcements.php`
- `/admin/analytics.php` → `public/admin/analytics.php`

### API Endpoints
- `/api/*` → `public/api/index.php`

### Assets
- `/assets/*` → `assets/*` (served from root)

## Best Practices for Links

### In PHP Files

Include the path helper:
```php
<?php require_once __DIR__ . '/../../config/paths.php'; ?>
```

Use helper functions:
```php
// For page links
<a href="<?= url('user-dashboard.php') ?>">Dashboard</a>

// For assets
<link rel="stylesheet" href="<?= asset('style.css') ?>">
<script src="<?= asset('app.js') ?>"></script>

// For API calls
fetch('<?= api('auth/login') ?>', { ... })

// For redirects
redirect('login.php');
```

### In JavaScript Files

Always use absolute paths starting with `/`:
```javascript
// Page navigation
window.location.href = '/user-dashboard.php';

// API calls
fetch('/api/auth/login', { ... })

// Assets (already in HTML, but if needed dynamically)
const link = '/assets/style.css';
```

### Important Rules

1. **Always use leading slash** for absolute paths: `/login.php` not `login.php`
2. **Use clean URLs** without `public/` in links: `/login.php` not `/public/pages/auth/login.php`
3. **Assets use `/assets/`** prefix: `/assets/style.css`
4. **API calls use `/api/`** prefix: `/api/auth/login`

## File Organization

```
fix-it-mati/
├── .htaccess              # Apache rewrite rules
├── router.php             # PHP built-in server router
├── index.php              # Entry point (includes router.php)
├── config/
│   └── paths.php          # Path helper functions
├── assets/                # CSS, JS, images (served from root)
│   ├── style.css
│   ├── app.js
│   └── ...
├── public/
│   ├── pages/
│   │   ├── auth/         # Authentication pages
│   │   │   ├── login.php
│   │   │   ├── register.php
│   │   │   └── logout.php
│   │   └── user/         # User pages
│   │       ├── user-dashboard.php
│   │       └── ...
│   ├── admin/            # Admin pages
│   │   ├── dashboard.php
│   │   └── ...
│   └── api/              # API endpoints
│       └── index.php
└── ...
```

## Troubleshooting

### 404 Errors

1. **Check the URL format**: Use `/login.php` not `login.php` or `/public/pages/auth/login.php`
2. **Verify file exists**: Check that the file exists in the correct location
3. **Apache users**: Ensure `mod_rewrite` is enabled and `.htaccess` is being read
4. **PHP built-in server**: Make sure you started with `router.php` specified

### Assets Not Loading

1. **Use absolute paths**: `/assets/style.css` not `assets/style.css` or `../assets/style.css`
2. **Check file location**: Assets should be in the `assets/` folder at the root
3. **Check permissions**: Ensure files are readable

### Links Not Working

1. **Use helper functions** in PHP files
2. **Use absolute paths** starting with `/` in JavaScript
3. **Test in both servers**: PHP built-in and Apache may behave slightly differently

## Testing

Test your routing with these URLs:

1. Root: `http://localhost:8000/` (should redirect to login)
2. Login: `http://localhost:8000/login.php`
3. Dashboard: `http://localhost:8000/user-dashboard.php`
4. API: `http://localhost:8000/api/auth/login`
5. Asset: `http://localhost:8000/assets/style.css`

## Migration from Old Structure

If you have old links in your code:

**Old format:**
```html
<a href="../../login.php">Login</a>
<a href="../pages/auth/login.php">Login</a>
<script src="../../assets/app.js"></script>
```

**New format:**
```html
<a href="/login.php">Login</a>
<a href="/login.php">Login</a>
<script src="/assets/app.js"></script>
```

Or better yet, use the helper functions:
```php
<a href="<?= url('login.php') ?>">Login</a>
<script src="<?= asset('app.js') ?>"></script>
```
