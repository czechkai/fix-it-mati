# PowerShell script to fix relative paths in PHP files
# Converts relative paths like href="login.php" to href="/login.php"

Write-Host "Fixing relative paths in PHP files..." -ForegroundColor Green

# Get all PHP files in public directory
$phpFiles = Get-ChildItem -Path "public" -Filter "*.php" -Recurse -File

$totalFiles = $phpFiles.Count
$processedFiles = 0

foreach ($file in $phpFiles) {
    $processedFiles++
    Write-Host "[$processedFiles/$totalFiles] Processing: $($file.FullName)" -ForegroundColor Cyan
    
    $content = Get-Content $file.FullName -Raw -Encoding UTF8
    $originalContent = $content
    
    # Fix href="page.php" to href="/page.php"
    # Only fix if it doesn't already start with /, http, https, #, or ?
    $content = $content -replace 'href="(?!http|/|#|\?)([a-zA-Z0-9_-]+\.php)"', 'href="/$1"'
    $content = $content -replace "href='(?!http|/|#|\?)([a-zA-Z0-9_-]+\.php)'", "href='/$1'"
    
    # Fix window.location.href = "page.php"
    $content = $content -replace 'window\.location\.href\s*=\s*"(?!http|/|#|\?)([a-zA-Z0-9_-]+\.php)"', 'window.location.href = "/$1"'
    $content = $content -replace "window\.location\.href\s*=\s*'(?!http|/|#|\?)([a-zA-Z0-9_-]+\.php)'", "window.location.href = '/$1'"
    
    # Fix window.location.replace("page.php")
    $content = $content -replace 'window\.location\.replace\("(?!http|/|#|\?)([a-zA-Z0-9_-]+\.php)"\)', 'window.location.replace("/$1")'
    $content = $content -replace "window\.location\.replace\('(?!http|/|#|\?)([a-zA-Z0-9_-]+\.php)'\)", "window.location.replace('/$1')"
    
    # Only write if content changed
    if ($content -ne $originalContent) {
        Set-Content -Path $file.FullName -Value $content -Encoding UTF8 -NoNewline
        Write-Host "  Updated" -ForegroundColor Yellow
    } else {
        Write-Host "  No changes needed" -ForegroundColor Gray
    }
}

Write-Host ""
Write-Host "Path fixing complete! Processed $totalFiles files." -ForegroundColor Green
