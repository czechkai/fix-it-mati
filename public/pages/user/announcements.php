<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Announcements - FixItMati</title>
    <script>
        (function() {
            const token = sessionStorage.getItem('auth_token');
            if (!token) {
                window.location.replace('login.php');
                throw new Error('Not authenticated');
            }
        })();
    </script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link rel="stylesheet" href="/assets/style.css">
    <link rel="stylesheet" href="/assets/announcements.css">
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
                <nav class="flex -mb-px space-x-8">
                    <a href="user-dashboard.php" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm flex items-center gap-2 transition-colors border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300">
                        <i data-lucide="arrow-left" class="w-4 h-4"></i> Back to Dashboard
                    </a>
                </nav>
            </div>
        </div>
    </div>

    <!-- MAIN CONTENT -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="layout-container">
            
            <!-- LEFT SIDEBAR: FILTERS -->
            <div class="sidebar">
                <div class="sidebar-sticky">
                    <h3 class="sidebar-title">
                        <i data-lucide="filter" style="width: 14px; height: 14px;"></i>
                        Filter By Utility
                    </h3>
                    <nav class="filter-nav">
                        <button class="filter-btn active" data-value="All">
                            <span class="filter-icon">
                                <i data-lucide="megaphone" style="width: 14px; height: 14px;"></i>
                            </span>
                            <span class="filter-label">All Updates</span>
                            <span class="check-icon">
                                <i data-lucide="check-circle-2" style="width: 14px; height: 14px;"></i>
                            </span>
                        </button>
                        <button class="filter-btn" data-value="Water Supply">
                            <span class="filter-icon">
                                <i data-lucide="droplets" style="width: 14px; height: 14px;"></i>
                            </span>
                            <span class="filter-label">Water Supply</span>
                            <span class="check-icon">
                                <i data-lucide="check-circle-2" style="width: 14px; height: 14px;"></i>
                            </span>
                        </button>
                        <button class="filter-btn" data-value="Electricity">
                            <span class="filter-icon">
                                <i data-lucide="zap" style="width: 14px; height: 14px;"></i>
                            </span>
                            <span class="filter-label">Electricity</span>
                            <span class="check-icon">
                                <i data-lucide="check-circle-2" style="width: 14px; height: 14px;"></i>
                            </span>
                        </button>
                        <button class="filter-btn" data-value="Urgent">
                            <span class="filter-icon">
                                <i data-lucide="alert-triangle" style="width: 14px; height: 14px;"></i>
                            </span>
                            <span class="filter-label">Urgent Alerts</span>
                            <span class="check-icon">
                                <i data-lucide="check-circle-2" style="width: 14px; height: 14px;"></i>
                            </span>
                        </button>
                    </nav>

                    <div class="sms-alert-box">
                        <div class="sms-alert-card">
                            <h4 class="sms-alert-title">
                                <i data-lucide="mail" style="width: 12px; height: 12px;"></i>
                                SMS Alerts
                            </h4>
                            <p class="sms-alert-text">Get notified instantly about interruptions.</p>
                            <button class="sms-alert-btn">Subscribe</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- CENTER: NEWS FEED -->
            <div class="feed-container">
                
                <!-- Context Header -->
                <div class="feed-header">
                    <h2 class="feed-title" id="feedTitle">Latest Announcements</h2>
                    <span class="feed-count" id="feedCount">Showing 4 posts</span>
                </div>

                <!-- List -->
                <div class="announcements-list" id="announcementsList">
                    <!-- Announcements will be dynamically inserted here by JavaScript -->
                </div>
            </div>
        </div>
    </main>

    <!-- MODAL: POST TO DISCUSSIONS -->
    <div id="discussionModal" class="hidden"></div>

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
                <a href="user-dashboard.php" class="block px-3 py-2 text-base font-medium text-slate-600 hover:bg-slate-50 rounded-md">Dashboard</a>
                <a href="active-requests.php" class="block px-3 py-2 text-base font-medium text-slate-600 hover:bg-slate-50 rounded-md">My Requests</a>
                <a href="announcements.php" class="block px-3 py-2 text-base font-medium text-slate-600 hover:bg-slate-50 rounded-md">Announcements</a>
                <a href="payments.php" class="block px-3 py-2 text-base font-medium text-slate-600 hover:bg-slate-50 rounded-md">Payments</a>
                <a href="#" class="block px-3 py-2 text-base font-medium text-slate-600 hover:bg-slate-50 rounded-md">Help Center</a>
            </nav>
        </div>
    </div>

    <script src="/assets/api-client.js"></script>
    <script src="/assets/dashboard.js?v=20"></script>
    <script src="/assets/announcements.js"></script>
    <script>lucide.createIcons();</script>
</body>
</html>
