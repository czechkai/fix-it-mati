<!DOCTYPE html>
<html>
<head>
    <title>Token Debug</title>
    <style>
        body { font-family: monospace; padding: 20px; background: #f5f5f5; }
        .box { background: white; padding: 20px; margin: 20px 0; border-radius: 8px; }
        pre { background: #1e293b; color: #e2e8f0; padding: 15px; border-radius: 4px; overflow-x: auto; }
        button { padding: 10px 20px; background: #3b82f6; color: white; border: none; border-radius: 4px; cursor: pointer; }
    </style>
</head>
<body>
    <h1>🔐 Authentication Token Debug</h1>
    
    <div class="box">
        <h3>sessionStorage Token:</h3>
        <pre id="sessionToken">Checking...</pre>
    </div>
    
    <div class="box">
        <h3>localStorage Token:</h3>
        <pre id="localToken">Checking...</pre>
    </div>
    
    <div class="box">
        <h3>Decoded User Info:</h3>
        <pre id="decoded">Checking...</pre>
    </div>
    
    <div class="box">
        <button onclick="testAPI()">Test API Call</button>
        <h3>API Test Result:</h3>
        <pre id="apiResult">Click button to test...</pre>
    </div>
    
    <script src="/assets/api-client.js?v=7"></script>
    <script>
        const sessionToken = sessionStorage.getItem('auth_token');
        const localToken = localStorage.getItem('auth_token');
        
        document.getElementById('sessionToken').textContent = sessionToken || 'null';
        document.getElementById('localToken').textContent = localToken || 'null';
        
        const token = sessionToken || localToken;
        
        if (token) {
            try {
                const parts = token.split('.');
                const payload = JSON.parse(atob(parts[1]));
                document.getElementById('decoded').textContent = JSON.stringify(payload, null, 2);
            } catch (e) {
                document.getElementById('decoded').textContent = 'Error: ' + e.message;
            }
        } else {
            document.getElementById('decoded').textContent = 'No token found';
        }
        
        async function testAPI() {
            const resultEl = document.getElementById('apiResult');
            resultEl.textContent = 'Loading...';
            
            if (token && !sessionStorage.getItem('auth_token')) {
                sessionStorage.setItem('auth_token', token);
            }
            
            try {
                const response = await ApiClient.requests.getAll();
                resultEl.textContent = JSON.stringify(response, null, 2);
            } catch (error) {
                resultEl.textContent = 'Error: ' + error.message;
            }
        }
    </script>
</body>
</html>
