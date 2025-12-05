@echo off
REM FixItMati Development Server Starter
REM Simply run: start.bat

echo.
echo ========================================
echo   FixItMati Development Server
echo ========================================
echo.
echo Starting server on http://localhost:8000
echo Press Ctrl+C to stop the server
echo.

cd /d "%~dp0"
php -S localhost:8000

pause
