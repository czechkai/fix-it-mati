<?php
// Edit Profile page for FixItMati
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Edit Profile - FixItMati</title>
  <!-- Authentication check and cross-tab sync -->
  <script src="/assets/auth-check.js"></script>
  <!-- Tailwind CSS via CDN -->
  <script src="https://cdn.tailwindcss.com"></script>
  <!-- Lucide Icons CDN -->
  <script src="https://unpkg.com/lucide@latest"></script>
  <!-- App styles -->
  <link rel="stylesheet" href="/assets/style.css" />
</head>

<body class="min-h-screen bg-slate-50 font-sans text-slate-800">

  <!-- HEADER -->
  <header class="bg-white border-b border-slate-200 sticky top-0 z-30">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex justify-between items-center h-16">
        <!-- Logo & Mobile Menu -->
        <div class="flex items-center gap-4">
          <button id="mobileMenuBtn" class="lg:hidden p-2 rounded-md text-slate-500 hover:bg-slate-100" aria-label="Toggle Menu">
            <i data-lucide="menu" class="w-6 h-6"></i>
          </button>
          <a href="/user-dashboard.php" class="flex items-center gap-2 cursor-pointer hover:opacity-80 transition-opacity">
            <div class="bg-blue-600 p-1.5 rounded-lg">
              <i data-lucide="hammer" class="text-white w-5 h-5"></i>
            </div>
            <span class="text-xl font-bold tracking-tight text-blue-900">FixItMati</span>
          </a>
        </div>
        <!-- Right Actions -->
        <div class="flex items-center gap-3">
          <a href="/user-dashboard.php" class="p-2 text-slate-500 hover:bg-slate-100 rounded-full cursor-pointer transition-colors" title="Go to Dashboard">
            <i data-lucide="home" class="w-5 h-5"></i>
          </a>
          <div class="relative p-2 text-slate-500 hover:bg-slate-100 rounded-full cursor-pointer" id="notificationBtn">
            <i data-lucide="bell" class="w-5 h-5"></i>
            <span class="absolute top-1.5 right-1.5 block h-2 w-2 rounded-full bg-red-500 ring-2 ring-white" id="notificationDot"></span>
          </div>
          <!-- Profile Dropdown -->
          <div class="relative">
            <div class="h-8 w-8 rounded-full bg-gradient-to-tr from-blue-500 to-cyan-400 border-2 border-white shadow-sm cursor-pointer" id="profileBtn"></div>

            <!-- Profile Dropdown Menu -->
            <div id="profileDropdown" class="hidden absolute right-0 mt-2 w-72 bg-white rounded-lg shadow-lg border border-slate-200 z-50">
              <div class="p-4 border-b border-slate-100">
                <div class="flex items-center gap-3">
                  <div class="h-12 w-12 rounded-full bg-gradient-to-tr from-blue-500 to-cyan-400 flex items-center justify-center text-white font-bold text-lg" id="profileAvatarLarge"></div>
                  <div class="flex-1 min-w-0">
                    <p class="font-semibold text-slate-900 truncate" id="profileName">Loading...</p>
                    <p class="text-sm text-slate-500 truncate" id="profileEmail">Loading...</p>
                  </div>
                </div>
              </div>

              <div class="p-2">
                <button id="profileEditBtn" class="w-full flex items-center gap-3 px-3 py-2 text-sm text-slate-700 hover:bg-slate-50 rounded-md transition-colors">
                  <i data-lucide="user-pen" class="w-4 h-4"></i>
                  <span>Edit Profile</span>
                </button>
                <button id="serviceAddressesBtn" class="w-full flex items-center gap-3 px-3 py-2 text-sm text-slate-700 hover:bg-slate-50 rounded-md transition-colors">
                  <i data-lucide="map-pin" class="w-4 h-4"></i>
                  <span>Service Addresses</span>
                </button>
                <button id="linkedMetersBtn" class="w-full flex items-center gap-3 px-3 py-2 text-sm text-slate-700 hover:bg-slate-50 rounded-md transition-colors">
                  <i data-lucide="gauge" class="w-4 h-4"></i>
                  <span>Linked Meters</span>
                </button>
                <button id="helpSupportBtn" class="w-full flex items-center gap-3 px-3 py-2 text-sm text-slate-700 hover:bg-slate-50 rounded-md transition-colors">
                  <i data-lucide="help-circle" class="w-4 h-4"></i>
                  <span>Help & Support</span>
                </button>
              </div>

              <div class="p-2 border-t border-slate-100">
                <button id="logoutBtn" class="w-full flex items-center gap-3 px-3 py-2 text-sm text-red-600 hover:bg-red-50 rounded-md transition-colors">
                  <i data-lucide="log-out" class="w-4 h-4"></i>
                  <span>Logout</span>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </header>

  <!-- SUB-NAV -->
  <div class="bg-white border-b border-slate-200 overflow-x-auto">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex items-center justify-between">
        <nav class="flex -mb-px items-center space-x-8">
          <div class="flex items-center gap-2 py-4 border-b-2 border-blue-600">
            <a href="/user-dashboard.php" class="text-slate-500 hover:text-blue-600 transition-colors" title="Back to Dashboard">
              <i data-lucide="arrow-left" class="w-4 h-4"></i>
            </a>
            <h1 class="text-lg font-bold text-slate-800">Profile</h1>
          </div>
        </nav>
        <button id="saveChangesBtn" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold px-4 py-2 rounded-lg flex items-center gap-2 shadow-sm transition-colors">
          <i data-lucide="save" class="w-4 h-4"></i>
          <span class="hidden sm:inline">Save Changes</span>
        </button>
      </div>
    </div>
  </div>

  <!-- MAIN CONTENT -->
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <!-- Success Message -->
    <div id="successMessage" class="hidden mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl flex items-center gap-2">
      <i data-lucide="check-circle-2" class="w-5 h-5"></i>
      <span class="text-sm font-medium">Profile details updated successfully.</span>
    </div>

    <div class="flex flex-col lg:flex-row gap-8">

      <!-- LEFT COLUMN: AVATAR & SUMMARY -->
      <div class="w-full lg:w-1/3 space-y-6">

        <!-- Profile Card -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6 flex flex-col items-center text-center">
          <input type="file" id="profileImageInput" accept="image/*" class="hidden" />
          <div id="profileAvatarContainer" class="relative mb-4 group cursor-pointer">
            <div class="w-24 h-24 rounded-full bg-gradient-to-tr from-blue-500 to-cyan-400 p-1">
              <div id="profileAvatarImage" class="w-full h-full rounded-full bg-white flex items-center justify-center overflow-hidden">
                <img id="profileImage" class="hidden w-full h-full object-cover" alt="Profile" />
                <i data-lucide="user" class="text-slate-300 w-10 h-10" id="profileImagePlaceholder"></i>
              </div>
            </div>
            <div class="absolute bottom-0 right-0 bg-white rounded-full p-1.5 shadow-md border border-slate-100 group-hover:scale-110 transition-transform">
              <i data-lucide="camera" class="text-blue-600 w-4 h-4"></i>
            </div>
          </div>

          <h2 id="profileDisplayName" class="text-lg font-bold text-slate-800">Loading...</h2>
          <p class="text-xs text-slate-500 mb-4">Resident • <span id="profileLocation">Loading...</span></p>

          <div class="w-full border-t border-slate-100 pt-4 text-left space-y-2">
            <div class="flex justify-between text-xs">
              <span class="text-slate-400">Member Since</span>
              <span id="memberSince" class="font-medium text-slate-700">Loading...</span>
            </div>
            <div class="flex justify-between text-xs">
              <span class="text-slate-400">Status</span>
              <span class="font-medium text-green-600 bg-green-50 px-2 py-0.5 rounded-full">Verified</span>
            </div>
          </div>
        </div>

        <!-- Quick Tips -->
        <div class="bg-blue-50 border border-blue-100 rounded-xl p-4 flex gap-3 items-start">
          <i data-lucide="alert-circle" class="text-blue-600 flex-shrink-0 mt-0.5 w-5 h-5"></i>
          <div class="text-xs text-blue-800">
            <p class="font-bold mb-1">Keep your phone updated</p>
            <p class="opacity-80">
              We use your mobile number to send urgent alerts about water and power interruptions.
            </p>
          </div>
        </div>

      </div>

      <!-- RIGHT COLUMN: FORMS -->
      <div class="flex-1 space-y-6">

        <!-- Personal Details Form -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
          <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider mb-5 flex items-center gap-2">
            <i data-lucide="user" class="text-slate-400 w-4 h-4"></i> Personal Details
          </h3>

          <form id="personalDetailsForm" class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="space-y-1.5">
              <label class="text-xs font-bold text-slate-500 uppercase">First Name</label>
              <div class="relative">
                <i data-lucide="user" class="absolute left-3 top-3 text-slate-400 w-4 h-4"></i>
                <input
                  type="text"
                  id="firstName"
                  name="first_name"
                  placeholder="Enter first name"
                  class="w-full border border-slate-200 rounded-lg pl-10 pr-3 py-2.5 text-sm font-medium focus:ring-2 focus:ring-blue-500 outline-none" />
              </div>
            </div>
            <div class="space-y-1.5">
              <label class="text-xs font-bold text-slate-500 uppercase">Last Name</label>
              <div class="relative">
                <i data-lucide="user" class="absolute left-3 top-3 text-slate-400 w-4 h-4"></i>
                <input
                  type="text"
                  id="lastName"
                  name="last_name"
                  placeholder="Enter last name"
                  class="w-full border border-slate-200 rounded-lg pl-10 pr-3 py-2.5 text-sm font-medium focus:ring-2 focus:ring-blue-500 outline-none" />
              </div>
            </div>
            <div class="space-y-1.5 md:col-span-2">
              <label class="text-xs font-bold text-slate-500 uppercase">Email Address</label>
              <div class="relative">
                <i data-lucide="mail" class="absolute left-3 top-3 text-slate-400 w-4 h-4"></i>
                <input
                  type="email"
                  id="email"
                  name="email"
                  class="w-full border border-slate-200 rounded-lg pl-10 pr-3 py-2.5 text-sm font-medium focus:ring-2 focus:ring-blue-500 outline-none"
                  required />
              </div>
            </div>
            <div class="space-y-1.5 md:col-span-2">
              <label class="text-xs font-bold text-slate-500 uppercase">Mobile Number</label>
              <div class="relative">
                <i data-lucide="phone" class="absolute left-3 top-3 text-slate-400 w-4 h-4"></i>
                <input
                  type="tel"
                  id="phone"
                  name="phone"
                  class="w-full border border-slate-200 rounded-lg pl-10 pr-3 py-2.5 text-sm font-medium focus:ring-2 focus:ring-blue-500 outline-none" />
              </div>
            </div>
            <div class="space-y-1.5 md:col-span-2">
              <label class="text-xs font-bold text-slate-500 uppercase">Address</label>
              <div class="relative">
                <i data-lucide="map-pin" class="absolute left-3 top-3 text-slate-400 w-4 h-4"></i>
                <input
                  type="text"
                  id="address"
                  name="address"
                  class="w-full border border-slate-200 rounded-lg pl-10 pr-3 py-2.5 text-sm font-medium focus:ring-2 focus:ring-blue-500 outline-none" />
              </div>
            </div>
          </form>
        </div>

        <!-- Security Form -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
          <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider mb-5 flex items-center gap-2">
            <i data-lucide="lock" class="text-slate-400 w-4 h-4"></i> Security & Password
          </h3>

          <form id="securityForm" class="space-y-4">
            <div class="space-y-1.5">
              <label class="text-xs font-bold text-slate-500 uppercase">Current Password</label>
              <input
                type="password"
                id="currentPassword"
                name="current_password"
                placeholder="••••••••"
                class="w-full border border-slate-200 rounded-lg p-2.5 text-sm outline-none focus:ring-2 focus:ring-blue-500" />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div class="space-y-1.5">
                <label class="text-xs font-bold text-slate-500 uppercase">New Password</label>
                <input
                  type="password"
                  id="newPassword"
                  name="new_password"
                  placeholder="New password"
                  class="w-full border border-slate-200 rounded-lg p-2.5 text-sm outline-none focus:ring-2 focus:ring-blue-500" />
              </div>
              <div class="space-y-1.5">
                <label class="text-xs font-bold text-slate-500 uppercase">Confirm Password</label>
                <input
                  type="password"
                  id="confirmPassword"
                  name="confirm_password"
                  placeholder="Confirm new password"
                  class="w-full border border-slate-200 rounded-lg p-2.5 text-sm outline-none focus:ring-2 focus:ring-blue-500" />
              </div>
            </div>
          </form>
        </div>

      </div>
    </div>
  </div>

  <!-- FOOTER -->
  <footer class="bg-white border-t border-slate-200 mt-12 py-8">
    <div class="max-w-7xl mx-auto px-4 text-center">
      <div class="flex justify-center items-center gap-2 mb-4">
        <i data-lucide="hammer" class="text-blue-600 w-5 h-5"></i>
        <span class="text-lg font-bold text-slate-700">FixItMati</span>
      </div>
      <p class="text-slate-400 text-sm mb-6">Mati Public Utilities Online Service Request &amp; Tracking System</p>
      <div class="flex justify-center gap-6 text-sm text-slate-500">
        <a href="#" class="hover:text-blue-600">Privacy Policy</a>
        <a href="#" class="hover:text-blue-600">Terms of Service</a>
        <a href="#" class="hover:text-blue-600">Contact Support</a>
      </div>
      <p class="text-slate-400 text-xs mt-6">&copy; 2025 City of Mati. All rights reserved.</p>
    </div>
  </footer>

  <!-- Mobile Drawer (hidden by default) -->
  <div id="mobileDrawer" class="hidden"></div>

  <!-- API Client -->
  <script src="/assets/api-client.js"></script>
  <!-- Dashboard JS -->
  <script src="/assets/dashboard.js?v=20"></script>
  <!-- Edit Profile JS -->
  <script src="/assets/edit-profile.js"></script>
</body>

</html>