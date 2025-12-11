@echo off
REM ===================================================================
REM Fix Database Connection - For Team Members with Connection Issues
REM ===================================================================

echo.
echo ============================================
echo   Fixing Database Connection
echo ============================================
echo.
echo This will update your .env file to use
echo the working Transaction Pooler connection.
echo.

if not exist .env (
    if not exist .env.example (
        echo ERROR: .env.example not found!
        pause
        exit /b 1
    )
    echo Creating .env from template...
    copy .env.example .env >nul
    echo Done! Run setup.bat now.
    pause
    exit /b 0
)

echo Backing up your .env...
copy .env .env.backup >nul
echo Backup saved to .env.backup

echo.
echo Updating database connection settings...

powershell -Command "$content = Get-Content .env; $content = $content -replace 'DB_HOST=db\.qyuwbrougimcexrjvrcm\.supabase\.co', 'DB_HOST=aws-1-ap-southeast-2.pooler.supabase.com'; $content = $content -replace 'DB_HOST=aws-0-ap-southeast-1\.pooler\.supabase\.com', 'DB_HOST=aws-1-ap-southeast-2.pooler.supabase.com'; $content = $content -replace 'DB_PORT=5432', 'DB_PORT=6543'; $content = $content -replace 'DB_USER=postgres$', 'DB_USER=postgres.qyuwbrougimcexrjvrcm'; $content | Set-Content .env"

echo.
echo ============================================
echo   Connection Settings Updated!
echo ============================================
echo.
echo Your .env now uses:
echo   Host: aws-1-ap-southeast-2.pooler.supabase.com
echo   Port: 6543
echo   User: postgres.qyuwbrougimcexrjvrcm
echo.
echo Original .env backed up to: .env.backup
echo.
echo Now run: setup.bat
echo.
pause
