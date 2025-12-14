# Server Start Migration Guide

## ✅ NEW WAY (Simplified)

### Option 1: Quick Start Script
```bash
start.bat
```

### Option 2: Manual Start
```bash
php -S localhost:8000
```

**Benefits:**
- No need to navigate to `public` folder
- No need for `-t public` flag
- Cleaner, simpler command
- Works from project root

---

## ❌ OLD WAY (Deprecated)

```bash
cd public
php -S localhost:8000
```

**OR**

```bash
php -S localhost:8000 -t public public/router.php
```

---

## 🔧 How It Works

The new `index.php` router in the root directory handles all routing:

1. **API Requests** (`/api/*`) → Routes to `public/api/index.php`
2. **Pages** (`/login.php`, `/user-dashboard.php`, etc.) → Serves from `public/`
3. **Assets** (`/assets/*`) → Serves from `public/assets/` with correct MIME types
4. **Root** (`/`) → Redirects to `/login.php`

---

## 📝 URLs Stay The Same

All URLs remain unchanged:
- ✅ `http://localhost:8000/login.php`
- ✅ `http://localhost:8000/user-dashboard.php`
- ✅ `http://localhost:8000/api/test`
- ✅ `http://localhost:8000/assets/style.css`

---

## 🚀 Quick Test

```powershell
# Start the server
php -S localhost:8000

# In another terminal, test:
curl http://localhost:8000/api/test
```

Should return JSON with `"message": "FixItMati API is working!"`

---

## 💡 For Team Members

Simply pull the latest changes and use the new start method. The old method will no longer work as expected.

```bash
git pull origin main
start.bat
```

That's it! 🎉
