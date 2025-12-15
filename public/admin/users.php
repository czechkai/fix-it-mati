<?php
// Admin Users Management Page
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Citizen Users - Admin - FixItMati</title>
  <!-- Check authentication and admin role -->
  <script>
    (function() {
      const token = localStorage.getItem('auth_token');
      const userData = localStorage.getItem('user');
      
      if (!token) {
        window.location.replace('/login.php');
        throw new Error('Not authenticated');
      }
      
      // Check if user is admin or staff
      if (userData) {
        try {
          const user = JSON.parse(userData);
          if (user.role !== 'admin' && user.role !== 'staff') {
            alert('Access denied. Admin privileges required.');
            window.location.replace('/user-dashboard.php');
            throw new Error('Insufficient permissions');
          }
        } catch (e) {
          window.location.replace('/login.php');
          throw new Error('Invalid user data');
        }
      }
    })();
  </script>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/lucide@latest"></script>
  <link rel="stylesheet" href="/assets/style.css">
  <link rel="stylesheet" href="/assets/users-admin.css">
  <style>
    @keyframes fade-in {
      from { opacity: 0; transform: translateY(10px); }
      to { opacity: 1; transform: translateY(0); }
    }
    .animate-in {
      animation: fade-in 0.3s ease-out;
    }
    
    /* Drawer animations */
    @keyframes slide-in-right {
      from { transform: translateX(100%); }
      to { transform: translateX(0); }
    }
    @keyframes zoom-in {
      from { transform: scale(0.95); opacity: 0; }
      to { transform: scale(1); opacity: 1; }
    }
    @keyframes fade-in-backdrop {
      from { opacity: 0; }
      to { opacity: 1; }
    }
    .drawer-slide-in {
      animation: slide-in-right 0.3s ease-out;
    }
    .modal-zoom-in {
      animation: zoom-in 0.2s ease-out;
    }
    .drawer-backdrop-in {
      animation: fade-in-backdrop 0.2s ease-out;
    }
    
    /* Smooth transitions for table rows */
    tbody tr {
      transition: all 0.15s ease;
    }
    tbody tr:hover {
      transform: translateX(2px);
    }
    tbody tr:active {
      transform: scale(0.995);
    }
    
    /* Hide scrollbar but keep functionality */
    .hide-scrollbar::-webkit-scrollbar {
      display: none;
    }
    .hide-scrollbar {
      -ms-overflow-style: none;
      scrollbar-width: none;
    }
    
    /* Hide all scrollbars globally */
    ::-webkit-scrollbar {
      width: 0px;
      height: 0px;
    }
    
    body {
      overflow: hidden;
    }
    /* Specific scrollbar hiding for main containers */
    body, html {
      overflow-x: hidden;
    }
    main {
      overflow-x: hidden;
    }
  </style>
