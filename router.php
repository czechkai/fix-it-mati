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

// Define mappings for old paths to new paths
$pathMappings = [
    'login.php' => 'public/pages/auth/login.php',
    'register.php' => 'public/pages/auth/register.php',
    'logout.php' => 'public/pages/auth/logout.php',
    'user-dashboard.php' => 'public/pages/user/user-dashboard.php',
    'active-requests.php' => 'public/pages/user/active-requests.php',
    'announcements.php' => 'public/pages/user/announcements.php',
    'notifications.php' => 'public/pages/user/notifications.php',
    'payments.php' => 'public/pages/user/payments.php',
    'payment-history.php' => 'public/pages/user/payment-history.php',
    'create-request.php' => 'public/pages/user/create-request.php',
    'service-addresses.php' => 'public/pages/user/service-addresses.php',
    'linked-meters.php' => 'public/pages/user/linked-meters.php',
    'help-support.php' => 'public/pages/user/help-support.php',
    'edit-profile.php' => 'public/pages/user/edit-profile.php',
    'service-history.php' => 'public/pages/user/service-history.php',
    'discussions.php' => 'public/pages/user/discussions.php',
    'discussion-detail.php' => 'public/pages/user/discussion-detail.php',
    'settings.php' => 'public/pages/user/settings.php',
    'admin-dashboard.php' => 'public/admin/dashboard.php'
];

// Check if requesting a mapped page
if (isset($pathMappings[$uri])) {
    $file = __DIR__ . '/' . $pathMappings[$uri];
    if (file_exists($file)) {
        require $file;
        exit;
    }
}

// Check if requesting a file in public/ directory structure
if (strpos($uri, 'pages/') === 0 || strpos($uri, 'admin/') === 0) {
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
    header('Location: /pages/auth/login.php');
    exit;
}

// For PHP built-in server, return false to let it handle static files
return false;
