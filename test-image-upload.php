<?php
/**
 * Test script to verify image upload functionality
 * Run this from command line: php test-image-upload.php
 */

require_once __DIR__ . '/autoload.php';

echo "Testing Image Upload Functionality\n";
echo "==================================\n\n";

// Test 1: Check if getimagesize function works
echo "1. Testing getimagesize function... ";
$testImagePath = __DIR__ . '/uploads/profiles';
if (function_exists('getimagesize')) {
    echo "✓ getimagesize is available\n";
} else {
    echo "✗ getimagesize is NOT available\n";
    exit(1);
}

// Test 2: Check uploads directory
echo "2. Checking uploads directory... ";
if (is_dir($testImagePath)) {
    echo "✓ Directory exists: $testImagePath\n";
} else {
    echo "✗ Directory does NOT exist\n";
    if (mkdir($testImagePath, 0755, true)) {
        echo "  → Created directory\n";
    }
}

// Test 3: Check if directory is writable
echo "3. Checking directory permissions... ";
if (is_writable($testImagePath)) {
    echo "✓ Directory is writable\n";
} else {
    echo "✗ Directory is NOT writable\n";
    exit(1);
}

// Test 4: List existing profile images
echo "4. Listing existing profile images... ";
$files = glob($testImagePath . '/profile_*.*');
if (empty($files)) {
    echo "No profile images found\n";
} else {
    echo "\n";
    foreach ($files as $file) {
        $basename = basename($file);
        $size = filesize($file);
        $imageInfo = @getimagesize($file);
        $mimeType = $imageInfo ? $imageInfo['mime'] : 'unknown';
        echo "   - $basename ($size bytes, $mimeType)\n";
    }
}

// Test 5: Verify AuthController doesn't use finfo
echo "\n5. Checking AuthController for finfo usage... ";
$authControllerPath = __DIR__ . '/Controllers/AuthController.php';
$content = file_get_contents($authControllerPath);
if (strpos($content, 'new \\finfo') !== false || strpos($content, 'new finfo') !== false) {
    echo "⚠ WARNING: AuthController still uses finfo class\n";
} else {
    echo "✓ AuthController does not use finfo\n";
}

// Test 6: Check if getimagesize is used instead
echo "6. Checking if getimagesize is used... ";
if (strpos($content, 'getimagesize') !== false) {
    echo "✓ getimagesize is being used\n";
} else {
    echo "✗ getimagesize is NOT being used\n";
}

echo "\n==================================\n";
echo "All tests completed!\n\n";
echo "You can now test the profile image upload at:\n";
echo "http://localhost:8000/public/edit-profile.php\n";
