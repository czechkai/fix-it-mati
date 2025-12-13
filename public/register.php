<?php
// Registration page for FixItMati
session_start();

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: user-dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Create Account - FixItMati</title>
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
    input:focus, select:focus {
      transform: translateY(-1px);
    }
    .feature-card {
      transition: all 0.3s ease;
    }
    .feature-card:hover {
      transform: translateX(8px);
    }
    .step-indicator {
      position: relative;
    }
    .step-indicator::after {
      content: '';
      position: absolute;
      left: 50%;
      top: 100%;
      width: 2px;
      height: 24px;
      background: #e2e8f0;
      transform: translateX(-50%);
    }
    .step-indicator.active::after {
      background: #3b82f6;
    }
    .step-indicator:last-child::after {
      display: none;
    }
    .form-step {
      display: none;
      animation: fadeIn 0.3s ease-out;
    }
    .form-step.active {
      display: block;
    }
    /* Ensure consistent height for both steps */
    #step1, #step2 {
      min-height: 380px;
    }
  </style>
</head>
<body class="min-h-screen flex bg-white font-sans text-slate-800">
  
  <!-- LEFT SIDE: BRANDING & INFO -->
  <div class="hidden lg:flex w-1/2 bg-gradient-to-br from-blue-950 via-blue-900 to-blue-800 relative overflow-hidden flex-col justify-between p-8 text-white h-screen">
    
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
    <div class="relative z-10 max-w-lg -mt-8">
      <div class="inline-block mb-3">
        <span class="px-4 py-1.5 bg-blue-500/20 backdrop-blur-sm border border-blue-400/30 rounded-full text-xs font-semibold text-blue-200 uppercase tracking-wider">
          Join Mati City Today
        </span>
      </div>
      <h1 class="text-5xl font-extrabold mb-6 leading-tight tracking-tight">
        Become Part of Our 
        <span class="text-transparent bg-clip-text bg-gradient-to-r from-cyan-300 to-blue-300">Smart City Community</span>
      </h1>
      <p class="text-blue-100/90 text-lg leading-relaxed mb-10 font-light">
        Get instant access to all public utility services. Track requests, pay bills securely, and stay connected with your local government.
      </p>
      
      <div class="space-y-5">
        <div class="feature-card flex items-start gap-4 p-4 rounded-xl glass-effect">
          <div class="flex-shrink-0 w-10 h-10 bg-gradient-to-br from-purple-400 to-indigo-500 rounded-lg flex items-center justify-center shadow-lg">
            <i data-lucide="rocket" class="w-5 h-5 text-white"></i>
          </div>
          <div>
            <h3 class="font-semibold text-white mb-1">Quick Setup</h3>
            <p class="text-blue-200/80 text-sm">Account creation takes less than 2 minutes</p>
          </div>
        </div>
        <div class="feature-card flex items-start gap-4 p-4 rounded-xl glass-effect">
          <div class="flex-shrink-0 w-10 h-10 bg-gradient-to-br from-green-400 to-emerald-500 rounded-lg flex items-center justify-center shadow-lg">
            <i data-lucide="shield-check" class="w-5 h-5 text-white"></i>
          </div>
          <div>
            <h3 class="font-semibold text-white mb-1">Secure & Private</h3>
            <p class="text-blue-200/80 text-sm">Your data is encrypted and protected</p>
          </div>
        </div>
        <div class="feature-card flex items-start gap-4 p-4 rounded-xl glass-effect">
          <div class="flex-shrink-0 w-10 h-10 bg-gradient-to-br from-amber-400 to-orange-500 rounded-lg flex items-center justify-center shadow-lg">
            <i data-lucide="users" class="w-5 h-5 text-white"></i>
          </div>
          <div>
            <h3 class="font-semibold text-white mb-1">Community Access</h3>
            <p class="text-blue-200/80 text-sm">Connect with 50,000+ verified citizens</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Footer -->
    <div class="relative z-10">
      <div class="flex items-center gap-2 mb-3 text-blue-300/70">
        <i data-lucide="shield" class="w-4 h-4"></i>
        <span class="text-sm font-medium">Trusted by 50,000+ citizens</span>
      </div>
      <div class="text-sm text-blue-200/50">
        &copy; 2025 City Government of Mati. All rights reserved.
      </div>
    </div>
  </div>

  <!-- RIGHT SIDE: REGISTRATION FORM -->
  <div class="w-full lg:w-1/2 bg-gradient-to-br from-slate-50 via-white to-blue-50/30 lg:bg-white h-screen overflow-y-auto">
    <div class="flex flex-col justify-center items-center min-h-full p-4 sm:p-8">
      <div class="w-full max-w-md bg-white p-6 sm:p-10 rounded-3xl shadow-xl border border-slate-100 lg:border-none lg:shadow-none lg:p-0 my-8">
      
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
        <h2 class="text-3xl font-extrabold text-slate-900 mb-2 tracking-tight">Create Account</h2>
        <p class="text-slate-500 text-sm">Join Mati City's digital platform.</p>
      </div>

      <!-- Step Indicator -->
      <div class="flex items-center justify-center gap-2 mb-6">
        <div class="flex items-center gap-1.5">
          <div id="step1Indicator" class="w-7 h-7 rounded-full bg-blue-600 text-white flex items-center justify-center text-xs font-bold transition-all duration-300">1</div>
          <span id="step1Label" class="text-xs font-semibold text-blue-600 transition-all duration-300">Personal</span>
        </div>
        <div class="w-10 h-0.5 bg-slate-200 transition-all duration-300" id="stepConnector"></div>
        <div class="flex items-center gap-1.5">
          <div id="step2Indicator" class="w-7 h-7 rounded-full bg-slate-200 text-slate-400 flex items-center justify-center text-xs font-bold transition-all duration-300">2</div>
          <span id="step2Label" class="text-xs font-medium text-slate-400 transition-all duration-300">Security</span>
        </div>
      </div>

      <!-- Error Message -->
      <div id="errorMessage" class="hidden mb-4 bg-red-50 border border-red-200 text-red-600 px-3 py-2 rounded-lg text-xs flex items-center gap-2 animate-fade-in">
        <i data-lucide="alert-circle" class="w-3.5 h-3.5 flex-shrink-0"></i>
        <span id="errorText"></span>
      </div>

      <!-- Success Message -->
      <div id="successMessage" class="hidden mb-4 bg-green-50 border border-green-200 text-green-600 px-3 py-2 rounded-lg text-xs flex items-center gap-2 animate-fade-in">
        <i data-lucide="check-circle-2" class="w-3.5 h-3.5 flex-shrink-0"></i>
        <span id="successText"></span>
      </div>

      <form id="registerForm" class="space-y-4">
        
        <!-- STEP 1: Personal Information -->
        <div id="step1" class="form-step active space-y-3.5">
        
        <!-- First Name -->
        <div class="space-y-1.5">
          <label class="text-xs font-bold text-slate-800 ml-1 flex items-center gap-1.5">
            First Name
            <span class="text-red-500">*</span>
          </label>
          <div class="relative group">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
              <i data-lucide="user" class="h-4 w-4 text-slate-400 group-focus-within:text-blue-600 transition-colors duration-200"></i>
            </div>
            <input
              type="text"
              id="firstName"
              name="firstName"
              class="block w-full pl-10 pr-3 py-2.5 border-2 border-slate-200 rounded-xl text-sm leading-5 bg-white placeholder-slate-400 focus:outline-none focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-200 shadow-sm hover:border-slate-300"
              placeholder="Juan"
              required
            />
          </div>
        </div>

        <!-- Last Name -->
        <div class="space-y-1.5">
          <label class="text-xs font-bold text-slate-800 ml-1 flex items-center gap-1.5">
            Last Name
            <span class="text-red-500">*</span>
          </label>
          <div class="relative group">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
              <i data-lucide="user" class="h-4 w-4 text-slate-400 group-focus-within:text-blue-600 transition-colors duration-200"></i>
            </div>
            <input
              type="text"
              id="lastName"
              name="lastName"
              class="block w-full pl-10 pr-3 py-2.5 border-2 border-slate-200 rounded-xl text-sm leading-5 bg-white placeholder-slate-400 focus:outline-none focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-200 shadow-sm hover:border-slate-300"
              placeholder="Dela Cruz"
              required
            />
          </div>
        </div>

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
              placeholder="juan@example.com"
              required
            />
          </div>
        </div>

        <!-- Phone Number -->
        <div class="space-y-1.5">
          <label class="text-xs font-bold text-slate-800 ml-1 flex items-center gap-1.5">
            Phone Number
            <span class="text-red-500">*</span>
          </label>
          <div class="relative group">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
              <i data-lucide="phone" class="h-4 w-4 text-slate-400 group-focus-within:text-blue-600 transition-colors duration-200"></i>
            </div>
            <input
              type="tel"
              id="phone"
              name="phone"
              class="block w-full pl-10 pr-3 py-2.5 border-2 border-slate-200 rounded-xl text-sm leading-5 bg-white placeholder-slate-400 focus:outline-none focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-200 shadow-sm hover:border-slate-300"
              placeholder="+63 912 345 6789"
              required
            />
          </div>
        </div>

        <!-- Address -->
        <div class="space-y-1.5">
          <label class="text-xs font-bold text-slate-800 ml-1 flex items-center gap-1.5">
            Address
            <span class="text-red-500">*</span>
          </label>
          <div class="relative group">
            <div class="absolute top-2.5 left-0 pl-3 flex items-start pointer-events-none">
              <i data-lucide="map-pin" class="h-4 w-4 text-slate-400 group-focus-within:text-blue-600 transition-colors duration-200"></i>
            </div>
            <textarea
              id="address"
              name="address"
              rows="2"
              class="block w-full pl-10 pr-3 py-2.5 border-2 border-slate-200 rounded-xl text-sm leading-5 bg-white placeholder-slate-400 focus:outline-none focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-200 shadow-sm hover:border-slate-300 resize-none"
              placeholder="Barangay, Street, Mati City"
              required
            ></textarea>
          </div>
        </div>

        <!-- Next Button -->
        <button
          type="button"
          id="nextBtn"
          class="w-full flex items-center justify-center gap-2 py-3 px-6 border border-transparent rounded-xl shadow-lg text-sm font-bold text-white bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 focus:outline-none focus:ring-4 focus:ring-blue-500/50 transition-all duration-200 transform hover:scale-[1.02] active:scale-[0.98]"
        >
          <span>Continue</span>
          <i data-lucide="arrow-right" class="w-4 h-4"></i>
        </button>

        </div>

        <!-- STEP 2: Security & Account Type -->
        <div id="step2" class="form-step space-y-3.5">

        <!-- Password Field -->
        <div class="space-y-1.5">
          <label class="text-xs font-bold text-slate-800 ml-1 flex items-center gap-1.5">
            Password
            <span class="text-red-500">*</span>
          </label>
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
              required
            />
            <button
              type="button"
              id="togglePassword"
              class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-700 focus:outline-none transition-colors"
            >
              <i data-lucide="eye" id="eyeIcon" class="w-4 h-4"></i>
            </button>
          </div>
          <p class="text-[10px] text-slate-500 ml-1">At least 8 characters</p>
        </div>

        <!-- Confirm Password -->
        <div class="space-y-1.5">
          <label class="text-xs font-bold text-slate-800 ml-1 flex items-center gap-1.5">
            Confirm Password
            <span class="text-red-500">*</span>
          </label>
          <div class="relative group">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
              <i data-lucide="lock" class="h-4 w-4 text-slate-400 group-focus-within:text-blue-600 transition-colors duration-200"></i>
            </div>
            <input
              type="password"
              id="confirmPassword"
              name="confirmPassword"
              class="block w-full pl-10 pr-10 py-2.5 border-2 border-slate-200 rounded-xl text-sm leading-5 bg-white placeholder-slate-400 focus:outline-none focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-200 shadow-sm hover:border-slate-300"
              placeholder="••••••••"
              required
            />
            <button
              type="button"
              id="toggleConfirmPassword"
              class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-700 focus:outline-none transition-colors"
            >
              <i data-lucide="eye" id="eyeIconConfirm" class="w-4 h-4"></i>
            </button>
          </div>
        </div>

        <!-- Account Type -->
        <div class="space-y-1.5">
          <label class="text-xs font-bold text-slate-800 ml-1 flex items-center gap-1.5">
            I am registering as
            <span class="text-red-500">*</span>
          </label>
          <div class="relative group">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
              <i data-lucide="briefcase" class="h-4 w-4 text-slate-400 group-focus-within:text-blue-600 transition-colors duration-200"></i>
            </div>
            <select
              id="role"
              name="role"
              class="block w-full pl-10 pr-3 py-2.5 border-2 border-slate-200 rounded-xl text-sm leading-5 bg-white text-slate-700 focus:outline-none focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-200 shadow-sm hover:border-slate-300 cursor-pointer"
              required
            >
              <option value="customer">Citizen / Resident</option>
              <option value="technician">Technician / Service Provider</option>
            </select>
          </div>
        </div>

        <!-- Terms and Conditions -->
        <div class="flex items-start pt-1">
          <input
            id="terms"
            name="terms"
            type="checkbox"
            class="h-3.5 w-3.5 text-blue-600 focus:ring-blue-500 border-slate-300 rounded cursor-pointer mt-0.5"
            required
          />
          <label for="terms" class="ml-2 block text-xs text-slate-600 cursor-pointer select-none">
            I agree to the <a href="#" class="text-blue-600 hover:text-blue-700 font-semibold hover:underline">Terms</a> and <a href="#" class="text-blue-600 hover:text-blue-700 font-semibold hover:underline">Privacy Policy</a>
          </label>
        </div>

        <!-- Buttons Row -->
        <div class="flex gap-2.5">
          <button
            type="button"
            id="backBtn"
            class="flex-1 flex items-center justify-center gap-1.5 py-3 px-3 border-2 border-slate-200 rounded-xl shadow-sm text-sm font-bold text-slate-700 bg-white hover:bg-slate-50 hover:border-slate-300 focus:outline-none focus:ring-4 focus:ring-slate-200 transition-all duration-200"
          >
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            <span>Back</span>
          </button>
          <button
            type="submit"
            id="submitBtn"
            class="flex-[2] flex items-center justify-center gap-1.5 py-3 px-3 border border-transparent rounded-xl shadow-lg text-sm font-bold text-white bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 focus:outline-none focus:ring-4 focus:ring-blue-500/50 transition-all duration-200 transform hover:scale-[1.02] active:scale-[0.98]"
          >
            <span id="btnText">Create Account</span>
            <i data-lucide="user-plus" id="userPlusIcon" class="w-4 h-4"></i>
            <div id="spinner" class="hidden w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></div>
          </button>
        </div>

        </div>

        <!-- Divider -->
        <div class="relative my-5">
          <div class="absolute inset-0 flex items-center">
            <div class="w-full border-t-2 border-slate-100"></div>
          </div>
          <div class="relative flex justify-center text-xs">
            <span class="px-3 bg-white text-slate-400 font-medium">Or sign up with</span>
          </div>
        </div>

        <!-- Social Registration -->
        <button
          type="button"
          class="w-full flex items-center justify-center gap-2.5 py-3 px-4 border-2 border-slate-200 rounded-xl shadow-sm bg-white text-xs font-semibold text-slate-700 hover:bg-slate-50 hover:border-slate-300 transition-all duration-200 transform hover:scale-[1.01]"
        >
          <img src="https://www.svgrepo.com/show/475656/google-color.svg" alt="Google" class="h-4 w-4" />
          Sign up with Google
        </button>

      </form>

      <!-- Trust Badges -->
      <div class="mt-6 pt-6 border-t border-slate-100">
        <div class="flex items-center justify-center gap-4 text-[10px] text-slate-400">
          <div class="flex flex-col items-center gap-1">
            <i data-lucide="shield-check" class="w-4 h-4"></i>
            <span>SSL Secured</span>
          </div>
          <div class="flex flex-col items-center gap-1">
            <i data-lucide="lock" class="w-4 h-4"></i>
            <span>Encrypted</span>
          </div>
          <div class="flex flex-col items-center gap-1">
            <i data-lucide="check-circle" class="w-4 h-4"></i>
            <span>Verified</span>
          </div>
        </div>
      </div>

      <div class="mt-4 text-center">
        <p class="text-xs text-slate-600">
          Already have an account?
          <a href="login.php" class="font-bold text-blue-600 hover:text-blue-700 hover:underline transition-colors">
            Sign In
          </a>
        </p>
      </div>
      
      </div>
    </div>
  </div>

  <script src="/assets/api-client.js"></script>
  <script>
    // Initialize Lucide icons
    lucide.createIcons();

    // Get form elements
    const registerForm = document.getElementById('registerForm');
    const submitBtn = document.getElementById('submitBtn');
    const btnText = document.getElementById('btnText');
    const userPlusIcon = document.getElementById('userPlusIcon');
    const spinner = document.getElementById('spinner');
    const errorMessage = document.getElementById('errorMessage');
    const errorText = document.getElementById('errorText');
    const successMessage = document.getElementById('successMessage');
    const successText = document.getElementById('successText');
    const togglePassword = document.getElementById('togglePassword');
    const toggleConfirmPassword = document.getElementById('toggleConfirmPassword');
    const passwordInput = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('confirmPassword');
    const eyeIcon = document.getElementById('eyeIcon');
    const eyeIconConfirm = document.getElementById('eyeIconConfirm');

    // Step navigation
    const step1 = document.getElementById('step1');
    const step2 = document.getElementById('step2');
    const step1Indicator = document.getElementById('step1Indicator');
    const step2Indicator = document.getElementById('step2Indicator');
    const step1Label = document.getElementById('step1Label');
    const step2Label = document.getElementById('step2Label');
    const stepConnector = document.getElementById('stepConnector');
    const nextBtn = document.getElementById('nextBtn');
    const backBtn = document.getElementById('backBtn');

    let currentStep = 1;

    // Navigate to step 2
    nextBtn.addEventListener('click', () => {
      // Validate step 1 fields
      const firstName = document.getElementById('firstName').value;
      const lastName = document.getElementById('lastName').value;
      const email = document.getElementById('email').value;
      const phone = document.getElementById('phone').value;
      const address = document.getElementById('address').value;

      if (!firstName || !lastName || !email || !phone || !address) {
        showError('Please fill in all required fields before continuing.');
        return;
      }

      // Email validation
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (!emailRegex.test(email)) {
        showError('Please enter a valid email address.');
        return;
      }

      hideMessages();
      currentStep = 2;
      step1.classList.remove('active');
      step2.classList.add('active');

      // Update indicators
      step1Indicator.classList.remove('bg-blue-600', 'text-white');
      step1Indicator.classList.add('bg-green-500', 'text-white');
      step1Indicator.innerHTML = '<i data-lucide="check" class="w-4 h-4"></i>';
      step1Label.classList.remove('text-blue-600');
      step1Label.classList.add('text-green-600');

      step2Indicator.classList.remove('bg-slate-200', 'text-slate-400');
      step2Indicator.classList.add('bg-blue-600', 'text-white');
      step2Label.classList.remove('text-slate-400');
      step2Label.classList.add('text-blue-600', 'font-semibold');

      stepConnector.classList.remove('bg-slate-200');
      stepConnector.classList.add('bg-green-500');

      lucide.createIcons();

      // No scroll needed - form fits in viewport
    });

    // Navigate back to step 1
    backBtn.addEventListener('click', () => {
      hideMessages();
      currentStep = 1;
      step2.classList.remove('active');
      step1.classList.add('active');

      // Update indicators
      step1Indicator.classList.remove('bg-green-500');
      step1Indicator.classList.add('bg-blue-600', 'text-white');
      step1Indicator.textContent = '1';
      step1Label.classList.remove('text-green-600');
      step1Label.classList.add('text-blue-600');

      step2Indicator.classList.remove('bg-blue-600', 'text-white');
      step2Indicator.classList.add('bg-slate-200', 'text-slate-400');
      step2Label.classList.remove('text-blue-600', 'font-semibold');
      step2Label.classList.add('text-slate-400');

      stepConnector.classList.remove('bg-green-500');
      stepConnector.classList.add('bg-slate-200');

      lucide.createIcons();

      // No scroll needed - form fits in viewport
    });

    // Password visibility toggles
    togglePassword.addEventListener('click', () => {
      const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
      passwordInput.setAttribute('type', type);
      eyeIcon.setAttribute('data-lucide', type === 'password' ? 'eye' : 'eye-off');
      lucide.createIcons();
    });

    toggleConfirmPassword.addEventListener('click', () => {
      const type = confirmPasswordInput.getAttribute('type') === 'password' ? 'text' : 'password';
      confirmPasswordInput.setAttribute('type', type);
      eyeIconConfirm.setAttribute('data-lucide', type === 'password' ? 'eye' : 'eye-off');
      lucide.createIcons();
    });

    // Show error message
    function showError(message) {
      errorText.textContent = message;
      errorMessage.classList.remove('hidden');
      successMessage.classList.add('hidden');
      lucide.createIcons();
      errorMessage.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    // Show success message
    function showSuccess(message) {
      successText.textContent = message;
      successMessage.classList.remove('hidden');
      errorMessage.classList.add('hidden');
      lucide.createIcons();
      successMessage.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
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
        userPlusIcon.classList.add('hidden');
        spinner.classList.remove('hidden');
      } else {
        submitBtn.disabled = false;
        submitBtn.classList.remove('opacity-75', 'cursor-not-allowed');
        btnText.classList.remove('hidden');
        userPlusIcon.classList.remove('hidden');
        spinner.classList.add('hidden');
      }
    }

    // Validate form
    function validateForm() {
      const password = passwordInput.value;
      const confirmPassword = confirmPasswordInput.value;

      // Check password length
      if (password.length < 8) {
        showError('Password must be at least 8 characters long.');
        return false;
      }

      // Check passwords match
      if (password !== confirmPassword) {
        showError('Passwords do not match. Please try again.');
        return false;
      }

      // Check terms acceptance
      const terms = document.getElementById('terms');
      if (!terms.checked) {
        showError('You must agree to the Terms of Service and Privacy Policy.');
        return false;
      }

      return true;
    }

    // Handle form submission
    registerForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      hideMessages();

      // Validate form
      if (!validateForm()) {
        return;
      }

      setLoading(true);

      const formData = {
        first_name: document.getElementById('firstName').value,
        last_name: document.getElementById('lastName').value,
        email: document.getElementById('email').value,
        phone: document.getElementById('phone').value,
        address: document.getElementById('address').value,
        password: document.getElementById('password').value,
        password_confirmation: document.getElementById('confirmPassword').value,
        role: document.getElementById('role').value
      };

      try {
        // Call registration API
        const result = await ApiClient.auth.register(formData);

        if (result.success) {
          showSuccess('Account created successfully! Redirecting to login...');
          
          // Redirect to login after 2 seconds
          setTimeout(() => {
            window.location.href = 'login.php';
          }, 2000);
        } else {
          showError(result.message || 'Registration failed. Please try again.');
          setLoading(false);
        }
      } catch (error) {
        console.error('Registration error:', error);
        showError(error.message || 'An error occurred. Please try again later.');
        setLoading(false);
      }
    });

    // Check if already logged in
    const token = sessionStorage.getItem('auth_token');
    if (token) {
      // User is already logged in, redirect to dashboard
      window.location.href = 'user-dashboard.php';
    }

    // Phone number formatting
    const phoneInput = document.getElementById('phone');
    phoneInput.addEventListener('input', (e) => {
      let value = e.target.value.replace(/\D/g, '');
      if (value.startsWith('63')) {
        value = value.substring(2);
      }
      if (value.length > 10) {
        value = value.substring(0, 10);
      }
      if (value.length > 0) {
        e.target.value = '+63 ' + value.match(/.{1,3}/g)?.join(' ') || value;
      }
    });
  </script>
</body>
</html>
