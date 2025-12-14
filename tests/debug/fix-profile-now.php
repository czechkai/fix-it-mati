<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Fix Profile</title>
    <style>
        body { font-family: Arial; text-align: center; padding: 50px; background: #f3f4f6; }
        .box { background: white; padding: 40px; border-radius: 10px; max-width: 500px; margin: 0 auto; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        h1 { color: #1e40af; margin-bottom: 20px; }
        button { background: #3b82f6; color: white; border: none; padding: 15px 30px; font-size: 16px; border-radius: 6px; cursor: pointer; font-weight: 600; margin: 10px; }
        button:hover { background: #2563eb; }
        .success { color: #16a34a; font-weight: 600; font-size: 18px; }
        .error { color: #dc2626; font-weight: 600; }
        #status { margin: 20px 0; min-height: 60px; }
        .profile-preview { width: 100px; height: 100px; border-radius: 50%; margin: 20px auto; }
    </style>
</head>
<body>
    <div class="box">
        <h1>🔧 Fix Profile Image</h1>
        <p>Click the button to refresh your profile data from the database.</p>
        <div id="status">Ready</div>
        <button onclick="fixNow()">Fix Profile Now</button>
        <button onclick="window.location.href='user-dashboard.php'">Go to Dashboard</button>
    </div>

    <script src="/assets/api-client.js"></script>
    <script>
        async function fixNow() {
            const status = document.getElementById('status');
            status.innerHTML = '<p class="info">⏳ Refreshing profile data...</p>';
            
            try {
                // Get fresh data from API
                const response = await fetch('/api/auth/me', {
                    headers: {
                        'Authorization': 'Bearer ' + sessionStorage.getItem('auth_token')
                    }
                });
                
                if (!response.ok) {
                    throw new Error('Failed to fetch profile data');
                }
                
                const data = await response.json();
                
                if (data.success && data.data) {
                    // Update sessionStorage with fresh data
                    sessionStorage.setItem('user', JSON.stringify(data.data));
                    
                    const user = data.data;
                    let msg = '<p class="success">✅ Profile data refreshed!</p>';
                    
                    if (user.profile_image) {
                        msg += '<p class="success">✅ Profile image found: ' + user.profile_image + '</p>';
                        msg += '<img src="/api/uploads/profiles/' + user.profile_image + '" class="profile-preview" alt="Profile">';
                        msg += '<p><strong>The image should now appear on all pages!</strong></p>';
                    } else {
                        msg += '<p class="error">⚠️ No profile image in database</p>';
                        msg += '<p>Go to Edit Profile to upload an image.</p>';
                    }
                    
                    status.innerHTML = msg;
                    
                    // Reload page after 2 seconds
                    setTimeout(() => {
                        window.location.reload();
                    }, 2000);
                } else {
                    status.innerHTML = '<p class="error">❌ Failed to get profile data</p>';
                }
            } catch (error) {
                status.innerHTML = '<p class="error">❌ Error: ' + error.message + '</p><p>Make sure you are logged in!</p>';
            }
        }
        
        // Check login status on load
        window.addEventListener('DOMContentLoaded', () => {
            const token = sessionStorage.getItem('auth_token');
            if (!token) {
                document.getElementById('status').innerHTML = '<p class="error">❌ You are not logged in!</p>';
                setTimeout(() => {
                    window.location.href = 'login.php';
                }, 2000);
            }
        });
    </script>
</body>
</html>
