@echo off
REM Sync assets from public\assets to assets
REM This ensures PHP's built-in server serves the latest files

echo Syncing assets from public\assets\ to assets\...
echo.

xcopy /Y /Q "public\assets\*.*" "assets\"

echo.
echo Done! All assets synchronized.
echo.
pause
