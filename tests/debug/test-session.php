<?php
session_start();

// Prevent caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
?><!DOCTYPE html>
<html>
<head>
    <title>Session Test</title>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
        .box { background: white; padding: 20px; margin: 15px 0; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .error { border-left: 4px solid #f44336; }
        .success { border-left: 4px solid #4caf50; }
        button { padding: 12px 24px; margin: 5px; cursor: pointer; background: #2196F3; color: white; border: none; border-radius: 4px; }
        button:hover { background: #1976D2; }
        pre { background: #f5f5f5; padding: 10px; border-radius: 4px; overflow-x: auto; }
        h1 { color: #333; }
        h3 { color: #666; margin-top: 0; }
    </style>
</head>
<body>
    <h1>🔍 Session Debug Test</h1>
    
    <div class="box">
        <h3>Session ID</h3>
        <code><?php echo session_id(); ?></code>
    </div>
    
    <div class="box <?php echo isset($_SESSION['user_id']) ? 'error' : 'success'; ?>">
        <h3>PHP Session Data</h3>
        <pre><?php 
        if (empty($_SESSION)) {
            echo "Session is EMPTY ✓";
        } else {
            print_r($_SESSION); 
        }
        ?></pre>
        <p><strong>user_id in session:</strong> 
        <?php 
        if (isset($_SESSION['user_id'])) {
            echo '<span style="color: red;">YES - This will block register.php! (user_id=' . $_SESSION['user_id'] . ')</span>';
        } else {
            echo '<span style="color: green;">NO - register.php should work ✓</span>';
        }
        ?>
        </p>
    </div>
    
    <div class="box">
        <h3>Cookies</h3>
        <pre><?php 
        if (empty($_COOKIE)) {
            echo "No cookies";
        } else {
            print_r($_COOKIE); 
        }
        ?></pre>
    </div>
    
    <div class="box">
        <h3>Client-side Storage (JavaScript)</h3>
        <p>sessionStorage.auth_token: <strong id="sessionToken">checking...</strong></p>
        <p>localStorage.auth_token: <strong id="localToken">checking...</strong></p>
    </div>
    
    <div class="box">
        <h3>Actions</h3>
        <button onclick="location.href='register.php'">📝 Try Register Page</button>
        <button onclick="location.href='login.php'">🔑 Go to Login</button>
        <button onclick="location.href='logout.php'">🚪 Logout</button>
        <button onclick="clearStorage()">🗑️ Clear Storage</button>
        <button onclick="location.reload()">🔄 Refresh</button>
    </div>
    
    <script>
        const sessionToken = sessionStorage.getItem('auth_token');
        const localToken = localStorage.getItem('auth_token');
        
        document.getElementById('sessionToken').textContent = sessionToken ? '❌ EXISTS' : '✓ NONE';
        document.getElementById('sessionToken').style.color = sessionToken ? 'red' : 'green';
        
        document.getElementById('localToken').textContent = localToken ? '❌ EXISTS' : '✓ NONE';
        document.getElementById('localToken').style.color = localToken ? 'red' : 'green';
        
        function clearStorage() {
            sessionStorage.clear();
            localStorage.clear();
            alert('✓ Cleared! Refreshing...');
            location.reload();
        }
        
        console.log('Session Test Debug:', {
            sessionToken: sessionToken ? 'EXISTS' : 'NONE',
            localToken: localToken ? 'EXISTS' : 'NONE'
        });
    </script>
</body>
</html>
