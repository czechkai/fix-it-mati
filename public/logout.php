<?php
/**
 * Logout page for FixItMati
 * Clears session and redirects to login
 */

session_start();

// Clear all session variables
$_SESSION = [];

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
</head>
<body>
    <script>
        // Clear all client-side storage
        sessionStorage.clear();
        localStorage.clear();
        
        // Redirect to login
        window.location.href = 'login.php';
    </script>
    <p>Logging out...</p>
</body>
</html>
