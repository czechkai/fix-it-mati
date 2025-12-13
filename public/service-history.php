<?php
// Service History / Resolved Issues Page for FixItMati
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Service History - FixItMati</title>
  <!-- Check authentication client-side -->
  <script>
    // Redirect to login if not authenticated
    (function() {
      const token = sessionStorage.getItem('auth_token');
      if (!token) {
        window.location.replace('login.php');
        throw new Error('Not authenticated');
      }
    })();
  </script>
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
  <div class="bg-white border-b border-slate-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex items-center justify-between h-14">
        <div class="flex items-center gap-2">
          <a href="user-dashboard.php" class="text-slate-500 hover:text-blue-600 transition-colors">
            <i data-lucide="arrow-left" class="w-5 h-5"></i>
          </a>
          <h1 class="text-lg font-bold text-slate-800">Service History</h1>
        </div>
        
        <!-- Date Filter -->
        <div class="relative">
          <button id="dateFilterBtn" class="flex items-center gap-2 px-3 py-2 bg-white border border-slate-200 rounded-lg text-xs font-medium text-slate-600 hover:bg-slate-50 transition-colors">
            <i data-lucide="calendar" class="w-4 h-4"></i>
            <span id="dateFilterText">Last 30 Days</span>
            <i data-lucide="chevron-down" class="w-3.5 h-3.5"></i>
          </button>
          
          <!-- Date Filter Dropdown -->
          <div id="dateFilterDropdown" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-slate-200 z-50 py-1">
            <button data-filter="7" class="date-filter-option w-full text-left px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 transition-colors">
              Last 7 Days
            </button>
            <button data-filter="30" class="date-filter-option w-full text-left px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 transition-colors bg-slate-50">
              Last 30 Days
            </button>
            <button data-filter="90" class="date-filter-option w-full text-left px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 transition-colors">
              Last 3 Months
            </button>
            <button data-filter="180" class="date-filter-option w-full text-left px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 transition-colors">
              Last 6 Months
            </button>
            <button data-filter="365" class="date-filter-option w-full text-left px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 transition-colors">
              Last Year
            </button>
            <div class="border-t border-slate-200 my-1"></div>
            <button data-filter="all" class="date-filter-option w-full text-left px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 transition-colors">
              All Time
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- MAIN CONTENT -->
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <div class="flex flex-col lg:flex-row gap-6 min-h-[600px]">
      
      <!-- LEFT: LIST OF RESOLVED ITEMS -->
      <div class="w-full lg:w-1/3 flex flex-col bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden" style="height: calc(100vh - 200px);">
        
        <!-- Search/Filter Header -->
        <div class="p-4 border-b border-slate-200 flex gap-2">
          <div class="relative flex-1">
            <i data-lucide="search" class="absolute left-3 top-2.5 text-slate-400 w-4 h-4"></i>
            <input 
              type="text" 
              id="searchInput"
              placeholder="Search history..." 
              class="w-full pl-9 pr-3 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500"
            />
          </div>
          <button id="filterBtn" class="p-2 border border-slate-200 rounded-lg text-slate-500 hover:bg-slate-50">
            <i data-lucide="filter" class="w-4.5 h-4.5"></i>
          </button>
        </div>

        <!-- List Container -->
        <div id="issuesList" class="flex-1 overflow-y-auto p-2 space-y-2">
          <!-- Issues will be loaded here via JavaScript -->
          <div class="text-center py-8 text-slate-400">
            <i data-lucide="loader-2" class="w-8 h-8 mx-auto animate-spin mb-2"></i>
            <p class="text-sm">Loading resolved issues...</p>
          </div>
        </div>
      </div>

      <!-- RIGHT: DETAIL VIEW -->
      <div id="detailView" class="w-full lg:w-2/3 bg-white rounded-xl border border-slate-200 shadow-sm flex flex-col overflow-hidden" style="height: calc(100vh - 200px);">
        
        <!-- Initial state -->
        <div class="flex-1 flex items-center justify-center text-slate-400">
          <div class="text-center">
            <i data-lucide="file-check-2" class="w-16 h-16 mx-auto mb-4"></i>
            <p class="text-sm">Select an issue to view details</p>
          </div>
        </div>

      </div>
    </div>
  </div>

  <!-- API Client -->
  <script src="/assets/api-client.js"></script>
  <!-- Dashboard JS -->
  <script src="/assets/dashboard.js"></script>
  <!-- Service History JS -->
  <script src="/assets/service-history.js"></script>
</body>
</html>
