<!DOCTYPE html>
<html>
<head>
    <title>Registration Test</title>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
</head>
<body>
    <h1>Registration Test</h1>
    <div id="output"></div>
    
    <script>
        const output = document.getElementById('output');
        
        async function testRegistration() {
            try {
                // Step 1: Send verification code
                output.innerHTML += '<p>Step 1: Sending verification code...</p>';
                const email = 'test-' + Date.now() + '@example.com';
                
                const step1 = await axios.post('/api/auth/send-verification-code', {
                    email: email,
                    firstName: 'Test',
                    lastName: 'User'
                }, {
                    withCredentials: true
                });
                
                output.innerHTML += '<p>✅ Step 1 success</p>';
                output.innerHTML += '<pre>' + JSON.stringify(step1.data, null, 2) + '</pre>';
                
                // Step 2: Verify code and register
                output.innerHTML += '<p>Step 2: Verifying code and registering...</p>';
                
                const step2 = await axios.post('/api/auth/verify-and-register', {
                    email: email,
                    firstName: 'Test',
                    lastName: 'User',
                    phone: '09123456789',
                    street: '123 Main St',
                    barangay: 'Badas',
                    password: 'TestPassword123!',
                    verification_code: '000000' // Will fail but we want to see the response
                }, {
                    withCredentials: true
                });
                
                output.innerHTML += '<p>✅ Step 2 success</p>';
                output.innerHTML += '<pre>' + JSON.stringify(step2.data, null, 2) + '</pre>';
                
            } catch(err) {
                output.innerHTML += '<p>❌ Error</p>';
                output.innerHTML += '<pre>' + JSON.stringify(err.response?.data || err.message, null, 2) + '</pre>';
            }
        }
        
        // Run test
        testRegistration();
    </script>
</body>
</html>
