<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Profile Image Diagnostic</title>
    <style>
        body { font-family: Arial; max-width: 900px; margin: 30px auto; padding: 20px; background: #f5f5f5; }
        .box { background: white; padding: 25px; margin: 15px 0; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        h2 { margin-top: 0; color: #1e40af; }
        .success { color: #16a34a; font-weight: 600; }
        .error { color: #dc2626; font-weight: 600; }
        .info { color: #0369a1; }
        button { background: #3b82f6; color: white; border: none; padding: 12px 24px; border-radius: 6px; cursor: pointer; font-weight: 600; margin: 5px; }
        button:hover { background: #2563eb; }
        pre { background: #1e293b; color: #e2e8f0; padding: 15px; border-radius: 4px; overflow-x: auto; font-size: 12px; }
        .profile-test { width: 80px; height: 80px; border-radius: 50%; border: 3px solid #3b82f6; }
    </style>
</head>
<body>
    <h1>🔍 Profile Image Diagnostic</h1>

    <div class="box">
        <h2>1. SessionStorage Check</h2>
        <div id="sessionCheck">Checking...</div>
        <button onclick="refreshSession()">Refresh from API</button>
        <button onclick="forceLogout()">Logout & Login Again</button>
    </div>

    <div class="box">
        <h2>2. Database Check (via API)</h2>
        <div id="apiCheck">Checking...</div>
        <button onclick="checkAPI()">Check API</button>
    </div>

    <div class="box">
        <h2>3. Image URL Test</h2>
        <div id="imageTest">Testing...</div>
    </div>

    <div class="box">
        <h2>4. Profile Display Test</h2>
        <div style="display: flex; gap: 20px; align-items: center;">
            <div>
                <p><strong>Header Icon:</strong></p>
                <div id="profileBtn" style="width: 64px; height: 64px; border-radius: 50%; background: linear-gradient(135deg, #3b82f6, #06b6d4); display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 24px;"></div>
            </div>
            <div>
                <p><strong>Dropdown Avatar:</strong></p>
                <div id="profileAvatarLarge" style="width: 64px; height: 64px; border-radius: 50%; background: linear-gradient(135deg, #3b82f6, #06b6d4); display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 24px;"></div>
            </div>
        </div>
        <div id="profileName" style="margin-top: 10px;"></div>
        <button onclick="testLoadProfile()">Test Load Profile</button>
    </div>

    <div class="box">
        <h2>5. Console Logs</h2>
        <pre id="logs" style="max-height: 300px; overflow-y: auto;"></pre>
    </div>

    <script src="/assets/api-client.js"></script>
    <script>
        const logs = [];
        
        function log(msg) {
            const time = new Date().toLocaleTimeString();
            logs.push(`[${time}] ${msg}`);
            document.getElementById('logs').textContent = logs.join('\n');
            console.log(msg);
        }

        async function checkSession() {
            log('=== Checking SessionStorage ===');
            const sessionDiv = document.getElementById('sessionCheck');
            
            const token = sessionStorage.getItem('auth_token');
            const userStr = sessionStorage.getItem('user');
            
            if (!token || !userStr) {
                sessionDiv.innerHTML = '<p class="error">❌ SessionStorage is empty!</p><p>You need to login first.</p>';
                log('ERROR: SessionStorage is empty');
                return false;
            }
            
            try {
                const user = JSON.parse(userStr);
                log('User email: ' + user.email);
                log('Profile image: ' + (user.profile_image || 'NULL'));
                
                let html = '<p class="success">✅ SessionStorage has user data</p>';
                html += `<p><strong>Email:</strong> ${user.email}</p>`;
                html += `<p><strong>Profile Image:</strong> ${user.profile_image || '<span class="error">NULL</span>'}</p>`;
                
                if (!user.profile_image) {
                    html += '<p class="error">⚠️ profile_image is NULL in sessionStorage!</p>';
                    html += '<p>Click "Refresh from API" to get latest data from database.</p>';
                }
                
                sessionDiv.innerHTML = html;
                return user;
            } catch (e) {
                sessionDiv.innerHTML = '<p class="error">❌ Invalid user data in sessionStorage</p>';
                log('ERROR: ' + e.message);
                return false;
            }
        }

        async function checkAPI() {
            log('=== Checking API /auth/me ===');
            const apiDiv = document.getElementById('apiCheck');
            apiDiv.innerHTML = '<p class="info">⏳ Loading from API...</p>';
            
            try {
                const response = await ApiClient.get('/auth/me');
                log('API Response: ' + JSON.stringify(response, null, 2));
                
                if (response.success && response.data) {
                    const user = response.data;
                    let html = '<p class="success">✅ API returned user data</p>';
                    html += `<p><strong>Email:</strong> ${user.email}</p>`;
                    html += `<p><strong>Profile Image:</strong> ${user.profile_image || '<span class="error">NULL</span>'}</p>`;
                    
                    if (user.profile_image) {
                        html += `<p class="success">✅ Database has profile image!</p>`;
                        testImageURL(user.profile_image);
                    } else {
                        html += '<p class="error">❌ Database profile_image is NULL</p>';
                        html += '<p>You need to upload an image in Edit Profile.</p>';
                    }
                    
                    apiDiv.innerHTML = html;
                    return user;
                } else {
                    apiDiv.innerHTML = '<p class="error">❌ API Error: ' + (response.message || 'Unknown') + '</p>';
                    log('ERROR: API failed');
                }
            } catch (error) {
                apiDiv.innerHTML = '<p class="error">❌ API Error: ' + error.message + '</p>';
                log('ERROR: ' + error.message);
            }
        }

        function testImageURL(filename) {
            log('=== Testing Image URL ===');
            const testDiv = document.getElementById('imageTest');
            
            if (!filename) {
                testDiv.innerHTML = '<p class="error">No filename to test</p>';
                return;
            }
            
            const imageUrl = '/api/uploads/profiles/' + filename;
            log('Image URL: ' + imageUrl);
            
            testDiv.innerHTML = `
                <p><strong>Image URL:</strong> <a href="${imageUrl}" target="_blank">${imageUrl}</a></p>
                <p>Testing image load...</p>
                <img src="${imageUrl}" class="profile-test" 
                     onload="document.getElementById('imageTest').innerHTML += '<p class=\\'success\\'>✅ Image loaded successfully!</p>'"
                     onerror="document.getElementById('imageTest').innerHTML += '<p class=\\'error\\'>❌ Image failed to load (404 or error)</p>'"
                     alt="Profile Test">
            `;
        }

        async function refreshSession() {
            log('=== Refreshing SessionStorage from API ===');
            const sessionDiv = document.getElementById('sessionCheck');
            sessionDiv.innerHTML = '<p class="info">⏳ Refreshing...</p>';
            
            try {
                const response = await ApiClient.get('/auth/me');
                if (response.success && response.data) {
                    sessionStorage.setItem('user', JSON.stringify(response.data));
                    log('✅ SessionStorage updated!');
                    sessionDiv.innerHTML = '<p class="success">✅ SessionStorage refreshed!</p>';
                    
                    // Reload page to apply changes
                    setTimeout(() => location.reload(), 1000);
                } else {
                    sessionDiv.innerHTML = '<p class="error">❌ Failed to refresh</p>';
                }
            } catch (error) {
                sessionDiv.innerHTML = '<p class="error">❌ Error: ' + error.message + '</p>';
            }
        }

        function forceLogout() {
            sessionStorage.clear();
            localStorage.clear();
            alert('Logged out! Now login again.');
            window.location.href = 'login.php';
        }

        function testLoadProfile() {
            log('=== Testing Profile Display Function ===');
            
            const user = sessionStorage.getItem('user');
            if (!user) {
                alert('No user in sessionStorage!');
                return;
            }

            try {
                const userData = JSON.parse(user);
                const profileBtn = document.getElementById('profileBtn');
                const profileAvatarLarge = document.getElementById('profileAvatarLarge');
                const profileName = document.getElementById('profileName');
                
                const firstName = (userData.first_name || '').trim();
                const lastName = (userData.last_name || '').trim();
                let displayName = `${firstName} ${lastName}`.trim();
                
                if (!displayName && userData.email) {
                    const username = userData.email.split('@')[0];
                    displayName = username.replace(/[._-]/g, ' ').split(' ')
                        .map(word => word.charAt(0).toUpperCase() + word.slice(1).toLowerCase())
                        .join(' ');
                }
                
                if (profileName) profileName.textContent = displayName || userData.email;
                
                if (userData.profile_image) {
                    log('Has profile_image: ' + userData.profile_image);
                    const filename = userData.profile_image.includes('/') || userData.profile_image.includes('\\')
                        ? userData.profile_image.split(/[\\/]/).pop()
                        : userData.profile_image;
                    const imageSrc = '/api/uploads/profiles/' + filename;
                    log('Image URL: ' + imageSrc);
                    
                    const imgTag = `<img src="${imageSrc}" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;" alt="Profile" />`;
                    
                    if (profileAvatarLarge) profileAvatarLarge.innerHTML = imgTag;
                    if (profileBtn) profileBtn.innerHTML = imgTag;
                    
                    log('✅ Profile display updated with image');
                } else {
                    log('No profile_image, using initials');
                    const initials = displayName.split(' ').map(n => n[0]).join('').toUpperCase().substring(0, 2);
                    if (profileAvatarLarge) profileAvatarLarge.textContent = initials;
                    if (profileBtn) profileBtn.textContent = initials;
                    log('Set initials: ' + initials);
                }
            } catch (error) {
                log('ERROR: ' + error.message);
                alert('Error: ' + error.message);
            }
        }

        // Auto-run checks on load
        window.addEventListener('DOMContentLoaded', async () => {
            log('Page loaded, starting diagnostics...');
            const user = await checkSession();
            if (user) {
                await checkAPI();
                if (user.profile_image) {
                    testImageURL(user.profile_image);
                    testLoadProfile();
                }
            }
        });
    </script>
</body>
</html>
