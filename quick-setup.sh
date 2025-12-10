#!/bin/bash
# ====================================
#   FixItMati - Quick Setup Script
# ====================================

echo ""
echo "===================================="
echo "   FixItMati Quick Setup"
echo "===================================="
echo ""

# Step 1: Check requirements
echo "[1/5] Checking system requirements..."
php check-requirements.php
if [ $? -ne 0 ]; then
    echo ""
    echo "ERROR: Requirements check failed!"
    echo "Please fix the issues above before continuing."
    exit 1
fi

echo ""
echo "[2/5] Setting up configuration..."
if [ ! -f "config/database.php" ]; then
    echo "Creating database config from template..."
    cp "config/database.template.php" "config/database.php"
    echo "Database config created with team credentials."
fi

if [ ! -f ".env" ]; then
    echo "Creating .env file from example..."
    cp ".env.example" ".env"
    echo ".env file created."
fi

echo "Configuration ready."
echo ""

# Step 3: Check if database exists
echo "[3/5] Verifying database connection..."
php -r "require 'config/database.php'; try { \$pdo = new PDO('pgsql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME, DB_USER, DB_PASSWORD); echo 'Database connection successful!'; } catch (Exception \$e) { echo 'Database connection failed: ' . \$e->getMessage(); exit(1); }"
if [ $? -ne 0 ]; then
    echo ""
    echo "ERROR: Cannot connect to database!"
    echo "Please check your config/database.php settings."
    exit 1
fi

echo ""
echo "[4/5] Setting up database schema..."
if [ -f "run-migration.php" ]; then
    php run-migration.php
    echo "Database schema created."
else
    echo "Warning: run-migration.php not found. Skipping database setup."
fi

echo ""
echo "[5/5] Seeding initial data..."
if [ -f "seed-all-data.php" ]; then
    php seed-all-data.php
    echo "Initial data seeded."
else
    echo "Warning: seed-all-data.php not found. Skipping data seeding."
fi

echo ""
echo "===================================="
echo "  Setup Complete!"
echo "===================================="
echo ""
echo "The FixItMati application is ready to use."
echo ""
echo "To start the server:"
echo "  ./start.sh"
echo ""
echo "Or manually:"
echo "  php -S localhost:8000"
echo ""
echo "Then open: http://localhost:8000"
echo ""
