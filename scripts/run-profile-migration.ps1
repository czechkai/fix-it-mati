# Run Database Migration for Profile Picture Feature
# This script applies the profile_image column migration to your database

Write-Host "======================================" -ForegroundColor Cyan
Write-Host "Profile Picture Database Migration" -ForegroundColor Cyan
Write-Host "======================================" -ForegroundColor Cyan
Write-Host ""

# Check if database config exists
if (-not (Test-Path "config\database.php")) {
    Write-Host "❌ Error: config\database.php not found" -ForegroundColor Red
    Write-Host "Please ensure your database configuration is set up"
    exit 1
}

Write-Host "📖 Reading database configuration..." -ForegroundColor Yellow

# For Supabase or remote PostgreSQL users
Write-Host ""
Write-Host "For Supabase users (recommended):" -ForegroundColor Green
Write-Host "  1. Open your Supabase project dashboard"
Write-Host "  2. Go to SQL Editor"
Write-Host "  3. Copy and paste the contents of: database\migrations\add_profile_image_column.sql"
Write-Host "  4. Click 'Run'"
Write-Host ""
Write-Host "Press Enter to view the migration file, or Ctrl+C to cancel..."
Read-Host

# Display the migration file
Write-Host ""
Write-Host "========== Migration SQL ==========" -ForegroundColor Cyan
Get-Content "database\migrations\add_profile_image_column.sql"
Write-Host "===================================" -ForegroundColor Cyan
Write-Host ""

Write-Host "Copy the SQL above and run it in your database." -ForegroundColor Yellow
Write-Host ""
Write-Host "After running the migration:" -ForegroundColor Green
Write-Host "  ✅ The profile_image column will be added to your users table"
Write-Host "  ✅ Profile pictures will save and persist"
Write-Host "  ✅ Profile pictures will display in the header on all pages"
Write-Host ""
Write-Host "Next steps:" -ForegroundColor Cyan
Write-Host "  1. Run the migration SQL in your database"
Write-Host "  2. Restart your PHP server if it's running"
Write-Host "  3. Go to Edit Profile page"
Write-Host "  4. Upload a profile picture"
Write-Host "  5. Save changes and refresh"
Write-Host "  6. Your profile picture should now display!"
Write-Host ""
