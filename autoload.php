<?php
/**
 * Autoloader for FixItMati
 * PSR-4 compatible autoloader
 */

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
