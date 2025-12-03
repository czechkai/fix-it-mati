<?php
// Simple PHP template for FixItMati Dashboard
// Splits markup, CSS, and JS for a standard web hosting environment
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>FixItMati Dashboard</title>
  <!-- Tailwind CSS via CDN for utility classes similar to original -->
  <script src="https://cdn.tailwindcss.com"></script>
  <!-- Lucide Icons CDN to replace lucide-react -->
  <script src="https://unpkg.com/lucide@latest"></script>
  <!-- App styles -->
  <link rel="stylesheet" href="../assets/style.css" />
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
          <div class="flex items-center gap-2">
            <div class="bg-blue-600 p-1.5 rounded-lg">
              <i data-lucide="hammer" class="text-white w-5 h-5"></i>
            </div>
            <span class="text-xl font-bold tracking-tight text-blue-900">FixItMati</span>
          </div>
        </div>
        <!-- Desktop Search -->
        <div class="hidden md:flex flex-1 max-w-lg mx-8 relative">
          <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <i data-lucide="search" class="h-5 w-5 text-slate-400"></i>
          </div>
          <input type="text" class="block w-full pl-10 pr-3 py-2 border border-slate-200 rounded-full leading-5 bg-slate-50 placeholder-slate-400 focus:outline-none focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition duration-150 ease-in-out" placeholder="Search requests, announcements, or help articles..." />
          <div class="absolute inset-y-0 right-0 pr-2 flex items-center">
            <span class="text-xs border border-slate-200 rounded px-1.5 py-0.5 text-slate-400">/</span>
          </div>
        </div>
        <!-- Right Actions -->
        <div class="flex items-center gap-3">
          <button class="hidden sm:flex items-center gap-2 px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-md shadow-sm transition-colors">
            <i data-lucide="plus" class="w-4 h-4"></i>
            <span>New Request</span>
          </button>
          <div class="relative p-2 text-slate-500 hover:bg-slate-100 rounded-full cursor-pointer">
            <i data-lucide="bell" class="w-5 h-5"></i>
            <span class="absolute top-1.5 right-1.5 block h-2 w-2 rounded-full bg-red-500 ring-2 ring-white"></span>
          </div>
          <div class="h-8 w-8 rounded-full bg-gradient-to-tr from-blue-500 to-cyan-400 border-2 border-white shadow-sm cursor-pointer"></div>
        </div>
      </div>
    </div>
  </header>

  <!-- SUB-NAV -->
  <div class="bg-white border-b border-slate-200 hidden md:block overflow-x-auto">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <nav class="flex -mb-px space-x-8" id="tabsNav">
        <button data-tab="dashboard" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm flex items-center gap-2 transition-colors border-blue-500 text-blue-600">
          <i data-lucide="home" class="w-4 h-4"></i> Dashboard
        </button>
        <button data-tab="my requests" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm flex items-center gap-2 transition-colors border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300">
          <i data-lucide="file-text" class="w-4 h-4"></i> My Requests
        </button>
        <button data-tab="announcements" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm flex items-center gap-2 transition-colors border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300">
          <i data-lucide="megaphone" class="w-4 h-4"></i> Announcements
        </button>
        <button data-tab="discussions" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm flex items-center gap-2 transition-colors border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300">
          <i data-lucide="message-square" class="w-4 h-4"></i> Discussions
        </button>
        <button data-tab="payments" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm flex items-center gap-2 transition-colors border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300">
          <i data-lucide="credit-card" class="w-4 h-4"></i> Payments
        </button>
      </nav>
    </div>
  </div>

  <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- HERO CARDS -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
      <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl p-5 shadow-sm text-white relative overflow-hidden group">
        <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:scale-110 transition-transform duration-500">
          <i data-lucide="clock" class="w-16 h-16"></i>
        </div>
        <div class="relative z-10">
          <div class="flex items-center gap-2 mb-3 opacity-90">
            <i data-lucide="clock" class="w-5 h-5"></i>
            <span class="text-sm font-medium">Active Requests</span>
          </div>
          <div class="text-2xl font-bold mb-1">2</div>
          <div class="text-xs opacity-80 font-medium bg-white/20 inline-block px-2 py-0.5 rounded-full">In Progress</div>
        </div>
      </div>

      <div class="bg-gradient-to-br from-sky-400 to-blue-500 rounded-xl p-5 shadow-sm text-white relative overflow-hidden group">
        <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:scale-110 transition-transform duration-500">
          <i data-lucide="credit-card" class="w-16 h-16"></i>
        </div>
        <div class="relative z-10">
          <div class="flex items-center gap-2 mb-3 opacity-90">
            <i data-lucide="credit-card" class="w-5 h-5"></i>
            <span class="text-sm font-medium">Total Amount Due</span>
          </div>
          <div class="text-2xl font-bold mb-1">₱1,250.00</div>
          <div class="text-xs opacity-80 font-medium bg-white/20 inline-block px-2 py-0.5 rounded-full">Due: Oct 25, 2023</div>
        </div>
      </div>

      <div class="bg-gradient-to-br from-indigo-500 to-blue-600 rounded-xl p-5 shadow-sm text-white relative overflow-hidden group">
        <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:scale-110 transition-transform duration-500">
          <i data-lucide="megaphone" class="w-16 h-16"></i>
        </div>
        <div class="relative z-10">
          <div class="flex items-center gap-2 mb-3 opacity-90">
            <i data-lucide="megaphone" class="w-5 h-5"></i>
            <span class="text-sm font-medium">Announcements</span>
          </div>
          <div class="text-2xl font-bold mb-1">1 New</div>
          <div class="text-xs opacity-80 font-medium bg-white/20 inline-block px-2 py-0.5 rounded-full">Water interruption</div>
        </div>
      </div>

      <div class="bg-gradient-to-br from-slate-700 to-slate-800 rounded-xl p-5 shadow-sm text-white relative overflow-hidden group">
        <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:scale-110 transition-transform duration-500">
          <i data-lucide="check-circle-2" class="w-16 h-16"></i>
        </div>
        <div class="relative z-10">
          <div class="flex items-center gap-2 mb-3 opacity-90">
            <i data-lucide="check-circle-2" class="w-5 h-5"></i>
            <span class="text-sm font-medium">Resolved Issues</span>
          </div>
          <div class="text-2xl font-bold mb-1">12</div>
          <div class="text-xs opacity-80 font-medium bg-white/20 inline-block px-2 py-0.5 rounded-full">This Year</div>
        </div>
      </div>
    </div>

    <!-- MAIN LAYOUT GRID -->
    <div class="lg:grid lg:grid-cols-12 lg:gap-8">
      <!-- LEFT SIDEBAR -->
      <div class="hidden lg:block lg:col-span-3">
        <nav class="space-y-1">
          <h3 class="px-3 text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Categories</h3>
          <a href="#" class="bg-blue-50 text-blue-700 border-l-4 border-blue-600 group flex items-center px-3 py-2 text-sm font-medium rounded-r-md transition-colors">
            <span class="text-blue-600 mr-3 flex-shrink-0"><i data-lucide="file-text" class="w-4.5 h-4.5"></i></span>
            All Requests
          </a>
          <a href="#" class="text-slate-600 hover:bg-slate-50 hover:text-slate-900 group flex items-center px-3 py-2 text-sm font-medium rounded-r-md transition-colors">
            <span class="text-slate-400 group-hover:text-slate-500 mr-3 flex-shrink-0"><i data-lucide="droplets" class="w-4.5 h-4.5"></i></span>
            Water Supply
          </a>
          <a href="#" class="text-slate-600 hover:bg-slate-50 hover:text-slate-900 group flex items-center px-3 py-2 text-sm font-medium rounded-r-md transition-colors">
            <span class="text-slate-400 group-hover:text-slate-500 mr-3 flex-shrink-0"><i data-lucide="zap" class="w-4.5 h-4.5"></i></span>
            Electricity
          </a>
          <a href="#" class="text-slate-600 hover:bg-slate-50 hover:text-slate-900 group flex items-center px-3 py-2 text-sm font-medium rounded-r-md transition-colors">
            <span class="text-slate-400 group-hover:text-slate-500 mr-3 flex-shrink-0"><i data-lucide="hammer" class="w-4.5 h-4.5"></i></span>
            Roads &amp; Infra
          </a>
          <a href="#" class="text-slate-600 hover:bg-slate-50 hover:text-slate-900 group flex items-center px-3 py-2 text-sm font-medium rounded-r-md transition-colors">
            <span class="text-slate-400 group-hover:text-slate-500 mr-3 flex-shrink-0"><i data-lucide="help-circle" class="w-4.5 h-4.5"></i></span>
            Help Center
          </a>
          <a href="#" class="text-slate-600 hover:bg-slate-50 hover:text-slate-900 group flex items-center px-3 py-2 text-sm font-medium rounded-r-md transition-colors">
            <span class="text-slate-400 group-hover:text-slate-500 mr-3 flex-shrink-0"><i data-lucide="message-square" class="w-4.5 h-4.5"></i></span>
            Discussions
          </a>

          <div class="pt-8">
            <h3 class="px-3 text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">My Account</h3>
            <a href="#" class="group flex items-center px-3 py-2 text-sm font-medium text-slate-600 rounded-md hover:bg-slate-50 hover:text-slate-900">
              <span class="mr-3 text-slate-400 group-hover:text-slate-500"><i data-lucide="credit-card" class="w-4.5 h-4.5"></i></span>
              Payment History
            </a>
            <a href="#" class="group flex items-center px-3 py-2 text-sm font-medium text-slate-600 rounded-md hover:bg-slate-50 hover:text-slate-900">
              <span class="mr-3 text-slate-400 group-hover:text-slate-500"><i data-lucide="settings" class="w-4.5 h-4.5"></i></span>
              Settings
            </a>
          </div>
        </nav>
      </div>

      <!-- CENTER CONTENT -->
      <div class="lg:col-span-6">
        <!-- Search/Filter Bar -->
        <div class="bg-white p-2 rounded-lg border border-slate-200 shadow-sm mb-4 flex items-center justify-between gap-2">
          <div class="flex-1 relative">
            <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400"></i>
            <input type="text" placeholder="Filter requests..." class="w-full pl-9 pr-4 py-1.5 text-sm outline-none text-slate-700 placeholder:text-slate-400" />
          </div>
          <div class="flex items-center gap-2 border-l border-slate-200 pl-2">
            <button class="flex items-center gap-1 text-xs font-medium text-slate-600 hover:bg-slate-50 px-3 py-1.5 rounded border border-slate-200">
              Sort <i data-lucide="chevron-down" class="w-3.5 h-3.5"></i>
            </button>
            <button class="flex items-center gap-1 text-xs font-medium text-white bg-green-600 hover:bg-green-700 px-3 py-1.5 rounded shadow-sm transition-colors">
              <i data-lucide="plus" class="w-3.5 h-3.5"></i> New Discussion
            </button>
          </div>
        </div>

        <!-- Pinned / Highlight Box -->
        <div class="bg-blue-50 border border-blue-100 rounded-lg p-4 mb-6">
          <div class="flex items-start gap-4">
            <div class="bg-blue-100 p-2 rounded-lg">
              <i data-lucide="megaphone" class="text-blue-600 w-6 h-6"></i>
            </div>
            <div>
              <h3 class="text-blue-900 font-semibold text-sm">Welcome to FixItMati!</h3>
              <p class="text-blue-800/80 text-sm mt-1 leading-relaxed">
                Connect with your local utility providers. Submit requests, track progress, and pay bills all in one place. 
                Join the community discussion below for general queries.
              </p>
            </div>
          </div>
        </div>

        <!-- Requests List -->
        <div class="bg-white border border-slate-200 rounded-lg shadow-sm divide-y divide-slate-100">
          <div class="px-4 py-3 bg-slate-50/50 border-b border-slate-200 flex justify-between items-center rounded-t-lg">
            <h3 class="text-sm font-semibold text-slate-700">Recent Requests &amp; Discussions</h3>
            <span class="text-xs text-slate-500">Showing 3 of 12</span>
          </div>

          <!-- Item 1 -->
          <div class="p-4 hover:bg-slate-50 transition-colors group cursor-pointer">
            <div class="flex items-start gap-3">
              <div class="mt-1 flex-shrink-0">
                <i data-lucide="droplets" class="w-4 h-4 text-blue-500"></i>
              </div>
              <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 mb-1">
                  <h4 class="text-base font-medium text-slate-900 group-hover:text-blue-600 transition-colors truncate">Leaking Pipe - Main Street Extension</h4>
                  <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">Pinned</span>
                </div>
                <div class="flex items-center flex-wrap gap-x-4 gap-y-2 text-xs text-slate-500">
                  <span class="flex items-center gap-1 bg-slate-100 px-2 py-0.5 rounded-full">#101</span>
                  <span class="font-medium text-amber-600">In Progress</span>
                  <span>Opened on Oct 20, 2023</span>
                  <span class="hidden sm:inline">&bull;</span>
                  <span class="flex items-center gap-1 text-slate-400 group-hover:text-blue-500"><i data-lucide="message-square" class="w-3 h-3"></i> 3</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Item 2 -->
          <div class="p-4 hover:bg-slate-50 transition-colors group cursor-pointer">
            <div class="flex items-start gap-3">
              <div class="mt-1 flex-shrink-0">
                <i data-lucide="zap" class="w-4 h-4 text-yellow-500"></i>
              </div>
              <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 mb-1">
                  <h4 class="text-base font-medium text-slate-900 group-hover:text-blue-600 transition-colors truncate">Street Light Malfunction - Brgy. Central</h4>
                </div>
                <div class="flex items-center flex-wrap gap-x-4 gap-y-2 text-xs text-slate-500">
                  <span class="flex items-center gap-1 bg-slate-100 px-2 py-0.5 rounded-full">#102</span>
                  <span class="font-medium text-slate-500">Pending Review</span>
                  <span>Opened on Oct 22, 2023</span>
                  <span class="hidden sm:inline">&bull;</span>
                  <span class="flex items-center gap-1 text-slate-400 group-hover:text-blue-500"><i data-lucide="message-square" class="w-3 h-3"></i> 0</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Item 3 -->
          <div class="p-4 hover:bg-slate-50 transition-colors group cursor-pointer">
            <div class="flex items-start gap-3">
              <div class="mt-1 flex-shrink-0">
                <i data-lucide="hammer" class="w-4 h-4 text-gray-500"></i>
              </div>
              <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 mb-1">
                  <h4 class="text-base font-medium text-slate-900 group-hover:text-blue-600 transition-colors truncate">Pothole Repair Request</h4>
                </div>
                <div class="flex items-center flex-wrap gap-x-4 gap-y-2 text-xs text-slate-500">
                  <span class="flex items-center gap-1 bg-slate-100 px-2 py-0.5 rounded-full">#103</span>
                  <span class="font-medium text-green-600">Resolved</span>
                  <span>Opened on Oct 10, 2023</span>
                  <span class="hidden sm:inline">&bull;</span>
                  <span class="flex items-center gap-1 text-slate-400 group-hover:text-blue-500"><i data-lucide="message-square" class="w-3 h-3"></i> 8</span>
                </div>
              </div>
            </div>
          </div>

          <div class="p-3 text-center border-t border-slate-100">
            <button class="text-sm text-blue-600 hover:text-blue-700 font-medium hover:underline">View all requests</button>
          </div>
        </div>
      </div>

      <!-- RIGHT SIDEBAR -->
      <div class="lg:col-span-3 space-y-6 mt-6 lg:mt-0">
        <!-- Announcements Widget -->
        <div class="bg-white rounded-lg border border-slate-200 shadow-sm overflow-hidden">
          <div class="px-4 py-3 border-b border-slate-200 bg-slate-50/50 flex justify-between items-center">
            <h3 class="font-semibold text-slate-800 text-sm">Latest Announcements</h3>
          </div>
          <div class="divide-y divide-slate-100">
            <div class="p-3 hover:bg-slate-50 transition-colors">
              <div class="flex items-center justify-between mb-1">
                <span class="text-[10px] font-bold px-1.5 py-0.5 rounded uppercase bg-red-100 text-red-600">Urgent</span>
                <span class="text-xs text-slate-400">2 hours ago</span>
              </div>
              <h4 class="text-sm font-medium text-slate-800 mb-1 leading-snug">Scheduled Water Interruption</h4>
              <p class="text-xs text-slate-500 line-clamp-2">Please be advised of a scheduled maintenance on Oct 28...</p>
            </div>
            <div class="p-3 hover:bg-slate-50 transition-colors">
              <div class="flex items-center justify-between mb-1">
                <span class="text-[10px] font-bold px-1.5 py-0.5 rounded uppercase bg-blue-100 text-blue-600">News</span>
                <span class="text-xs text-slate-400">1 day ago</span>
              </div>
              <h4 class="text-sm font-medium text-slate-800 mb-1 leading-snug">New Online Payment Partners</h4>
              <p class="text-xs text-slate-500 line-clamp-2">You can now pay via GCash and Maya directly in the app...</p>
            </div>
          </div>
          <div class="px-4 py-2 border-t border-slate-100 bg-slate-50">
            <a href="#" class="text-xs font-medium text-blue-600 hover:text-blue-700">View all announcements &rarr;</a>
          </div>
        </div>

        <!-- Quick Support Widget -->
        <div class="bg-white rounded-lg border border-slate-200 shadow-sm p-4">
          <h3 class="font-semibold text-slate-800 text-sm mb-3">Help Center</h3>
          <ul class="space-y-3">
            <li class="flex items-center gap-2 text-sm text-slate-600 hover:text-blue-600 cursor-pointer transition-colors">
              <div class="w-1.5 h-1.5 rounded-full bg-slate-300"></div>
              How to pay bills online?
            </li>
            <li class="flex items-center gap-2 text-sm text-slate-600 hover:text-blue-600 cursor-pointer transition-colors">
              <div class="w-1.5 h-1.5 rounded-full bg-slate-300"></div>
              Reporting a water leak
            </li>
            <li class="flex items-center gap-2 text-sm text-slate-600 hover:text-blue-600 cursor-pointer transition-colors">
              <div class="w-1.5 h-1.5 rounded-full bg-slate-300"></div>
              Application for new connection
            </li>
          </ul>
        </div>

        <!-- Download Receipt Widget -->
        <div class="bg-slate-800 rounded-lg p-4 text-white shadow-md relative overflow-hidden">
          <div class="absolute top-0 right-0 p-4 opacity-10">
            <i data-lucide="credit-card" class="w-12 h-12"></i>
          </div>
          <h3 class="font-bold text-sm mb-1 relative z-10">Latest Receipt</h3>
          <p class="text-xs text-slate-300 mb-4 relative z-10">Ref: #INV-2023-001</p>
          <button class="w-full py-2 bg-white/10 hover:bg-white/20 border border-white/20 rounded text-xs font-medium flex items-center justify-center gap-2 transition-colors">
            <i data-lucide="download" class="w-3.5 h-3.5"></i> Download PDF
          </button>
        </div>
      </div>
    </div>
  </main>

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

  <!-- MOBILE DRAWER -->
  <div id="mobileDrawer" class="fixed inset-0 z-50 hidden">
    <div class="fixed inset-0 bg-slate-900/50" data-close></div>
    <div class="relative bg-white w-64 h-full shadow-xl flex flex-col">
      <div class="p-4 border-b border-slate-100 flex justify-between items-center">
        <span class="font-bold text-blue-900">Menu</span>
        <button class="text-slate-500" data-close><i data-lucide="x" class="w-5 h-5"></i></button>
      </div>
      <nav class="flex-1 p-4 space-y-1">
        <a href="#" class="block px-3 py-2 text-base font-medium text-slate-600 hover:bg-slate-50 rounded-md">Dashboard</a>
        <a href="#" class="block px-3 py-2 text-base font-medium text-slate-600 hover:bg-slate-50 rounded-md">My Requests</a>
        <a href="#" class="block px-3 py-2 text-base font-medium text-slate-600 hover:bg-slate-50 rounded-md">Announcements</a>
        <a href="#" class="block px-3 py-2 text-base font-medium text-slate-600 hover:bg-slate-50 rounded-md">Payments</a>
        <a href="#" class="block px-3 py-2 text-base font-medium text-slate-600 hover:bg-slate-50 rounded-md">Help Center</a>
      </nav>
    </div>
  </div>

  <!-- App JS -->
  <script src="../assets/app.js"></script>
  <script>
    // Initialize Lucide icons after DOM is ready
    document.addEventListener('DOMContentLoaded', () => {
      lucide.createIcons();
    });
  </script>
</body>
</html>
