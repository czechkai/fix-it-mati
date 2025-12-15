<?php
// Admin Announcements Management Page
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Announcements - Admin - FixItMati</title>
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
  <link rel="stylesheet" href="/assets/announcements-admin.css">
  <style>
    @keyframes fade-in {
      from { opacity: 0; transform: translateY(10px); }
      to { opacity: 1; transform: translateY(0); }
    }
    .animate-in {
      animation: fade-in 0.3s ease-out;
    }
    
    @keyframes zoom-in {
      from { transform: scale(0.95); opacity: 0; }
      to { transform: scale(1); opacity: 1; }
    }
    .zoom-in {
      animation: zoom-in 0.2s ease-out;
    }
    
    /* Hide all scrollbars */
    ::-webkit-scrollbar {
      width: 0px;
      height: 0px;
    }
    * {
      scrollbar-width: none;
      -ms-overflow-style: none;
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
        <a href="/admin/technicians.php" class="nav-item w-full flex items-center justify-between px-3 py-3 rounded-lg text-sm font-medium transition-colors hover:bg-slate-800 hover:text-white">
          <div class="flex items-center gap-3">
            <i data-lucide="hammer" class="w-5 h-5"></i>
            <span>Technicians</span>
          </div>
        </a>
        <a href="/admin/announcements.php" class="nav-item active w-full flex items-center justify-between px-3 py-3 rounded-lg text-sm font-medium transition-colors bg-blue-600 text-white shadow-md">
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
              placeholder="Search announcements..." 
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
              <p class="text-sm font-bold text-slate-800" id="adminName">Admin User</p>
              <p class="text-xs text-slate-500" id="adminRole">Mati City Hall</p>
            </div>
            <div class="h-9 w-9 rounded-full bg-slate-800 text-white flex items-center justify-center font-bold" id="adminAvatar">AD</div>
          </div>
        </div>
      </header>

      <!-- Scrollable Canvas -->
      <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8 bg-slate-100">
        
        <div class="space-y-6 animate-in">
          
          <!-- Page Header -->
          <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
              <h2 class="text-2xl font-bold text-slate-800">Announcements</h2>
              <p class="text-sm text-slate-500">Manage public advisories and notifications.</p>
            </div>
            <button 
              id="createAnnouncementBtn"
              class="px-3 py-2 bg-blue-600 text-white rounded-lg text-sm font-bold hover:bg-blue-700 flex items-center gap-2 shadow-sm"
            >
              <i data-lucide="plus" class="w-4 h-4"></i> Create Announcement
            </button>
          </div>

          <!-- Stats Row -->
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm flex items-center justify-between">
              <div>
                <p class="text-slate-500 text-xs font-bold uppercase">Active Alerts</p>
                <h3 class="text-2xl font-bold text-slate-800 mt-1" id="statActiveAlerts">0</h3>
              </div>
              <div class="p-3 bg-green-100 text-green-600 rounded-lg">
                <i data-lucide="check-circle-2" class="w-6 h-6"></i>
              </div>
            </div>
            <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm flex items-center justify-between">
              <div>
                <p class="text-slate-500 text-xs font-bold uppercase">Total Reach</p>
                <h3 class="text-2xl font-bold text-blue-600 mt-1" id="statTotalReach">0</h3>
                <p class="text-[10px] text-slate-400">Views this week</p>
              </div>
              <div class="p-3 bg-blue-100 text-blue-600 rounded-lg">
                <i data-lucide="eye" class="w-6 h-6"></i>
              </div>
            </div>
            <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm flex items-center justify-between">
              <div>
                <p class="text-slate-500 text-xs font-bold uppercase">Urgent Sent</p>
                <h3 class="text-2xl font-bold text-red-600 mt-1" id="statUrgentSent">0</h3>
                <p class="text-[10px] text-slate-400">Via SMS Broadcast</p>
              </div>
              <div class="p-3 bg-red-100 text-red-600 rounded-lg">
                <i data-lucide="alert-triangle" class="w-6 h-6"></i>
              </div>
            </div>
          </div>

          <!-- Content Area -->
          <div class="bg-white rounded-xl border border-slate-200 shadow-sm flex flex-col min-h-[500px]">
            
            <!-- Filters -->
            <div class="p-4 border-b border-slate-100 flex items-center gap-2 overflow-x-auto">
              <button data-filter="All" class="filter-btn px-4 py-2 rounded-lg text-sm font-medium transition-colors whitespace-nowrap bg-slate-800 text-white">
                All
              </button>
              <button data-filter="published" class="filter-btn px-4 py-2 rounded-lg text-sm font-medium transition-colors whitespace-nowrap text-slate-600 hover:bg-slate-50">
                Published
              </button>
              <button data-filter="draft" class="filter-btn px-4 py-2 rounded-lg text-sm font-medium transition-colors whitespace-nowrap text-slate-600 hover:bg-slate-50">
                Draft
              </button>
              <button data-filter="archived" class="filter-btn px-4 py-2 rounded-lg text-sm font-medium transition-colors whitespace-nowrap text-slate-600 hover:bg-slate-50">
                Archived
              </button>
            </div>

            <!-- List -->
            <div id="announcementsList" class="divide-y divide-slate-100">
              <!-- Announcements will be inserted here by JavaScript -->
            </div>

          </div>

        </div>

      </main>

    </div>
  </div>

  <!-- CREATE/EDIT MODAL -->
  <div id="announcementModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
    <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" id="modalBackdrop"></div>
    <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-lg overflow-hidden zoom-in">
      
      <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50">
        <h3 class="font-bold text-slate-800" id="modalTitle">Compose Announcement</h3>
        <button id="closeModalBtn" class="text-slate-400 hover:text-slate-600">
          <i data-lucide="x" class="w-5 h-5"></i>
        </button>
      </div>
      
      <form id="announcementForm" class="p-6 space-y-4">
        
        <input type="hidden" id="announcementId" />
        
        <div>
          <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Headline</label>
          <input 
            type="text" 
            id="announcementTitle" 
            class="w-full border border-slate-200 rounded-lg p-2.5 text-sm outline-none focus:ring-2 focus:ring-blue-500" 
            placeholder="e.g. Emergency Water Interruption" 
            required 
          />
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Category</label>
            <select id="announcementCategory" class="w-full border border-slate-200 rounded-lg p-2.5 text-sm bg-white outline-none focus:ring-2 focus:ring-blue-500">
              <option value="water">Water Supply</option>
              <option value="electricity">Electricity</option>
            </select>
          </div>
          <div>
            <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Type</label>
            <select id="announcementType" class="w-full border border-slate-200 rounded-lg p-2.5 text-sm bg-white outline-none focus:ring-2 focus:ring-blue-500">
              <option value="urgent">Urgent (Red)</option>
              <option value="maintenance">Maintenance (Yellow)</option>
              <option value="info">Info (Blue)</option>
              <option value="news">News</option>
            </select>
          </div>
        </div>

        <div>
          <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Content</label>
          <textarea 
            id="announcementContent" 
            rows="5" 
            class="w-full border border-slate-200 rounded-lg p-2.5 text-sm outline-none focus:ring-2 focus:ring-blue-500 resize-none" 
            placeholder="Enter the details of the advisory here..." 
            required
          ></textarea>
        </div>

        <!-- Broadcast Options -->
        <div class="bg-slate-50 p-4 rounded-lg border border-slate-200 space-y-3">
          <p class="text-xs font-bold text-slate-500 uppercase">Broadcast Options</p>
          <label class="flex items-center gap-2 cursor-pointer">
            <input type="checkbox" id="pinToDashboard" class="w-4 h-4 text-blue-600 rounded focus:ring-blue-500" />
            <span class="text-sm text-slate-700">Pin to Dashboard Top</span>
          </label>
          <label class="flex items-center gap-2 cursor-pointer">
            <input type="checkbox" id="sendSms" class="w-4 h-4 text-blue-600 rounded focus:ring-blue-500" />
            <span class="text-sm text-slate-700">Send SMS Alert (Urgent)</span>
          </label>
        </div>

        <div class="pt-2 flex gap-3">
          <button 
            type="button" 
            id="saveDraftBtn"
            class="flex-1 bg-white border border-slate-200 text-slate-600 font-bold py-2.5 rounded-lg text-sm hover:bg-slate-50 transition-colors"
          >
            Save Draft
          </button>
          <button 
            type="submit" 
            class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 rounded-lg text-sm shadow-sm flex items-center justify-center gap-2 transition-colors"
          >
            <i data-lucide="send" class="w-4 h-4"></i> Publish Now
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
  <script src="/assets/api-client.js"></script>
  <script src="/assets/announcements-admin.js"></script>

</body>
</html>
