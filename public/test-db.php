<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Connection Test - FixItMati</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="bg-slate-50 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-2xl w-full">
        <div class="bg-white rounded-lg shadow-lg p-8">
            <div class="flex items-center gap-3 mb-6">
                <div class="bg-blue-600 p-2 rounded-lg">
                    <i data-lucide="database" class="text-white w-6 h-6"></i>
                </div>
                <h1 class="text-2xl font-bold text-slate-800">Database Connection Test</h1>
            </div>

            <?php
            require_once __DIR__ . '/../config/database.php';

            try {
                $db = Database::getInstance();
                $result = $db->testConnection();
                
                if ($result['success']) {
                    echo '<div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-4">';
                    echo '<div class="flex items-start gap-3">';
                    echo '<i data-lucide="check-circle-2" class="text-green-600 w-5 h-5 mt-0.5"></i>';
                    echo '<div>';
                    echo '<h3 class="font-semibold text-green-800 mb-1">Connection Successful!</h3>';
                    echo '<p class="text-sm text-green-700">' . htmlspecialchars($result['message']) . '</p>';
                    if (isset($result['version'])) {
                        echo '<p class="text-xs text-green-600 mt-2 font-mono">' . htmlspecialchars($result['version']) . '</p>';
                    }
                    echo '</div>';
                    echo '</div>';
                    echo '</div>';
                } else {
                    echo '<div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-4">';
                    echo '<div class="flex items-start gap-3">';
                    echo '<i data-lucide="x-circle" class="text-red-600 w-5 h-5 mt-0.5"></i>';
                    echo '<div>';
                    echo '<h3 class="font-semibold text-red-800 mb-1">Connection Failed</h3>';
                    echo '<p class="text-sm text-red-700">' . htmlspecialchars($result['message']) . '</p>';
                    echo '</div>';
                    echo '</div>';
                    echo '</div>';
                }

                // Display configuration (without sensitive data)
                echo '<div class="bg-slate-50 border border-slate-200 rounded-lg p-4">';
                echo '<h3 class="font-semibold text-slate-800 mb-3 flex items-center gap-2">';
                echo '<i data-lucide="settings" class="w-4 h-4"></i>';
                echo 'Configuration Details';
                echo '</h3>';
                echo '<div class="space-y-2 text-sm">';
                
                $host = getenv('DB_HOST');
                $port = getenv('DB_PORT');
                $dbname = getenv('DB_NAME');
                $user = getenv('DB_USER');
                $supabaseUrl = getenv('SUPABASE_URL');
                
                echo '<div class="grid grid-cols-2 gap-2">';
                echo '<span class="text-slate-600">Host:</span>';
                echo '<span class="font-mono text-slate-800">' . htmlspecialchars($host) . '</span>';
                echo '<span class="text-slate-600">Port:</span>';
                echo '<span class="font-mono text-slate-800">' . htmlspecialchars($port) . '</span>';
                echo '<span class="text-slate-600">Database:</span>';
                echo '<span class="font-mono text-slate-800">' . htmlspecialchars($dbname) . '</span>';
                echo '<span class="text-slate-600">User:</span>';
                echo '<span class="font-mono text-slate-800">' . htmlspecialchars($user) . '</span>';
                echo '<span class="text-slate-600">Supabase URL:</span>';
                echo '<span class="font-mono text-slate-800 text-xs">' . htmlspecialchars($supabaseUrl) . '</span>';
                echo '<span class="text-slate-600">Password:</span>';
                echo '<span class="font-mono text-slate-800">' . (getenv('DB_PASSWORD') ? '••••••••' : '(not set)') . '</span>';
                echo '</div>';
                echo '</div>';
                echo '</div>';

            } catch(Exception $e) {
                echo '<div class="bg-red-50 border border-red-200 rounded-lg p-4">';
                echo '<div class="flex items-start gap-3">';
                echo '<i data-lucide="alert-triangle" class="text-red-600 w-5 h-5 mt-0.5"></i>';
                echo '<div>';
                echo '<h3 class="font-semibold text-red-800 mb-1">Error</h3>';
                echo '<p class="text-sm text-red-700">' . htmlspecialchars($e->getMessage()) . '</p>';
                echo '</div>';
                echo '</div>';
                echo '</div>';
            }
            ?>

            <div class="mt-6 pt-6 border-t border-slate-200">
                <div class="flex gap-3">
                    <a href="user-dashboard.php" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-center font-medium transition-colors flex items-center justify-center gap-2">
                        <i data-lucide="home" class="w-4 h-4"></i>
                        Go to Dashboard
                    </a>
                    <button onclick="location.reload()" class="px-4 py-2 border border-slate-300 hover:bg-slate-50 rounded-lg font-medium transition-colors flex items-center gap-2">
                        <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                        Retry
                    </button>
                </div>
            </div>

            <div class="mt-4 text-xs text-slate-500 text-center">
                <p>If connection fails, run <code class="bg-slate-100 px-2 py-0.5 rounded">setup.bat</code> to reconfigure</p>
            </div>
        </div>
    </div>

    <script>lucide.createIcons();</script>
</body>
</html>
