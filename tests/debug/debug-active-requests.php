<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Debug - Active Requests</title>
    <style>
        body { font-family: monospace; padding: 20px; background: #f5f5f5; }
        .section { background: white; padding: 20px; margin-bottom: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        h2 { margin-top: 0; color: #2563eb; }
        pre { background: #1e293b; color: #e2e8f0; padding: 15px; border-radius: 4px; overflow-x: auto; }
        .status { display: inline-block; padding: 4px 12px; border-radius: 4px; font-size: 12px; font-weight: bold; }
        .status.pending { background: #fef3c7; color: #92400e; }
        .status.completed { background: #d1fae5; color: #065f46; }
        .status.in_progress { background: #dbeafe; color: #1e40af; }
    </style>
</head>
<body>
    <h1>🔍 Active Requests Debug Panel</h1>
    
    <div class="section">
        <h2>1. Authentication Status</h2>
        <div id="authStatus">Checking...</div>
    </div>

    <div class="section">
        <h2>2. Current User Info (from JWT)</h2>
        <div id="userInfo">Decoding token...</div>
    </div>

    <div class="section">
        <h2>3. API Response</h2>
        <div id="apiResponse">Loading...</div>
    </div>

    <div class="section">
        <h2>4. Filtered Active Requests</h2>
        <div id="activeRequests">Processing...</div>
    </div>

    <script src="/assets/api-client.js?v=7"></script>
    <script>
        async function debugActiveRequests() {
            // 1. Check auth token
            const token = sessionStorage.getItem('auth_token');
            const authDiv = document.getElementById('authStatus');
            
            if (!token) {
                authDiv.innerHTML = '<strong style="color: red;">❌ No auth token found</strong><br>You need to <a href="/login.php">login</a> first.';
                return;
            }
            
            authDiv.innerHTML = '<strong style="color: green;">✅ Auth token present</strong><br>Token: ' + token.substring(0, 50) + '...';
            
            // 2. Decode JWT to see user info
            try {
                const payload = JSON.parse(atob(token.split('.')[1]));
                const userDiv = document.getElementById('userInfo');
                userDiv.innerHTML = `
                    <strong>User ID:</strong> ${payload.user_id}<br>
                    <strong>Email:</strong> ${payload.email}<br>
                    <strong>Role:</strong> ${payload.role}<br>
                    <strong>Token Issued:</strong> ${new Date(payload.iat * 1000).toLocaleString()}<br>
                    <strong>Token Expires:</strong> ${new Date(payload.exp * 1000).toLocaleString()}
                `;
            } catch (e) {
                document.getElementById('userInfo').innerHTML = '<strong style="color: red;">Error decoding token:</strong> ' + e.message;
            }
            
            // 3. Make API call
            try {
                const response = await ApiClient.requests.getAll();
                const apiDiv = document.getElementById('apiResponse');
                
                apiDiv.innerHTML = '<pre>' + JSON.stringify(response, null, 2) + '</pre>';
                
                // 4. Filter active requests
                const activeDiv = document.getElementById('activeRequests');
                
                if (response.success && response.data && response.data.requests) {
                    const allRequests = response.data.requests;
                    const activeRequests = allRequests.filter(r => r.status === 'pending' || r.status === 'in_progress');
                    
                    if (activeRequests.length === 0) {
                        activeDiv.innerHTML = `
                            <strong>Total Requests:</strong> ${allRequests.length}<br>
                            <strong>Active Requests:</strong> 0<br><br>
                            <em style="color: #dc2626;">No pending or in_progress requests found.</em><br><br>
                            <strong>Request Statuses:</strong><br>
                            ${allRequests.map(r => `• ${r.title}: <span class="status ${r.status}">${r.status}</span>`).join('<br>')}
                        `;
                    } else {
                        activeDiv.innerHTML = `
                            <strong>Total Requests:</strong> ${allRequests.length}<br>
                            <strong>Active Requests:</strong> ${activeRequests.length}<br><br>
                            ${activeRequests.map(r => `
                                <div style="padding: 10px; border: 1px solid #e5e7eb; border-radius: 4px; margin-bottom: 10px;">
                                    <strong>${r.title}</strong><br>
                                    Status: <span class="status ${r.status}">${r.status}</span><br>
                                    Category: ${r.category}<br>
                                    Created: ${new Date(r.created_at).toLocaleString()}
                                </div>
                            `).join('')}
                        `;
                    }
                } else {
                    activeDiv.innerHTML = '<strong style="color: red;">Invalid API response structure</strong>';
                }
                
            } catch (error) {
                document.getElementById('apiResponse').innerHTML = '<strong style="color: red;">API Error:</strong> ' + error.message;
                document.getElementById('activeRequests').innerHTML = '<strong style="color: red;">Cannot filter requests due to API error</strong>';
            }
        }
        
        // Run debug on page load
        debugActiveRequests();
    </script>
</body>
</html>
