<?php
// Admin Dashboard for FixItMati
// Requires admin authentication
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Admin Dashboard - FixItMati</title>
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
          window.location.replace('../pages/auth/login.php');
          throw new Error('Invalid user data');
        }
      }
    })();
  </script>
  <!-- Tailwind CSS -->
  <script src="https://cdn.tailwindcss.com"></script>
  <!-- Lucide Icons -->
  <script src="https://unpkg.com/lucide@latest"></script>
  <!-- App styles -->
  <link rel="stylesheet" href="/assets/style.css" />
  <style>
    @keyframes fade-in {
      from { opacity: 0; transform: translateY(10px); }
      to { opacity: 1; transform: translateY(0); }
    }
    .animate-in {
      animation: fade-in 0.3s ease-out;
    }
  </style>
</head>
<body class="min-h-screen bg-slate-100 font-sans text-slate-800">

  <div class="flex min-h-screen">
    
    <!-- SIDEBAR -->
    <aside class="w-64 bg-slate-900 text-slate-300 flex-shrink-0 hidden lg:flex flex-col">
      <div class="h-16 flex items-center gap-3 px-6 border-b border-slate-800">
        <div class="bg-blue-600 p-1.5 rounded-lg">
          <i data-lucide="hammer" class="text-white w-5 h-5"></i>
        </div>
        <span class="text-lg font-bold text-white tracking-tight">
          FixItMati 
          <span class="text-xs font-normal text-slate-400 bg-slate-800 px-1.5 py-0.5 rounded ml-1">ADMIN</span>
        </span>
      </div>

      <nav class="flex-1 px-4 py-6 space-y-1" id="sidebarNav">
        <button data-view="overview" class="nav-item active w-full flex items-center justify-between px-3 py-3 rounded-lg text-sm font-medium transition-colors bg-blue-600 text-white shadow-md">
          <div class="flex items-center gap-3">
            <i data-lucide="layout-dashboard" class="w-5 h-5"></i>
            <span>Overview</span>
          </div>
        </button>
        <button data-view="tickets" class="nav-item w-full flex items-center justify-between px-3 py-3 rounded-lg text-sm font-medium transition-colors hover:bg-slate-800 hover:text-white">
          <div class="flex items-center gap-3">
            <i data-lucide="ticket" class="w-5 h-5"></i>
            <span>Service Requests</span>
          </div>
          <span id="pendingBadge" class="bg-red-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full">0</span>
        </button>
        <button data-view="billing" class="nav-item w-full flex items-center justify-between px-3 py-3 rounded-lg text-sm font-medium transition-colors hover:bg-slate-800 hover:text-white">
          <div class="flex items-center gap-3">
            <i data-lucide="credit-card" class="w-5 h-5"></i>
            <span>Billing & Payments</span>
          </div>
        </button>
        <button data-view="users" class="nav-item w-full flex items-center justify-between px-3 py-3 rounded-lg text-sm font-medium transition-colors hover:bg-slate-800 hover:text-white">
          <div class="flex items-center gap-3">
            <i data-lucide="users" class="w-5 h-5"></i>
            <span>Citizen Users</span>
          </div>
        </button>
        <button data-view="technicians" class="nav-item w-full flex items-center justify-between px-3 py-3 rounded-lg text-sm font-medium transition-colors hover:bg-slate-800 hover:text-white">
          <div class="flex items-center gap-3">
            <i data-lucide="hammer" class="w-5 h-5"></i>
            <span>Technicians</span>
          </div>
        </button>
        <button data-view="announcements" class="nav-item w-full flex items-center justify-between px-3 py-3 rounded-lg text-sm font-medium transition-colors hover:bg-slate-800 hover:text-white">
          <div class="flex items-center gap-3">
            <i data-lucide="megaphone" class="w-5 h-5"></i>
            <span>Announcements</span>
          </div>
        </button>
        <button data-view="reports" class="nav-item w-full flex items-center justify-between px-3 py-3 rounded-lg text-sm font-medium transition-colors hover:bg-slate-800 hover:text-white">
          <div class="flex items-center gap-3">
            <i data-lucide="bar-chart-3" class="w-5 h-5"></i>
            <span>Analytics</span>
          </div>
        </button>
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
    <div class="flex-1 flex flex-col min-w-0">
      
      <!-- Header -->
      <header class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-4 sm:px-6 lg:px-8 sticky top-0 z-30">
        <div class="lg:hidden font-bold text-slate-800">FixItMati Admin</div>
        <div class="hidden md:flex flex-1 max-w-md ml-4">
          <div class="relative w-full">
            <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 w-[18px] h-[18px]"></i>
            <input 
              type="text" 
              id="globalSearch"
              placeholder="Search ticket ID, citizen name, or technician..." 
              class="w-full pl-10 pr-4 py-2 bg-slate-100 border-transparent focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-200 rounded-lg text-sm transition-all outline-none"
            />
          </div>
        </div>
        <div class="flex items-center gap-4">
          <button class="relative p-2 text-slate-500 hover:bg-slate-100 rounded-full" id="notificationBtn">
            <i data-lucide="bell" class="w-5 h-5"></i>
            <span id="notificationDot" class="absolute top-1.5 right-1.5 h-2 w-2 bg-red-500 rounded-full border border-white"></span>
          </button>
          <div class="h-8 w-px bg-slate-200 mx-1"></div>
          <div class="flex items-center gap-3 cursor-pointer hover:bg-slate-50 p-1.5 rounded-lg transition-colors" id="profileDropdownBtn">
            <div class="text-right hidden sm:block">
              <p class="text-sm font-bold text-slate-800" id="adminName">Admin User</p>
              <p class="text-xs text-slate-500" id="adminRole">City Engineering Office</p>
            </div>
            <div class="h-9 w-9 rounded-full bg-slate-800 text-white flex items-center justify-center font-bold" id="adminAvatar">AD</div>
          </div>
        </div>
      </header>

      <!-- Scrollable Canvas -->
      <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8">
        
        <!-- VIEW: OVERVIEW -->
        <div id="viewOverview" class="view-content space-y-6">
          <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <h2 class="text-2xl font-bold text-slate-800">Dashboard Overview</h2>
            <div class="flex gap-2">
              <button class="px-3 py-2 bg-white border border-slate-200 rounded-lg text-sm font-medium text-slate-600 hover:bg-slate-50 flex items-center gap-2">
                <i data-lucide="download" class="w-4 h-4"></i>
                Export Report
              </button>
              <button onclick="switchView('tickets')" class="px-3 py-2 bg-blue-600 text-white rounded-lg text-sm font-bold hover:bg-blue-700 flex items-center gap-2 shadow-sm">
                <i data-lucide="ticket" class="w-4 h-4"></i>
                View All Requests
              </button>
            </div>
          </div>

          <!-- Stats Cards -->
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4" id="statsGrid">
            <!-- Stats will be loaded here -->
          </div>

          <!-- Recent Tickets Table -->
          <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex justify-between items-center">
              <h3 class="font-bold text-slate-800">Recent Service Requests</h3>
              <button onclick="switchView('tickets')" class="text-sm text-blue-600 hover:underline font-medium">View All</button>
            </div>
            <div class="overflow-x-auto">
              <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-slate-500 border-b border-slate-200">
                  <tr>
                    <th class="px-5 py-3 font-medium">Ticket ID</th>
                    <th class="px-5 py-3 font-medium">Issue</th>
                    <th class="px-5 py-3 font-medium">Priority</th>
                    <th class="px-5 py-3 font-medium">Status</th>
                    <th class="px-5 py-3 font-medium text-right">Action</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-slate-100" id="recentTicketsBody">
                  <!-- Tickets will be loaded here -->
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <?php include 'tabs/service-requests-tab.php'; ?>

        <?php include 'tabs/announcements-tab.php'; ?>

        <?php include 'tabs/billing-tab.php'; ?>

        <?php include 'tabs/technicians-tab.php'; ?>

        <?php include 'tabs/users-tab.php'; ?>

        <?php include 'tabs/analytics-tab.php'; ?>

      </main>
    </div>
  </div>

  <!-- CREATE ANNOUNCEMENT MODAL -->
  <div id="announcementModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
    <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeAnnouncementModal()"></div>
    <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-lg overflow-hidden">
      <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50">
        <h3 class="font-bold text-slate-800">New Announcement</h3>
        <button onclick="closeAnnouncementModal()" class="text-slate-400 hover:text-slate-600">
          <i data-lucide="x" class="w-5 h-5"></i>
        </button>
      </div>
      <form id="announcementForm" class="p-6 space-y-4">
        <div>
          <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Title</label>
          <input type="text" name="title" required class="w-full border border-slate-200 rounded-lg p-2.5 text-sm outline-none focus:ring-2 focus:ring-blue-500" placeholder="e.g. Emergency Maintenance" />
        </div>
        <div class="flex gap-4">
          <div class="flex-1">
            <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Category</label>
            <select name="category" required class="w-full border border-slate-200 rounded-lg p-2.5 text-sm bg-white outline-none">
              <option value="Water Supply">Water Supply</option>
              <option value="Electricity">Electricity</option>
              <option value="Public Works">Public Works</option>
              <option value="General">General</option>
            </select>
          </div>
          <div class="flex-1">
            <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Type</label>
            <select name="priority" required class="w-full border border-slate-200 rounded-lg p-2.5 text-sm bg-white outline-none">
              <option value="urgent">Urgent</option>
              <option value="info">Info</option>
              <option value="maintenance">Maintenance</option>
            </select>
          </div>
        </div>
        <div>
          <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Content</label>
          <textarea name="content" required rows="4" class="w-full border border-slate-200 rounded-lg p-2.5 text-sm outline-none focus:ring-2 focus:ring-blue-500 resize-none" placeholder="Details..."></textarea>
        </div>
        <div class="pt-2">
          <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 rounded-lg flex items-center justify-center gap-2 shadow-sm">
            <i data-lucide="save" class="w-4 h-4"></i>
            Publish Announcement
          </button>
        </div>
      </form>
    </div>
  </div>

  <!-- API Client -->
  <script src="/assets/api-client.js"></script>
  <!-- Admin Dashboard JS -->
  <script src="/assets/admin-dashboard.js"></script>
</body>
</html>
