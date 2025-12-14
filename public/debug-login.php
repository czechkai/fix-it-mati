<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login & Session Debug</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 900px;
            margin: 30px auto;
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
        .status {
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 12px;
            font-weight: 500;
        }
        .success { background: #dcfce7; color: #166534; }
        .error { background: #fee2e2; color: #991b1b; }
        .info { background: #dbeafe; color: #1e40af; }
        .warning { background: #fef3c7; color: #92400e; }
        pre {
            background: #1e293b;
            color: #e2e8f0;
            padding: 15px;
            border-radius: 4px;
            overflow-x: auto;
            font-size: 12px;
            max-height: 300px;
            overflow-y: auto;
        }
        button {
            background: #3b82f6;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            margin-right: 10px;
            margin-bottom: 10px;
        }
        button:hover { background: #2563eb; }
        button.danger { background: #dc2626; }
        button.danger:hover { background: #b91c1c; }
        input {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border: 2px solid #e2e8f0;
            border-radius: 6px;
            font-size: 14px;
        }
        .grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        h2 { margin-top: 0; color: #1e293b; }
        .log-entry {
            padding: 8px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 13px;
        }
        .log-entry:last-child { border-bottom: none; }
    </style>
</head>
<body>
    <h1>🔐 Login & Session Debug Tool</h1>

    <div class="section">
        <h2>1. Current Status</h2>
        <div id="currentStatus"></div>
        <button onclick="checkStatus()">Refresh Status</button>
        <button onclick="window.location.href='debug-profile.php'">Go to Profile Debug</button>
    </div>

    <div class="grid">
        <div class="section">
            <h2>2. Test Login</h2>
            <form id="loginForm" onsubmit="testLogin(event)">
                <input type="email" id="email" placeholder="Email" required>
                <input type="password" id="password" placeholder="Password" required>
                <button type="submit">Test Login</button>
            </form>
            <div id="loginStatus"></div>
        </div>

        <div class="section">
            <h2>3. SessionStorage</h2>
            <pre id="sessionData">Loading...</pre>
            <button onclick="viewSession()">View Session</button>
            <button class="danger" onclick="clearSession()">Clear Session</button>
        </div>
    </div>

    <div class="section">
        <h2>4. LocalStorage (Remember Me)</h2>
        <pre id="localData">Loading...</pre>
        <button onclick="viewLocal()">View Local</button>
        <button class="danger" onclick="clearLocal()">Clear Local</button>
    </div>

    <div class="section">
        <h2>5. Test API Direct</h2>
        <button onclick="testMeEndpoint()">Test /api/auth/me</button>
        <button onclick="testLoginEndpoint()">Test /api/auth/login</button>
        <div id="apiStatus"></div>
    </div>

    <div class="section">
        <h2>6. Debug Logs</h2>
        <button onclick="clearLogs()">Clear Logs</button>
        <button onclick="copyLogs()">Copy to Clipboard</button>
        <div id="logs" style="background: #f8fafc; padding: 15px; border-radius: 6px; max-height: 400px; overflow-y: auto;"></div>
    </div>

    <script src="/assets/api-client.js"></script>
    <script>
        const logs = [];

        function log(message, type = 'info') {
            const timestamp = new Date().toLocaleTimeString();
            logs.push({timestamp, message, type});
            updateLogs();
            console.log(`[${timestamp}] ${message}`);
        }

        function updateLogs() {
            const logsDiv = document.getElementById('logs');
            logsDiv.innerHTML = logs.slice(-50).reverse().map(l => 
                `<div class="log-entry" style="color: ${l.type === 'error' ? '#dc2626' : l.type === 'success' ? '#16a34a' : '#64748b'}">
                    <strong>${l.timestamp}</strong>: ${l.message}
                </div>`
            ).join('');
        }

        function clearLogs() {
            logs.length = 0;
            updateLogs();
        }

        function copyLogs() {
            const text = logs.map(l => `[${l.timestamp}] ${l.message}`).join('\n');
            navigator.clipboard.writeText(text);
            alert('Logs copied to clipboard!');
        }

        function checkStatus() {
            log('Checking authentication status...');
            const statusDiv = document.getElementById('currentStatus');
            
            const sessionToken = sessionStorage.getItem('auth_token');
            const sessionUser = sessionStorage.getItem('user');
            const localToken = localStorage.getItem('auth_token');
            const localUser = localStorage.getItem('user');

            let html = '';

            if (sessionToken) {
                html += '<div class="status success">✅ SessionStorage: Has auth_token</div>';
                log('SessionStorage has auth_token', 'success');
            } else {
                html += '<div class="status error">❌ SessionStorage: No auth_token</div>';
                log('SessionStorage missing auth_token', 'error');
            }

            if (sessionUser) {
                try {
                    const user = JSON.parse(sessionUser);
                    html += '<div class="status success">✅ SessionStorage: Has user data</div>';
                    html += `<div class="status info">User: ${user.email} (ID: ${user.id})</div>`;
                    log(`SessionStorage has user: ${user.email}`, 'success');
                } catch (e) {
                    html += '<div class="status error">❌ SessionStorage: Invalid user data</div>';
                    log('SessionStorage has invalid user JSON', 'error');
                }
            } else {
                html += '<div class="status error">❌ SessionStorage: No user data</div>';
                log('SessionStorage missing user data', 'error');
            }

            if (localToken) {
                html += '<div class="status info">ℹ️ LocalStorage: Has auth_token (Remember Me)</div>';
                log('LocalStorage has auth_token (remember me)', 'info');
            }

            if (localUser) {
                html += '<div class="status info">ℹ️ LocalStorage: Has user data (Remember Me)</div>';
                log('LocalStorage has user data', 'info');
            }

            statusDiv.innerHTML = html;
        }

        function viewSession() {
            log('Viewing sessionStorage...');
            const data = {
                auth_token: sessionStorage.getItem('auth_token') ? 'Present (hidden)' : null,
                user: sessionStorage.getItem('user')
            };
            document.getElementById('sessionData').textContent = JSON.stringify(data, null, 2);
        }

        function viewLocal() {
            log('Viewing localStorage...');
            const data = {
                auth_token: localStorage.getItem('auth_token') ? 'Present (hidden)' : null,
                user: localStorage.getItem('user')
            };
            document.getElementById('localData').textContent = JSON.stringify(data, null, 2);
        }

        function clearSession() {
            if (confirm('Clear sessionStorage? This will log you out.')) {
                sessionStorage.clear();
                log('SessionStorage cleared', 'warning');
                checkStatus();
                viewSession();
            }
        }

        function clearLocal() {
            if (confirm('Clear localStorage? This will remove "Remember Me".')) {
                localStorage.clear();
                log('LocalStorage cleared', 'warning');
                checkStatus();
                viewLocal();
            }
        }

        async function testLogin(event) {
            event.preventDefault();
            log('Testing login...');
            
            const statusDiv = document.getElementById('loginStatus');
            statusDiv.innerHTML = '<div class="status info">⏳ Logging in...</div>';

            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;

            try {
                log(`Calling ApiClient.auth.login(${email}, ****)...`);
                const result = await ApiClient.auth.login(email, password);
                
                log('Login API response received', 'success');
                log(JSON.stringify(result, null, 2));

                if (result.success) {
                    statusDiv.innerHTML = '<div class="status success">✅ Login successful!</div>';
                    log('Login successful!', 'success');
                    
                    // Check what was stored
                    setTimeout(() => {
                        checkStatus();
                        viewSession();
                        
                        const token = sessionStorage.getItem('auth_token');
                        const user = sessionStorage.getItem('user');
                        
                        if (token && user) {
                            statusDiv.innerHTML += '<div class="status success">✅ SessionStorage updated correctly</div>';
                            log('SessionStorage contains auth data', 'success');
                        } else {
                            statusDiv.innerHTML += '<div class="status error">❌ SessionStorage NOT updated!</div>';
                            log('ERROR: SessionStorage not updated after login!', 'error');
                        }
                    }, 100);
                } else {
                    statusDiv.innerHTML = `<div class="status error">❌ ${result.error || result.message}</div>`;
                    log(`Login failed: ${result.error || result.message}`, 'error');
                }
            } catch (error) {
                statusDiv.innerHTML = `<div class="status error">❌ Error: ${error.message}</div>`;
                log(`Login error: ${error.message}`, 'error');
                console.error('Login error:', error);
            }
        }

        async function testMeEndpoint() {
            log('Testing /api/auth/me endpoint...');
            const statusDiv = document.getElementById('apiStatus');
            statusDiv.innerHTML = '<div class="status info">⏳ Testing...</div>';

            try {
                const result = await ApiClient.get('/auth/me');
                log('API /auth/me response:', 'success');
                log(JSON.stringify(result, null, 2));
                
                if (result.success) {
                    statusDiv.innerHTML = `<div class="status success">✅ API returned user data</div>`;
                    statusDiv.innerHTML += `<pre>${JSON.stringify(result.data, null, 2)}</pre>`;
                } else {
                    statusDiv.innerHTML = `<div class="status error">❌ ${result.message}</div>`;
                    log(`API error: ${result.message}`, 'error');
                }
            } catch (error) {
                statusDiv.innerHTML = `<div class="status error">❌ ${error.message}</div>`;
                log(`API error: ${error.message}`, 'error');
            }
        }

        async function testLoginEndpoint() {
            const email = prompt('Email:');
            const password = prompt('Password:');
            
            if (!email || !password) return;

            log('Testing /api/auth/login endpoint directly...');
            const statusDiv = document.getElementById('apiStatus');
            statusDiv.innerHTML = '<div class="status info">⏳ Testing...</div>';

            try {
                const response = await fetch('/api/auth/login', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({email, password})
                });

                const result = await response.json();
                log('Raw API response:', 'success');
                log(JSON.stringify(result, null, 2));

                statusDiv.innerHTML = `<pre>${JSON.stringify(result, null, 2)}</pre>`;
            } catch (error) {
                statusDiv.innerHTML = `<div class="status error">❌ ${error.message}</div>`;
                log(`API error: ${error.message}`, 'error');
            }
        }

        // Run on load
        window.addEventListener('DOMContentLoaded', () => {
            log('Debug tool loaded');
            checkStatus();
            viewSession();
            viewLocal();
        });
    </script>
</body>
</html>
