<?php
// FixItMati Notifications Page
// Displays and manages user notifications in real-time
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Notifications - FixItMati</title>
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
          <a href="user-dashboard.php" class="flex items-center gap-2 cursor-pointer hover:opacity-80 transition-opacity">
            <div class="bg-blue-600 p-1.5 rounded-lg">
              <i data-lucide="hammer" class="text-white w-5 h-5"></i>
            </div>
            <span class="text-xl font-bold tracking-tight text-blue-900">FixItMati</span>
          </a>
        </div>
        <!-- Right Actions -->
        <div class="flex items-center gap-3">
          <a href="user-dashboard.php" class="p-2 text-slate-500 hover:bg-slate-100 rounded-full cursor-pointer transition-colors" title="Go to Dashboard">
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
            <a href="user-dashboard.php" class="text-slate-500 hover:text-blue-600 transition-colors" title="Back to Dashboard">
              <i data-lucide="arrow-left" class="w-4 h-4"></i>
            </a>
            <h1 class="text-sm font-bold text-slate-900">Notifications</h1>
            <span id="newCountBadge" class="hidden bg-red-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full"></span>
          </div>
        </nav>
      <div class="flex items-center gap-2">
        <button 
          id="markAllReadBtn"
          class="text-xs font-bold text-blue-600 hover:bg-blue-50 px-3 py-2 rounded-lg transition-colors flex items-center gap-1"
        >
          <i data-lucide="check" class="w-3.5 h-3.5"></i> Mark all read
        </button>
        <button id="settingsBtn" class="text-slate-400 hover:text-slate-600 p-2 hover:bg-slate-100 rounded-lg" title="Notification Preferences">
          <i data-lucide="settings" class="w-4.5 h-4.5"></i>
        </button>
      </div>
    </div>
  </div>

  <!-- MAIN CONTENT -->
  <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    
    <!-- Filters -->
    <div class="flex items-center gap-2 overflow-x-auto pb-4 mb-2 no-scrollbar">
      <button data-filter="All" class="filter-btn whitespace-nowrap px-4 py-2 rounded-full text-sm font-medium transition-colors border bg-slate-800 text-white border-slate-800">
        All
      </button>
      <button data-filter="Unread" class="filter-btn whitespace-nowrap px-4 py-2 rounded-full text-sm font-medium transition-colors border bg-white text-slate-600 border-slate-200 hover:bg-slate-50">
        Unread
      </button>
      <button data-filter="Urgent" class="filter-btn whitespace-nowrap px-4 py-2 rounded-full text-sm font-medium transition-colors border bg-white text-slate-600 border-slate-200 hover:bg-slate-50">
        Urgent
      </button>
      <button data-filter="Billing" class="filter-btn whitespace-nowrap px-4 py-2 rounded-full text-sm font-medium transition-colors border bg-white text-slate-600 border-slate-200 hover:bg-slate-50">
        Billing
      </button>
    </div>

    <!-- Notifications List -->
    <div id="notificationsList" class="space-y-3">
      <!-- Loading state -->
      <div class="text-center py-16">
        <div style="width: 48px; height: 48px; margin: 0 auto 16px; border: 3px solid #e2e8f0; border-top-color: #3b82f6; border-radius: 50%; animation: spin 1s linear infinite;"></div>
        <p style="font-size: 16px; font-weight: 500; color: #64748b;">Loading notifications...</p>
      </div>
    </div>

  </div>

  <!-- NOTIFICATION PREFERENCES MODAL -->
  <div id="preferencesModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
      <!-- Modal Header -->
      <div class="flex items-center justify-between p-6 border-b border-slate-200">
        <h2 class="text-lg font-semibold text-slate-900">Notification Preferences</h2>
        <button id="closePreferencesBtn" class="p-1 hover:bg-slate-100 rounded-full transition-colors">
          <i data-lucide="x" class="w-5 h-5 text-slate-500"></i>
        </button>
      </div>
      
      <!-- Modal Body -->
      <div class="p-6">
        <p class="text-sm text-slate-500 mb-6">Control which notifications you want to receive.</p>
        
        <div class="space-y-4">
          <!-- Urgent Alerts -->
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center">
                <i data-lucide="alert-triangle" class="w-5 h-5 text-red-600"></i>
              </div>
              <span class="font-semibold text-slate-900">Urgent Alerts</span>
            </div>
            <label class="relative inline-flex items-center cursor-pointer">
              <input type="checkbox" id="urgentToggle" class="sr-only peer" checked>
              <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
            </label>
          </div>
          
          <!-- Billing Reminders -->
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center">
                <i data-lucide="credit-card" class="w-5 h-5 text-green-600"></i>
              </div>
              <span class="font-semibold text-slate-900">Billing Reminders</span>
            </div>
            <label class="relative inline-flex items-center cursor-pointer">
              <input type="checkbox" id="billingToggle" class="sr-only peer" checked>
              <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
            </label>
          </div>
          
          <!-- Service Updates -->
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center">
                <i data-lucide="wrench" class="w-5 h-5 text-slate-600"></i>
              </div>
              <span class="font-semibold text-slate-900">Service Updates</span>
            </div>
            <label class="relative inline-flex items-center cursor-pointer">
              <input type="checkbox" id="serviceToggle" class="sr-only peer" checked>
              <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
            </label>
          </div>
          
          <!-- System Info -->
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center">
                <i data-lucide="info" class="w-5 h-5 text-slate-600"></i>
              </div>
              <span class="font-semibold text-slate-900">System Info</span>
            </div>
            <label class="relative inline-flex items-center cursor-pointer">
              <input type="checkbox" id="systemToggle" class="sr-only peer">
              <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
            </label>
          </div>
        </div>
      </div>
      
      <!-- Modal Footer -->
      <div class="p-6 border-t border-slate-200">
        <button id="savePreferencesBtn" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded-lg transition-colors">
          Save Preferences
        </button>
      </div>
    </div>
  </div>

  <!-- NOTIFICATION DETAIL MODAL -->
  <div id="notificationModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-hidden">
      <!-- Modal Header -->
      <div class="flex items-center justify-between p-6 border-b border-slate-200">
        <div class="flex items-center gap-3">
          <div id="modalIcon" class="w-10 h-10 rounded-full flex items-center justify-center">
            <i data-lucide="bell" class="w-5 h-5"></i>
          </div>
          <div>
            <h2 id="modalTitle" class="text-lg font-semibold text-slate-900">Notification</h2>
            <p id="modalTime" class="text-sm text-slate-500">Just now</p>
          </div>
        </div>
        <button id="closeModalBtn" class="p-2 hover:bg-slate-100 rounded-full transition-colors">
          <i data-lucide="x" class="w-5 h-5 text-slate-500"></i>
        </button>
      </div>
      
      <!-- Modal Body -->
      <div class="p-6 overflow-y-auto" style="max-height: calc(90vh - 180px);">
        <p id="modalMessage" class="text-slate-700 leading-relaxed whitespace-pre-wrap"></p>
      </div>
      
      <!-- Modal Footer -->
      <div id="modalFooter" class="flex items-center justify-end gap-3 p-6 border-t border-slate-200">
        <!-- Action button will be added dynamically -->
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

  <!-- API Client -->
  <script src="/assets/api-client.js"></script>
  <!-- Dashboard JS -->
  <script src="/assets/dashboard.js"></script>
  <!-- Notifications JS -->
  <script src="/assets/notifications.js?v=5"></script>
</body>
</html>
