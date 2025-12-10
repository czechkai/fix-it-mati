@echo off
REM ===================================================================
REM FixItMati - ONE-CLICK Setup
REM No PostgreSQL installation needed! Database is cloud-hosted.
REM ===================================================================

cls
echo.
echo ============================================
echo   FixItMati - ONE-CLICK Setup
echo ============================================
echo.
echo  No PostgreSQL installation needed!
echo  Database is cloud-hosted on Supabase.
echo.
echo ============================================
echo.

REM Step 1: Create .env
echo [1/4] Setting up configuration...

if not exist .env (
    if not exist .env.example (
        echo ERROR: .env.example not found!
        pause
        exit /b 1
    )
    copy .env.example .env >nul
    echo Created .env with database credentials!
) else (
    echo .env already exists - ready!
)

if not exist logs mkdir logs >nul 2>&1
echo.

REM Step 2: Check PHP
echo [2/4] Checking PHP...

where php >nul 2>&1
if errorlevel 1 (
    echo ERROR: PHP not installed!
    echo Install PHP or XAMPP first.
    pause
    exit /b 1
)

for /f "tokens=2" %%i in ('php --version ^| findstr /C:"PHP"') do (
    echo Found PHP %%i
    goto :phpfound
)
:phpfound
echo.

REM Step 3: Check extension
echo [3/4] Checking pdo_pgsql extension...

php -m | findstr /C:"pdo_pgsql" >nul 2>&1
if errorlevel 1 (
    echo.
    echo ERROR: pdo_pgsql extension not enabled!
    echo.
    echo Quick fix:
    echo   1. Run: php --ini
    echo   2. Open php.ini file
    echo   3. Find: ;extension=pdo_pgsql
    echo   4. Remove semicolon
    echo   5. Save and rerun setup.bat
    echo.
    pause
    exit /b 1
)

echo pdo_pgsql ready!
echo.

REM Step 4: Test database
echo [4/4] Testing database...
echo.

php -r "require 'Core/Database.php'; try { $db = FixItMati\Core\Database::getInstance(); $conn = $db->getConnection(); $stmt = $conn->query('SELECT COUNT(*) FROM users'); $count = $stmt->fetchColumn(); echo 'SUCCESS: Connected!' . PHP_EOL; echo 'Found ' . $count . ' users' . PHP_EOL; exit(0); } catch(Exception $e) { echo 'ERROR: ' . $e->getMessage() . PHP_EOL; exit(1); }"

if not errorlevel 1 goto :success

echo.
echo Connection failed! This means:
echo   - Internet down
echo   - Supabase project paused
echo.
echo To resume paused project:
echo   Visit: https://supabase.com/dashboard
echo   Find: qyuwbrougimcexrjvrcm
echo   Click: Resume Project
echo.
pause
exit /b 1

:success
echo.
echo ============================================
echo   SETUP COMPLETE!
echo ============================================
echo.
echo Your project is ready!
echo.
echo TO START THE SERVER:
echo   Double-click: start.bat
echo.
echo THEN LOGIN AT:
echo   http://localhost:8000/login.php
echo.
echo TEST ACCOUNT:
echo   Email: test.customer@example.com
echo   Password: customer123
echo.
echo ============================================
echo.
pause
