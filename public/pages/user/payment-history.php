<?php
// Payment History Page
// Displays transaction history with real-time database integration
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Payment History - FixItMati</title>
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
        <div class="flex items-center gap-4">
          <a href="user-dashboard.php" class="text-slate-500 hover:text-blue-600 transition-colors py-4">
            <i data-lucide="arrow-left" class="w-5 h-5"></i>
          </a>
          <h1 class="text-lg font-bold text-slate-800">Payment History</h1>
        </div>
        
        <button id="exportStatementBtn" class="bg-white hover:bg-slate-50 text-slate-600 text-sm font-medium px-4 py-2 rounded-lg flex items-center gap-2 border border-slate-200 shadow-sm transition-colors my-2">
          <i data-lucide="file-text" class="w-4 h-4 text-blue-600"></i>
          <span class="hidden sm:inline">Export Statement</span>
        </button>
      </div>
    </div>
  </div>

  <!-- MAIN CONTENT -->
  <main class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    
    <!-- Filters & Search -->
    <div class="flex flex-col sm:flex-row gap-4 mb-6 justify-between items-center">
      
      <!-- Filter Tabs -->
      <div class="flex items-center gap-2 w-full sm:w-auto overflow-x-auto pb-2 sm:pb-0">
        <button data-filter="All" class="filter-tab px-4 py-2 rounded-full text-sm font-medium transition-colors whitespace-nowrap bg-blue-600 text-white shadow-sm">
          All
        </button>
        <button data-filter="Water" class="filter-tab px-4 py-2 rounded-full text-sm font-medium transition-colors whitespace-nowrap bg-white text-slate-600 border border-slate-200 hover:bg-slate-50">
          Water
        </button>
        <button data-filter="Electricity" class="filter-tab px-4 py-2 rounded-full text-sm font-medium transition-colors whitespace-nowrap bg-white text-slate-600 border border-slate-200 hover:bg-slate-50">
          Electricity
        </button>
        <button data-filter="Services" class="filter-tab px-4 py-2 rounded-full text-sm font-medium transition-colors whitespace-nowrap bg-white text-slate-600 border border-slate-200 hover:bg-slate-50">
          Services
        </button>
      </div>

      <!-- Search & Calendar -->
      <div class="flex items-center gap-2 w-full sm:w-auto">
        <div class="relative flex-1 sm:flex-initial">
          <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 w-4 h-4"></i>
          <input 
            type="text" 
            id="searchInput"
            placeholder="Search Reference No." 
            class="w-full sm:w-64 pl-9 pr-4 py-2 rounded-lg border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
          />
        </div>
        <button class="p-2 border border-slate-200 rounded-lg bg-white text-slate-500 hover:text-blue-600 transition-colors">
          <i data-lucide="calendar" class="w-5 h-5"></i>
        </button>
      </div>

    </div>

    <!-- Transactions Table -->
    <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
      
      <!-- Table Header (Desktop) -->
      <div class="hidden md:grid grid-cols-12 gap-4 p-4 border-b border-slate-200 bg-slate-50 text-xs font-bold text-slate-500 uppercase tracking-wider">
        <div class="col-span-4">Transaction / Biller</div>
        <div class="col-span-2">Date</div>
        <div class="col-span-2">Amount</div>
        <div class="col-span-2">Status</div>
        <div class="col-span-2 text-right">Receipt</div>
      </div>

      <!-- Loading State -->
      <div id="loadingState" class="p-12 text-center">
        <i data-lucide="loader-2" class="w-10 h-10 text-blue-600 animate-spin mx-auto mb-3"></i>
        <p class="text-slate-500 text-sm">Loading payment history...</p>
      </div>

      <!-- Empty State -->
      <div id="emptyState" class="hidden p-12 text-center">
        <div class="h-16 w-16 rounded-full bg-slate-100 flex items-center justify-center mx-auto mb-4">
          <i data-lucide="credit-card" class="w-8 h-8 text-slate-400"></i>
        </div>
        <p class="text-slate-600 font-medium mb-1">No payment history yet</p>
        <p class="text-slate-400 text-sm">Your payment transactions will appear here</p>
      </div>

      <!-- Transactions List -->
      <div id="transactionsList" class="divide-y divide-slate-100">
        <!-- Transactions will be inserted here by JavaScript -->
      </div>

      <!-- Footer Pagination -->
      <div id="paginationFooter" class="hidden p-4 border-t border-slate-200 bg-slate-50 flex justify-between items-center">
        <span class="text-xs text-slate-500" id="paginationInfo">Showing 1-5 of 24 transactions</span>
        <div class="flex gap-2">
          <button id="prevBtn" class="px-3 py-1 bg-white border border-slate-200 rounded text-xs font-medium text-slate-600 disabled:opacity-50 hover:bg-slate-50 transition-colors" disabled>Previous</button>
          <button id="nextBtn" class="px-3 py-1 bg-white border border-slate-200 rounded text-xs font-medium text-slate-600 hover:bg-slate-50 transition-colors">Next</button>
        </div>
      </div>

    </div>

  </main>

  <!-- Footer -->
  <footer class="bg-white border-t border-slate-200 py-12 mt-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
      <div class="flex items-center justify-center gap-2 mb-4">
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

  <!-- Include shared navigation script -->
  <script src="/assets/api-client.js"></script>
  <script src="/assets/payment-history.js"></script>
  <script>
    // Profile Dropdown Toggle
    document.getElementById('profileBtn')?.addEventListener('click', function(e) {
      e.stopPropagation();
      document.getElementById('profileDropdown').classList.toggle('hidden');
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
      const dropdown = document.getElementById('profileDropdown');
      const btn = document.getElementById('profileBtn');
      if (dropdown && !dropdown.contains(e.target) && e.target !== btn) {
        dropdown.classList.add('hidden');
      }
    });

    // Logout
    document.getElementById('logoutBtn')?.addEventListener('click', function() {
      sessionStorage.removeItem('auth_token');
      sessionStorage.removeItem('user_data');
      window.location.href = 'logout.php';
    });

    // Navigation buttons
    document.getElementById('serviceAddressesBtn')?.addEventListener('click', () => {
      window.location.href = 'service-addresses.php';
    });
    
    document.getElementById('linkedMetersBtn')?.addEventListener('click', () => {
      window.location.href = 'linked-meters.php';
    });

    // Load profile data
    async function loadProfile() {
      try {
        const response = await ApiClient.get('/auth/me');
        if (response.success && response.data) {
          const user = response.data;
          const initials = (user.first_name?.[0] || '') + (user.last_name?.[0] || '');
          document.getElementById('profileBtn').textContent = initials;
          document.getElementById('profileAvatarLarge').textContent = initials;
          document.getElementById('profileName').textContent = `${user.first_name} ${user.last_name}`;
          document.getElementById('profileEmail').textContent = user.email;
        }
      } catch (error) {
        console.error('Error loading profile:', error);
      }
    }

    loadProfile();
    lucide.createIcons();
  </script>
</body>
</html>
