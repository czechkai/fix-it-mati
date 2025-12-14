# Quick Start Guide - FixItMati

## Starting the Application

### Option 1: Using PHP Built-in Server (Recommended for Development)

1. Open a terminal in the project root directory
2. Run one of these commands:

**Using the start script (Windows):**
```cmd
start.bat
```

**Or manually:**
```cmd
php -S localhost:8000 router.php
```

3. Open your browser and navigate to:
   - `http://localhost:8000`
   - You will be redirected to the login page automatically

### Option 2: Using Apache

1. Place the project in your web server's document root (e.g., `htdocs`, `www`)
2. Ensure Apache has `mod_rewrite` enabled
3. Verify `.htaccess` file is in the project root
4. Access via: `http://localhost/fix-it-mati/`

## Accessing Pages

All pages are accessed using simple URLs from the root:

### Authentication
- Login: `http://localhost:8000/login.php`
- Register: `http://localhost:8000/register.php`
- Logout: `http://localhost:8000/logout.php`

### User Dashboard
- Dashboard: `http://localhost:8000/user-dashboard.php`
- Active Requests: `http://localhost:8000/active-requests.php`
- Create Request: `http://localhost:8000/create-request.php`
- Announcements: `http://localhost:8000/announcements.php`
- Payments: `http://localhost:8000/payments.php`
- Service Addresses: `http://localhost:8000/service-addresses.php`

### Admin Dashboard
- Admin Dashboard: `http://localhost:8000/admin-dashboard.php`

## API Access

API endpoints are accessed via `/api/`:
- `http://localhost:8000/api/auth/login`
- `http://localhost:8000/api/requests`
- etc.

## Troubleshooting

### "Page Not Found" Errors

If you see 404 errors:

1. **Check you're using the correct URL format**
   - ✅ Correct: `http://localhost:8000/login.php`
   - ❌ Wrong: `http://localhost:8000/public/pages/auth/login.php`

2. **For PHP built-in server:**
   - Make sure you started with `router.php`:
     ```cmd
     php -S localhost:8000 router.php
     ```

3. **For Apache:**
   - Verify `mod_rewrite` is enabled
   - Check `.htaccess` exists in project root
   - Ensure `AllowOverride All` is set in Apache config

### Assets Not Loading

If CSS/JS files don't load:
- Assets should be at: `http://localhost:8000/assets/style.css`
- Check browser console for 404 errors
- Verify files exist in `/assets/` folder

### Database Connection Issues

Run the setup script:
```cmd
setup.bat
```

Or manually configure in `config/database.php`

## Testing the Fix

To verify all pages are working:

1. **Test login page:**
   ```
   http://localhost:8000/login.php
   ```

2. **Test register page:**
   ```
   http://localhost:8000/register.php
   ```

3. **Test dashboard (after login):**
   ```
   http://localhost:8000/user-dashboard.php
   ```

4. **Test API endpoint:**
   ```
   http://localhost:8000/api/auth/check
   ```

5. **Test asset loading:**
   ```
   http://localhost:8000/assets/style.css
   ```

All should return valid responses (not 404).

## Default Test Accounts

After running database setup:
- **Admin:** admin@example.com / password123
- **User:** user@example.com / password123

## Additional Resources

- Full routing documentation: `docs/ROUTING.md`
- Project structure: `docs/PROJECT_STRUCTURE.md`
- API documentation: `docs/api/`

## Need Help?

If pages still don't work:
1. Check terminal for error messages
2. Check browser console for JavaScript errors
3. Verify database is set up correctly
4. Review `docs/ROUTING.md` for detailed information