</head>
<body class="min-h-screen bg-slate-100 font-sans text-slate-800">

  <div class="flex min-h-screen">
  
  <!-- SIDEBAR -->
  <aside class="w-64 bg-slate-900 text-slate-300 flex-shrink-0 hidden lg:flex flex-col">
    <div class="h-16 flex items-center gap-3 px-6 border-b border-slate-800">
      <div class="bg-blue-600 p-1.5 rounded-lg">
        <i data-lucide="hammer" class="w-5 h-5 text-white"></i>
      </div>
      <span class="text-lg font-bold text-white tracking-tight">
        FixItMati 
        <span class="text-xs font-normal text-slate-400 bg-slate-800 px-1.5 py-0.5 rounded ml-1">ADMIN</span>
      </span>
    </div>

    <nav class="flex-1 px-4 py-6 space-y-1" id="sidebarNav">
      <a href="/admin-dashboard.php" class="nav-item w-full flex items-center justify-between px-3 py-3 rounded-lg text-sm font-medium transition-colors hover:bg-slate-800 hover:text-white">
        <div class="flex items-center gap-3">
          <i data-lucide="layout-dashboard" class="w-5 h-5"></i>
          <span>Overview</span>
        </div>
      </a>
      <a href="/admin/service-requests.php" class="nav-item w-full flex items-center justify-between px-3 py-3 rounded-lg text-sm font-medium transition-colors hover:bg-slate-800 hover:text-white">
        <div class="flex items-center gap-3">
          <i data-lucide="ticket" class="w-5 h-5"></i>
          <span>Service Requests</span>
        </div>
        <span id="pendingBadge" class="bg-red-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full">0</span>
      </a>
      <a href="/admin/billing.php" class="nav-item w-full flex items-center justify-between px-3 py-3 rounded-lg text-sm font-medium transition-colors hover:bg-slate-800 hover:text-white">
        <div class="flex items-center gap-3">
          <i data-lucide="credit-card" class="w-5 h-5"></i>
          <span>Billing & Payments</span>
        </div>
      </a>
      <a href="/admin/users.php" class="nav-item active w-full flex items-center justify-between px-3 py-3 rounded-lg text-sm font-medium transition-colors bg-blue-600 text-white shadow-md">
        <div class="flex items-center gap-3">
          <i data-lucide="users" class="w-5 h-5"></i>
          <span>Citizen Users</span>
        </div>
      </a>
      <a href="/admin/technicians.php" class="nav-item w-full flex items-center justify-between px-3 py-3 rounded-lg text-sm font-medium transition-colors hover:bg-slate-800 hover:text-white">
        <div class="flex items-center gap-3">
          <i data-lucide="hammer" class="w-5 h-5"></i>
          <span>Technicians</span>
        </div>
      </a>
      <a href="/admin/announcements.php" class="nav-item w-full flex items-center justify-between px-3 py-3 rounded-lg text-sm font-medium transition-colors hover:bg-slate-800 hover:text-white">
        <div class="flex items-center gap-3">
          <i data-lucide="megaphone" class="w-5 h-5"></i>
          <span>Announcements</span>
        </div>
      </a>
    </nav>

    <div class="p-4 border-t border-slate-800">
      <button class="flex items-center gap-3 px-3 py-2 text-sm font-medium text-slate-400 hover:text-white transition-colors w-full">
        <i data-lucide="settings" class="w-5 h-5"></i>
        <span>Settings</span>
      </button>
      <button id="logoutBtn" class="flex items-center gap-3 px-3 py-2 text-sm font-medium text-red-400 hover:text-red-300 transition-colors w-full mt-1">
        <i data-lucide="log-out" class="w-5 h-5"></i>
        <span>Logout</span>
      </button>
    </div>
  </aside>

  <!-- MAIN CONTENT -->
  <div class="flex-1 flex flex-col min-w-0 h-screen relative">
    
    <!-- Header -->
    <header class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-4 sm:px-6 lg:px-8 sticky top-0 z-30 flex-shrink-0">
      <div class="lg:hidden font-bold text-slate-800">FixItMati Admin</div>
      <div class="hidden md:flex flex-1 max-w-md ml-4">
        <div class="relative w-full">
          <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 w-4 h-4"></i>
          <input 
            type="text" 
            id="searchInput"
            placeholder="Search by name, email, or barangay..." 
            class="w-full pl-10 pr-4 py-2 bg-slate-100 border-transparent focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-200 rounded-lg text-sm transition-all outline-none"
          />
        </div>
      </div>
      <div class="flex items-center gap-4">
        <button class="relative p-2 text-slate-500 hover:bg-slate-100 rounded-full" id="notificationBtn">
          <i data-lucide="bell" class="w-5 h-5"></i>
          <span class="absolute top-1.5 right-1.5 h-2 w-2 bg-red-500 rounded-full border border-white"></span>
        </button>
        <div class="h-8 w-px bg-slate-200 mx-1"></div>
        <div class="flex items-center gap-3 cursor-pointer hover:bg-slate-50 p-1.5 rounded-lg transition-colors">
          <div class="text-right hidden sm:block">
            <p class="text-sm font-bold text-slate-800" id="headerUserName">Admin User</p>
            <p class="text-xs text-slate-500">Mati City Hall</p>
          </div>
          <div class="h-9 w-9 rounded-full bg-slate-800 text-white flex items-center justify-center font-bold" id="headerUserAvatar">AD</div>
        </div>
      </div>
    </header>

    <!-- Scrollable Canvas -->
    <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8 bg-slate-100">
      
      <div class="space-y-6 animate-in">
        
        <!-- Page Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
          <div>
            <h2 class="text-2xl font-bold text-slate-800">Citizen Database</h2>
            <p class="text-sm text-slate-500">Manage user accounts, verification, and profiles.</p>
          </div>
        </div>

        <!-- Quick Stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <div class="bg-white rounded-xl p-5 border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
              <p class="text-slate-500 text-xs font-bold uppercase">Total Users</p>
              <h3 class="text-2xl font-bold text-slate-800 mt-1" id="statTotalUsers">0</h3>
            </div>
            <div class="p-3 bg-blue-100 text-blue-600 rounded-lg">
              <i data-lucide="users" class="w-5 h-5"></i>
            </div>
          </div>
          <div class="bg-white rounded-xl p-5 border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
              <p class="text-slate-500 text-xs font-bold uppercase">Pending Review</p>
              <h3 class="text-2xl font-bold text-amber-600 mt-1" id="statPendingReview">0</h3>
            </div>
            <div class="p-3 bg-amber-100 text-amber-600 rounded-lg">
              <i data-lucide="alert-circle" class="w-5 h-5"></i>
            </div>
          </div>
          <div class="bg-white rounded-xl p-5 border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
              <p class="text-slate-500 text-xs font-bold uppercase">Verified (This Month)</p>
              <h3 class="text-2xl font-bold text-green-600 mt-1" id="statVerifiedMonth">0</h3>
            </div>
            <div class="p-3 bg-green-100 text-green-600 rounded-lg">
              <i data-lucide="check-circle-2" class="w-5 h-5"></i>
            </div>
          </div>
        </div>

        <!-- Users Table -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden flex flex-col min-h-[400px]">
          
          <!-- Table Controls -->
          <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row justify-between items-center gap-4">
            <div class="flex items-center gap-2 overflow-x-auto pb-1 w-full sm:w-auto">
              <button data-filter="All" class="filter-btn px-3 py-1.5 rounded-lg text-xs font-medium transition-colors border bg-slate-800 text-white border-slate-800">
                All
              </button>
              <button data-filter="customer" class="filter-btn px-3 py-1.5 rounded-lg text-xs font-medium transition-colors border bg-white text-slate-600 border-slate-200 hover:bg-slate-50">
                Verified
              </button>
              <button data-filter="pending" class="filter-btn px-3 py-1.5 rounded-lg text-xs font-medium transition-colors border bg-white text-slate-600 border-slate-200 hover:bg-slate-50">
                Pending
              </button>
              <button data-filter="suspended" class="filter-btn px-3 py-1.5 rounded-lg text-xs font-medium transition-colors border bg-white text-slate-600 border-slate-200 hover:bg-slate-50">
                Suspended
              </button>
            </div>
          </div>

          <!-- Table -->
          <div class="overflow-x-auto flex-1">
            <table class="w-full text-left text-sm">
              <thead class="bg-slate-50 text-slate-500 border-b border-slate-200 sticky top-0">
                <tr>
                  <th class="px-6 py-4 font-semibold">Name</th>
                  <th class="px-6 py-4 font-semibold">Contact Info</th>
                  <th class="px-6 py-4 font-semibold">Location</th>
                  <th class="px-6 py-4 font-semibold">Linked Meters</th>
                  <th class="px-6 py-4 font-semibold">Status</th>
                  <th class="px-6 py-4 font-semibold text-right">Action</th>
                </tr>
              </thead>
              <tbody id="usersTableBody" class="divide-y divide-slate-100">
                <!-- Table rows will be inserted here by JavaScript -->
              </tbody>
            </table>
          </div>

          <!-- Footer -->
          <div class="p-4 border-t border-slate-200 bg-slate-50 text-xs text-slate-500 flex justify-between items-center">
            <span id="tableFooterText">Showing 0 users</span>
            <div class="flex gap-2">
              <button id="prevPageBtn" class="px-3 py-1 bg-white border border-slate-200 rounded hover:bg-slate-50 disabled:opacity-50" disabled>Previous</button>
              <button id="nextPageBtn" class="px-3 py-1 bg-white border border-slate-200 rounded hover:bg-slate-50">Next</button>
            </div>
          </div>
        </div>

      </div>

    </main>

    <!-- USER PROFILE DRAWER (Hidden by default) -->
    <div id="userDrawer" class="hidden absolute inset-0 z-50 flex justify-end">
      <div 
        id="drawerBackdrop"
        class="absolute inset-0 bg-slate-900/20 backdrop-blur-sm transition-opacity drawer-backdrop-in" 
      ></div>
      
      <div id="drawerContent" class="relative w-full sm:w-[480px] bg-white shadow-2xl border-l border-slate-200 flex flex-col h-full drawer-slide-in">
        
        <div class="h-16 px-6 border-b border-slate-100 flex items-center justify-between bg-slate-50 flex-shrink-0">
          <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-full bg-slate-800 text-white flex items-center justify-center font-bold text-sm" id="drawerAvatar">
              U
            </div>
            <div>
              <h3 class="font-bold text-slate-800 text-sm" id="drawerUserName">User Name</h3>
              <p class="text-[10px] text-slate-500" id="drawerUserId">ID</p>
            </div>
          </div>
          <button id="closeDrawerBtn" class="text-slate-400 hover:text-slate-600 p-2 hover:bg-slate-200 rounded-full">
            <i data-lucide="x" class="w-5 h-5"></i>
          </button>
        </div>

        <div class="flex-1 overflow-y-auto p-6 space-y-6" id="drawerBody">
          <!-- Content will be populated by JavaScript -->
        </div>
      </div>
    </div>

  </div>

</div>

<!-- Toast Notification Container -->
<div id="toastContainer" class="fixed top-4 right-4 z-50 space-y-2"></div>

<script>
  // Initialize Lucide icons
  console.log('=== Users Page Loading ===');
  console.log('Lucide available:', typeof lucide !== 'undefined');
  lucide.createIcons();
  console.log('Lucide icons initialized');
</script>
<script src="/assets/users-admin.js"></script>
<script>
  console.log('users-admin.js loaded');
  console.log('openDrawerForUser function available:', typeof openDrawerForUser !== 'undefined');
</script>

</body>
</html>
