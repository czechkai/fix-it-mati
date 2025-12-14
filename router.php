<?php
/**
 * Root Router for PHP Built-in Server
 * Allows running: php -S localhost:8000 router.php
 * Also works with Apache when .htaccess is not available
 */

// Get the requested URI
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = ltrim($uri, '/');

// Remove any query strings
$uri = strtok($uri, '?');

// If it's an API request, route to public/api/index.php
if (strpos($uri, 'api/') === 0 || strpos($uri, 'api') === 0) {
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
    'admin-dashboard.php' => 'public/admin/dashboard.php',
    'admin/service-requests.php' => 'public/admin/service-requests.php',
    'admin/billing.php' => 'public/admin/billing.php',
    'admin/users.php' => 'public/admin/users.php',
    'admin/technicians.php' => 'public/admin/technicians.php',
    'admin/announcements.php' => 'public/admin/announcements.php',
    'admin/analytics.php' => 'public/admin/analytics.php'
];

// Check if requesting a mapped page
if (isset($pathMappings[$uri])) {
    $file = __DIR__ . '/' . $pathMappings[$uri];
    if (file_exists($file) && is_file($file)) {
        require $file;
        exit;
    }
}

// Check if requesting with public/ prefix (strip it and try mapping)
if (strpos($uri, 'public/') === 0) {
    $strippedUri = substr($uri, 7); // Remove 'public/'
    
    // Try to map the stripped URI
    if (isset($pathMappings[$strippedUri])) {
        $file = __DIR__ . '/' . $pathMappings[$strippedUri];
        if (file_exists($file) && is_file($file)) {
            require $file;
            exit;
        }
    }
    
    // Try direct access to public path
    $file = __DIR__ . '/' . $uri;
    if (file_exists($file) && is_file($file)) {
        require $file;
        exit;
    }
}

// Check if requesting a file in public/ directory structure
if (strpos($uri, 'pages/') === 0 || strpos($uri, 'admin/') === 0) {
    $file = __DIR__ . '/public/' . $uri;
    if (file_exists($file) && is_file($file)) {
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

// If still not found and it's a .php file, return 404
if (!empty($uri) && substr($uri, -4) === '.php') {
    http_response_code(404);
    echo "<!DOCTYPE html>
<html>
<head>
    <title>404 - Page Not Found</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; padding: 50px; }
        h1 { color: #e74c3c; }
        p { color: #7f8c8d; }
        a { color: #3498db; text-decoration: none; }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <h1>404 - Page Not Found</h1>
    <p>The requested page '{$uri}' was not found on this server.</p>
    <p><a href='/login.php'>Return to Login</a></p>
</body>
</html>";
    exit;
}

// For PHP built-in server, return false to let it handle static files
return false;
