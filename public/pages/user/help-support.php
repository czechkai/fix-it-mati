<?php
// Help & Support page for FixItMati
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Help Center - FixItMati</title>
  <!-- Check authentication client-side -->
  <script>
    // Redirect to login if not authenticated - must happen IMMEDIATELY
    (function() {
      const token = sessionStorage.getItem('auth_token');
      if (!token) {
        window.location.replace('login.php');
        // Stop execution
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

  <!-- HERO SEARCH SECTION -->
  <div class="bg-blue-600 px-4 sm:px-6 lg:px-8 py-10 text-center">
    <h2 class="text-2xl font-bold text-white mb-2">How can we help you today?</h2>
    <p class="text-blue-100 text-sm mb-6">Search for common questions or browse topics below.</p>
    
    <div class="max-w-xl mx-auto relative">
      <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
        <i data-lucide="search" class="h-5 w-5 text-slate-400"></i>
      </div>
      <input
        type="text"
        id="searchInput"
        class="block w-full pl-11 pr-4 py-3.5 rounded-xl text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-4 focus:ring-blue-400/30 shadow-lg"
        placeholder="e.g., 'payment failed', 'no water', 'new connection'"
        autocomplete="off"
      />
      <!-- Search Suggestions -->
      <div id="searchSuggestions" class="hidden absolute top-full mt-2 w-full bg-white rounded-xl shadow-xl border border-slate-200 max-h-80 overflow-y-auto z-10">
        <!-- Suggestions will be rendered here -->
      </div>
    </div>
  </div>

  <!-- MAIN CONTENT -->
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 -mt-2">
    
    <!-- Topic Tabs -->
    <div class="flex flex-wrap gap-2 justify-center mb-8 mt-4" id="categoryTabs">
      <!-- Categories will be dynamically rendered -->
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
      
      <!-- LEFT: FAQ LIST -->
      <div class="lg:col-span-2 space-y-4">
        <h3 class="text-sm font-bold text-slate-500 uppercase tracking-wider mb-2" id="faqHeading">
          Frequently Asked Questions
        </h3>
        
        <div id="faqList" class="space-y-4">
          <!-- FAQs will be dynamically rendered -->
        </div>

        <!-- No Results -->
        <div id="noResults" class="hidden text-center py-12 bg-white rounded-xl border border-slate-200 border-dashed">
          <p class="text-slate-500">No questions found matching "<span id="searchTerm"></span>".</p>
          <button 
            id="clearSearchBtn"
            class="text-blue-600 font-bold text-sm mt-2 hover:underline"
          >
            Clear Search
          </button>
        </div>
      </div>

      <!-- RIGHT: CONTACT CARDS -->
      <div class="space-y-6">
        
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
           <h4 class="font-bold text-slate-800 mb-4">Still need help?</h4>
           <p class="text-xs text-slate-500 mb-4">
             Our support team is available Mon-Fri, 8:00 AM to 5:00 PM.
           </p>
           
           <div class="space-y-3">
             <button class="w-full flex items-center gap-3 p-3 rounded-lg border border-slate-100 hover:bg-blue-50 hover:border-blue-100 transition-colors group text-left">
                <div class="h-8 w-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center flex-shrink-0">
                  <i data-lucide="phone" class="w-4 h-4"></i>
                </div>
                <div>
                  <div class="text-xs font-bold text-slate-700 group-hover:text-blue-700">Call Support</div>
                  <div class="text-[10px] text-slate-500">0917-123-4567</div>
                </div>
             </button>

             <button class="w-full flex items-center gap-3 p-3 rounded-lg border border-slate-100 hover:bg-blue-50 hover:border-blue-100 transition-colors group text-left">
                <div class="h-8 w-8 rounded-full bg-green-100 text-green-600 flex items-center justify-center flex-shrink-0">
                  <i data-lucide="message-square" class="w-4 h-4"></i>
                </div>
                <div>
                  <div class="text-xs font-bold text-slate-700 group-hover:text-green-700">Chat with Us</div>
                  <div class="text-[10px] text-slate-500">Average wait: 5 mins</div>
                </div>
             </button>

             <button class="w-full flex items-center gap-3 p-3 rounded-lg border border-slate-100 hover:bg-blue-50 hover:border-blue-100 transition-colors group text-left">
                <div class="h-8 w-8 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center flex-shrink-0">
                  <i data-lucide="mail" class="w-4 h-4"></i>
                </div>
                <div>
                  <div class="text-xs font-bold text-slate-700 group-hover:text-amber-700">Email Support</div>
                  <div class="text-[10px] text-slate-500">support@mati.gov.ph</div>
                </div>
             </button>
           </div>
        </div>

        <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl p-5 text-white shadow-lg">
          <h4 class="font-bold text-sm mb-2">My Support Tickets</h4>
          <p class="text-xs text-slate-400 mb-4">
            Track the status of your reported bugs or billing inquiries.
          </p>
          <button class="w-full bg-white/10 hover:bg-white/20 border border-white/20 text-white text-xs font-bold py-2 rounded-lg transition-colors flex items-center justify-center gap-2">
            View Ticket History <i data-lucide="external-link" class="w-3 h-3"></i>
          </button>
        </div>

      </div>

    </div>
  </div>

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
        <a href="help-support.php" class="block px-3 py-2 text-base font-medium text-slate-600 hover:bg-slate-50 rounded-md">Help Center</a>
      </nav>
    </div>
  </div>

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

  <!-- API Client -->
  <script src="/assets/api-client.js"></script>
  <!-- Dashboard JS -->
  <script src="/assets/dashboard.js?v=20"></script>
  <!-- Help Support JS -->
  <script src="/assets/help-support.js"></script>
</body>
</html>
