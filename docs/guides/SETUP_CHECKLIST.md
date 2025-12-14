# Team Setup Checklist

Use this checklist when setting up the FixItMati project for the first time.

## For Project Lead / Database Admin

- [ ] **Supabase Setup**
  - [ ] Create Supabase account at https://supabase.com
  - [ ] Create new project
  - [ ] Note down database credentials (host, port, password)
  - [ ] Note down API keys (anon public, service role)
  - [ ] Run SQL schema from `database/schema.sql` in Supabase SQL Editor
  - [ ] Configure Row Level Security (RLS) policies
  - [ ] Test connection from local machine

- [ ] **Share Credentials Securely**
  - [ ] Share database password with team (NOT via git)
  - [ ] Document credentials in team password manager
  - [ ] Update `.env.example` if structure changes

- [ ] **Git Repository**
  - [ ] Push code to GitHub
  - [ ] Verify `.env` is in `.gitignore`
  - [ ] Add team members as collaborators
  - [ ] Protect `main` branch (require PRs)

## For Each Team Member

### Initial Setup

- [ ] **Install Prerequisites**
  - [ ] PHP 7.4+ installed
  - [ ] Git installed
  - [ ] Text editor (VS Code recommended)
  - [ ] Check PHP version: `php --version`

- [ ] **Clone Repository**
  ```bash
  git clone https://github.com/czechkai/fix-it-mati.git
  cd fix-it-mati
  ```

- [ ] **Run Setup Script**
  ```bash
  setup.bat
  ```
  - [ ] Enter database password when prompted
  - [ ] Verify all checks pass

- [ ] **Enable PHP Extensions**
  - [ ] Find php.ini: `php --ini`
  - [ ] Enable: `extension=pdo_pgsql`
  - [ ] Enable: `extension=pdo`
  - [ ] Enable: `extension=mbstring`
  - [ ] Restart if needed

- [ ] **Test Database Connection**
  ```bash
  cd public
  php -S localhost:8000
  ```
  - [ ] Visit: http://localhost:8000/test-db.php
  - [ ] Verify connection successful

- [ ] **Verify Pages Load**
  - [ ] http://localhost:8000/user-dashboard.php
  - [ ] http://localhost:8000/active-requests.php
  - [ ] http://localhost:8000/announcements.php
  - [ ] http://localhost:8000/payments.php

### Configuration Verification

- [ ] **Check Files Exist**
  - [ ] `.env` file exists (created by setup.bat)
  - [ ] `.gitignore` includes `.env`
  - [ ] All pages in `public/` directory
  - [ ] All assets in `assets/` directory

- [ ] **Verify Environment Variables**
  - [ ] Open `.env` file
  - [ ] DB_HOST is set correctly
  - [ ] DB_PASSWORD is filled in
  - [ ] SUPABASE_URL is set
  - [ ] SUPABASE_ANON_KEY is set

- [ ] **Test Git Configuration**
  ```bash
  git status
  # .env should NOT appear in untracked files
  ```

## Daily Development Checklist

- [ ] **Before Starting Work**
  - [ ] `git pull origin main`
  - [ ] Check for merge conflicts
  - [ ] Start dev server: `cd public; php -S localhost:8000`

- [ ] **During Development**
  - [ ] Work on feature branch
  - [ ] Test changes locally
  - [ ] Commit frequently with clear messages
  - [ ] Don't commit `.env` file

- [ ] **Before Pushing**
  - [ ] Test all functionality
  - [ ] Check no sensitive data in commits
  - [ ] Verify `.env` not staged: `git status`
  - [ ] Push to feature branch
  - [ ] Create Pull Request

## Troubleshooting Checklist

### Database Connection Failed

- [ ] `.env` file exists
- [ ] Database password is correct in `.env`
- [ ] PHP pdo_pgsql extension is enabled
- [ ] Internet connection is active
- [ ] Visit `/test-db.php` for detailed error

### PHP Extensions Not Found

- [ ] PHP is installed and in PATH
- [ ] Find php.ini: `php --ini`
- [ ] Edit php.ini and uncomment extensions
- [ ] Restart terminal/server
- [ ] Verify: `php -m | findstr pdo_pgsql`

### Pages Not Loading

- [ ] Server is running: `php -S localhost:8000`
- [ ] Correct URL: `http://localhost:8000/user-dashboard.php`
- [ ] Check browser console for errors
- [ ] Check PHP error log

### Git Issues

- [ ] `.gitignore` exists and includes `.env`
- [ ] Run: `git rm --cached .env` if already committed
- [ ] Verify: `git status` (should not show .env)

## Security Checklist

- [ ] **Never Commit**
  - [ ] `.env` file
  - [ ] Database passwords
  - [ ] API keys
  - [ ] Any credentials

- [ ] **Always Use**
  - [ ] `.env.example` for templates
  - [ ] `.gitignore` for sensitive files
  - [ ] Secure channels for sharing passwords
  - [ ] Environment variables for config

- [ ] **Review Before Push**
  - [ ] `git diff` to see changes
  - [ ] No hardcoded credentials
  - [ ] No debug/test data

## Team Collaboration Checklist

- [ ] **Communication**
  - [ ] Notify team before major changes
  - [ ] Document database schema changes
  - [ ] Update README for new features
  - [ ] Use descriptive branch names

- [ ] **Code Quality**
  - [ ] Follow existing code style
  - [ ] Add comments for complex logic
  - [ ] Test before pushing
  - [ ] Review own code before PR

## Resources

- **Documentation**
  - [ ] Read `README.md`
  - [ ] Review `QUICK_REFERENCE.md`
  - [ ] Check `config/database_examples.php`

- **External Links**
  - [ ] [Supabase Docs](https://supabase.com/docs)
  - [ ] [PHP Manual](https://www.php.net/manual/en/)
  - [ ] [Git Guide](https://git-scm.com/docs)

---

**Setup Complete?** Start coding! 🚀

If you encounter issues not covered here, contact the team lead or check the troubleshooting section in README.md.
