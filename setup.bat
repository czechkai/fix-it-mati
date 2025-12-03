@echo off
REM ===================================================================
REM FixItMati Project Setup Script
REM This script helps team members quickly set up the project
REM ===================================================================

echo.
echo ========================================
echo   FixItMati Project Setup
echo ========================================
echo.

REM Check if .env file exists
if exist .env (
    echo [INFO] .env file already exists.
    choice /C YN /M "Do you want to overwrite it"
    if errorlevel 2 goto :skip_env_creation
    if errorlevel 1 goto :create_env
) else (
    goto :create_env
)

:create_env
echo.
echo [STEP 1] Creating .env file from template...
if not exist .env.example (
    echo [ERROR] .env.example not found!
    echo Please ensure .env.example exists in the project root.
    pause
    exit /b 1
)

copy .env.example .env >nul
echo [SUCCESS] .env file created!
echo.

:skip_env_creation

REM Prompt for database password
echo [STEP 2] Database Configuration
echo.
echo Please enter your Supabase database password.
echo (This will be stored in .env file - which is NOT committed to git)
echo.
set /p DB_PASSWORD="Enter Database Password: "

if "%DB_PASSWORD%"=="" (
    echo [WARNING] No password entered. You'll need to add it manually to .env
    pause
) else (
    REM Update .env file with password
    powershell -Command "(Get-Content .env) -replace 'DB_PASSWORD=.*', 'DB_PASSWORD=%DB_PASSWORD%' | Set-Content .env"
    echo [SUCCESS] Database password saved to .env
)

echo.
echo [STEP 3] Verifying setup...
echo.

REM Check if PHP is installed
where php >nul 2>&1
if %ERRORLEVEL% NEQ 0 (
    echo [WARNING] PHP is not installed or not in PATH.
    echo Please install PHP to run this project.
    echo Download from: https://windows.php.net/download/
    echo.
) else (
    echo [SUCCESS] PHP is installed.
    php --version | findstr /C:"PHP"
)

REM Check PHP extensions
echo.
echo [INFO] Checking PHP extensions...
php -m | findstr /C:"pdo_pgsql" >nul 2>&1
if %ERRORLEVEL% NEQ 0 (
    echo [WARNING] PHP pdo_pgsql extension not found.
    echo This extension is required for PostgreSQL connection.
    echo Please enable it in your php.ini file:
    echo   1. Find php.ini file
    echo   2. Uncomment: extension=pdo_pgsql
    echo   3. Restart PHP
    echo.
) else (
    echo [SUCCESS] pdo_pgsql extension is enabled.
)

echo.
echo [STEP 4] Testing database connection...
echo.

REM Test database connection
php -r "try { require 'config/database.php'; $db = Database::getInstance(); $result = $db->testConnection(); if($result['success']) { echo '[SUCCESS] ' . $result['message'] . PHP_EOL; } else { echo '[ERROR] ' . $result['message'] . PHP_EOL; } } catch(Exception $e) { echo '[ERROR] ' . $e->getMessage() . PHP_EOL; }"

echo.
echo ========================================
echo   Setup Complete!
echo ========================================
echo.
echo Next steps:
echo   1. Review your .env file and ensure all values are correct
echo   2. Start the PHP development server:
echo      cd public
echo      php -S localhost:8000
echo   3. Open your browser to: http://localhost:8000
echo.
echo For team members:
echo   - Always run 'git pull' before starting work
echo   - Never commit the .env file (it's in .gitignore)
echo   - Run setup.bat if you encounter database connection issues
echo.
pause
