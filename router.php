<?php
/**
 * Root Router for PHP Built-in Server
 * Allows running: php -S localhost:8000
 * Without needing: php -S localhost:8000 -t public
 */

// Get the requested URI
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Remove leading slash
$uri = ltrim($uri, '/');

// If it's an API request, route to public/api/index.php
if (strpos($uri, 'api/') === 0) {
    $_SERVER['SCRIPT_NAME'] = '/public/api/index.php';
    require __DIR__ . '/public/api/index.php';
    exit;
}

// Define public pages that should be served from public/
$publicPages = [
    'login.php',
    'register.php',
    'logout.php',
    'user-dashboard.php',
    'active-requests.php',
    'announcements.php',
    'payments.php',
    'payment-history.php',
    'create-request.php',
    'service-addresses.php',
    'linked-meters.php',
    'help-support.php',
    'edit-profile.php',
    'service-history.php',
    'discussions.php',
    'settings.php',
    'debug-active-requests.php',
    'token-debug.php',
    'test-login.php',
    'test-db.php',
    'test-frontend.html',
    'test-sprint3.php',
    'setup-check.html'
];

// Check if requesting a public page
if (in_array($uri, $publicPages)) {
    $file = __DIR__ . '/public/' . $uri;
    if (file_exists($file)) {
        require $file;
        exit;
    }
}

// Check if requesting an asset file
if (strpos($uri, 'assets/') === 0) {
    $file = __DIR__ . '/' . $uri;  // Serve from root assets/
    if (file_exists($file)) {
        // Determine content type
        $ext = pathinfo($file, PATHINFO_EXTENSION);
        $contentTypes = [
            'css' => 'text/css',
            'js' => 'application/javascript',
            'json' => 'application/json',
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'svg' => 'image/svg+xml',
            'ico' => 'image/x-icon',
            'woff' => 'font/woff',
            'woff2' => 'font/woff2',
            'ttf' => 'font/ttf',
            'eot' => 'application/vnd.ms-fontobject'
        ];
        
        if (isset($contentTypes[$ext])) {
            header('Content-Type: ' . $contentTypes[$ext]);
        }
        
        readfile($file);
        exit;
    }
}

// If empty URI or root, redirect to login
if (empty($uri) || $uri === 'index.php') {
    header('Location: /login.php');
    exit;
}

// For PHP built-in server, return false to let it handle static files
return false;
