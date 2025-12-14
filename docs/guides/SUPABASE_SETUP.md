# 🔍 How to Get Supabase Database Credentials

## Problem
If you're getting this error:
```
could not translate host name "db.xxx.supabase.co" to address: Unknown host
```

This means the database host URL is incorrect or the Supabase project doesn't exist.

## Solution: Get Correct Credentials from Supabase

### Step 1: Login to Supabase
1. Go to https://supabase.com
2. Login with your account
3. Select your project: **FixItMati** (or whatever your team's project is named)

### Step 2: Get Database Credentials

1. **Click on "Project Settings"** (⚙️ icon in the left sidebar)
2. **Click on "Database"** tab
3. Scroll down to **"Connection string"** section
4. Select **"URI"** format
5. Copy the connection details

You'll see something like:
```
Host: db.xxxxxxxxxxxxxx.supabase.co
Database name: postgres
Port: 5432
User: postgres
Password: [your-project-password]
```

### Step 3: Update Your Config

**Option A: Using config/database.php**

Edit `config/database.php` and add these lines at the top (before the class):

```php
<?php
// Supabase Database Configuration
define('DB_HOST', 'db.xxxxxxxxxxxxxx.supabase.co');  // Replace with YOUR host
define('DB_PORT', '5432');
define('DB_NAME', 'postgres');
define('DB_USER', 'postgres');
define('DB_PASSWORD', 'your-actual-password');  // Replace with YOUR password
```

**Option B: Using .env file**

1. Copy `.env.example` to `.env`:
   ```bash
   copy .env.example .env
   ```

2. Edit `.env` and update:
   ```env
   DB_HOST=db.xxxxxxxxxxxxxx.supabase.co
   DB_PORT=5432
   DB_NAME=postgres
   DB_USER=postgres
   DB_PASSWORD=your-actual-password
   ```

### Step 4: Verify Connection

Run the requirements checker:
```bash
php check-requirements.php
```

It should show:
```
✓ Database connection successful
```

## Common Mistakes

### ❌ Wrong: Using pooler URL
```
aws-0-ap-southeast-1.pooler.supabase.com:6543
```

### ✓ Correct: Using direct database URL
```
db.xxxxxxxxxxxxxx.supabase.co:5432
```

**Note:** The pooler URL is for connection pooling and might not work with all PDO connections. Use the direct database URL instead.

## Alternative: Share Credentials with Team

If one team member has it working:

1. **Team member with working setup:**
   ```bash
   # Show your config (WITHOUT password)
   php -r "require 'config/database.php'; echo 'Host: ' . DB_HOST . '\nPort: ' . DB_PORT . '\n';"
   ```

2. **Share the output** + password separately (via secure channel)

3. **Other team members:** Update their config with these credentials

## Quick Test

After updating credentials, test the connection:

```bash
php -r "try { \$pdo = new PDO('pgsql:host=YOUR_HOST;port=5432;dbname=postgres', 'postgres', 'YOUR_PASSWORD'); echo 'SUCCESS!\n'; } catch (Exception \$e) { echo 'FAIL: ' . \$e->getMessage() . '\n'; }"
```

Replace `YOUR_HOST` and `YOUR_PASSWORD` with actual values.

## Still Not Working?

1. **Check if Supabase project is paused**
   - Free tier projects pause after 1 week of inactivity
   - Go to Supabase dashboard and click "Resume Project"

2. **Check if you're on the correct network**
   - Some corporate/school networks block database connections
   - Try from home network or use VPN

3. **Verify project exists**
   - Login to Supabase
   - Make sure the project is listed and active

4. **Ask team lead**
   - They should have the correct credentials
   - Get them to share via secure channel (not in code/git)

## Security Note ⚠️

- **NEVER** commit database credentials to git
- Use `.env` file (already in `.gitignore`)
- Or use `config/database.php` with defines (also in `.gitignore`)
- Share credentials via secure channels (encrypted chat, password manager, etc.)
