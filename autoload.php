<?php
/**
 * Autoloader for FixItMati
 * PSR-4 compatible autoloader
 */

// Load PHPMailer if available (check both vendor and manually extracted)
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require __DIR__ . '/vendor/autoload.php';
} elseif (file_exists(__DIR__ . '/PHPMailer-master/src/PHPMailer.php')) {
    // Manual PHPMailer loading (if not installed via Composer)
    require __DIR__ . '/PHPMailer-master/src/Exception.php';
    require __DIR__ . '/PHPMailer-master/src/PHPMailer.php';
    require __DIR__ . '/PHPMailer-master/src/SMTP.php';
}

spl_autoload_register(function ($class) {
    // Base namespace
    $baseNamespace = 'FixItMati\\';
    
    // Base directory for namespace (now at project root)
    $baseDir = __DIR__ . '/';
    
    // Check if class uses the base namespace
    $len = strlen($baseNamespace);
    if (strncmp($baseNamespace, $class, $len) !== 0) {
        // Not our namespace, let other autoloaders handle it
        return;
    }
    
    // Get relative class name
    $relativeClass = substr($class, $len);
    
    // Replace namespace separators with directory separators
    // and append .php
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
    
    // If file exists, require it
    if (file_exists($file)) {
        require $file;
    }
});
