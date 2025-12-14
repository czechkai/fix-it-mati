<?php
// Login page for FixItMati
// Don't start session or check login status - let JavaScript handle it
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Sign In - FixItMati</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/lucide@latest"></script>
  <style>
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(-10px); }
      to { opacity: 1; transform: translateY(0); }
    }
    .animate-fade-in {
      animation: fadeIn 0.3s ease-out;
    }
    @keyframes spin {
      to { transform: rotate(360deg); }
    }
    .animate-spin {
      animation: spin 1s linear infinite;
    }
    @keyframes float {
      0%, 100% { transform: translateY(0px); }
      50% { transform: translateY(-20px); }
    }
    .animate-float {
      animation: float 6s ease-in-out infinite;
    }
    @keyframes pulse-glow {
      0%, 100% { opacity: 0.1; }
      50% { opacity: 0.15; }
    }
    .animate-pulse-glow {
      animation: pulse-glow 4s ease-in-out infinite;
    }
    .glass-effect {
      background: rgba(255, 255, 255, 0.05);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(255, 255, 255, 0.1);
    }
    .gradient-text {
      background: linear-gradient(135deg, #60a5fa 0%, #3b82f6 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }
    input:focus {
      transform: translateY(-1px);
    }
    .feature-card {
      transition: all 0.3s ease;
    }
    .feature-card:hover {
      transform: translateX(8px);
    }
  </style>
</head>
<body class="min-h-screen flex bg-white font-sans text-slate-800 overflow-hidden">
  
  <!-- LEFT SIDE: BRANDING & INFO -->
  <div class="hidden lg:flex w-1/2 bg-gradient-to-br from-blue-950 via-blue-900 to-blue-800 relative overflow-hidden flex-col justify-between p-12 pb-10 text-white">
    
    <!-- Background Decorative Circles -->
    <div class="absolute top-0 left-0 w-full h-full overflow-hidden z-0 pointer-events-none">
      <div class="absolute -top-24 -left-24 w-96 h-96 rounded-full bg-cyan-400/20 blur-3xl animate-pulse-glow"></div>
      <div class="absolute top-1/3 right-0 w-80 h-80 rounded-full bg-blue-500/10 blur-3xl animate-float"></div>
      <div class="absolute bottom-0 right-0 w-[500px] h-[500px] rounded-full bg-indigo-400/15 blur-3xl"></div>
      
      <!-- Grid Pattern Overlay -->
      <div class="absolute inset-0 opacity-[0.02]" style="background-image: radial-gradient(circle, white 1px, transparent 1px); background-size: 30px 30px;"></div>
    </div>

    <!-- Top Branding -->
    <div class="relative z-10 flex items-center gap-3 group">
      <div class="glass-effect p-3 rounded-2xl shadow-lg transform transition-all duration-300 group-hover:scale-105 group-hover:shadow-2xl">
        <i data-lucide="hammer" class="w-7 h-7"></i>
      </div>
      <span class="text-2xl font-bold tracking-tight">FixItMati</span>
    </div>

    <!-- Central Message -->
    <div class="relative z-10 max-w-lg">
      <div class="inline-block mb-4">
      
      </div>
      <h1 class="text-5xl font-extrabold mb-6 leading-tight tracking-tight">
        Streamlining Public Services for a 
        <span class="text-transparent bg-clip-text bg-gradient-to-r from-cyan-300 to-blue-300">Better Mati City</span>
      </h1>
      <p class="text-blue-100/90 text-lg leading-relaxed mb-10 font-light">
        Submit requests, track utility repairs, and manage your monthly bills—all in one centralized platform designed for efficiency and transparency.
      </p>
      
      <div class="space-y-5">
        <div class="feature-card flex items-start gap-4 p-4 rounded-xl glass-effect">
          <div class="flex-shrink-0 w-10 h-10 bg-gradient-to-br from-cyan-400 to-blue-500 rounded-lg flex items-center justify-center shadow-lg">
            <i data-lucide="activity" class="w-5 h-5 text-white"></i>
          </div>
          <div>
            <h3 class="font-semibold text-white mb-1">Real-time Tracking</h3>
            <p class="text-blue-200/80 text-sm">Monitor repair tickets and service requests instantly</p>
          </div>
        </div>
        <div class="feature-card flex items-start gap-4 p-4 rounded-xl glass-effect">
          <div class="flex-shrink-0 w-10 h-10 bg-gradient-to-br from-green-400 to-emerald-500 rounded-lg flex items-center justify-center shadow-lg">
            <i data-lucide="shield-check" class="w-5 h-5 text-white"></i>
          </div>
          <div>
            <h3 class="font-semibold text-white mb-1">Secure Payments</h3>
            <p class="text-blue-200/80 text-sm">Safe digital transactions via GCash and Visa cards </p>
          </div>
        </div>
        <div class="feature-card flex items-start gap-4 p-4 rounded-xl glass-effect">
          <div class="flex-shrink-0 w-10 h-10 bg-gradient-to-br from-amber-400 to-orange-500 rounded-lg flex items-center justify-center shadow-lg">
            <i data-lucide="zap" class="w-5 h-5 text-white"></i>
          </div>
          <div>
            <h3 class="font-semibold text-white mb-1">Direct Communication</h3>
            <p class="text-blue-200/80 text-sm">Connect instantly with Water & Electric Districts</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Footer -->
    <div class="relative z-10 mb-8">
      <div class="flex items-center gap-2 mb-3 text-blue-300/70">
        <i data-lucide="shield" class="w-4 h-4"></i>
        <span class="text-sm font-medium">Trusted by 50,000+ citizens</span>
      </div>
      <div class="text-sm text-blue-200/50">
        &copy; 2025 City Government of Mati. All rights reserved.
      </div>
    </div>
  </div>

  <!-- RIGHT SIDE: LOGIN FORM -->
  <div class="w-full lg:w-1/2 flex flex-col justify-center items-center p-8 py-10 bg-gradient-to-br from-slate-50 via-white to-blue-50/30 lg:bg-white h-screen">
    
    <div class="w-full max-w-md bg-white p-10 rounded-3xl shadow-xl border border-slate-100 lg:border-none lg:shadow-none lg:p-0">
      
      <!-- Mobile Logo (Visible only on small screens) -->
      <div class="lg:hidden flex justify-center mb-6">
        <div class="flex items-center gap-3 group">
          <div class="bg-gradient-to-br from-blue-600 to-blue-700 p-3 rounded-xl shadow-lg transform transition-transform group-hover:scale-105">
            <i data-lucide="hammer" class="text-white w-6 h-6"></i>
          </div>
          <span class="text-2xl font-bold bg-gradient-to-r from-blue-900 to-blue-700 bg-clip-text text-transparent">FixItMati</span>
        </div>
      </div>

      <div class="mb-6">
        <h2 class="text-3xl font-extrabold text-slate-900 mb-2 tracking-tight">Welcome back</h2>
        <p class="text-slate-500 text-sm">Please enter your credentials to access your account.</p>
      </div>

      <!-- Error Message -->
      <div id="errorMessage" class="hidden mb-4 bg-red-50 border border-red-200 text-red-600 px-3 py-2 rounded-lg text-xs flex items-center gap-2 animate-fade-in">
        <i data-lucide="alert-circle" class="w-3.5 h-3.5"></i>
        <span id="errorText"></span>
      </div>

      <!-- Success Message -->
      <div id="successMessage" class="hidden mb-4 bg-green-50 border border-green-200 text-green-600 px-3 py-2 rounded-lg text-xs flex items-center gap-2 animate-fade-in">
        <i data-lucide="check-circle-2" class="w-3.5 h-3.5"></i>
        <span id="successText"></span>
      </div>

      <form id="loginForm" class="space-y-4" novalidate>
        
        <!-- Email Field -->
        <div class="space-y-1.5">
          <label class="text-xs font-bold text-slate-800 ml-1 flex items-center gap-1.5">
            Email Address
            <span class="text-red-500">*</span>
          </label>
          <div class="relative group">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
              <i data-lucide="mail" class="h-4 w-4 text-slate-400 group-focus-within:text-blue-600 transition-colors duration-200"></i>
            </div>
            <input
              type="email"
              id="email"
              name="email"
              class="block w-full pl-10 pr-3 py-2.5 border-2 border-slate-200 rounded-xl text-sm leading-5 bg-white placeholder-slate-400 focus:outline-none focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-200 shadow-sm hover:border-slate-300"
              placeholder="citizen@mati.gov.ph"
            />
          </div>
          <p id="emailError" class="hidden text-xs text-red-600 ml-1 flex items-center gap-1">
            <i data-lucide="alert-circle" class="w-3 h-3"></i>
            <span id="emailErrorText"></span>
          </p>
        </div>

        <!-- Password Field -->
        <div class="space-y-1.5">
          <div class="flex justify-between items-center ml-1">
            <label class="text-xs font-bold text-slate-800 flex items-center gap-1.5">
              Password
              <span class="text-red-500">*</span>
            </label>
            <a href="/forgot-password.php" class="text-[10px] font-bold text-blue-600 hover:text-blue-700 hover:underline transition-colors">Forgot password?</a>
          </div>
          <div class="relative group">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
              <i data-lucide="lock" class="h-4 w-4 text-slate-400 group-focus-within:text-blue-600 transition-colors duration-200"></i>
            </div>
            <input
              type="password"
              id="password"
              name="password"
              class="block w-full pl-10 pr-10 py-2.5 border-2 border-slate-200 rounded-xl text-sm leading-5 bg-white placeholder-slate-400 focus:outline-none focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-200 shadow-sm hover:border-slate-300"
              placeholder="••••••••"
            />
            <button
              type="button"
              id="togglePassword"
              class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-700 focus:outline-none transition-colors"
            >
              <i data-lucide="eye" id="eyeIcon" class="w-4 h-4"></i>
            </button>
          </div>
          <p id="passwordError" class="hidden text-xs text-red-600 ml-1 flex items-center gap-1">
            <i data-lucide="alert-circle" class="w-3 h-3"></i>
            <span id="passwordErrorText"></span>
          </p>
        </div>

        <!-- Remember Me -->
        <div class="flex items-center pt-1">
          <input
            id="remember"
            name="remember"
            type="checkbox"
            class="h-3.5 w-3.5 text-blue-600 focus:ring-blue-500 border-slate-300 rounded cursor-pointer"
          />
          <label for="remember" class="ml-2 block text-xs text-slate-600 cursor-pointer select-none">
            Keep me signed in for 30 days
          </label>
        </div>

        <!-- Submit Button -->
        <button
          type="submit"
          id="submitBtn"
          class="w-full flex items-center justify-center gap-2 py-3 px-6 border border-transparent rounded-xl shadow-lg text-sm font-bold text-white bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 focus:outline-none focus:ring-4 focus:ring-blue-500/50 transition-all duration-200 transform hover:scale-[1.02] active:scale-[0.98]"
        >
          <span id="btnText">Sign In</span>
          <i data-lucide="arrow-right" id="arrowIcon" class="w-4 h-4"></i>
          <div id="spinner" class="hidden w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></div>
        </button>

        <!-- Divider -->
        <div class="relative my-5">
          <div class="absolute inset-0 flex items-center">
            <div class="w-full border-t-2 border-slate-100"></div>
          </div>
          <div class="relative flex justify-center text-xs">
            <span class="px-3 bg-white text-slate-400 font-medium">Or continue with</span>
          </div>
        </div>

        <!-- Social Login -->
        <button
          type="button"
          class="w-full flex items-center justify-center gap-2.5 py-3 px-4 border-2 border-slate-200 rounded-xl shadow-sm bg-white text-xs font-semibold text-slate-700 hover:bg-slate-50 hover:border-slate-300 transition-all duration-200 transform hover:scale-[1.01]"
        >
          <img src="https://www.svgrepo.com/show/475656/google-color.svg" alt="Google" class="h-4 w-4" />
          Sign in with Google
        </button>

      </form>

      <div class="mt-6 text-center">
        <p class="text-xs text-slate-600">
          Don't have an account?
          <a href="/register.php" class="font-bold text-blue-600 hover:text-blue-700 hover:underline transition-colors">
            Register as a New User
          </a>
        </p>
      </div>
      
      <!-- Trust Badges -->
      <div class="mt-6 pt-6 border-t border-slate-100">
        <div class="flex items-center justify-center gap-4 text-[10px] text-slate-400">
          <div class="flex items-center gap-1">
            <i data-lucide="shield-check" class="w-3 h-3"></i>
            <span>SSL Secured</span>
          </div>
          <div class="flex items-center gap-1">
            <i data-lucide="lock" class="w-3 h-3"></i>
            <span>Encrypted</span>
          </div>
          <div class="flex items-center gap-1">
            <i data-lucide="check-circle" class="w-3 h-3"></i>
            <span>Verified</span>
          </div>
        </div>
      </div>
      
    </div>
  </div>

  <script src="/assets/api-client.js?v=6"></script>
  <script>
    console.log('=== Login Page Script Execution Started ===');
    console.log('typeof ApiClient:', typeof ApiClient);
    console.log('typeof window.ApiClient:', typeof window.ApiClient);
    
    // Initialize Lucide icons
    if (typeof lucide !== 'undefined') {
      lucide.createIcons();
    }

    // Get form elements
    const loginForm = document.getElementById('loginForm');
    const submitBtn = document.getElementById('submitBtn');
    const btnText = document.getElementById('btnText');
    const arrowIcon = document.getElementById('arrowIcon');
    const spinner = document.getElementById('spinner');
    const errorMessage = document.getElementById('errorMessage');
    const errorText = document.getElementById('errorText');
    const successMessage = document.getElementById('successMessage');
    const successText = document.getElementById('successText');
    const togglePassword = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('password');
    const eyeIcon = document.getElementById('eyeIcon');
    const emailInput = document.getElementById('email');

    // Field error elements
    const emailError = document.getElementById('emailError');
    const emailErrorText = document.getElementById('emailErrorText');
    const passwordError = document.getElementById('passwordError');
    const passwordErrorText = document.getElementById('passwordErrorText');

    // Show field error
    function showFieldError(field, message) {
      if (field === 'email') {
        emailErrorText.textContent = message;
        emailError.classList.remove('hidden');
        lucide.createIcons();
      } else if (field === 'password') {
        passwordErrorText.textContent = message;
        passwordError.classList.remove('hidden');
        lucide.createIcons();
      }
    }

    // Clear all field errors
    function clearFieldErrors() {
      emailError.classList.add('hidden');
      passwordError.classList.add('hidden');
    }

    // Show error message
    function showError(message) {
      errorText.textContent = message;
      errorMessage.classList.remove('hidden');
      successMessage.classList.add('hidden');
      lucide.createIcons();
    }

    // Show success message
    function showSuccess(message) {
      successText.textContent = message;
      successMessage.classList.remove('hidden');
      errorMessage.classList.add('hidden');
      lucide.createIcons();
    }

    // Hide messages
    function hideMessages() {
      errorMessage.classList.add('hidden');
      successMessage.classList.add('hidden');
    }

    // Set loading state
    function setLoading(loading) {
      if (loading) {
        submitBtn.disabled = true;
        submitBtn.classList.add('opacity-75', 'cursor-not-allowed');
        btnText.classList.add('hidden');
        arrowIcon.classList.add('hidden');
        spinner.classList.remove('hidden');
      } else {
        submitBtn.disabled = false;
        submitBtn.classList.remove('opacity-75', 'cursor-not-allowed');
        btnText.classList.remove('hidden');
        arrowIcon.classList.remove('hidden');
        spinner.classList.add('hidden');
      }
    }
    
    // Verify ApiClient loaded
    if (typeof ApiClient === 'undefined') {
      console.error('ApiClient is undefined!');
      console.error('window object keys:', Object.keys(window).filter(k => k.includes('Api')));
      showError('Failed to load API client. Please refresh the page.');
    } else if (typeof ApiClient.auth === 'undefined') {
      console.error('ApiClient exists but auth is undefined!');
      console.error('ApiClient keys:', Object.keys(ApiClient));
      showError('Failed to load authentication module. Please refresh the page.');
    } else {
      console.log('✓ ApiClient loaded successfully');
      console.log('✓ ApiClient.auth is available');
    }

    // Clear field errors on input
    emailInput.addEventListener('focus', () => {
      emailError.classList.add('hidden');
    });
    
    passwordInput.addEventListener('focus', () => {
      passwordError.classList.add('hidden');
    });

    // Password visibility toggle
    togglePassword.addEventListener('click', () => {
      const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
      passwordInput.setAttribute('type', type);
      
      // Toggle eye icon
      eyeIcon.setAttribute('data-lucide', type === 'password' ? 'eye' : 'eye-off');
      lucide.createIcons();
    });

    // Handle form submission
    loginForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      hideMessages();
      clearFieldErrors();
      setLoading(true);

      const email = emailInput.value.trim();
      const password = passwordInput.value;
      const remember = document.getElementById('remember').checked;

      // Validation
      let hasErrors = false;
      if (!email) {
        showFieldError('email', 'Email is required');
        hasErrors = true;
      } else if (!email.includes('@') || !email.includes('.')) {
        showFieldError('email', 'Please enter a valid email address');
        hasErrors = true;
      }

      if (!password) {
        showFieldError('password', 'Password is required');
        hasErrors = true;
      }

      if (hasErrors) {
        setLoading(false);
        return;
      }

      try {
        // Call login API
        const result = await ApiClient.auth.login(email, password);

        if (result.success) {
          showSuccess('Login successful! Redirecting to dashboard...');
          
          // Store token in localStorage (shared across tabs)
          localStorage.setItem('auth_token', result.data.token);
          localStorage.setItem('user', JSON.stringify(result.data.user));
          
          // Set session duration based on "remember me"
          if (remember) {
            localStorage.setItem('remember_me', 'true');
          } else {
            localStorage.setItem('remember_me', 'false');
          }

          // Redirect based on user role
          setTimeout(() => {
            const role = result.data.user.role;
            if (role === 'admin') {
              window.location.href = '/admin-dashboard.php';
            } else if (role === 'technician') {
              window.location.href = '/technician-dashboard.php';
            } else {
              window.location.href = '/user-dashboard.php';
            }
          }, 1000);
        } else {
          // Handle error responses from server
          if (result.errors && (result.errors.email || result.errors.password)) {
            // Specific field validation errors
            if (result.errors.email) {
              showFieldError('email', result.errors.email);
            }
            if (result.errors.password) {
              showFieldError('password', result.errors.password);
            }
          } else {
            // No field specifics provided -> show top-level generic message
            showError(result.message || result.error || 'Login failed. Please try again.');
          }
          
          setLoading(false);
        }
      } catch (error) {
        console.error('Login error:', error);
        
        // Detailed error handling
        if (error.response) {
          const status = error.response.status;
          
          if (status === 401 || status === 422) {
            // Unauthorized or validation error - invalid credentials
            showFieldError('email', 'Invalid email or password');
            showFieldError('password', 'Invalid email or password');
          } else if (status === 404) {
            // Not found - user account doesn't exist
            showFieldError('email', 'Email account not found. Please check and try again.');
          } else if (status === 429) {
            // Too many login attempts
            showFieldError('email', 'Too many login attempts. Please try again in a few minutes.');
          } else if (status === 500 || status === 502 || status === 503) {
            // Server error
            showFieldError('email', 'Server error. Please try again later.');
          } else if (error.response.errors) {
            // Backend validation errors
            if (error.response.errors.email) {
              showFieldError('email', error.response.errors.email);
            }
            if (error.response.errors.password) {
              showFieldError('password', error.response.errors.password);
            }
          } else {
            // Other server error
            showFieldError('email', 'Login failed. Please try again.');
          }
        } else if (error.message === 'Network Error' || error.code === 'ECONNABORTED') {
          // Network timeout or connection error
          showFieldError('email', 'Connection timeout. Please check your internet and try again.');
        } else if (!navigator.onLine) {
          // No internet connection
          showFieldError('email', 'No internet connection. Please check your network.');
        } else {
          // Unknown error
          showFieldError('email', 'An unexpected error occurred. Please try again.');
        }
        
        setLoading(false);
      }
    });

    // Check if already logged in and auto-redirect
    const authToken = localStorage.getItem('auth_token');
    
    // Auto-redirect if user is already authenticated
    if (authToken) {
      
      // Verify token by calling /auth/me endpoint
      ApiClient.get('/auth/me').then(result => {
        if (result.success) {
          const user = result.data;
          const role = user.role;
          
          // Redirect based on role
          if (role === 'admin') {
            window.location.href = '/admin-dashboard.php';
          } else if (role === 'technician') {
            window.location.href = '/technician-dashboard.php';
          } else {
            window.location.href = '/user-dashboard.php';
          }
        }
      }).catch(() => {
        // Token invalid or expired, clear it
        localStorage.removeItem('auth_token');
        localStorage.removeItem('user');
        localStorage.removeItem('remember_me');
      });
    }
  </script>
</body>
</html>
