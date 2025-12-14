# Path Verification Test Script
# Tests that all critical pages can be accessed

Write-Host "=== FixItMati Path Verification Test ===" -ForegroundColor Cyan
Write-Host ""

# Check if server is running
$serverUrl = "http://localhost:8000"

Write-Host "Checking if server is running at $serverUrl..." -ForegroundColor Yellow

try {
    $response = Invoke-WebRequest -Uri $serverUrl -Method Head -TimeoutSec 2 -ErrorAction Stop
    Write-Host "✓ Server is running!" -ForegroundColor Green
} catch {
    Write-Host "✗ Server is not running!" -ForegroundColor Red
    Write-Host ""
    Write-Host "Please start the server first:" -ForegroundColor Yellow
    Write-Host "  php -S localhost:8000 router.php" -ForegroundColor White
    Write-Host "or run:" -ForegroundColor Yellow
    Write-Host "  start.bat" -ForegroundColor White
    exit 1
}

Write-Host ""
Write-Host "Testing page accessibility..." -ForegroundColor Yellow
Write-Host ""

# Define pages to test
$pages = @(
    @{Name="Login"; Url="/login.php"; ExpectedStatus=200},
    @{Name="Register"; Url="/register.php"; ExpectedStatus=200},
    @{Name="User Dashboard"; Url="/user-dashboard.php"; ExpectedStatus=200},
    @{Name="Admin Dashboard"; Url="/admin-dashboard.php"; ExpectedStatus=200},
    @{Name="Active Requests"; Url="/active-requests.php"; ExpectedStatus=200},
    @{Name="Announcements"; Url="/announcements.php"; ExpectedStatus=200},
    @{Name="Payments"; Url="/payments.php"; ExpectedStatus=200},
    @{Name="Service Addresses"; Url="/service-addresses.php"; ExpectedStatus=200},
    @{Name="Create Request"; Url="/create-request.php"; ExpectedStatus=200},
    @{Name="API Check"; Url="/api/auth/check"; ExpectedStatus=200}
)

$passCount = 0
$failCount = 0

foreach ($page in $pages) {
    $url = "$serverUrl$($page.Url)"
    
    try {
        $response = Invoke-WebRequest -Uri $url -Method Get -TimeoutSec 5 -ErrorAction Stop
        
        if ($response.StatusCode -eq $page.ExpectedStatus) {
            Write-Host "✓ $($page.Name) - PASSED" -ForegroundColor Green
            Write-Host "  URL: $($page.Url)" -ForegroundColor Gray
            $passCount++
        } else {
            Write-Host "✗ $($page.Name) - FAILED (Status: $($response.StatusCode))" -ForegroundColor Red
            Write-Host "  URL: $($page.Url)" -ForegroundColor Gray
            $failCount++
        }
    } catch {
        Write-Host "✗ $($page.Name) - FAILED" -ForegroundColor Red
        Write-Host "  URL: $($page.Url)" -ForegroundColor Gray
        Write-Host "  Error: $($_.Exception.Message)" -ForegroundColor DarkRed
        $failCount++
    }
    
    Write-Host ""
}

# Test assets
Write-Host "Testing asset accessibility..." -ForegroundColor Yellow
Write-Host ""

$assets = @(
    "/assets/style.css",
    "/assets/app.js",
    "/assets/auth-check.js"
)

foreach ($asset in $assets) {
    $url = "$serverUrl$asset"
    
    try {
        $response = Invoke-WebRequest -Uri $url -Method Head -TimeoutSec 5 -ErrorAction Stop
        
        if ($response.StatusCode -eq 200) {
            Write-Host "✓ $asset - ACCESSIBLE" -ForegroundColor Green
            $passCount++
        } else {
            Write-Host "✗ $asset - NOT ACCESSIBLE (Status: $($response.StatusCode))" -ForegroundColor Red
            $failCount++
        }
    } catch {
        Write-Host "✗ $asset - NOT ACCESSIBLE" -ForegroundColor Red
        Write-Host "  Error: $($_.Exception.Message)" -ForegroundColor DarkRed
        $failCount++
    }
}

Write-Host ""
Write-Host "=== Test Results ===" -ForegroundColor Cyan
Write-Host "Passed: $passCount" -ForegroundColor Green
Write-Host "Failed: $failCount" -ForegroundColor Red
Write-Host ""

if ($failCount -eq 0) {
    Write-Host "All tests passed! ✓" -ForegroundColor Green
    Write-Host "Your application is properly configured and all pages are accessible." -ForegroundColor Green
} else {
    Write-Host "Some tests failed. Please check the errors above." -ForegroundColor Yellow
    Write-Host "Common issues:" -ForegroundColor Yellow
    Write-Host "  - Database not configured (pages that require DB will fail)" -ForegroundColor White
    Write-Host "  - Missing files in assets/ directory" -ForegroundColor White
    Write-Host "  - Check docs/ROUTING.md for more information" -ForegroundColor White
}

Write-Host ""
