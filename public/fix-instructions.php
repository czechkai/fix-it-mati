<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Profile Image Fix - Simple Instructions</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 700px;
            margin: 50px auto;
            padding: 20px;
            background: #f0f9ff;
        }
        .box {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        h1 {
            color: #1e40af;
            margin-top: 0;
        }
        .step {
            background: #f1f5f9;
            padding: 20px;
            margin: 20px 0;
            border-radius: 8px;
            border-left: 4px solid #3b82f6;
        }
        .step-number {
            display: inline-block;
            width: 32px;
            height: 32px;
            background: #3b82f6;
            color: white;
            border-radius: 50%;
            text-align: center;
            line-height: 32px;
            font-weight: bold;
            margin-right: 10px;
        }
        button, a.button {
            display: inline-block;
            background: #3b82f6;
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            margin: 10px 5px;
        }
        button:hover, a.button:hover {
            background: #2563eb;
        }
        .success {
            color: #16a34a;
            font-weight: 600;
        }
        code {
            background: #1e293b;
            color: #e2e8f0;
            padding: 2px 6px;
            border-radius: 4px;
            font-family: 'Courier New', monospace;
        }
        .important {
            background: #fef3c7;
            border: 2px solid #f59e0b;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="box">
        <h1>🎯 Fix Profile Image - 3 Simple Steps</h1>
        
        <div class="important">
            <strong>⚠️ Database Issue:</strong> PostgreSQL is not running. But don't worry - we can fix this using the Edit Profile page!
        </div>

        <div class="step">
            <span class="step-number">1</span>
            <strong>Go to Edit Profile Page</strong>
            <p>Click this button to open Edit Profile:</p>
            <a href="edit-profile.php" class="button">📝 Open Edit Profile</a>
        </div>

        <div class="step">
            <span class="step-number">2</span>
            <strong>Re-upload Any Image</strong>
            <p>
                • Click on the profile picture circle on the left<br>
                • Choose ANY image from your computer (JPG, PNG, GIF)<br>
                • You'll see a preview
            </p>
        </div>

        <div class="step">
            <span class="step-number">3</span>
            <strong>Click Save Changes</strong>
            <p>
                • Scroll down and click the blue <strong>"Save Changes"</strong> button<br>
                • Wait for the success message<br>
                • <span class="success">✅ Done! Your image is now saved to the database!</span>
            </p>
        </div>

        <hr style="margin: 30px 0; border: none; border-top: 1px solid #e2e8f0;">

        <h2>🧪 Alternative: Use Browser Console</h2>
        <p>If re-uploading doesn't work, try this:</p>
        
        <div class="step">
            <p><strong>1.</strong> Go to Edit Profile page</p>
            <p><strong>2.</strong> Press <code>F12</code> to open DevTools</p>
            <p><strong>3.</strong> Go to <strong>Console</strong> tab</p>
            <p><strong>4.</strong> Copy and paste this code, then press Enter:</p>
            <textarea readonly onclick="this.select(); document.execCommand('copy'); alert('Copied!');" style="width: 100%; height: 200px; font-family: monospace; padding: 10px; margin: 10px 0; border: 2px solid #cbd5e1; border-radius: 4px;">
(async function() {
    try {
        const token = sessionStorage.getItem('auth_token');
        if (!token) {
            alert('❌ Not logged in. Please login first.');
            return;
        }
        
        const formData = new FormData();
        formData.append('_method', 'PUT');
        formData.append('fix_profile_image', 'profile_5f9b00be-dbdc-45b5-9df4-b2341cdfdb8b_1765701097.jpg');
        formData.append('email', 'adie.lacson@gmail.com');
        
        console.log('Updating database...');
        const response = await fetch('/api/auth/profile', {
            method: 'POST',
            headers: {'Authorization': 'Bearer ' + token},
            body: formData
        });
        
        const result = await response.json();
        console.log('Result:', result);
        
        if (result.success) {
            alert('✅ SUCCESS! Database updated!');
            // Refresh user data
            const userResp = await fetch('/api/auth/me', {
                headers: {'Authorization': 'Bearer ' + token}
            });
            const userData = await userResp.json();
            sessionStorage.setItem('user', JSON.stringify(userData.data));
            console.log('SessionStorage updated');
            location.reload();
        } else {
            alert('❌ Error: ' + (result.message || 'Update failed'));
        }
    } catch (error) {
        alert('❌ Error: ' + error.message);
        console.error(error);
    }
})();
</textarea>
            <button onclick="navigator.clipboard.writeText(document.querySelector('textarea').value).then(() => alert('✅ Copied to clipboard!')); ">📋 Copy Code</button>
        </div>

        <hr style="margin: 30px 0;">

        <h2>✅ After Fixing</h2>
        <p>Test these pages to see your profile image:</p>
        <a href="active-requests.php" class="button">Active Requests</a>
        <a href="announcements.php" class="button">Announcements</a>
        <a href="user-dashboard.php" class="button">Dashboard</a>
        
        <p style="margin-top: 20px; color: #64748b; font-size: 14px;">
            <strong>💡 Tip:</strong> Press <code>Ctrl+F5</code> on each page to hard refresh and see the updated image!
        </p>
    </div>
</body>
</html>
