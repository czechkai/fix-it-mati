#!/bin/bash
# Script to fix relative paths in PHP files
# Converts relative paths like href="login.php" to href="/login.php"

echo "Fixing relative paths in PHP files..."

# Find all PHP files in public directory
find public/pages public/admin -name "*.php" -type f | while read file; do
    echo "Processing: $file"
    
    # Use sed to add leading slash to relative .php hrefs
    # Match href="somepage.php" and convert to href="/somepage.php"
    # But skip those that already have /, http, https, #, or ?
    
    sed -i 's|href="\([a-zA-Z0-9_-]\+\.php\)"|href="/\1"|g' "$file"
    sed -i "s|href='\([a-zA-Z0-9_-]\+\.php\)'|href='/\1'|g" "$file"
    
    # Also fix window.location.href assignments
    sed -i 's|window\.location\.href = "\([a-zA-Z0-9_-]\+\.php\)"|window.location.href = "/\1"|g' "$file"
    sed -i "s|window\.location\.href = '\([a-zA-Z0-9_-]\+\.php\)'|window.location.href = '/\1'|g" "$file"
    
    # Fix window.location.replace
    sed -i 's|window\.location\.replace("\([a-zA-Z0-9_-]\+\.php\)"|window.location.replace("/\1"|g' "$file"
    sed -i "s|window\.location\.replace('\([a-zA-Z0-9_-]\+\.php\)'|window.location.replace('/\1'|g" "$file"
done

echo "Path fixing complete!"
