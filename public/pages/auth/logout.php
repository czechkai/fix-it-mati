<?php
/**
 * Logout page for FixItMati
 * Clears session and redirects to login
 */

session_start();

// Clear all session variables
$_SESSION = [];

// If it's desired to kill the session, also delete the session cookie.
// Note: This will destroy the session, and not just the session data!
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroy the session
session_destroy();

// Clear remember me cookie if it exists
if (isset($_COOKIE['remember_token'])) {
    setcookie('remember_token', '', time() - 3600, '/');
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Logging out...</title>
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
</head>
<body>
    <script>
        // Clear all client-side storage FIRST
        sessionStorage.clear();
        localStorage.clear();
        
        // Clear any cached pages
        if ('caches' in window) {
            caches.keys().then(function(names) {
                names.forEach(function(name) {
                    caches.delete(name);
                });
            });
        }
        
        // Redirect to login with cache-busting parameter
        window.location.replace('login.php?t=' + Date.now());
    </script>
    <p>Logging out...</p>
</body>
</html>
