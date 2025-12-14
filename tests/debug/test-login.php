<!DOCTYPE html>
<html>
<head>
    <title>Login Test</title>
    <style>
        body { font-family: monospace; padding: 20px; background: #f5f5f5; }
        .box { background: white; padding: 20px; margin: 20px 0; border-radius: 8px; }
        input { padding: 10px; margin: 5px 0; width: 300px; display: block; }
        button { padding: 10px 20px; background: #3b82f6; color: white; border: none; border-radius: 4px; cursor: pointer; margin: 10px 0; }
        pre { background: #1e293b; color: #e2e8f0; padding: 15px; border-radius: 4px; overflow-x: auto; }
        .success { color: #10b981; }
        .error { color: #ef4444; }
    </style>
</head>
<body>
    <h1>🔐 Direct Login Test</h1>
    
    <div class="box">
        <h3>Test Login:</h3>
        <input type="email" id="email" placeholder="Email" value="test.customer@example.com">
        <input type="password" id="password" placeholder="Password" value="password123">
        <button onclick="testLogin()">Login</button>
        
        <h3>Result:</h3>
        <pre id="result">Enter credentials and click Login</pre>
    </div>
    
    <div class="box">
        <h3>Available Test Accounts:</h3>
        <pre>
test.customer@example.com / password123 (3 active requests)
jaysonB354@gmail.com / password123 (4 active requests)
saerlibanon0@gmail.com / password123 (1 active request)
newuser@mati.gov.ph / password123 (2 active requests)
jaysonb458@gmail.com / password123 (2 active requests)
testuser99@mati.gov.ph / password123 (2 active requests)
        </pre>
    </div>
    
    <script src="/assets/api-client.js?v=7"></script>
    <script>
        async function testLogin() {
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const resultEl = document.getElementById('result');
            
            resultEl.textContent = 'Logging in...';
            
            try {
                const response = await fetch('/api/login', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ email, password })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    resultEl.innerHTML = '<span class="success">✅ Login successful!</span>\n\n' + JSON.stringify(result, null, 2);
                    
                    // Save token
                    sessionStorage.setItem('auth_token', result.data.token);
                    sessionStorage.setItem('user', JSON.stringify(result.data.user));
                    localStorage.setItem('auth_token', result.data.token);
                    localStorage.setItem('user', JSON.stringify(result.data.user));
                    
                    resultEl.innerHTML += '\n\n<span class="success">✅ Token saved to sessionStorage and localStorage</span>';
                    
                    // Test API call
                    setTimeout(async () => {
                        try {
                            const apiResult = await ApiClient.requests.getAll();
                            resultEl.innerHTML += '\n\n<span class="success">✅ API Test:</span>\n' + JSON.stringify(apiResult, null, 2);
                            
                            resultEl.innerHTML += `\n\n<span class="success">You can now go to <a href="/active-requests.php">active-requests.php</a></span>`;
                        } catch (err) {
                            resultEl.innerHTML += '\n\n<span class="error">❌ API Test failed: ' + err.message + '</span>';
                        }
                    }, 500);
                    
                } else {
                    resultEl.innerHTML = '<span class="error">❌ Login failed!</span>\n\n' + JSON.stringify(result, null, 2);
                }
            } catch (error) {
                resultEl.innerHTML = '<span class="error">❌ Error: ' + error.message + '</span>';
            }
        }
    </script>
</body>
</html>
