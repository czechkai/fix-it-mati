#!/bin/bash
# Run Database Migration for Profile Picture Feature
# This script applies the profile_image column migration to your database

echo "======================================"
echo "Profile Picture Database Migration"
echo "======================================"
echo ""

# Check if database config exists
if [ ! -f "config/database.php" ]; then
    echo "❌ Error: config/database.php not found"
    echo "Please ensure your database configuration is set up"
    exit 1
fi

# Extract database credentials from config file
echo "📖 Reading database configuration..."

# Get database connection details
DB_HOST=$(php -r "require 'config/database.php'; echo DB_HOST;")
DB_NAME=$(php -r "require 'config/database.php'; echo DB_NAME;")
DB_USER=$(php -r "require 'config/database.php'; echo DB_USER;")
DB_PASSWORD=$(php -r "require 'config/database.php'; echo DB_PASSWORD;")

echo "   Database: $DB_NAME"
echo "   Host: $DB_HOST"
echo "   User: $DB_USER"
echo ""

# Check if psql is available
if ! command -v psql &> /dev/null; then
    echo "❌ Error: psql command not found"
    echo "This script requires PostgreSQL client tools"
    echo ""
    echo "For Supabase users:"
    echo "  1. Open your Supabase project dashboard"
    echo "  2. Go to SQL Editor"
    echo "  3. Copy and paste the contents of: database/migrations/add_profile_image_column.sql"
    echo "  4. Click 'Run'"
    exit 1
fi

# Run the migration
echo "🚀 Running migration..."
echo ""

PGPASSWORD="$DB_PASSWORD" psql -h "$DB_HOST" -U "$DB_USER" -d "$DB_NAME" -f "database/migrations/add_profile_image_column.sql"

if [ $? -eq 0 ]; then
    echo ""
    echo "✅ Migration completed successfully!"
    echo ""
    echo "Next steps:"
    echo "  1. Restart your PHP server if it's running"
    echo "  2. Go to Edit Profile page"
    echo "  3. Upload a profile picture"
    echo "  4. Save changes and refresh"
    echo "  5. Your profile picture should now display!"
else
    echo ""
    echo "❌ Migration failed!"
    echo "Please check the error messages above"
    echo ""
    echo "If using Supabase, run the migration manually:"
    echo "  1. Open Supabase SQL Editor"
    echo "  2. Copy contents of: database/migrations/add_profile_image_column.sql"
    echo "  3. Run the SQL"
fi
