<?php
// Admin Technicians Management Page
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Field Technicians - Admin - FixItMati</title>
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
  <link rel="stylesheet" href="/assets/technicians-admin.css">
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
    @keyframes fade-in-backdrop {
      from { opacity: 0; }
      to { opacity: 1; }
    }
    .drawer-slide-in {
      animation: slide-in-right 0.3s ease-out;
    }
    .drawer-backdrop-in {
      animation: fade-in-backdrop 0.2s ease-out;
    }
    
    /* Card hover effects */
    .team-card {
      transition: all 0.2s ease;
    }
    .team-card:hover {
      transform: translateY(-2px);
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
    * {
      scrollbar-width: none;
      -ms-overflow-style: none;
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
        <a href="/admin/service-requests.php" class="nav-item w-full flex items-center gap-3 px-3 py-3 rounded-lg text-sm font-medium transition-colors hover:bg-slate-800 hover:text-white">
          <i data-lucide="ticket" class="w-5 h-5"></i>
          <span>Service Requests</span>
        </a>
        <a href="/admin/billing.php" class="nav-item w-full flex items-center justify-between px-3 py-3 rounded-lg text-sm font-medium transition-colors hover:bg-slate-800 hover:text-white">
          <div class="flex items-center gap-3">
            <i data-lucide="credit-card" class="w-5 h-5"></i>
            <span>Billing & Payments</span>
          </div>
        </a>
        <a href="/admin/users.php" class="nav-item w-full flex items-center justify-between px-3 py-3 rounded-lg text-sm font-medium transition-colors hover:bg-slate-800 hover:text-white">
          <div class="flex items-center gap-3">
            <i data-lucide="users" class="w-5 h-5"></i>
            <span>Citizen Users</span>
          </div>
        </a>
        <a href="/admin/technicians.php" class="nav-item active w-full flex items-center justify-between px-3 py-3 rounded-lg text-sm font-medium transition-colors bg-blue-600 text-white shadow-md">
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

    <!-- MAIN CONTENT AREA -->
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
              placeholder="Search team name, leader, or status..." 
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
              <h2 class="text-2xl font-bold text-slate-800">Field Technicians</h2>
              <p class="text-sm text-slate-500">Track team availability and assignments.</p>
            </div>
            <button 
              id="addTeamBtn"
              class="px-3 py-2 bg-blue-600 text-white rounded-lg text-sm font-bold hover:bg-blue-700 flex items-center gap-2 shadow-sm"
            >
              <i data-lucide="user-plus" class="w-4 h-4"></i> Register New Team
            </button>
          </div>

          <!-- Department Filter Tabs -->
          <div class="flex items-center gap-2 bg-white p-1 rounded-xl w-fit border border-slate-200">
            <button data-filter="All" class="filter-btn px-4 py-1.5 rounded-lg text-sm font-medium transition-all bg-slate-800 text-white shadow-sm">
              All
            </button>
            <button data-filter="Water" class="filter-btn px-4 py-1.5 rounded-lg text-sm font-medium transition-all text-slate-500 hover:text-slate-900 hover:bg-slate-50">
              Water
            </button>
            <button data-filter="Electric" class="filter-btn px-4 py-1.5 rounded-lg text-sm font-medium transition-all text-slate-500 hover:text-slate-900 hover:bg-slate-50">
              Electric
            </button>
          </div>

          <!-- Stats Row -->
          <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
              <div class="text-slate-500 text-xs font-bold uppercase mb-1">Total Teams</div>
              <div class="text-2xl font-bold text-slate-800" id="statTotalTeams">0</div>
            </div>
            <div class="bg-green-50 p-4 rounded-xl border border-green-100 shadow-sm">
              <div class="text-green-600 text-xs font-bold uppercase mb-1">Available Now</div>
              <div class="text-2xl font-bold text-green-700" id="statAvailable">0</div>
            </div>
            <div class="bg-red-50 p-4 rounded-xl border border-red-100 shadow-sm">
              <div class="text-red-600 text-xs font-bold uppercase mb-1">Busy / On-Site</div>
              <div class="text-2xl font-bold text-red-700" id="statBusy">0</div>
            </div>
            <div class="bg-blue-50 p-4 rounded-xl border border-blue-100 shadow-sm">
              <div class="text-blue-600 text-xs font-bold uppercase mb-1">On Route</div>
              <div class="text-2xl font-bold text-blue-700" id="statOnRoute">0</div>
            </div>
          </div>

          <!-- Loading Indicator -->
          <div id="loadingIndicator" class="hidden flex items-center justify-center py-12">
            <div class="flex flex-col items-center gap-3">
              <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
              <p class="text-sm text-slate-500 font-medium">Loading teams...</p>
            </div>
          </div>

          <!-- Team Grid -->
          <div id="teamsGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Teams will be inserted here by JavaScript -->
          </div>

        </div>

      </main>

      <!-- TEAM DETAILS DRAWER -->
      <div id="teamDrawer" class="hidden absolute inset-0 z-50 flex justify-end">
        <div 
          id="drawerBackdrop"
          class="absolute inset-0 bg-slate-900/20 backdrop-blur-sm transition-opacity drawer-backdrop-in"
        ></div>
        
        <div id="drawerContent" class="relative w-full sm:w-[450px] bg-white shadow-2xl border-l border-slate-200 flex flex-col h-full drawer-slide-in">
          
          <div class="h-16 px-6 border-b border-slate-100 flex items-center justify-between bg-slate-50 flex-shrink-0">
            <div class="flex items-center gap-3">
              <div class="w-8 h-8 rounded-full bg-slate-800 text-white flex items-center justify-center font-bold text-sm" id="drawerTeamInitial">
                A
              </div>
              <div>
                <h3 class="font-bold text-slate-800 text-sm" id="drawerTeamName">Team Name</h3>
                <p class="text-[10px] text-slate-500" id="drawerTeamId">ID</p>
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

  <!-- ADD TEAM MODAL -->
  <div id="addTeamModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
    <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" id="modalBackdrop"></div>
    <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-md overflow-hidden animate-in">
      <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50">
        <h3 class="font-bold text-slate-800">Register New Team</h3>
        <button id="closeModalBtn" class="text-slate-400 hover:text-slate-600">
          <i data-lucide="x" class="w-5 h-5"></i>
        </button>
      </div>
      
      <form id="addTeamForm" class="p-6 space-y-4">
        
        <div>
          <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Team Name</label>
          <input type="text" id="teamName" class="w-full border border-slate-200 rounded-lg p-2.5 text-sm outline-none focus:ring-2 focus:ring-blue-500" placeholder="e.g. Team Foxtrot" required />
        </div>

        <div>
          <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Department</label>
          <select id="teamDepartment" class="w-full border border-slate-200 rounded-lg p-2.5 text-sm bg-white outline-none focus:ring-2 focus:ring-blue-500" required>
            <option value="Water">Water District</option>
            <option value="Electric">Davao Oriental Electric Cooperative (DORECO)</option>
          </select>
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Team Leader</label>
            <input type="text" id="teamLead" class="w-full border border-slate-200 rounded-lg p-2.5 text-sm outline-none focus:ring-2 focus:ring-blue-500" placeholder="Name" required />
          </div>
          <div>
            <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Member Count</label>
            <input type="number" id="teamMembers" class="w-full border border-slate-200 rounded-lg p-2.5 text-sm outline-none focus:ring-2 focus:ring-blue-500" placeholder="0" min="1" required />
          </div>
        </div>

        <div>
          <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Contact Number</label>
          <input type="tel" id="teamContact" class="w-full border border-slate-200 rounded-lg p-2.5 text-sm outline-none focus:ring-2 focus:ring-blue-500" placeholder="0917-XXX-XXXX" required />
        </div>

        <div class="pt-2 flex gap-3">
          <button type="button" id="cancelModalBtn" class="flex-1 bg-white border border-slate-200 text-slate-600 font-bold py-2.5 rounded-lg text-sm hover:bg-slate-50 transition-colors">
            Cancel
          </button>
          <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 rounded-lg text-sm shadow-sm flex items-center justify-center gap-2 transition-colors">
            <i data-lucide="save" class="w-4 h-4"></i> Register
          </button>
        </div>

      </form>
    </div>
  </div>

  <!-- Toast Notification Container -->
  <div id="toastContainer" class="fixed top-4 right-4 z-50 space-y-2"></div>

  <script>
    // Initialize Lucide icons
    lucide.createIcons();
  </script>
  <script src="/assets/technicians-admin.js"></script>

</body>
</html>
