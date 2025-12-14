<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fix Profile Image in Database</title>
    <!-- CRITICAL: Check authentication immediately -->
    <script>
        // Run BEFORE page renders
        (function() {
            console.log('=== AUTH CHECK STARTED ===');
            console.log('Current URL:', window.location.href);
            console.log('Checking sessionStorage...');
            
            const token = sessionStorage.getItem('auth_token');
            const user = sessionStorage.getItem('user');
            
            console.log('Token exists:', !!token);
            console.log('User exists:', !!user);
            
            if (token) {
                console.log('Token preview:', token.substring(0, 50) + '...');
            }
            if (user) {
                try {
                    const userData = JSON.parse(user);
                    console.log('User email:', userData.email);
                } catch (e) {
                    console.error('User data parse error:', e);
                }
            }
            
            // Also check localStorage
            const localToken = localStorage.getItem('auth_token');
            const localUser = localStorage.getItem('user');
            console.log('LocalStorage token:', !!localToken);
            console.log('LocalStorage user:', !!localUser);
            
            // If not in sessionStorage but in localStorage, copy over
            if (!token && localToken) {
                console.log('Found token in localStorage, copying to sessionStorage...');
                sessionStorage.setItem('auth_token', localToken);
                if (localUser) {
                    sessionStorage.setItem('user', localUser);
                }
                console.log('Copied from localStorage to sessionStorage');
            }
            
            console.log('=== AUTH CHECK COMPLETE ===');
        })();
    </script>
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
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .status {
            padding: 15px;
            border-radius: 6px;
            margin: 15px 0;
            font-weight: 500;
        }
        .success { background: #dcfce7; color: #166534; }
        .error { background: #fee2e2; color: #991b1b; }
        .info { background: #dbeafe; color: #1e40af; }
        .warning { background: #fef3c7; color: #92400e; }
        button {
            background: #3b82f6;
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
        }
        button:hover { background: #2563eb; }
        button:disabled {
            background: #94a3b8;
            cursor: not-allowed;
        }
        pre {
            background: #1e293b;
            color: #e2e8f0;
            padding: 15px;
            border-radius: 4px;
            overflow-x: auto;
        }
        h1 { color: #1e293b; }
        h2 { color: #475569; margin-top: 0; }
    </style>
</head>
<body>
    <div class="section">
        <h1>🔧 Fix Profile Image Database</h1>
        
        <div class="status info">
            <strong>Problem Detected:</strong><br>
            Your profile image files exist in the uploads folder, but the database field is NULL.<br>
            This tool will update the database with your most recent uploaded image.
        </div>

        <div id="status"></div>

        <h2>Step 1: Check Your Current Status</h2>
        <button onclick="checkStatus()" id="checkBtn">Check Status</button>
        <div id="checkResult"></div>

        <h2>Step 2: Fix the Database</h2>
        <button onclick="fixDatabase()" id="fixBtn" disabled>Fix Database Now</button>
        <div id="fixResult"></div>

        <h2>Step 3: Verify Fix</h2>
        <button onclick="verifyFix()" id="verifyBtn" disabled>Verify Fix</button>
        <div id="verifyResult"></div>

        <div style="margin-top: 30px;">
            <a href="edit-profile.php" style="color: #3b82f6; text-decoration: none; font-weight: 600;">← Go to Edit Profile</a> | 
            <a href="debug-login.php" style="color: #3b82f6; text-decoration: none; font-weight: 600;">Go to Debug Login →</a>
        </div>
    </div>

    <script src="/assets/api-client.js"></script>
    <script>
        let latestFilename = null;

        async function checkStatus() {
            const btn = document.getElementById('checkBtn');
            const result = document.getElementById('checkResult');
            
            btn.disabled = true;
            btn.textContent = 'Checking...';
            result.innerHTML = '<div class="status info">⏳ Checking...</div>';

            try {
                // Verify we have auth token
                const token = sessionStorage.getItem('auth_token');
                if (!token) {
                    result.innerHTML = '<div class="status error">❌ No auth token. Please <a href="login.php">login</a> first.</div>';
                    btn.disabled = false;
                    btn.textContent = 'Check Status';
                    return;
                }

                // Get current user data
                const userData = await ApiClient.get('/auth/me');
                
                if (!userData.success) {
                    result.innerHTML = `<div class="status error">❌ API Error: ${userData.message || 'Not authenticated'}</div>`;
                    btn.disabled = false;
                    btn.textContent = 'Check Status';
                    return;
                }

                const user = userData.data;
                
                // Check uploaded files via API
                const filesResponse = await fetch(`/api/check-profile-files.php?user_id=${user.id}`);
                const filesData = await filesResponse.json();

                let html = '<div class="status info">';
                html += '<strong>Current Database:</strong><br>';
                html += `Email: ${user.email}<br>`;
                html += `Profile Image: ${user.profile_image || 'NULL'}<br><br>`;
                
                if (filesData.files && filesData.files.length > 0) {
                    html += '<strong>Files Found in Uploads:</strong><br>';
                    filesData.files.forEach(file => {
                        html += `- ${file.name} (${file.size} KB)<br>`;
                    });
                    latestFilename = filesData.latest;
                    html += `<br><strong>Latest:</strong> ${latestFilename}`;
                    html += '</div>';
                    
                    if (!user.profile_image) {
                        html += '<div class="status warning">⚠️ Database is NULL but files exist. Click "Fix Database Now" below.</div>';
                        document.getElementById('fixBtn').disabled = false;
                    } else if (user.profile_image !== latestFilename) {
                        html += '<div class="status warning">⚠️ Database has old filename. Click "Fix Database Now" to update.</div>';
                        document.getElementById('fixBtn').disabled = false;
                    } else {
                        html += '<div class="status success">✅ Database is correct!</div>';
                    }
                } else {
                    html += '<br>❌ No files found in uploads folder.';
                    html += '</div>';
                }
                
                result.innerHTML = html;
                
            } catch (error) {
                result.innerHTML = `<div class="status error">❌ Error: ${error.message}</div>`;
            } finally {
                btn.disabled = false;
                btn.textContent = 'Check Status';
            }
        }

        async function fixDatabase() {
            if (!latestFilename) {
                alert('Please check status first!');
                return;
            }

            if (!confirm(`Update database with: ${latestFilename}?`)) {
                return;
            }

            const btn = document.getElementById('fixBtn');
            const result = document.getElementById('fixResult');
            
            btn.disabled = true;
            btn.textContent = 'Fixing...';
            result.innerHTML = '<div class="status info">⏳ Updating database...</div>';

            try {
                // Create FormData with just the filename update
                const formData = new FormData();
                formData.append('_method', 'PUT');
                formData.append('fix_profile_image', latestFilename);

                const token = sessionStorage.getItem('auth_token');
                const response = await fetch('/api/auth/profile', {
                    method: 'POST',
                    headers: {
                        'Authorization': `Bearer ${token}`
                    },
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    result.innerHTML = '<div class="status success">✅ Database updated successfully!</div>';
                    
                    // Update sessionStorage
                    const userResponse = await ApiClient.get('/auth/me');
                    if (userResponse.success) {
                        sessionStorage.setItem('user', JSON.stringify(userResponse.data));
                        result.innerHTML += '<div class="status success">✅ SessionStorage updated!</div>';
                    }
                    
                    document.getElementById('verifyBtn').disabled = false;
                } else {
                    result.innerHTML = `<div class="status error">❌ ${data.message}</div>`;
                }
                
            } catch (error) {
                result.innerHTML = `<div class="status error">❌ Error: ${error.message}</div>`;
            } finally {
                btn.disabled = false;
                btn.textContent = 'Fix Database Now';
            }
        }

        async function verifyFix() {
            const btn = document.getElementById('verifyBtn');
            const result = document.getElementById('verifyResult');
            
            btn.disabled = true;
            btn.textContent = 'Verifying...';
            result.innerHTML = '<div class="status info">⏳ Verifying...</div>';

            try {
                const userData = await ApiClient.get('/auth/me');
                
                if (userData.success && userData.data.profile_image) {
                    const imageUrl = `/api/uploads/profiles/${userData.data.profile_image}`;
                    
                    result.innerHTML = `
                        <div class="status success">
                            ✅ SUCCESS! Profile image is now set!<br><br>
                            <strong>Filename:</strong> ${userData.data.profile_image}<br>
                            <strong>URL:</strong> <a href="${imageUrl}" target="_blank">${imageUrl}</a><br><br>
                            <img src="${imageUrl}" style="width: 100px; height: 100px; border-radius: 50%; object-fit: cover;" alt="Profile">
                        </div>
                        <div class="status info">
                            🎉 Now go to these pages to see your image:<br>
                            <a href="active-requests.php">• Active Requests</a><br>
                            <a href="announcements.php">• Announcements</a><br>
                            <a href="user-dashboard.php">• Dashboard</a>
                        </div>
                    `;
                } else {
                    result.innerHTML = '<div class="status error">❌ Profile image still NULL. Try Fix Database again.</div>';
                }
                
            } catch (error) {
                result.innerHTML = `<div class="status error">❌ Error: ${error.message}</div>`;
            } finally {
                btn.disabled = false;
                btn.textContent = 'Verify Fix';
            }
        }

        // Auto-check on load - with delay to ensure sessionStorage is loaded
        window.addEventListener('DOMContentLoaded', () => {
            setTimeout(() => {
                let token = sessionStorage.getItem('auth_token');
                let user = sessionStorage.getItem('user');
                
                // Check localStorage as fallback
                const localToken = localStorage.getItem('auth_token');
                const localUser = localStorage.getItem('user');
                
                console.log('DOMContentLoaded auth check:', {
                    sessionToken: !!token,
                    sessionUser: !!user,
                    localToken: !!localToken,
                    localUser: !!localUser
                });
                
                // If not in session but in local, copy over
                if ((!token || !user) && (localToken || localUser)) {
                    console.log('Restoring from localStorage...');
                    if (localToken) {
                        sessionStorage.setItem('auth_token', localToken);
                        token = localToken;
                    }
                    if (localUser) {
                        sessionStorage.setItem('user', localUser);
                        user = localUser;
                    }
                }
                
                if (!token || !user) {
                    document.getElementById('status').innerHTML = `
                        <div class="status error">
                            ❌ Not logged in or session data missing.<br><br>
                            <strong>SessionStorage:</strong><br>
                            - auth_token: ${token ? '✅ Present' : '❌ Missing'}<br>
                            - user: ${user ? '✅ Present' : '❌ Missing'}<br><br>
                            <strong>LocalStorage (Remember Me):</strong><br>
                            - auth_token: ${localToken ? '✅ Present' : '❌ Missing'}<br>
                            - user: ${localUser ? '✅ Present' : '❌ Missing'}<br><br>
                            <strong>Actions:</strong><br>
                            <a href="login.php" style="color: #dc2626; font-weight: 600;">→ Login Again</a> |
                            <a href="debug-login.php" style="color: #3b82f6; font-weight: 600;">→ Debug Tool</a><br><br>
                            <button onclick="location.reload()" style="padding: 8px 16px; background: #64748b; color: white; border: none; border-radius: 4px; cursor: pointer;">
                                🔄 Reload Page
                            </button>
                        </div>
                    `;
                    document.getElementById('checkBtn').disabled = true;
                } else {
                    try {
                        const userData = JSON.parse(user);
                        document.getElementById('status').innerHTML = '<div class="status success">✅ Logged in as ' + userData.email + '</div>';
                        checkStatus();
                    } catch (e) {
                        document.getElementById('status').innerHTML = '<div class="status error">❌ Invalid user data. Please <a href="login.php">login again</a>.</div>';
                        document.getElementById('checkBtn').disabled = true;
                    }
                }
            }, 300);
        });
    </script>
</body>
</html>
