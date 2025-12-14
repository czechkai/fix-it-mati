<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Display Debug Test</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .section {
            background: white;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .profile-preview {
            display: flex;
            gap: 20px;
            align-items: center;
        }
        #profileBtn, #profileAvatarLarge {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            background: linear-gradient(135deg, #3b82f6, #06b6d4);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 24px;
            overflow: hidden;
        }
        #profileBtn img, #profileAvatarLarge img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        pre {
            background: #1e293b;
            color: #e2e8f0;
            padding: 15px;
            border-radius: 4px;
            overflow-x: auto;
            font-size: 12px;
        }
        .status {
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 10px;
        }
        .status.success {
            background: #dcfce7;
            color: #166534;
        }
        .status.error {
            background: #fee2e2;
            color: #991b1b;
        }
        .status.info {
            background: #dbeafe;
            color: #1e40af;
        }
        button {
            background: #3b82f6;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            margin-right: 10px;
        }
        button:hover {
            background: #2563eb;
        }
    </style>
</head>
<body>
    <h1>🔍 Profile Display Debug Test</h1>
    
    <div class="section">
        <h2>1. SessionStorage Check</h2>
        <div id="sessionStorageStatus"></div>
        <button onclick="checkSessionStorage()">Refresh Check</button>
        <button onclick="clearSessionStorage()">Clear SessionStorage</button>
        <pre id="sessionStorageData">Loading...</pre>
    </div>

    <div class="section">
        <h2>2. Profile Display Test</h2>
        <div class="profile-preview">
            <div>
                <p><strong>profileBtn:</strong></p>
                <div id="profileBtn"></div>
            </div>
            <div>
                <p><strong>profileAvatarLarge:</strong></p>
                <div id="profileAvatarLarge"></div>
            </div>
        </div>
        <p style="margin-top: 20px;">
            <strong>Profile Name:</strong> <span id="profileName">Not loaded</span><br>
            <strong>Profile Email:</strong> <span id="profileEmail">Not loaded</span>
        </p>
        <button onclick="testLoadProfile()">Test Load Profile</button>
    </div>

    <div class="section">
        <h2>3. Console Logs</h2>
        <div id="consoleStatus" class="status info">
            Open browser DevTools (F12) and check Console tab for detailed logs
        </div>
        <pre id="consoleLogs">Press F12 to open DevTools Console</pre>
    </div>

    <div class="section">
        <h2>4. Quick Actions</h2>
        <button onclick="window.location.href='edit-profile.php'">Go to Edit Profile</button>
        <button onclick="window.location.href='active-requests.php'">Go to Active Requests</button>
        <button onclick="window.location.href='user-dashboard.php'">Go to Dashboard</button>
    </div>

    <script>
        // Intercept console.log
        const originalLog = console.log;
        const logs = [];
        console.log = function(...args) {
            logs.push(args.map(arg => 
                typeof arg === 'object' ? JSON.stringify(arg, null, 2) : String(arg)
            ).join(' '));
            originalLog.apply(console, args);
            updateConsoleLogs();
        };

        function updateConsoleLogs() {
            document.getElementById('consoleLogs').textContent = logs.slice(-20).join('\n');
        }

        function checkSessionStorage() {
            const statusDiv = document.getElementById('sessionStorageStatus');
            const dataDiv = document.getElementById('sessionStorageData');
            
            const user = sessionStorage.getItem('user');
            const token = sessionStorage.getItem('auth_token');
            
            if (!user) {
                statusDiv.innerHTML = '<div class="status error">❌ No user data in sessionStorage</div>';
                dataDiv.textContent = 'SessionStorage is empty. Please login first.';
                return;
            }
            
            if (!token) {
                statusDiv.innerHTML = '<div class="status error">❌ No auth token in sessionStorage</div>';
            } else {
                statusDiv.innerHTML = '<div class="status success">✅ Auth token found</div>';
            }
            
            try {
                const userData = JSON.parse(user);
                statusDiv.innerHTML += '<div class="status success">✅ User data found and valid JSON</div>';
                
                if (userData.profile_image) {
                    statusDiv.innerHTML += '<div class="status success">✅ Profile image field exists: ' + userData.profile_image + '</div>';
                } else {
                    statusDiv.innerHTML += '<div class="status info">ℹ️ No profile_image in user data</div>';
                }
                
                dataDiv.textContent = JSON.stringify(userData, null, 2);
            } catch (error) {
                statusDiv.innerHTML += '<div class="status error">❌ Error parsing user data: ' + error.message + '</div>';
                dataDiv.textContent = user;
            }
        }

        function clearSessionStorage() {
            if (confirm('This will clear sessionStorage and you will need to login again. Continue?')) {
                sessionStorage.clear();
                alert('SessionStorage cleared. Please login again.');
                window.location.href = 'login.php';
            }
        }

        function testLoadProfile() {
            console.log('=== MANUAL PROFILE LOAD TEST ===');
            
            const user = sessionStorage.getItem('user');
            if (!user) {
                alert('No user in sessionStorage. Please login first.');
                return;
            }

            try {
                const userData = JSON.parse(user);
                console.log('User data:', userData);
                
                const profileName = document.getElementById('profileName');
                const profileEmail = document.getElementById('profileEmail');
                const profileAvatarLarge = document.getElementById('profileAvatarLarge');
                const profileBtn = document.getElementById('profileBtn');
                
                console.log('Elements:', {
                    profileName: !!profileName,
                    profileEmail: !!profileEmail,
                    profileAvatarLarge: !!profileAvatarLarge,
                    profileBtn: !!profileBtn
                });

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
                
                console.log('Display name:', displayName);
                
                if (profileName) profileName.textContent = displayName || userData.email;
                if (profileEmail) profileEmail.textContent = userData.email;
                
                // Handle profile image
                if (userData.profile_image) {
                    console.log('Processing profile image:', userData.profile_image);
                    
                    let imageSrc;
                    if (userData.profile_image.startsWith('data:')) {
                        imageSrc = userData.profile_image;
                        console.log('Using base64 image');
                    } else {
                        const filename = userData.profile_image.includes('/') || userData.profile_image.includes('\\')
                            ? userData.profile_image.split(/[\\/]/).pop()
                            : userData.profile_image;
                        imageSrc = '/api/uploads/profiles/' + filename;
                        console.log('Image URL:', imageSrc);
                    }
                    
                    const imgTag = `<img src="${imageSrc}" class="w-full h-full object-cover rounded-full" alt="Profile" />`;
                    if (profileAvatarLarge) {
                        profileAvatarLarge.innerHTML = imgTag;
                        console.log('Updated profileAvatarLarge');
                    }
                    if (profileBtn) {
                        profileBtn.innerHTML = imgTag;
                        console.log('Updated profileBtn');
                    }
                    
                    alert('✅ Profile image loaded successfully!\nCheck the preview above and browser console.');
                } else {
                    console.log('No profile image, using initials');
                    const initials = displayName.split(' ').map(n => n[0]).join('').toUpperCase().substring(0, 2);
                    if (profileAvatarLarge) profileAvatarLarge.textContent = initials;
                    if (profileBtn) profileBtn.textContent = initials;
                    console.log('Set initials:', initials);
                    
                    alert('ℹ️ No profile image found. Showing initials: ' + initials);
                }
                
            } catch (error) {
                console.error('Error:', error);
                alert('❌ Error: ' + error.message);
            }
        }

        // Run checks on page load
        window.addEventListener('DOMContentLoaded', () => {
            console.log('Debug page loaded');
            checkSessionStorage();
            
            // Try to load profile automatically
            const user = sessionStorage.getItem('user');
            if (user) {
                setTimeout(testLoadProfile, 500);
            }
        });
    </script>
</body>
</html>
