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

# Step 3: Create logs directory
echo "[3/5] Creating directories..."
mkdir -p logs
echo "Directories ready."

# Step 4: Check database connection
echo "[4/5] Verifying database connection..."
php -r "require 'config/database.php'; try { \$pdo = new PDO('pgsql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';sslmode=require', DB_USER, DB_PASSWORD); echo 'Database connection successful!'; } catch (Exception \$e) { echo 'Database connection failed: ' . \$e->getMessage(); exit(1); }"
if [ $? -ne 0 ]; then
    echo ""
    echo "ERROR: Cannot connect to database!"
    echo "The database might be paused or unreachable."
    echo "You can continue setup and test connection later."
    echo ""
    read -p "Continue anyway? (y/n) " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        exit 1
    fi
fi

echo ""
echo "[5/5] Setting up database schema..."
if [ -f "run-migration.php" ]; then
    php run-migration.php
    echo "Database schema created."
else
    echo "Warning: run-migration.php not found. Skipping database setup."
fi

echo ""
echo "[6/6] Seeding initial data..."
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
