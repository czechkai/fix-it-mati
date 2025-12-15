<?php
// Admin Service Requests Page
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Service Requests - Admin - FixItMati</title>
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
  <link rel="stylesheet" href="/assets/service-requests-admin.css">
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
    
    /* Toast animations */
    @keyframes slide-in-from-top-5 {
      from { transform: translateY(-20px); opacity: 0; }
      to { transform: translateY(0); opacity: 1; }
    }
    @keyframes fade-out {
      from { opacity: 1; }
      to { opacity: 0; }
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
        <a href="/admin/service-requests.php" class="nav-item active w-full flex items-center justify-between px-3 py-3 rounded-lg text-sm font-medium transition-colors bg-blue-600 text-white shadow-md">
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
        <a href="/admin/users.php" class="nav-item w-full flex items-center justify-between px-3 py-3 rounded-lg text-sm font-medium transition-colors hover:bg-slate-800 hover:text-white">
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
        <a href="/admin/analytics.php" class="nav-item w-full flex items-center justify-between px-3 py-3 rounded-lg text-sm font-medium transition-colors hover:bg-slate-800 hover:text-white">
          <div class="flex items-center gap-3">
            <i data-lucide="bar-chart-3" class="w-5 h-5"></i>
            <span>Analytics</span>
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
            <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 w-[18px] h-[18px]"></i>
            <input 
              type="text" 
              id="searchInput"
              placeholder="Search ticket ID, citizen name, or technician..." 
              class="w-full pl-10 pr-4 py-2 bg-slate-100 border-transparent focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-200 rounded-lg text-sm transition-all outline-none"
            />
          </div>
        </div>
        <div class="flex items-center gap-4">
          <!-- Notification Button with Dropdown -->
          <div class="relative">
            <button class="relative p-2 text-slate-500 hover:bg-slate-100 rounded-full transition-colors" id="notificationBtn">
              <i data-lucide="bell" class="w-5 h-5"></i>
              <span id="notificationDot" class="absolute top-1.5 right-1.5 h-2 w-2 bg-red-500 rounded-full border border-white"></span>
            </button>
            <!-- Notification Dropdown -->
            <div id="notificationDropdown" class="hidden absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-xl border border-slate-200 z-50">
              <div class="p-4 border-b border-slate-100">
                <h3 class="font-bold text-slate-800 text-sm">Notifications</h3>
              </div>
              <div class="max-h-96 overflow-y-auto">
                <div class="p-4 hover:bg-slate-50 cursor-pointer border-b border-slate-50">
                  <div class="flex gap-3">
                    <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0">
                      <i data-lucide="ticket" class="w-4 h-4 text-blue-600"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                      <p class="text-sm text-slate-800 font-medium">New Service Request</p>
                      <p class="text-xs text-slate-500 mt-0.5">Water leak reported at Barangay Centro</p>
                      <p class="text-xs text-slate-400 mt-1">2 minutes ago</p>
                    </div>
                  </div>
                </div>
                <div class="p-4 text-center text-sm text-slate-500">
                  <p>No new notifications</p>
                </div>
              </div>
              <div class="p-3 border-t border-slate-100 text-center">
                <button class="text-xs font-medium text-blue-600 hover:text-blue-700">View All</button>
              </div>
            </div>
          </div>
          
          <div class="h-8 w-px bg-slate-200 mx-1"></div>
          
          <!-- Profile Dropdown -->
          <div class="relative">
            <div class="flex items-center gap-3 cursor-pointer hover:bg-slate-50 p-1.5 rounded-lg transition-colors" id="profileDropdownBtn">
              <div class="text-right hidden sm:block">
                <p class="text-sm font-bold text-slate-800" id="adminName">Admin User</p>
                <p class="text-xs text-slate-500" id="adminRole">City Engineering Office</p>
              </div>
              <div class="h-9 w-9 rounded-full bg-slate-800 text-white flex items-center justify-center font-bold" id="adminAvatar">AD</div>
            </div>
            <!-- Profile Dropdown Menu -->
            <div id="profileDropdown" class="hidden absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-xl border border-slate-200 z-50">
              <div class="p-3 border-b border-slate-100">
                <p class="text-sm font-bold text-slate-800" id="dropdownName">Admin User</p>
                <p class="text-xs text-slate-500" id="dropdownRole">System Administrator</p>
              </div>
              <div class="py-2">
                <a href="/admin/profile.php" class="flex items-center gap-3 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">
                  <i data-lucide="user" class="w-4 h-4"></i>
                  <span>View Profile</span>
                </a>
                <a href="/admin/settings.php" class="flex items-center gap-3 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">
                  <i data-lucide="settings" class="w-4 h-4"></i>
                  <span>Settings</span>
                </a>
                <a href="/admin/help.php" class="flex items-center gap-3 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">
                  <i data-lucide="help-circle" class="w-4 h-4"></i>
                  <span>Help & Support</span>
                </a>
              </div>
              <div class="py-2 border-t border-slate-100">
                <button id="logoutBtnHeader" class="flex items-center gap-3 px-4 py-2 text-sm text-red-600 hover:bg-red-50 w-full text-left">
                  <i data-lucide="log-out" class="w-4 h-4"></i>
                  <span>Logout</span>
                </button>
              </div>
            </div>
          </div>
        </div>
      </header>

      <!-- SERVICE REQUESTS MODULE -->
      <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8 bg-slate-100 flex flex-col">
        
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 flex-shrink-0 mb-6">
          <div>
            <h2 class="text-2xl font-bold text-slate-800">Service Requests</h2>
            <p class="text-sm text-slate-500">Manage and assign incoming citizen reports.</p>
          </div>
          <div class="flex gap-2">
            <button id="exportBtn" class="px-3 py-2 bg-white border border-slate-200 rounded-lg text-sm font-medium text-slate-600 hover:bg-slate-50 flex items-center gap-2">
              <i data-lucide="download" class="w-4 h-4"></i>
              Export CSV
            </button>
            <button id="manualTicketBtn" class="px-3 py-2 bg-blue-600 text-white rounded-lg text-sm font-bold hover:bg-blue-700 flex items-center gap-2 shadow-sm">
              <i data-lucide="plus" class="w-4 h-4"></i>
              Manual Ticket
            </button>
          </div>
        </div>

        <!-- Filters Bar -->
        <div class="flex items-center gap-2 overflow-x-auto pb-4 flex-shrink-0">
          <button data-status="All" class="filter-btn active px-4 py-2 rounded-full text-sm font-medium transition-colors whitespace-nowrap border bg-slate-800 text-white border-slate-800">
            All
          </button>
          <button data-status="Pending" class="filter-btn px-4 py-2 rounded-full text-sm font-medium transition-colors whitespace-nowrap border bg-white text-slate-600 border-slate-200 hover:bg-slate-50">
            Pending
          </button>
          <button data-status="In Progress" class="filter-btn px-4 py-2 rounded-full text-sm font-medium transition-colors whitespace-nowrap border bg-white text-slate-600 border-slate-200 hover:bg-slate-50">
            In Progress
          </button>
          <button data-status="Resolved" class="filter-btn px-4 py-2 rounded-full text-sm font-medium transition-colors whitespace-nowrap border bg-white text-slate-600 border-slate-200 hover:bg-slate-50">
            Resolved
          </button>
        </div>

        <!-- Ticket Table -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden flex-1 flex flex-col min-h-[400px]">
          <div class="overflow-x-auto flex-1">
            <table class="w-full text-left text-sm">
              <thead class="bg-slate-50 text-slate-500 border-b border-slate-200 sticky top-0 z-10">
                <tr>
                  <th class="px-6 py-4 font-semibold w-32">Ticket ID</th>
                  <th class="px-6 py-4 font-semibold">Issue Details</th>
                  <th class="px-6 py-4 font-semibold w-40">Priority</th>
                  <th class="px-6 py-4 font-semibold w-40">Assigned To</th>
                  <th class="px-6 py-4 font-semibold w-32">Status</th>
                  <th class="px-6 py-4 font-semibold w-16 text-right"></th>
                </tr>
              </thead>
              <tbody id="ticketsTableBody" class="divide-y divide-slate-100">
                <tr>
                  <td colspan="6" class="px-6 py-12 text-center text-slate-500">
                    <div class="flex flex-col items-center gap-3">
                      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                      <p>Loading service requests...</p>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          
          <div class="p-4 border-t border-slate-200 bg-slate-50 text-xs text-slate-500 flex justify-between items-center flex-shrink-0">
            <span id="ticketCount">Showing 0 tickets</span>
            <div class="flex gap-2">
              <button class="px-3 py-1 bg-white border border-slate-200 rounded hover:bg-slate-50 disabled:opacity-50" disabled>Previous</button>
              <button class="px-3 py-1 bg-white border border-slate-200 rounded hover:bg-slate-50">Next</button>
            </div>
          </div>
        </div>

      </main>

      <!-- SLIDE-OUT DRAWER -->
      <div id="ticketDrawer" class="hidden fixed inset-0 flex justify-end" style="z-index: 9999;">
        <div id="drawerBackdrop" class="absolute inset-0 bg-slate-900/20 backdrop-blur-sm drawer-backdrop-in cursor-pointer"></div>
        
        <div id="drawerPanel" class="relative w-full sm:w-[480px] bg-white shadow-2xl border-l border-slate-200 flex flex-col h-full drawer-slide-in overflow-hidden">
          
          <!-- Drawer Header -->
          <div class="h-16 px-6 border-b border-slate-200 flex items-center justify-between bg-white flex-shrink-0">
            <div class="flex items-center gap-3">
              <span id="drawerTicketId" class="font-mono font-bold text-slate-600 text-sm"></span>
              <span id="drawerStatusBadge" class="text-xs font-bold px-2 py-1 rounded border"></span>
            </div>
            <button id="closeDrawer" class="text-slate-400 hover:text-slate-600 p-2 hover:bg-slate-100 rounded transition-colors">
              <i data-lucide="x" class="w-5 h-5"></i>
            </button>
          </div>

          <div class="flex-1 overflow-y-auto p-6 space-y-6">
            
            <!-- Issue Details -->
            <div>
              <h2 id="drawerIssueTitle" class="text-xl font-bold text-slate-800 mb-2"></h2>
              <div id="drawerDescription" class="bg-slate-50 border border-slate-100 p-4 rounded-lg text-sm text-slate-700 leading-relaxed"></div>
            </div>

            <!-- Request Information -->
            <div class="grid grid-cols-2 gap-4">
              <div class="space-y-1 col-span-2">
                <label class="text-xs font-bold text-slate-500 uppercase">
                  Category
                </label>
                <p id="drawerCategory" class="text-sm font-semibold text-slate-800 bg-slate-100 px-3 py-2 rounded-lg inline-block"></p>
              </div>
              <div class="space-y-1">
                <label class="text-xs font-bold text-slate-500 uppercase">
                  Reported By
                </label>
                <p id="drawerCitizen" class="text-sm font-medium text-slate-800"></p>
              </div>
              <div class="space-y-1">
                <label class="text-xs font-bold text-slate-500 uppercase">
                  Date Reported
                </label>
                <p id="drawerDate" class="text-sm font-medium text-slate-800"></p>
              </div>
              <div class="space-y-1 col-span-2">
                <label class="text-xs font-bold text-slate-500 uppercase">
                  Location
                </label>
                <p id="drawerLocation" class="text-sm font-medium text-slate-800"></p>
              </div>
            </div>

            <!-- Management Section -->
            <div class="border-t border-slate-100 pt-6">
              <h3 class="text-sm font-bold text-slate-800 mb-4">Manage Request</h3>
              <div class="space-y-4">
                <div>
                  <label class="text-xs font-bold text-slate-500 uppercase mb-2 block">
                    Assign Technician
                  </label>
                  <select id="technicianSelect" class="w-full border border-slate-200 rounded-lg p-2.5 text-sm font-medium outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                    <option value="">Select Technician...</option>
                  </select>
                </div>
                
                <div class="space-y-2">
                  <button id="updateAssignmentBtn" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-lg text-sm transition-colors flex items-center justify-center gap-2">
                    <i data-lucide="user-check" class="w-4 h-4"></i>
                    Update Assignment
                  </button>
                  
                  <button id="markResolvedBtn" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 rounded-lg text-sm transition-colors">
                    Mark as Resolved
                  </button>
                </div>
              </div>
            </div>

            <!-- Internal Notes -->
            <div class="border-t border-slate-100 pt-6">
              <h3 class="text-sm font-bold text-slate-800 mb-3">Internal Notes</h3>
              <div id="notesContainer" class="space-y-3">
                <p class="text-sm text-slate-500 italic">No notes yet.</p>
              </div>
              <div class="mt-4 relative">
                <input type="text" id="newNoteInput" placeholder="Add a note..." class="w-full border border-slate-200 rounded-lg pl-3 pr-10 py-2 text-sm outline-none focus:border-blue-500" />
                <button id="sendNoteBtn" class="absolute right-2 top-2 text-blue-600 hover:text-blue-800">
                  <i data-lucide="send" class="w-4 h-4"></i>
                </button>
              </div>
            </div>

          </div>
        </div>
      </div>

    </div>
  </div>

  <!-- Debug: Check if scripts are loading -->
  <script>
    console.log('=== Scripts Section Start ===');
    console.log('Document ready state:', document.readyState);
  </script>
  
  <!-- API Client -->
  <script src="/assets/api-client.js"></script>
  <script>console.log('API Client loaded, ApiClient available:', typeof ApiClient !== 'undefined');</script>
  
  <!-- Service Requests Admin JS -->
  <script src="/assets/service-requests-admin.js"></script>
  <script>console.log('Service Requests Admin JS loaded');</script>
  
  <!-- Initialize Lucide Icons -->
  <script>
    console.log('Lucide available:', typeof lucide !== 'undefined');
    // Initialize icons after page load
    if (typeof lucide !== 'undefined') {
      lucide.createIcons();
      console.log('Lucide icons initialized');
    } else {
      console.error('Lucide library not loaded!');
    }
  </script>
</body>
</html>
