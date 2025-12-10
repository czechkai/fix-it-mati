# 🔧 FixItMati Troubleshooting Flowchart

## Start Here: What's Your Issue?

### 1️⃣ Can't Login / Error: "An error occurred. Please try again later"

**Check Browser Console (F12 → Console tab)**

#### See: "could not find driver"
→ PostgreSQL PDO extension not enabled
→ **FIX:** 
```bash
# Windows: Edit php.ini, uncomment extension=pdo_pgsql
php --ini  # Find php.ini location
# Edit it, remove ; from ;extension=pdo_pgsql

# Linux:
sudo apt-get install php-pgsql

# Verify:
php -m | grep pdo_pgsql
```

#### See: "Database connection failed"
→ Wrong database credentials
→ **FIX:** Edit `config/database.php` with correct credentials

#### See: "Cannot connect to server"
→ PostgreSQL server not running or wrong host
→ **FIX:** Verify DB_HOST and DB_PORT in `config/database.php`

---

### 2️⃣ Register Page Redirects After Logout

**Cause:** Browser cache or session cookie persisting

**FIX:**
1. Close ALL browser tabs
2. Open NEW incognito/private window
3. Go to: http://localhost:8000/logout.php
4. Then try: http://localhost:8000/register.php

**Or:** Clear browser cache (Ctrl+Shift+Delete)

---

### 3️⃣ Setup Script Fails / Requirements Check Fails

**Run diagnostic:**
```bash
php check-requirements.php
```

**Follow the specific instructions it shows for each error**

Common fixes:
- Missing extensions → Edit php.ini
- No database config → Create `config/database.php`
- Can't connect to DB → Check credentials

---

### 4️⃣ Server Won't Start / Port Already in Use

**Error:** `Address already in use`

**FIX:** Use different port:
```bash
php -S localhost:8080
```

**Or find what's using port 8000:**
```bash
# Windows:
netstat -ano | findstr :8000

# Linux/Mac:
lsof -i :8000
```

---

### 5️⃣ Pages Load but Look Broken / No Styling

**Cause:** Assets not loading

**Check:**
1. Open browser console (F12)
2. Look for 404 errors on .css/.js files

**FIX:**
- Verify server is running from project root
- Check `/assets/` folder exists
- Clear browser cache

---

### 6️⃣ API Calls Fail / 500 Internal Server Error

**Check server terminal logs** (where you ran `php -S`)

**Common causes:**
1. **Database connection issues**
   → Run: `php check-requirements.php`

2. **Missing PHP extensions**
   → Check: `php -m` for required extensions

3. **Wrong file permissions**
   → Ensure `/logs/` is writable

**FIX:** Check the specific error in terminal logs

---

### 7️⃣ After Git Pull, Things Break

**Quick fix sequence:**
```bash
# 1. Check requirements
php check-requirements.php

# 2. Run migrations (if database changed)
php run-migration.php

# 3. Clear any caches
# Close browser, open new tab

# 4. Restart server
# Ctrl+C to stop, then: start.bat
```

---

### 8️⃣ Fresh Install Not Working

**Follow checklist:**

```bash
# 1. Check requirements FIRST
php check-requirements.php
# Must show: ✓ ALL REQUIREMENTS MET

# 2. Create database config
copy config\database_examples.php config\database.php
# Edit with your credentials

# 3. Run automated setup
quick-setup.bat

# 4. Verify with web check
start.bat
# Open: http://localhost:8000/setup-check.html
```

---

## 🔍 Quick Diagnostic Commands

### Check PHP Extensions:
```bash
php -m | findstr "pdo pgsql json mbstring openssl"
```

### Check PHP Version:
```bash
php -v
```

### Test Database Connection:
```bash
php -r "require 'config/database.php'; try { new PDO('pgsql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASSWORD); echo 'OK'; } catch(Exception $e) { echo $e->getMessage(); }"
```

### Check if Server Running:
```bash
curl http://localhost:8000
# Or visit in browser
```

---

## 📞 Still Stuck?

### Gather This Info:
1. **Run this and copy output:**
   ```bash
   php check-requirements.php
   ```

2. **Browser console errors** (F12 → Console)

3. **Server terminal errors** (where you ran `php -S`)

4. **What you were trying to do** when error occurred

### Share with Team:
- Paste the outputs above
- Describe what you tried
- Screenshots help!

---

## ✅ Success Indicators

Your setup is working when:
- ✅ `php check-requirements.php` → All checks pass
- ✅ `http://localhost:8000/setup-check.html` → All green
- ✅ Can login at `http://localhost:8000/login.php`
- ✅ No errors in browser console
- ✅ No errors in server terminal

---

## 🎯 Most Common Issue

**90% of team issues:** PostgreSQL PDO extension not enabled

**Quick fix:**
1. `php --ini` to find php.ini
2. Edit php.ini
3. Uncomment: `extension=pdo_pgsql`
4. Restart terminal
5. Run: `php check-requirements.php`

That's it! 🎉
