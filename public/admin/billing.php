<?php
// Admin Billing & Payments Page
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Billing & Payments - Admin - FixItMati</title>
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
  <link rel="stylesheet" href="/assets/billing-admin.css">
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
    
    /* Hide all scrollbars */
    ::-webkit-scrollbar {
      width: 0px;
      height: 0px;
    }
    * {
      scrollbar-width: none;
      -ms-overflow-style: none;
    }
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
        </a>
        <a href="/admin/billing.php" class="nav-item active w-full flex items-center justify-between px-3 py-3 rounded-lg text-sm font-medium transition-colors bg-blue-600 text-white shadow-md">
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
              placeholder="Search transaction ID, citizen, or amount..." 
              class="w-full pl-10 pr-4 py-2 bg-slate-100 border-transparent focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-200 rounded-lg text-sm transition-all outline-none"
            />
          </div>
        </div>
        <div class="flex items-center gap-4">
          <!-- Notification Button -->
          <div class="relative">
            <button class="relative p-2 text-slate-500 hover:bg-slate-100 rounded-full transition-colors" id="notificationBtn">
              <i data-lucide="bell" class="w-5 h-5"></i>
              <span id="notificationDot" class="absolute top-1.5 right-1.5 h-2 w-2 bg-red-500 rounded-full border border-white"></span>
            </button>
          </div>
          
          <div class="h-8 w-px bg-slate-200 mx-1"></div>
          
          <!-- Profile -->
          <div class="flex items-center gap-3 cursor-pointer hover:bg-slate-50 p-1.5 rounded-lg transition-colors">
            <div class="text-right hidden sm:block">
              <p class="text-sm font-bold text-slate-800" id="adminName">Admin User</p>
              <p class="text-xs text-slate-500">City Treasury Office</p>
            </div>
            <div class="h-9 w-9 rounded-full bg-slate-800 text-white flex items-center justify-center font-bold" id="adminAvatar">AD</div>
          </div>
        </div>
      </header>

      <!-- BILLING MODULE -->
      <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8 bg-slate-100">
        
        <div class="space-y-6 animate-in">
          
          <!-- Page Header -->
          <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
              <h2 class="text-2xl font-bold text-slate-800">Billing Overview</h2>
              <p class="text-sm text-slate-500">Monitor revenue and verify payment transactions.</p>
            </div>
            <div class="flex gap-2">
              <button id="exportBtn" class="px-3 py-2 bg-white border border-slate-200 rounded-lg text-sm font-medium text-slate-600 hover:bg-slate-50 flex items-center gap-2">
                <i data-lucide="download" class="w-4 h-4"></i>
                Export Report
              </button>
              <button id="createInvoiceBtn" class="px-3 py-2 bg-blue-600 text-white rounded-lg text-sm font-bold hover:bg-blue-700 flex items-center gap-2 shadow-sm">
                <i data-lucide="plus" class="w-4 h-4"></i>
                Create Invoice
              </button>
            </div>
          </div>

          <!-- Financial Stats -->
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            
            <!-- Total Revenue -->
            <div class="bg-gradient-to-br from-blue-600 to-blue-800 rounded-xl p-6 text-white shadow-lg relative overflow-hidden">
              <div class="absolute top-0 right-0 p-4 opacity-10">
                <i data-lucide="credit-card" class="w-16 h-16"></i>
              </div>
              <p class="text-blue-100 text-sm font-medium mb-1">Total Revenue (Current Month)</p>
              <h3 class="text-3xl font-bold mb-2" id="totalRevenue">₱0.00</h3>
              <div class="flex items-center gap-1 text-xs bg-white/20 w-fit px-2 py-1 rounded-full">
                <i data-lucide="arrow-up-right" class="w-3 h-3"></i>
                <span id="revenueGrowth">+0% from last month</span>
              </div>
            </div>

            <!-- Pending Verification -->
            <div class="bg-white rounded-xl p-6 border border-slate-200 shadow-sm relative overflow-hidden">
              <p class="text-slate-500 text-sm font-medium mb-1">Pending Verification</p>
              <h3 class="text-3xl font-bold mb-2 text-amber-600" id="pendingCount">0</h3>
              <p class="text-xs text-slate-400">Transactions requiring manual approval</p>
              <div class="w-full bg-slate-100 h-1.5 rounded-full mt-4">
                <div class="bg-amber-500 h-1.5 rounded-full" id="pendingProgress" style="width: 0%"></div>
              </div>
            </div>

            <!-- Collection Rate -->
            <div class="bg-white rounded-xl p-6 border border-slate-200 shadow-sm">
              <p class="text-slate-500 text-sm font-medium mb-1">Collection Rate</p>
              <h3 class="text-3xl font-bold mb-2 text-green-600" id="collectionRate">0%</h3>
              <div class="flex items-center gap-1 text-xs text-green-700 bg-green-50 w-fit px-2 py-1 rounded-full">
                <i data-lucide="arrow-up-right" class="w-3 h-3"></i>
                <span id="collectionGrowth">+0% vs target</span>
              </div>
            </div>
          </div>

          <!-- Transactions Table Section -->
          <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden flex flex-col min-h-[400px]">
            
            <!-- Table Controls -->
            <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row justify-between items-center gap-4">
              <h3 class="font-bold text-slate-800">Recent Transactions</h3>
              <div class="flex items-center gap-2 overflow-x-auto pb-1 w-full sm:w-auto">
                <button data-status="All" class="filter-btn active px-3 py-1.5 rounded-lg text-xs font-medium transition-colors border bg-slate-800 text-white border-slate-800">
                  All
                </button>
                <button data-status="Success" class="filter-btn px-3 py-1.5 rounded-lg text-xs font-medium transition-colors border bg-white text-slate-600 border-slate-200 hover:bg-slate-50">
                  Success
                </button>
                <button data-status="Pending" class="filter-btn px-3 py-1.5 rounded-lg text-xs font-medium transition-colors border bg-white text-slate-600 border-slate-200 hover:bg-slate-50">
                  Pending
                </button>
                <button data-status="Failed" class="filter-btn px-3 py-1.5 rounded-lg text-xs font-medium transition-colors border bg-white text-slate-600 border-slate-200 hover:bg-slate-50">
                  Failed
                </button>
              </div>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto flex-1">
              <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-slate-500 border-b border-slate-200 sticky top-0">
                  <tr>
                    <th class="px-6 py-4 font-semibold">Transaction ID</th>
                    <th class="px-6 py-4 font-semibold">Citizen</th>
                    <th class="px-6 py-4 font-semibold">Type</th>
                    <th class="px-6 py-4 font-semibold">Amount</th>
                    <th class="px-6 py-4 font-semibold">Method</th>
                    <th class="px-6 py-4 font-semibold">Status</th>
                    <th class="px-6 py-4 font-semibold text-right">Action</th>
                  </tr>
                </thead>
                <tbody id="transactionsTableBody" class="divide-y divide-slate-100">
                  <tr>
                    <td colspan="7" class="px-6 py-12 text-center text-slate-500">
                      <div class="flex flex-col items-center gap-3">
                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                        <p>Loading transactions...</p>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- Footer -->
            <div class="p-4 border-t border-slate-200 bg-slate-50 text-xs text-slate-500 flex justify-between items-center">
              <span id="transactionCount">Showing 0 transactions</span>
              <div class="flex gap-2">
                <button id="prevBtn" class="px-3 py-1 bg-white border border-slate-200 rounded hover:bg-slate-50 disabled:opacity-50" disabled>Previous</button>
                <button id="nextBtn" class="px-3 py-1 bg-white border border-slate-200 rounded hover:bg-slate-50">Next</button>
              </div>
            </div>
          </div>

        </div>

      </main>

      <!-- CREATE INVOICE MODAL -->
      <div id="invoiceModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm drawer-backdrop-in" id="invoiceModalBackdrop"></div>
        <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-md overflow-hidden modal-zoom-in">
          
          <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50">
            <h3 class="font-bold text-slate-800">Generate New Invoice</h3>
            <button id="closeInvoiceModal" class="text-slate-400 hover:text-slate-600">
              <i data-lucide="x" class="w-5 h-5"></i>
            </button>
          </div>
          
          <form class="p-6 space-y-4" id="invoiceForm">
            
            <!-- Citizen Select -->
            <div>
              <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Citizen Account</label>
              <select id="citizenSelect" name="user_id" class="w-full border border-slate-200 rounded-lg p-2.5 text-sm bg-white outline-none focus:ring-2 focus:ring-blue-500" required>
                <option value="">Select User...</option>
              </select>
            </div>

            <!-- Bill Type & Amount -->
            <div class="flex gap-4">
              <div class="flex-1">
                <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Bill Type</label>
                <select id="billType" name="bill_type" class="w-full border border-slate-200 rounded-lg p-2.5 text-sm bg-white outline-none focus:ring-2 focus:ring-blue-500" required>
                  <option value="">Select Type...</option>
                  <option value="water">Water Bill</option>
                  <option value="electric">Electric Bill</option>
                </select>
              </div>
              <div class="flex-1">
                <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Amount (PHP)</label>
                <input type="number" id="billAmount" name="amount" step="0.01" min="0" class="w-full border border-slate-200 rounded-lg p-2.5 text-sm outline-none focus:ring-2 focus:ring-blue-500" placeholder="0.00" required />
              </div>
            </div>

            <!-- Due Date -->
            <div>
              <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Due Date</label>
              <div class="relative">
                <input type="date" id="dueDate" name="due_date" class="w-full border border-slate-200 rounded-lg p-2.5 text-sm outline-none focus:ring-2 focus:ring-blue-500" required />
                <i data-lucide="calendar" class="absolute right-3 top-2.5 text-slate-400 pointer-events-none w-4 h-4"></i>
              </div>
            </div>

            <!-- Description -->
            <div>
              <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Description / Notes</label>
              <textarea id="billDescription" name="description" rows="3" class="w-full border border-slate-200 rounded-lg p-2.5 text-sm outline-none focus:ring-2 focus:ring-blue-500 resize-none" placeholder="Reason for manual billing..."></textarea>
            </div>

            <!-- Actions -->
            <div class="pt-2 flex gap-3">
              <button type="button" id="cancelInvoiceBtn" class="flex-1 bg-white border border-slate-200 text-slate-600 font-bold py-2.5 rounded-lg text-sm hover:bg-slate-50 transition-colors">
                Cancel
              </button>
              <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 rounded-lg text-sm shadow-sm flex items-center justify-center gap-2 transition-colors">
                <i data-lucide="save" class="w-4 h-4"></i> Send Invoice
              </button>
            </div>

          </form>
        </div>
      </div>

      <!-- TRANSACTION DETAILS DRAWER -->
      <div id="transactionDrawer" class="hidden absolute inset-0 z-50 flex justify-end">
        <div class="absolute inset-0 bg-slate-900/20 backdrop-blur-sm transition-opacity drawer-backdrop-in" id="drawerBackdrop"></div>
        
        <div class="relative w-full sm:w-[400px] bg-white shadow-2xl border-l border-slate-200 flex flex-col h-full drawer-slide-in">
          
          <div class="h-16 px-6 border-b border-slate-100 flex items-center justify-between bg-slate-50 flex-shrink-0">
            <h3 class="font-bold text-slate-800">Transaction Details</h3>
            <button id="closeDrawer" class="text-slate-400 hover:text-slate-600 p-2 hover:bg-slate-200 rounded-full">
              <i data-lucide="x" class="w-5 h-5"></i>
            </button>
          </div>

          <div class="flex-1 overflow-y-auto p-6 space-y-6" id="drawerContent">
            <!-- Content will be populated dynamically -->
          </div>
        </div>
      </div>

    </div>
  </div>

  <!-- Initialize Lucide Icons -->
  <script>
    lucide.createIcons();
  </script>

  <!-- Main Billing Admin Script -->
  <script src="/assets/billing-admin.js"></script>

</body>
</html>
