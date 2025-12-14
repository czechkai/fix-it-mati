<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Profile Fix Tool</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial; padding: 30px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; }
        .box { background: white; padding: 30px; margin: 20px 0; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #1e40af; margin-bottom: 20px; }
        h2 { color: #3b82f6; margin: 20px 0 10px; }
        button { background: #3b82f6; color: white; border: none; padding: 15px 30px; font-size: 16px; border-radius: 8px; cursor: pointer; font-weight: 600; margin: 10px 5px; }
        button:hover { background: #2563eb; }
        .success { color: #16a34a; font-weight: 600; }
        .error { color: #dc2626; font-weight: 600; }
        .info { color: #0284c7; }
        pre { background: #1e293b; color: #e2e8f0; padding: 15px; border-radius: 6px; overflow-x: auto; margin: 10px 0; }
        .profile-preview { width: 120px; height: 120px; border-radius: 50%; border: 3px solid #3b82f6; margin: 15px 0; }
        .status { padding: 15px; border-radius: 6px; margin: 10px 0; }
        .status.ok { background: #dcfce7; border-left: 4px solid #16a34a; }
        .status.fail { background: #fee2e2; border-left: 4px solid #dc2626; }
    </style>
</head>
<body>
    <div class="container">
        <div class="box">
            <h1>🔧 Profile Image Fix Tool</h1>
            <p>This tool will diagnose and fix your profile image display issue.</p>
            
            <h2>Step 1: Check Status</h2>
            <div id="step1">
                <button onclick="checkStatus()">Check Profile Status</button>
            </div>
            <div id="statusResult"></div>
            
            <h2>Step 2: Fix Profile</h2>
            <div id="step2">
                <button onclick="fixProfile()">Refresh Profile Data</button>
                <button onclick="window.location.href='user-dashboard.php'">Go to Dashboard</button>
            </div>
            <div id="fixResult"></div>
            
            <h2>Step 3: Preview</h2>
            <div style="display: flex; gap: 30px; margin: 20px 0;">
                <div style="text-align: center;">
                    <p><strong>Profile Icon</strong></p>
                    <div id="profileBtn" style="width: 100px; height: 100px; border-radius: 50%; background: linear-gradient(135deg, #3b82f6, #06b6d4); display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 40px;">?</div>
                </div>
                <div style="text-align: center;">
                    <p><strong>Dropdown Avatar</strong></p>
                    <div id="profileAvatarLarge" style="width: 100px; height: 100px; border-radius: 50%; background: linear-gradient(135deg, #3b82f6, #06b6d4); display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 40px;">?</div>
                </div>
            </div>
            <button onclick="testDisplay()">Test Display Function</button>
            
            <h2>Console Log</h2>
            <pre id="console" style="max-height: 300px; overflow-y: auto;">Ready to test...</pre>
        </div>
    </div>

    <script>
        const logs = [];
        
        function log(msg) {
            const time = new Date().toLocaleTimeString();
            logs.push(`[${time}] ${msg}`);
            document.getElementById('console').textContent = logs.join('\n');
            console.log(msg);
        }

        async function checkStatus() {
            log('Checking status...');
            const statusDiv = document.getElementById('statusResult');
            statusDiv.innerHTML = '<p class="info">⏳ Checking...</p>';
            
            try {
                // Check sessionStorage
                const token = sessionStorage.getItem('auth_token');
                const userStr = sessionStorage.getItem('user');
                
                if (!token || !userStr) {
                    statusDiv.innerHTML = '<div class="status fail"><strong>❌ Not Logged In</strong><br>You need to login first.</div>';
                    log('ERROR: Not logged in');
                    return;
                }
                
                const user = JSON.parse(userStr);
                log('User email: ' + user.email);
                log('Profile image in sessionStorage: ' + (user.profile_image || 'NULL'));
                
                // Check API
                const response = await fetch('/api/auth/me', {
                    headers: { 'Authorization': 'Bearer ' + token }
                });
                
                const data = await response.json();
                
                if (data.success && data.data) {
                    const dbUser = data.data;
                    log('Profile image in database: ' + (dbUser.profile_image || 'NULL'));
                    
                    let html = '<div class="status ok"><strong>✅ Logged In</strong><br>';
                    html += 'Email: ' + dbUser.email + '<br>';
                    html += 'Profile Image (Database): ' + (dbUser.profile_image ? '<span class="success">' + dbUser.profile_image + '</span>' : '<span class="error">NULL</span>') + '<br>';
                    html += 'Profile Image (SessionStorage): ' + (user.profile_image ? '<span class="success">' + user.profile_image + '</span>' : '<span class="error">NULL</span>');
                    html += '</div>';
                    
                    // Check if they match
                    if (dbUser.profile_image && dbUser.profile_image !== user.profile_image) {
                        html += '<div class="status fail"><strong>⚠️ Mismatch Detected!</strong><br>';
                        html += 'Database has an image but sessionStorage doesn\'t match.<br>';
                        html += 'Click "Refresh Profile Data" to fix this.</div>';
                        log('MISMATCH: Database and sessionStorage have different values');
                    } else if (dbUser.profile_image && user.profile_image) {
                        html += '<div class="status ok"><strong>✅ Everything looks good!</strong><br>';
                        html += 'Both database and sessionStorage have the profile image.</div>';
                        
                        // Test if image actually loads
                        const imgUrl = '/api/uploads/profiles/' + dbUser.profile_image;
                        html += '<p>Testing image URL: <code>' + imgUrl + '</code></p>';
                        html += '<img src="' + imgUrl + '" class="profile-preview" onload="log(\'✅ Image loaded successfully\')" onerror="log(\'❌ Image failed to load - file not found or error\')">';
                    } else if (!dbUser.profile_image) {
                        html += '<div class="status fail"><strong>❌ No Profile Image</strong><br>';
                        html += 'Database has no profile image. Please upload one in Edit Profile.</div>';
                        log('No profile image in database');
                    }
                    
                    statusDiv.innerHTML = html;
                } else {
                    statusDiv.innerHTML = '<div class="status fail">❌ API Error: ' + (data.message || 'Unknown') + '</div>';
                    log('API error: ' + (data.message || 'Unknown'));
                }
            } catch (error) {
                statusDiv.innerHTML = '<div class="status fail">❌ Error: ' + error.message + '</div>';
                log('ERROR: ' + error.message);
            }
        }

        async function fixProfile() {
            log('Refreshing profile data from database...');
            const fixDiv = document.getElementById('fixResult');
            fixDiv.innerHTML = '<p class="info">⏳ Refreshing from API...</p>';
            
            try {
                const token = sessionStorage.getItem('auth_token');
                
                if (!token) {
                    fixDiv.innerHTML = '<div class="status fail">❌ Not logged in</div>';
                    return;
                }
                
                // Fetch fresh data from API
                const response = await fetch('/api/auth/me', {
                    headers: { 'Authorization': 'Bearer ' + token }
                });
                
                const data = await response.json();
                
                if (data.success && data.data) {
                    // Update sessionStorage
                    sessionStorage.setItem('user', JSON.stringify(data.data));
                    log('✅ SessionStorage updated with fresh data from database');
                    
                    const user = data.data;
                    
                    let html = '<div class="status ok"><strong>✅ Profile Data Refreshed!</strong><br>';
                    html += 'Profile Image: ' + (user.profile_image ? '<span class="success">' + user.profile_image + '</span>' : '<span class="error">NULL</span>');
                    html += '</div>';
                    
                    if (user.profile_image) {
                        html += '<p class="success">The profile image should now appear on all pages!</p>';
                        html += '<img src="/api/uploads/profiles/' + user.profile_image + '" class="profile-preview">';
                        log('Profile image URL: /api/uploads/profiles/' + user.profile_image);
                        
                        // Auto-test display
                        setTimeout(testDisplay, 500);
                    } else {
                        html += '<p class="info">No profile image in database. Upload one in Edit Profile.</p>';
                    }
                    
                    fixDiv.innerHTML = html;
                } else {
                    fixDiv.innerHTML = '<div class="status fail">❌ Failed: ' + (data.message || 'Unknown error') + '</div>';
                    log('Fix failed: ' + (data.message || 'Unknown'));
                }
            } catch (error) {
                fixDiv.innerHTML = '<div class="status fail">❌ Error: ' + error.message + '</div>';
                log('ERROR: ' + error.message);
            }
        }

        function testDisplay() {
            log('Testing profile display function...');
            
            const userStr = sessionStorage.getItem('user');
            if (!userStr) {
                alert('No user data in sessionStorage!');
                return;
            }

            try {
                const userData = JSON.parse(userStr);
                const profileBtn = document.getElementById('profileBtn');
                const profileAvatarLarge = document.getElementById('profileAvatarLarge');
                
                log('User data: ' + JSON.stringify(userData, null, 2));
                log('Profile image: ' + (userData.profile_image || 'NULL'));
                
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
                
                log('Display name: ' + displayName);
                
                if (userData.profile_image) {
                    log('✅ Has profile image');
                    
                    // Extract filename
                    const filename = userData.profile_image.includes('/') || userData.profile_image.includes('\\')
                        ? userData.profile_image.split(/[\\/]/).pop()
                        : userData.profile_image;
                    
                    const imageSrc = '/api/uploads/profiles/' + filename;
                    log('Image URL: ' + imageSrc);
                    
                    const imgTag = `<img src="${imageSrc}" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;" alt="Profile" onerror="log('❌ Image failed to load')" onload="log('✅ Image loaded in preview')" />`;
                    
                    profileAvatarLarge.innerHTML = imgTag;
                    profileBtn.innerHTML = imgTag;
                    
                    log('✅ Profile display updated with image');
                } else {
                    log('No profile image, using initials');
                    const initials = displayName.split(' ').map(n => n[0]).join('').toUpperCase().substring(0, 2);
                    profileAvatarLarge.textContent = initials;
                    profileBtn.textContent = initials;
                    log('Set initials: ' + initials);
                }
                
                log('✅ Display test complete');
            } catch (error) {
                log('❌ Display test error: ' + error.message);
                alert('Error: ' + error.message);
            }
        }

        // Auto-run check on page load
        window.addEventListener('DOMContentLoaded', () => {
            log('Page loaded');
            
            const token = sessionStorage.getItem('auth_token');
            if (!token) {
                document.getElementById('statusResult').innerHTML = '<div class="status fail"><strong>❌ Not Logged In</strong><br>Please <a href="login.php">login</a> first.</div>';
                log('Not logged in - redirecting in 3 seconds...');
                setTimeout(() => window.location.href = 'login.php', 3000);
            } else {
                log('User is logged in, ready to test');
                checkStatus();
            }
        });
    </script>
</body>
</html>
