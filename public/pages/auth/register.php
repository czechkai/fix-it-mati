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
  <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
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
    /* Smooth transition for error messages */
    .error-msg {
      transition: all 0.2s ease-in-out;
      max-height: 0;
      opacity: 0;
      overflow: hidden;
    }
    .error-msg.show {
      max-height: 24px; /* Adjust based on font size */
      opacity: 1;
      margin-top: 4px;
    }
  </style>
</head>
<body class="min-h-screen flex bg-white font-sans text-slate-800">
  
  <div class="hidden lg:flex w-1/2 bg-gradient-to-br from-blue-950 via-blue-900 to-blue-800 relative overflow-hidden flex-col justify-between p-12 text-white h-screen">
    
    <div class="absolute top-0 left-0 w-full h-full overflow-hidden z-0 pointer-events-none">
      <div class="absolute -top-24 -left-24 w-96 h-96 rounded-full bg-cyan-400/20 blur-3xl animate-pulse-glow"></div>
      <div class="absolute top-1/3 right-0 w-80 h-80 rounded-full bg-blue-500/10 blur-3xl animate-float"></div>
      <div class="absolute bottom-0 right-0 w-[500px] h-[500px] rounded-full bg-indigo-400/15 blur-3xl"></div>
      <div class="absolute inset-0 opacity-[0.02]" style="background-image: radial-gradient(circle, white 1px, transparent 1px); background-size: 30px 30px;"></div>
    </div>

    <div class="relative z-10 flex items-center gap-3 group">
      <div class="glass-effect p-3 rounded-2xl shadow-lg transform transition-all duration-300 group-hover:scale-105 group-hover:shadow-2xl">
        <i data-lucide="hammer" class="w-7 h-7"></i>
      </div>
      <span class="text-2xl font-bold tracking-tight">FixItMati</span>
    </div>

    <div class="relative z-10 max-w-lg -mt-8">
      <div class="inline-block mb-4">
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
      
      <div class="space-y-6">
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
      </div>
    </div>

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

  <div class="w-full lg:w-1/2 bg-white h-screen overflow-y-auto">
    <div class="flex flex-col justify-center items-center min-h-full p-6 lg:p-12">
      <div class="w-full max-w-lg bg-white p-6 sm:p-10 rounded-3xl lg:p-0 my-4">
      
      <div class="lg:hidden flex justify-center mb-8">
        <div class="flex items-center gap-3 group">
          <div class="bg-gradient-to-br from-blue-600 to-blue-700 p-3 rounded-xl shadow-lg">
            <i data-lucide="hammer" class="text-white w-6 h-6"></i>
          </div>
          <span class="text-2xl font-bold text-slate-800">FixItMati</span>
        </div>
      </div>

      <div class="mb-10 text-center lg:text-left">
        <h2 class="text-3xl font-bold text-slate-900 mb-3 tracking-tight">Create Account</h2>
        <p class="text-slate-500 text-base">Join Mati City's digital platform.</p>
      </div>

      <div class="flex items-center justify-center lg:justify-start gap-4 mb-10">
        <div class="flex items-center gap-2">
          <div id="step1Indicator" class="w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center text-sm font-bold shadow-sm transition-all duration-300">1</div>
          <span id="step1Label" class="text-sm font-semibold text-blue-600 transition-all duration-300">Personal</span>
        </div>
        <div class="w-12 h-0.5 bg-slate-100 transition-all duration-300" id="stepConnector1"></div>
        <div class="flex items-center gap-2">
          <div id="step2Indicator" class="w-8 h-8 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center text-sm font-bold transition-all duration-300">2</div>
          <span id="step2Label" class="text-sm font-medium text-slate-400 transition-all duration-300">Security</span>
        </div>
        <div class="w-12 h-0.5 bg-slate-100 transition-all duration-300" id="stepConnector2"></div>
        <div class="flex items-center gap-2">
          <div id="step3Indicator" class="w-8 h-8 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center text-sm font-bold transition-all duration-300">3</div>
          <span id="step3Label" class="text-sm font-medium text-slate-400 transition-all duration-300">Verify</span>
        </div>
      </div>

      <div id="errorMessage" class="hidden mb-6 bg-red-50 border border-red-100 text-red-600 px-4 py-3 rounded-xl text-sm flex items-center gap-3 animate-fade-in shadow-sm">
        <i data-lucide="alert-circle" class="w-4 h-4 flex-shrink-0"></i>
        <span id="errorText"></span>
      </div>
      <div id="successMessage" class="hidden mb-6 bg-green-50 border border-green-100 text-green-600 px-4 py-3 rounded-xl text-sm flex items-center gap-3 animate-fade-in shadow-sm">
        <i data-lucide="check-circle-2" class="w-4 h-4 flex-shrink-0"></i>
        <span id="successText"></span>
      </div>

      <form id="registerForm" class="block" novalidate>
        
        <div id="step1" class="form-step active">
            <div class="space-y-6">
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-slate-700 ml-1 flex items-center gap-1">First Name <span class="text-red-500">*</span></label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                                <i data-lucide="user" class="h-5 w-5 text-slate-400 group-focus-within:text-blue-600 transition-colors"></i>
                            </div>
                            <input type="text" id="firstName" name="firstName" class="block w-full pl-11 pr-4 py-3 border-2 border-slate-100 rounded-xl text-sm bg-white placeholder-slate-400 focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all duration-200" placeholder="Juan" />
                        </div>
                        <p id="firstNameError" class="error-msg text-xs text-red-600 ml-1"></p>
                    </div>

                    <div class="space-y-2">
                        <label class="text-sm font-medium text-slate-700 ml-1 flex items-center gap-1">Last Name <span class="text-red-500">*</span></label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                                <i data-lucide="user" class="h-5 w-5 text-slate-400 group-focus-within:text-blue-600 transition-colors"></i>
                            </div>
                            <input type="text" id="lastName" name="lastName" class="block w-full pl-11 pr-4 py-3 border-2 border-slate-100 rounded-xl text-sm bg-white placeholder-slate-400 focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all duration-200" placeholder="Dela Cruz" />
                        </div>
                        <p id="lastNameError" class="error-msg text-xs text-red-600 ml-1"></p>
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="text-sm font-medium text-slate-700 ml-1 flex items-center gap-1">Phone Number <span class="text-red-500">*</span></label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                            <i data-lucide="phone" class="h-5 w-5 text-slate-400 group-focus-within:text-blue-600 transition-colors"></i>
                        </div>
                        <input type="tel" id="phone" name="phone" maxlength="16" class="block w-full pl-11 pr-4 py-3 border-2 border-slate-100 rounded-xl text-sm bg-white placeholder-slate-400 focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all duration-200" placeholder="+63 912 345 6789" />
                    </div>
                    <p id="phoneError" class="error-msg text-xs text-red-600 ml-1"></p>
                </div>

                <div class="space-y-2">
                    <label class="text-sm font-medium text-slate-700 ml-1 flex items-center gap-1">Street Address <span class="text-red-500">*</span></label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                            <i data-lucide="map-pin" class="h-5 w-5 text-slate-400 group-focus-within:text-blue-600 transition-colors"></i>
                        </div>
                        <input type="text" id="street" name="street" class="block w-full pl-11 pr-4 py-3 border-2 border-slate-100 rounded-xl text-sm bg-white placeholder-slate-400 focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all duration-200" placeholder="e.g., 123 Main Street" />
                    </div>
                    <p id="streetError" class="error-msg text-xs text-red-600 ml-1"></p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-slate-700 ml-1 flex items-center gap-1">Barangay <span class="text-red-500">*</span></label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                                <i data-lucide="map" class="h-5 w-5 text-slate-400 group-focus-within:text-blue-600 transition-colors"></i>
                            </div>
                            <select id="barangay" name="barangay" class="block w-full pl-11 pr-4 py-3 border-2 border-slate-100 rounded-xl text-sm bg-white text-slate-700 focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all duration-200 cursor-pointer appearance-none">
                                <option value="">Select Barangay</option>
                                <option value="Badas">Badas</option>
                                <option value="Bobon">Bobon</option>
                                <option value="Buso">Buso</option>
                                <option value="Cabuaya">Cabuaya</option>
                                <option value="Central (Pob.)">Central (Pob.)</option>
                                <option value="Culian">Culian</option>
                                <option value="Dahican">Dahican</option>
                                <option value="Danao">Danao</option>
                                <option value="Dawan">Dawan</option>
                                <option value="Don Enrique Lopez">Don Enrique Lopez</option>
                                <option value="Don Martin Marundan">Don Martin Marundan</option>
                                <option value="Don Salvador Lopez, Sr.">Don Salvador Lopez, Sr.</option>
                                <option value="Langka">Langka</option>
                                <option value="Lawigan">Lawigan</option>
                                <option value="Libudon">Libudon</option>
                                <option value="Luban">Luban</option>
                                <option value="Macambol">Macambol</option>
                                <option value="Mamali">Mamali</option>
                                <option value="Matiao">Matiao</option>
                                <option value="Mayo">Mayo</option>
                                <option value="Sainz">Sainz</option>
                                <option value="Sanghay">Sanghay</option>
                                <option value="Tagabakid">Tagabakid</option>
                                <option value="Tagbinonga">Tagbinonga</option>
                                <option value="Taguibo">Taguibo</option>
                                <option value="Tamisan">Tamisan</option>
                            </select>
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                <i data-lucide="chevron-down" class="h-4 w-4 text-slate-400"></i>
                            </div>
                        </div>
                        <p id="barangayError" class="error-msg text-xs text-red-600 ml-1"></p>
                    </div>

                    <div class="space-y-2">
                        <label class="text-sm font-medium text-slate-700 ml-1 flex items-center gap-1">City</label>
                        <div class="relative group">
                             <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                                <i data-lucide="building-2" class="h-5 w-5 text-slate-400"></i>
                            </div>
                            <input type="text" id="city" name="city" class="block w-full pl-11 pr-4 py-3 border-2 border-slate-100 rounded-xl text-sm bg-slate-50 text-slate-500 font-medium cursor-not-allowed" value="City of Mati" disabled />
                        </div>
                    </div>
                </div>

                <div class="pt-4">
                    <button type="button" id="nextBtn" class="w-full flex items-center justify-center gap-2 py-3.5 px-6 rounded-xl shadow-lg shadow-blue-500/20 text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-500/30 transition-all duration-200 transform hover:-translate-y-0.5">
                        <span>Continue</span>
                        <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </button>
                </div>
            </div>
        </div>

        <div id="step2" class="form-step">
            <div class="space-y-6">
                
                <div class="space-y-2">
                    <label class="text-sm font-medium text-slate-700 ml-1 flex items-center gap-1">Password <span class="text-red-500">*</span></label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                            <i data-lucide="lock" class="h-5 w-5 text-slate-400 group-focus-within:text-blue-600 transition-colors"></i>
                        </div>
                        <input type="password" id="password" name="password" class="block w-full pl-11 pr-10 py-3 border-2 border-slate-100 rounded-xl text-sm bg-white placeholder-slate-400 focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all duration-200" placeholder="••••••••" />
                        <button type="button" id="togglePassword" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-700 transition-colors">
                            <i data-lucide="eye" id="eyeIcon" class="w-5 h-5"></i>
                        </button>
                    </div>
                    <p id="passwordError" class="error-msg text-xs text-red-600 ml-1"></p>
                </div>

                <div class="space-y-2">
                    <label class="text-sm font-medium text-slate-700 ml-1 flex items-center gap-1">Confirm Password <span class="text-red-500">*</span></label>
                    <div class="relative group">
                         <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                            <i data-lucide="lock" class="h-5 w-5 text-slate-400 group-focus-within:text-blue-600 transition-colors"></i>
                        </div>
                        <input type="password" id="confirmPassword" name="confirmPassword" class="block w-full pl-11 pr-10 py-3 border-2 border-slate-100 rounded-xl text-sm bg-white placeholder-slate-400 focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all duration-200" placeholder="••••••••" />
                        <button type="button" id="toggleConfirmPassword" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-700 transition-colors">
                            <i data-lucide="eye" id="eyeIconConfirm" class="w-5 h-5"></i>
                        </button>
                    </div>
                     <p id="matchStatus" class="hidden text-xs text-red-600 ml-1 mt-1 flex items-center gap-1">
                        <i data-lucide="alert-circle" id="matchIcon" class="w-3 h-3"></i>
                        <span id="matchText">Passwords do not match</span>
                    </p>
                    <p id="confirmPasswordError" class="error-msg text-xs text-red-600 ml-1"></p>
                </div>

                <div class="space-y-2">
                    <label class="text-sm font-medium text-slate-700 ml-1 flex items-center gap-1">Email Address <span class="text-red-500">*</span></label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                            <i data-lucide="mail" class="h-5 w-5 text-slate-400 group-focus-within:text-blue-600 transition-colors"></i>
                        </div>
                        <input type="email" id="email" name="email" class="block w-full pl-11 pr-3 py-3 border-2 border-slate-100 rounded-xl text-sm bg-white placeholder-slate-400 focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all duration-200" placeholder="juan@example.com" />
                    </div>
                    <p class="text-xs text-slate-500 ml-1">We'll send a verification code to this email</p>
                    <p id="emailError" class="error-msg text-xs text-red-600 ml-1"></p>
                </div>

                <div class="flex items-start pt-2">
                    <div class="flex items-center h-5">
                        <input id="terms" name="terms" type="checkbox" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-slate-300 rounded cursor-pointer" />
                    </div>
                    <label for="terms" class="ml-3 block text-sm text-slate-600 cursor-pointer select-none">
                        I agree to the <button type="button" id="termsBtn" class="text-blue-600 hover:text-blue-800 font-semibold hover:underline">Terms</button> and <button type="button" id="privacyBtn" class="text-blue-600 hover:text-blue-800 font-semibold hover:underline">Privacy Policy</button>
                    </label>
                </div>

                <div class="flex gap-3 pt-4">
                    <button type="button" id="backBtn" class="flex-1 flex items-center justify-center gap-2 py-3.5 px-6 border-2 border-slate-100 rounded-xl text-sm font-semibold text-slate-600 bg-white hover:bg-slate-50 hover:border-slate-200 transition-all">
                        <i data-lucide="arrow-left" class="w-4 h-4"></i>
                        <span>Back</span>
                    </button>
                    <button type="submit" id="submitBtn" class="flex-[2] flex items-center justify-center gap-2 py-3.5 px-6 rounded-xl shadow-lg shadow-blue-500/20 text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-500/30 transition-all transform hover:-translate-y-0.5">
                        <span id="btnText">Create Account</span>
                        <i data-lucide="user-plus" id="userPlusIcon" class="w-4 h-4"></i>
                        <div id="spinner" class="hidden w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></div>
                    </button>
                </div>
            </div>
        </div>

        <div id="step3" class="form-step">
           <div class="space-y-6">
               <div class="space-y-3 text-center">
                    <div class="w-16 h-16 bg-blue-50 rounded-full flex items-center justify-center mx-auto mb-2">
                        <i data-lucide="mail-check" class="w-8 h-8 text-blue-600"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900">Check your email</h3>
                    <p class="text-sm text-slate-500">We sent a verification code to <br><span id="displayEmail" class="font-semibold text-blue-600"></span></p>
               </div>

                <div class="space-y-2">
                    <label class="text-sm font-medium text-slate-700 ml-1 block text-center">Verification Code</label>
                    <div class="relative max-w-xs mx-auto">
                        <input type="text" id="verificationCode" name="verificationCode" maxlength="11" class="block w-full py-4 px-4 border-2 border-slate-100 rounded-xl text-2xl tracking-[0.5em] font-mono text-center text-slate-800 focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all" placeholder="000000" />
                    </div>
                    <p id="verificationCodeError" class="error-msg text-xs text-red-600 text-center"></p>
                </div>

                <div class="flex items-center justify-center gap-2">
                    <span class="text-sm text-slate-500">Didn't receive code?</span>
                    <button type="button" id="resendCodeBtn" class="text-sm font-semibold text-blue-600 hover:text-blue-800 disabled:text-slate-400 transition-colors" disabled>
                        <span id="resendText">Resend Code</span>
                        <span id="resendTimer" class="text-slate-500 ml-1"></span>
                    </button>
                </div>

                <div class="flex gap-3 pt-4">
                    <button type="button" id="backBtn3" class="flex-1 flex items-center justify-center gap-2 py-3.5 px-6 border-2 border-slate-100 rounded-xl text-sm font-semibold text-slate-600 bg-white hover:bg-slate-50 hover:border-slate-200 transition-all">
                        <i data-lucide="arrow-left" class="w-4 h-4"></i>
                        <span>Back</span>
                    </button>
                    <button type="submit" id="verifyBtn" class="flex-[2] flex items-center justify-center gap-2 py-3.5 px-6 rounded-xl shadow-lg shadow-green-500/20 text-sm font-bold text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-4 focus:ring-green-500/30 transition-all transform hover:-translate-y-0.5">
                        <span id="verifyBtnText">Verify & Create</span>
                        <i data-lucide="check-circle" id="verifyIcon" class="w-4 h-4"></i>
                    </button>
                </div>
           </div>
        </div>

      </form>
      
      <div class="mt-8 pt-6 border-t border-slate-100 text-center">
        <p class="text-sm text-slate-500">
          Already have an account? 
          <a href="/" class="font-semibold text-blue-600 hover:text-blue-700 hover:underline">Sign in</a>
        </p>
      </div>

    </div>
  </div>
  
  <div id="termsModal" class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl w-full max-w-2xl max-h-[80vh] flex flex-col shadow-2xl">
      <div class="p-6 border-b border-slate-100 flex justify-between items-center">
        <h3 class="text-xl font-bold text-slate-800" id="modalTitle">Terms of Service</h3>
        <button id="closeModal" class="text-slate-400 hover:text-slate-600 transition-colors">
          <i data-lucide="x" class="w-6 h-6"></i>
        </button>
      </div>
      <div class="p-6 overflow-y-auto custom-scrollbar">
        <div id="termsContent" class="space-y-4 text-sm text-slate-600">
          <p><strong>Last updated: January 2025</strong></p>
          <p>Please read these Terms of Service carefully before using the FixItMati platform.</p>
          <p>1. By accessing the website, you are agreeing to be bound by these terms of service...</p>
        </div>
        <div id="privacyContent" class="hidden space-y-4 text-sm text-slate-600">
          <p><strong>Last updated: January 2025</strong></p>
          <p>Your privacy is important to us. It is FixItMati's policy to respect your privacy...</p>
        </div>
      </div>
      <div class="p-6 border-t border-slate-100 flex justify-end">
        <button id="closeModalBtn" class="px-6 py-2 bg-blue-600 text-white rounded-xl hover:bg-blue-700 font-medium transition-colors">Close</button>
      </div>
    </div>
  </div>

  <script>
    // Initialize Lucide icons
    lucide.createIcons();

    // API Configuration
    const API_BASE_URL = 'http://localhost/fixit_mati/backend/api'; 
    
    // --- 1. SMART PHONE FORMATTING LOGIC ---
    const phoneInput = document.getElementById('phone');

    phoneInput.addEventListener('focus', function(e) {
        if (this.value === '') {
            this.value = '+63 ';
        }
    });

    phoneInput.addEventListener('blur', function(e) {
        if (this.value === '+63 ') {
            this.value = '';
        }
    });

    phoneInput.addEventListener('input', function(e) {
        // Remove all non-numeric characters
        let numbers = this.value.replace(/\D/g, '');
        
        // Handle if user pastes or types "09" or "639"
        if (numbers.startsWith('63')) {
            numbers = numbers.substring(2);
        } else if (numbers.startsWith('0')) {
            numbers = numbers.substring(1);
        }
        
        // Limit to 10 digits (mobile number length without +63)
        numbers = numbers.substring(0, 10);
        
        // Format the output
        let formatted = '+63 ';
        if (numbers.length > 0) {
            formatted += numbers.substring(0, 3);
        }
        if (numbers.length >= 4) {
            formatted += ' ' + numbers.substring(3, 6);
        }
        if (numbers.length >= 7) {
            formatted += ' ' + numbers.substring(6, 10);
        }
        
        this.value = formatted;
        
        // Validation check
        if (numbers.length === 10) {
            clearFieldError('phone');
        }
    });

    // --- 2. REAL-TIME PASSWORD DETECTION LOGIC ---
    const passwordInput = document.getElementById('password');
    const confirmInput = document.getElementById('confirmPassword');
    const matchStatus = document.getElementById('matchStatus');
    const matchIcon = document.getElementById('matchIcon');
    const matchText = document.getElementById('matchText');

    passwordInput.addEventListener('input', function() {
        const val = this.value;
        const requirements = [];
        
        if (val.length < 8) requirements.push("8+ chars");
        if (!/\d/.test(val)) requirements.push("one number");
        if (!/[!@#$%^&*]/.test(val)) requirements.push("one symbol");

        if (requirements.length > 0 && val.length > 0) {
            showFieldError('password', 'Missing: ' + requirements.join(', '));
        } else {
            clearFieldError('password');
        }
        
        // Also check match if confirm field is filled
        if (confirmInput.value) checkMatch();
    });

    confirmInput.addEventListener('input', checkMatch);

    function checkMatch() {
        if (confirmInput.value === '') {
            matchStatus.classList.add('hidden');
            return;
        }
        
        matchStatus.classList.remove('hidden');
        if (passwordInput.value === confirmInput.value) {
            matchIcon.setAttribute('data-lucide', 'check-circle');
            matchIcon.classList.replace('text-red-500', 'text-green-500');
            matchStatus.classList.replace('text-red-600', 'text-green-600');
            matchText.textContent = 'Passwords match';
        } else {
            matchIcon.setAttribute('data-lucide', 'alert-circle');
            matchIcon.classList.replace('text-green-500', 'text-red-500');
            matchStatus.classList.replace('text-green-600', 'text-red-600');
            matchText.textContent = 'Passwords do not match';
        }
        lucide.createIcons();
    }

    // --- GENERIC HELPER FUNCTIONS ---
    function showFieldError(fieldId, message) {
        const errorEl = document.getElementById(fieldId + 'Error');
        if (errorEl) {
            errorEl.textContent = message;
            errorEl.classList.add('show');
            const inputEl = document.getElementById(fieldId);
            if(inputEl) inputEl.classList.add('border-red-300', 'bg-red-50');
        }
    }

    function clearFieldError(fieldId) {
        const errorEl = document.getElementById(fieldId + 'Error');
        if (errorEl) {
            errorEl.classList.remove('show');
            errorEl.textContent = ''; // clear text so it doesn't linger
            const inputEl = document.getElementById(fieldId);
            if(inputEl) inputEl.classList.remove('border-red-300', 'bg-red-50');
        }
    }
    
    function showSuccess(message) {
        const successMsg = document.getElementById('successMessage');
        const successText = document.getElementById('successText');
        if (successMsg && successText) {
            successText.textContent = message;
            successMsg.classList.remove('hidden');
        }
    }
    
    function showError(message) {
        const errorMsg = document.getElementById('errorMessage');
        const errorText = document.getElementById('errorText');
        if (errorMsg && errorText) {
            errorText.textContent = message;
            errorMsg.classList.remove('hidden');
        }
    }

    // --- NAVIGATION & SUBMISSION LOGIC ---
    const step1 = document.getElementById('step1');
    const step2 = document.getElementById('step2');
    const step3 = document.getElementById('step3');
    const nextBtn = document.getElementById('nextBtn');
    const backBtn = document.getElementById('backBtn');
    const backBtn3 = document.getElementById('backBtn3');
    const submitBtn = document.getElementById('submitBtn');
    const verifyBtn = document.getElementById('verifyBtn');

    // Steps
    nextBtn.addEventListener('click', () => {
        // Validate Step 1
        const required = ['firstName', 'lastName', 'phone', 'street', 'barangay'];
        let valid = true;
        required.forEach(id => {
            if(!document.getElementById(id).value.trim()) {
                showFieldError(id, 'Required');
                valid = false;
            } else {
                if(id === 'phone' && document.getElementById(id).value.length < 15) { // +63 9XX XXX XXXX
                     showFieldError(id, 'Invalid format');
                     valid = false;
                } else {
                    clearFieldError(id);
                }
            }
        });

        if(valid) {
            step1.classList.remove('active');
            step2.classList.add('active');
            updateStepIndicator(2);
        }
    });

    backBtn.addEventListener('click', () => {
        step2.classList.remove('active');
        step1.classList.add('active');
        updateStepIndicator(1);
    });
    
    backBtn3.addEventListener('click', () => {
        step3.classList.remove('active');
        step2.classList.add('active');
        updateStepIndicator(2);
    });

    function updateStepIndicator(step) {
        // Simplified Logic for indicators
        const s1 = document.getElementById('step1Indicator');
        const s2 = document.getElementById('step2Indicator');
        const s3 = document.getElementById('step3Indicator');
        
        if (step >= 2) {
            s1.classList.replace('bg-blue-600', 'bg-green-500');
            s1.innerHTML = '<i data-lucide="check" class="w-4 h-4"></i>';
            s2.classList.replace('bg-slate-100', 'bg-blue-600');
            s2.classList.replace('text-slate-400', 'text-white');
            document.getElementById('step2Label').classList.replace('text-slate-400', 'text-blue-600');
            document.getElementById('stepConnector1').classList.replace('bg-slate-100', 'bg-green-500');
        } else {
            // Revert logic if going back (simplified for brevity)
             s1.classList.replace('bg-green-500', 'bg-blue-600');
             s1.textContent = '1';
             s2.classList.replace('bg-blue-600', 'bg-slate-100');
             s2.classList.replace('text-white', 'text-slate-400');
        }
        if (step === 3) {
             s2.classList.replace('bg-blue-600', 'bg-green-500');
             s2.innerHTML = '<i data-lucide="check" class="w-4 h-4"></i>';
             s3.classList.replace('bg-slate-100', 'bg-blue-600');
             s3.classList.replace('text-slate-400', 'text-white');
             document.getElementById('step3Label').classList.replace('text-slate-400', 'text-blue-600');
             document.getElementById('stepConnector2').classList.replace('bg-slate-100', 'bg-green-500');
        }
        lucide.createIcons();
    }

    // Create Account Click - Send Verification Code via Email
    submitBtn.addEventListener('click', async (e) => {
        e.preventDefault();
        
        const pwd = passwordInput.value;
        const cfm = confirmInput.value;
        const em = document.getElementById('email').value.trim();
        const firstName = document.getElementById('firstName').value.trim();
        const lastName = document.getElementById('lastName').value.trim();
        
        let valid = true;
        if(pwd.length < 8) { showFieldError('password', 'Password too short'); valid = false; }
        if(!/\d/.test(pwd) || !/[!@#$%^&*]/.test(pwd)) { showFieldError('password', 'Missing number or symbol'); valid = false; }
        if(pwd !== cfm) { showFieldError('confirmPassword', 'Mismatch'); valid = false; }
        if(!em) { showFieldError('email', 'Required'); valid = false; }
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if(!emailRegex.test(em)) { showFieldError('email', 'Invalid email'); valid = false; }
        if(!document.getElementById('terms').checked) { alert('Accept terms'); valid = false; }
        
        if(!valid) return;
        
        // Show loading state
        submitBtn.disabled = true;
        document.getElementById('btnText').textContent = 'Sending Code...';
        document.getElementById('userPlusIcon').classList.add('hidden');
        document.getElementById('spinner').classList.remove('hidden');
        
        try {
            // Send verification code to email via backend API
            const response = await axios.post('/api/auth/send-verification-code', {
                email: em,
                firstName: firstName,
                lastName: lastName
            }, {
                withCredentials: true
            });
            
            if(response.data.success) {
                // Move to Step 3
                step2.classList.remove('active');
                step3.classList.add('active');
                document.getElementById('displayEmail').textContent = em;
                updateStepIndicator(3);
                
                // Store email for verification
                sessionStorage.setItem('verifyEmail', em);
                
                showSuccess('Verification code sent! Check your email');
            } else {
                showFieldError('email', response.data.message || 'Failed to send code');
            }
        } catch(err) {
            console.error('Error:', err);
            showFieldError('email', 'Error sending code. Try again');
        } finally {
            // Reset button state
            submitBtn.disabled = false;
            document.getElementById('btnText').textContent = 'Create Account';
            document.getElementById('userPlusIcon').classList.remove('hidden');
            document.getElementById('spinner').classList.add('hidden');
        }
    });

    // Verification - Verify Code and Create Account
    verifyBtn.addEventListener('click', async (e) => {
        e.preventDefault();
        const rawCode = document.getElementById('verificationCode').value;
        const code = rawCode.replace(/\s/g, ''); // Remove ALL spaces (not just trim)
        const codeError = document.getElementById('verificationCodeError');
        
        if(!code || code.length !== 6) {
            codeError.textContent = "Enter 6-digit code";
            codeError.classList.add('show');
            return;
        }
        
        // Validate it's all digits
        if(!/^\d{6}$/.test(code)) {
            codeError.textContent = "Code must be 6 digits";
            codeError.classList.add('show');
            return;
        }
        
        // Show loading state
        verifyBtn.disabled = true;
        const originalText = verifyBtn.textContent;
        verifyBtn.innerHTML = '<div class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></div>';
        codeError.classList.remove('show');
        
        try {
            // Verify code and create account
            const email = sessionStorage.getItem('verifyEmail');
            const firstName = document.getElementById('firstName').value.trim();
            const lastName = document.getElementById('lastName').value.trim();
            const phone = document.getElementById('phone').value.trim();
            const street = document.getElementById('street').value.trim();
            const barangay = document.getElementById('barangay').value.trim();
            const password = passwordInput.value;
            
            console.log('Sending verification with:', {
                email, firstName, lastName, code, 
                codeLength: code.length,
                codeType: typeof code
            });
            
            const response = await axios.post('/api/auth/verify-and-register', {
                firstName: firstName,
                lastName: lastName,
                email: email,
                phone: phone,
                street: street,
                barangay: barangay,
                password: password,
                verification_code: code
            }, {
                withCredentials: true
            });
            
            if(response.data.success) {
                // Store auth token if provided
                if(response.data.token) {
                    sessionStorage.setItem('auth_token', response.data.token);
                }
                

                // Redirect to dashboard or login
                const redirectUrl = response.data.redirect || '/public/pages/user/user-dashboard.php';
                setTimeout(() => window.location.href = redirectUrl, 1500);
            } else {
                // Check for detailed validation errors
                let errorMsg = response.data.message || 'Registration failed';
                if(response.data.errors && typeof response.data.errors === 'object') {
                    const errorMessages = Object.values(response.data.errors);
                    if(errorMessages.length > 0) {
                        errorMsg = errorMessages[0]; // Show first error
                        console.error('Validation errors:', response.data.errors);
                    }
                }
                codeError.textContent = errorMsg;
                codeError.classList.add('show');
            }
        } catch(err) {
            console.error('Verification error details:', err.response?.data || err);
            console.error('Full error response:', {
                status: err.response?.status,
                statusText: err.response?.statusText,
                data: err.response?.data
            });
            let errorMsg = 'Error verifying code. Try again';
            
            // Check for detailed validation errors in catch
            if(err.response?.data?.errors && typeof err.response.data.errors === 'object') {
                const errorMessages = Object.values(err.response.data.errors);
                if(errorMessages.length > 0) {
                    errorMsg = errorMessages[0];
                    console.error('Validation errors:', err.response.data.errors);
                }
            } else if(err.response?.data?.message) {
                errorMsg = err.response.data.message;
            }
            
            codeError.textContent = errorMsg;
            codeError.classList.add('show');
        } finally {
            verifyBtn.disabled = false;
            verifyBtn.textContent = originalText;
        }
    });

    // Toggle Password Visibility
    document.getElementById('togglePassword').addEventListener('click', () => {
        passwordInput.type = passwordInput.type === 'password' ? 'text' : 'password';
    });
    document.getElementById('toggleConfirmPassword').addEventListener('click', () => {
        confirmInput.type = confirmInput.type === 'password' ? 'text' : 'password';
    });

    // Modal Logic
    const termsModal = document.getElementById('termsModal');
    document.getElementById('termsBtn').addEventListener('click', () => {
        document.getElementById('modalTitle').textContent = 'Terms of Service';
        document.getElementById('termsContent').classList.remove('hidden');
        document.getElementById('privacyContent').classList.add('hidden');
        termsModal.classList.remove('hidden');
    });
    document.getElementById('privacyBtn').addEventListener('click', () => {
        document.getElementById('modalTitle').textContent = 'Privacy Policy';
        document.getElementById('termsContent').classList.add('hidden');
        document.getElementById('privacyContent').classList.remove('hidden');
        termsModal.classList.remove('hidden');
    });
    document.getElementById('closeModal').addEventListener('click', () => termsModal.classList.add('hidden'));
    document.getElementById('closeModalBtn').addEventListener('click', () => termsModal.classList.add('hidden'));

  </script>
</body>
</html>