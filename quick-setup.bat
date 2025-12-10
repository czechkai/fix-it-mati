@echo off
REM ====================================
REM   FixItMati - Quick Setup Script
REM ====================================

echo.
echo ====================================
echo    FixItMati Quick Setup
echo ====================================
echo.

REM Step 1: Check requirements
echo [1/5] Checking system requirements...
php check-requirements.php
if %ERRORLEVEL% NEQ 0 (
    echo.
    echo ERROR: Requirements check failed!
    echo Please fix the issues above before continuing.
    pause
    exit /b 1
)

echo.
echo [2/5] Setting up configuration...
if not exist "config\database.php" (
    echo Creating database config from template...
    copy "config\database.template.php" "config\database.php"
    echo Database config created with team credentials.
)

if not exist ".env" (
    echo Creating .env file from example...
    copy ".env.example" ".env"
    echo .env file created.
)

echo Configuration ready.
echo.

REM Step 3: Create logs directory
echo [3/5] Creating directories...
if not exist "logs" mkdir logs
echo Directories ready.

REM Step 4: Check database connection
echo [4/5] Verifying database connection...
php -r "require 'config/database.php'; try { $pdo = new PDO('pgsql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';sslmode=require', DB_USER, DB_PASSWORD); echo 'Database connection successful!'; } catch (Exception $e) { echo 'Database connection failed: ' . $e->getMessage(); exit(1); }"
if %ERRORLEVEL% NEQ 0 (
    echo.
    echo ERROR: Cannot connect to database!
    echo The database might be paused or unreachable.
    echo You can continue setup and test connection later.
    echo.
    choice /C YN /M "Continue anyway"
    if errorlevel 2 exit /b 1
)

echo.
echo [5/5] Setting up database schema...
if exist "run-migration.php" (
    php run-migration.php
    echo Database schema created.
) else (
    echo Warning: run-migration.php not found. Skipping database setup.
)

echo.
echo [6/6] Seeding initial data...
if exist "seed-all-data.php" (
    php seed-all-data.php
    echo Initial data seeded.
) else (
    echo Warning: seed-all-data.php not found. Skipping data seeding.
)

echo.
echo ====================================
echo   Setup Complete!
echo ====================================
echo.
echo The FixItMati application is ready to use.
echo.
echo To start the server:
echo   start.bat
echo.
echo Or manually:
echo   php -S localhost:8000
echo.
echo Then open: http://localhost:8000
echo.
pause
