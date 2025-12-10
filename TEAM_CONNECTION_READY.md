# ✅ TEAM CONNECTION - READY TO USE!

## 🚀 ONE-CLICK Setup (Seriously!)

**No PostgreSQL installation needed!** Database is cloud-hosted on Supabase.

### For Windows:
1. `git pull` (get latest code)
2. Double-click **`setup.bat`**
3. Double-click **`start.bat`**
4. Done! 🎉

### For Linux/Mac:
```bash
git pull
cp .env.example .env
php -S localhost:8000
```

### Then Login:
Go to: **http://localhost:8000/login.php**

**Test Account:**
- Email: `test.customer@example.com`
- Password: `customer123`

---

## ✅ What's Already Done

✓ Database connection configured with **Transaction Pooler**  
✓ Tested and working (5 users found in database)  
✓ All credentials pre-configured in `.env.example`  
✓ No manual setup needed!

---

## 🔧 Connection Details

The `.env.example` file contains:

```env
DB_HOST=aws-1-ap-southeast-2.pooler.supabase.com
DB_PORT=6543
DB_NAME=postgres
DB_USER=postgres.qyuwbrougimcexrjvrcm
DB_PASSWORD=fIxITmAtI123
```

**Why Transaction Pooler?**
- ✅ Better performance
- ✅ Works with IPv4 networks
- ✅ No DNS issues
- ✅ Optimized for multiple connections

---

## 🧪 Verify Everything Works

After setup.bat completes, you'll see:

```
SUCCESS: Connected!
Found 5 users
SETUP COMPLETE!
```

That's it! If you see this, everything is working.

**No need for manual testing** - setup.bat already did it for you!

---

## ⚠️ Troubleshooting

### "pdo_pgsql extension not enabled"
The setup.bat will tell you exactly what to do:
1. Run: `php --ini` to find your php.ini
2. Open php.ini file
3. Find line: `;extension=pdo_pgsql`
4. Remove semicolon to make it: `extension=pdo_pgsql`
5. Save file
6. Rerun setup.bat

### "Connection failed"
- Check your internet connection
- Supabase project might be paused (free tier)
- Visit https://supabase.com/dashboard
- Find project `qyuwbrougimcexrjvrcm` and click "Resume Project"
- Wait 2 minutes and rerun setup.bat

### "Server already running on port 8000"
```powershell
Get-Process -Name php | Stop-Process -Force
.\start.bat
```

### Still Having Issues?
Just rerun: `setup.bat` - it will tell you exactly what's wrong!

---

## 📁 Important Files

- **`setup.bat`** - ONE-CLICK setup (run this first!)
- **`start.bat`** - Starts the server (run this every time)
- **`.env`** - Your local config (DON'T COMMIT!)
- **`.env.example`** - Template with working credentials

---

## 👥 Test Accounts

| Role | Email | Password |
|------|-------|----------|
| Customer | test.customer@example.com | customer123 |
| Technician | test.technician@example.com | tech123 |
| Admin | test.admin@example.com | admin123 |

---

## 📚 More Documentation

- **API_WORKING.md** - API endpoints reference
- **TROUBLESHOOTING.md** - Detailed problem solutions
- **QUICK_START_BACKEND.md** - Backend development guide

---

**Connection Status:** ✅ TESTED AND WORKING  
**Last Verified:** December 11, 2025  
**Database:** Supabase PostgreSQL (Transaction Pooler)
