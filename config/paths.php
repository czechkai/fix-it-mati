<?php
/**
 * Path Helper Functions
 * Provides consistent URL generation across the application
 * Include this file in all pages for consistent path handling
 */

// Define base path - works for both Apache and PHP built-in server
if (!defined('BASE_PATH')) {
    // Check if we're running on built-in server or Apache
    $scriptName = $_SERVER['SCRIPT_NAME'];
    if (strpos($scriptName, '/public/') !== false) {
        // We're accessing via public/ directory
        define('BASE_PATH', '/');
    } else {
        // Standard access
        define('BASE_PATH', '/');
    }
}

/**
 * Generate a URL for a page
 * @param string $page The page name (e.g., 'login.php', 'user-dashboard.php')
 * @return string The full URL path
 */
function url($page) {
    // Remove leading slash if present
    $page = ltrim($page, '/');
    
    // Return with base path
    return BASE_PATH . $page;
}

/**
 * Generate a URL for an asset
 * @param string $asset The asset path (e.g., 'style.css', 'app.js')
 * @return string The full asset URL path
 */
function asset($asset) {
    // Remove leading slash if present
    $asset = ltrim($asset, '/');
    
    // Add assets/ prefix if not present
    if (strpos($asset, 'assets/') !== 0) {
        $asset = 'assets/' . $asset;
    }
    
    return BASE_PATH . $asset;
}

/**
 * Generate a URL for an API endpoint
 * @param string $endpoint The API endpoint (e.g., 'auth/login', 'requests')
 * @return string The full API URL path
 */
function api($endpoint) {
    // Remove leading slash if present
    $endpoint = ltrim($endpoint, '/');
    
    // Add api/ prefix if not present
    if (strpos($endpoint, 'api/') !== 0) {
        $endpoint = 'api/' . $endpoint;
    }
    
    return BASE_PATH . $endpoint;
}

/**
 * Redirect to a page
 * @param string $page The page to redirect to
 * @param int $statusCode HTTP status code (default 302)
 */
function redirect($page, $statusCode = 302) {
    $url = url($page);
    header("Location: $url", true, $statusCode);
    exit;
}

/**
 * Check if current page matches the given page
 * @param string $page The page to check
 * @return bool True if current page matches
 */
function isCurrentPage($page) {
    $currentUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $currentUri = ltrim($currentUri, '/');
    
    // Remove query string
    $currentUri = strtok($currentUri, '?');
    
    // Check direct match
    if ($currentUri === $page || $currentUri === ltrim($page, '/')) {
        return true;
    }
    
    // Check if current URI ends with the page
    if (substr($currentUri, -strlen($page)) === $page) {
        return true;
    }
    
    return false;
}

/**
 * Get the current page name
 * @return string The current page name
 */
function currentPage() {
    $currentUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    return basename($currentUri);
}

/**
 * Generate absolute URL (with domain)
 * @param string $page The page name
 * @return string The absolute URL
 */
function absoluteUrl($page = '') {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    
    if (empty($page)) {
        return "$protocol://$host" . BASE_PATH;
    }
    
    return "$protocol://$host" . url($page);
}
