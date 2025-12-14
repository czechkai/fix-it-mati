<?php
// Comprehensive profile image debugging and fix
header('Content-Type: text/html; charset=utf-8');
session_start();

// Load database and user
require_once __DIR__ . '/../autoload.php';
require_once __DIR__ . '/../config/database.php';

use Models\User;

// Get auth token from request or session
$token = null;
if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
    $token = str_replace('Bearer ', '', $_SERVER['HTTP_AUTHORIZATION']);
} elseif (isset($_COOKIE['auth_token'])) {
    $token = $_COOKIE['auth_token'];
}

$user = null;
if ($token) {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("SELECT * FROM users WHERE id = (SELECT user_id FROM auth_tokens WHERE token = ? AND expires_at > NOW() LIMIT 1)");
    $stmt->execute([$token]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Profile Image Fix & Test</title>
    <style>
        body { font-family: 'Segoe UI', Arial; padding: 20px; background: #f8fafc; }
        .container { max-width: 1200px; margin: 0 auto; }
        .box { background: white; padding: 25px; margin: 15px 0; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        h1 { color: #0f172a; margin-bottom: 10px; }
        h2 { color: #1e40af; margin-top: 0; border-bottom: 2px solid #3b82f6; padding-bottom: 10px; }
        .success { color: #16a34a; font-weight: 600; }
        .error { color: #dc2626; font-weight: 600; }
        .warning { color: #ea580c; font-weight: 600; }
        .info { color: #0284c7; }
        button { background: #3b82f6; color: white; border: none; padding: 12px 24px; border-radius: 6px; cursor: pointer; font-weight: 600; margin: 5px; }
        button:hover { background: #2563eb; }
        button.danger { background: #dc2626; }
        button.danger:hover { background: #b91c1c; }
        pre { background: #1e293b; color: #e2e8f0; padding: 15px; border-radius: 6px; overflow-x: auto; font-size: 13px; }
        .profile-test { max-width: 150px; max-height: 150px; border-radius: 50%; border: 3px solid #3b82f6; margin: 10px 0; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px; }
        .status-box { padding: 15px; border-radius: 6px; }
        .status-box.ok { background: #dcfce7; border: 2px solid #16a34a; }
        .status-box.fail { background: #fee2e2; border: 2px solid #dc2626; }
        code { background: #f1f5f9; padding: 2px 6px; border-radius: 3px; color: #0f172a; font-family: monospace; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Profile Image Diagnostic & Fix Tool</h1>
        <p>Complete profile image testing and automatic fixes</p>

        <?php if (!$user): ?>
            <div class="box">
                <h2>❌ Not Logged In</h2>
                <p class="error">You must be logged in to use this tool.</p>
                <button onclick="window.location.href='login.php'">Go to Login</button>
            </div>
        <?php else: ?>

        <!-- User Information -->
        <div class="box">
            <h2>👤 Current User</h2>
            <div class="grid">
                <div>
                    <strong>Email:</strong> <?= htmlspecialchars($user['email']) ?><br>
                    <strong>Name:</strong> <?= htmlspecialchars(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')) ?><br>
                    <strong>User ID:</strong> <code><?= htmlspecialchars($user['id']) ?></code>
                </div>
                <div>
                    <strong>Profile Image (DB):</strong><br>
                    <?php if (!empty($user['profile_image'])): ?>
                        <span class="success">✅ <?= htmlspecialchars($user['profile_image']) ?></span>
                    <?php else: ?>
                        <span class="error">❌ NULL - No image in database</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Database Status -->
        <div class="box">
            <h2>💾 Database Check</h2>
            <?php
            $uploadDir = __DIR__ . '/../uploads/profiles';
            $userPattern = 'profile_' . $user['id'] . '_*';
            $files = glob($uploadDir . '/' . $userPattern);
            
            if (!empty($user['profile_image'])):
                $dbImagePath = $uploadDir . '/' . basename($user['profile_image']);
                $fileExists = file_exists($dbImagePath);
            ?>
                <div class="status-box <?= $fileExists ? 'ok' : 'fail' ?>">
                    <strong>Database has image:</strong> <code><?= htmlspecialchars($user['profile_image']) ?></code><br>
                    <strong>File exists:</strong> <?= $fileExists ? '<span class="success">✅ YES</span>' : '<span class="error">❌ NO (file deleted)</span>' ?>
                    <?php if ($fileExists): ?>
                        <br><strong>File size:</strong> <?= round(filesize($dbImagePath) / 1024) ?> KB
                        <br><img src="/api/uploads/profiles/<?= htmlspecialchars(basename($user['profile_image'])) ?>" class="profile-test" alt="DB Image">
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="status-box fail">
                    <span class="error">❌ Database profile_image is NULL</span>
                </div>
                
                <?php if (!empty($files)): ?>
                    <h3 class="warning">⚠️ Found Uploaded Files (not linked to database):</h3>
                    <?php foreach ($files as $file): 
                        $filename = basename($file);
                        $fileSize = round(filesize($file) / 1024);
                        $fileTime = date('Y-m-d H:i:s', filemtime($file));
                    ?>
                        <div style="margin: 10px 0; padding: 10px; background: #fef3c7; border-radius: 6px;">
                            <strong>File:</strong> <code><?= htmlspecialchars($filename) ?></code><br>
                            <strong>Size:</strong> <?= $fileSize ?> KB | <strong>Modified:</strong> <?= $fileTime ?><br>
                            <img src="/api/uploads/profiles/<?= htmlspecialchars($filename) ?>" class="profile-test" alt="File">
                            <br>
                            <button onclick="fixDatabase('<?= htmlspecialchars($filename) ?>')">Use This Image</button>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="info">No uploaded files found for this user. Please upload an image in Edit Profile.</p>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <!-- API Test -->
        <div class="box">
            <h2>🌐 API Endpoint Test</h2>
            <div id="apiTest">
                <button onclick="testAPI()">Test API /auth/me</button>
                <button onclick="testImageAPI()">Test Image Endpoint</button>
            </div>
            <div id="apiResult" style="margin-top: 15px;"></div>
        </div>

        <!-- JavaScript/SessionStorage Test -->
        <div class="box">
            <h2>💻 SessionStorage & JavaScript Test</h2>
            <div id="sessionTest">Testing...</div>
            <button onclick="testSessionStorage()">Check SessionStorage</button>
            <button onclick="refreshSessionStorage()">Refresh from API</button>
            <button onclick="testProfileDisplay()">Test Profile Display</button>
        </div>

        <!-- Live Profile Preview -->
        <div class="box">
            <h2>🎨 Live Profile Preview</h2>
            <div style="display: flex; gap: 30px; align-items: center; flex-wrap: wrap;">
                <div style="text-align: center;">
                    <p><strong>Header Icon</strong></p>
                    <div id="profileBtn" style="width: 80px; height: 80px; border-radius: 50%; background: linear-gradient(135deg, #3b82f6, #06b6d4); display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 32px;">?</div>
                </div>
                <div style="text-align: center;">
                    <p><strong>Dropdown Avatar</strong></p>
                    <div id="profileAvatarLarge" style="width: 80px; height: 80px; border-radius: 50%; background: linear-gradient(135deg, #3b82f6, #06b6d4); display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 32px;">?</div>
                </div>
            </div>
            <p id="profileName" style="margin-top: 15px; font-size: 18px; font-weight: 600;"></p>
        </div>

        <!-- Console Logs -->
        <div class="box">
            <h2>📋 Debug Console</h2>
            <pre id="console" style="max-height: 300px; overflow-y: auto; min-height: 100px;">Ready...</pre>
            <button onclick="document.getElementById('console').textContent = 'Cleared...'">Clear Console</button>
        </div>

        <?php endif; ?>
    </div>

    <script src="/assets/api-client.js"></script>
    <script>
        const logs = [];
        
        function log(msg, type = 'info') {
            const time = new Date().toLocaleTimeString();
            const icon = type === 'error' ? '❌' : type === 'success' ? '✅' : 'ℹ️';
            const logMsg = `[${time}] ${icon} ${msg}`;
            logs.push(logMsg);
            document.getElementById('console').textContent = logs.join('\n');
            console.log(msg);
        }

        async function testAPI() {
            log('Testing API /auth/me endpoint...');
            const resultDiv = document.getElementById('apiResult');
            resultDiv.innerHTML = '<p class="info">⏳ Loading...</p>';
            
            try {
                const token = sessionStorage.getItem('auth_token');
                const response = await fetch('/api/auth/me', {
                    headers: { 'Authorization': 'Bearer ' + token }
                });
                
                const data = await response.json();
                log('API Response: ' + JSON.stringify(data, null, 2));
                
                if (data.success && data.data) {
                    const user = data.data;
                    let html = '<div class="status-box ok">';
                    html += '<strong>✅ API Working</strong><br>';
                    html += '<strong>Email:</strong> ' + user.email + '<br>';
                    html += '<strong>Profile Image:</strong> ' + (user.profile_image || '<span class="error">NULL</span>');
                    html += '</div>';
                    resultDiv.innerHTML = html;
                    log('API test successful', 'success');
                } else {
                    resultDiv.innerHTML = '<div class="status-box fail">❌ API Error: ' + (data.message || 'Unknown') + '</div>';
                    log('API test failed: ' + data.message, 'error');
                }
            } catch (error) {
                resultDiv.innerHTML = '<div class="status-box fail">❌ Error: ' + error.message + '</div>';
                log('API test error: ' + error.message, 'error');
            }
        }

        async function testImageAPI() {
            log('Testing image endpoint...');
            const resultDiv = document.getElementById('apiResult');
            
            const profileImage = '<?= !empty($user['profile_image']) ? basename($user['profile_image']) : '' ?>';
            if (!profileImage) {
                resultDiv.innerHTML = '<div class="status-box fail">❌ No profile image in database</div>';
                return;
            }
            
            const imageUrl = '/api/uploads/profiles/' + profileImage;
            log('Testing URL: ' + imageUrl);
            
            resultDiv.innerHTML = '<p>Testing: <code>' + imageUrl + '</code></p><img src="' + imageUrl + '" class="profile-test" onload="imageLoadSuccess()" onerror="imageLoadFailed()">';
        }

        function imageLoadSuccess() {
            log('Image loaded successfully!', 'success');
            document.getElementById('apiResult').innerHTML += '<p class="success">✅ Image endpoint working!</p>';
        }

        function imageLoadFailed() {
            log('Image failed to load!', 'error');
            document.getElementById('apiResult').innerHTML += '<p class="error">❌ Image endpoint not working (404 or error)</p>';
        }

        async function testSessionStorage() {
            log('Checking sessionStorage...');
            const sessionDiv = document.getElementById('sessionTest');
            
            const token = sessionStorage.getItem('auth_token');
            const userStr = sessionStorage.getItem('user');
            
            if (!token || !userStr) {
                sessionDiv.innerHTML = '<div class="status-box fail">❌ SessionStorage is empty</div>';
                log('SessionStorage is empty', 'error');
                return;
            }
            
            try {
                const user = JSON.parse(userStr);
                let html = '<div class="status-box ok">';
                html += '<strong>✅ SessionStorage has data</strong><br>';
                html += '<strong>Email:</strong> ' + user.email + '<br>';
                html += '<strong>Profile Image:</strong> ' + (user.profile_image || '<span class="error">NULL</span>');
                html += '</div>';
                sessionDiv.innerHTML = html;
                log('SessionStorage check complete', 'success');
                log('Profile image in session: ' + (user.profile_image || 'NULL'));
            } catch (e) {
                sessionDiv.innerHTML = '<div class="status-box fail">❌ Invalid sessionStorage data</div>';
                log('SessionStorage parse error: ' + e.message, 'error');
            }
        }

        async function refreshSessionStorage() {
            log('Refreshing sessionStorage from API...');
            const sessionDiv = document.getElementById('sessionTest');
            sessionDiv.innerHTML = '<p class="info">⏳ Refreshing...</p>';
            
            try {
                const token = sessionStorage.getItem('auth_token');
                const response = await fetch('/api/auth/me', {
                    headers: { 'Authorization': 'Bearer ' + token }
                });
                
                const data = await response.json();
                
                if (data.success && data.data) {
                    sessionStorage.setItem('user', JSON.stringify(data.data));
                    sessionDiv.innerHTML = '<div class="status-box ok">✅ SessionStorage refreshed!</div>';
                    log('SessionStorage updated successfully', 'success');
                    
                    // Auto test profile display
                    setTimeout(testProfileDisplay, 500);
                } else {
                    sessionDiv.innerHTML = '<div class="status-box fail">❌ Failed to refresh</div>';
                    log('Refresh failed: ' + data.message, 'error');
                }
            } catch (error) {
                sessionDiv.innerHTML = '<div class="status-box fail">❌ Error: ' + error.message + '</div>';
                log('Refresh error: ' + error.message, 'error');
            }
        }

        function testProfileDisplay() {
            log('Testing profile display...');
            
            const userStr = sessionStorage.getItem('user');
            if (!userStr) {
                alert('No user in sessionStorage!');
                return;
            }

            try {
                const userData = JSON.parse(userStr);
                const profileBtn = document.getElementById('profileBtn');
                const profileAvatarLarge = document.getElementById('profileAvatarLarge');
                const profileName = document.getElementById('profileName');
                
                // Build display name
                const firstName = (userData.first_name || '').trim();
                const lastName = (userData.last_name || '').trim();
                let displayName = `${firstName} ${lastName}`.trim();
                
                if (!displayName && userData.email) {
                    const username = userData.email.split('@')[0];
                    displayName = username.replace(/[._-]/g, ' ').split(' ')
                        .map(word => word.charAt(0).toUpperCase() + word.slice(1).toLowerCase())
                        .join(' ');
                }
                
                profileName.textContent = displayName || userData.email;
                log('Display name: ' + displayName);
                
                if (userData.profile_image) {
                    log('Has profile_image: ' + userData.profile_image);
                    
                    const filename = userData.profile_image.includes('/') || userData.profile_image.includes('\\')
                        ? userData.profile_image.split(/[\\/]/).pop()
                        : userData.profile_image;
                    
                    const imageSrc = '/api/uploads/profiles/' + filename;
                    log('Image URL: ' + imageSrc);
                    
                    const imgTag = `<img src="${imageSrc}" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;" alt="Profile" />`;
                    
                    profileAvatarLarge.innerHTML = imgTag;
                    profileBtn.innerHTML = imgTag;
                    
                    log('Profile images updated!', 'success');
                } else {
                    log('No profile_image, using initials');
                    const initials = displayName.split(' ').map(n => n[0]).join('').toUpperCase().substring(0, 2);
                    profileAvatarLarge.textContent = initials;
                    profileBtn.textContent = initials;
                    log('Set initials: ' + initials);
                }
            } catch (error) {
                log('Profile display error: ' + error.message, 'error');
                alert('Error: ' + error.message);
            }
        }

        async function fixDatabase(filename) {
            if (!confirm('Update database to use this image: ' + filename + '?')) {
                return;
            }
            
            log('Updating database with: ' + filename);
            
            try {
                const token = sessionStorage.getItem('auth_token');
                const response = await fetch('/api/auth/profile', {
                    method: 'PUT',
                    headers: {
                        'Authorization': 'Bearer ' + token,
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        email: '<?= $user['email'] ?>',
                        fix_profile_image: filename
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    log('Database updated successfully!', 'success');
                    alert('✅ Profile image updated! Reloading page...');
                    location.reload();
                } else {
                    log('Database update failed: ' + data.message, 'error');
                    alert('❌ Error: ' + data.message);
                }
            } catch (error) {
                log('Database update error: ' + error.message, 'error');
                alert('❌ Error: ' + error.message);
            }
        }

        // Auto-run tests on load
        window.addEventListener('DOMContentLoaded', () => {
            log('Page loaded, running automatic tests...');
            testSessionStorage();
            setTimeout(testProfileDisplay, 500);
        });
    </script>
</body>
</html>
