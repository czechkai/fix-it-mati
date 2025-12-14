<?php
// Community Discussions Page for FixItMati
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Community Discussions - FixItMati</title>
  <!-- Authentication check and cross-tab sync -->
  <script src="/assets/auth-check.js"></script>
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
      <div class="flex items-center justify-between h-16">
        <div class="flex items-center gap-2">
          <a href="user-dashboard.php" class="text-slate-500 hover:text-blue-600 transition-colors">
            <i data-lucide="arrow-left" class="w-5 h-5"></i>
          </a>
          <div>
            <h1 class="text-lg font-bold text-slate-800">Community Discussions</h1>
            <p class="text-xs text-slate-500 flex items-center gap-2 mt-0.5">
              <span class="flex h-2 w-2">
                <span class="animate-ping absolute inline-flex h-2 w-2 rounded-full bg-green-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
              </span>
              <span id="lastUpdateIndicator">Real-time updates active</span>
              <button id="manualRefreshBtn" class="ml-2 text-slate-400 hover:text-blue-600 transition-colors" title="Refresh now">
                <i data-lucide="refresh-cw" class="w-3.5 h-3.5"></i>
              </button>
            </p>
          </div>
        </div>
        
        <button id="newDiscussionBtn" class="bg-green-600 hover:bg-green-700 text-white text-sm font-bold px-4 py-2 rounded-lg flex items-center gap-2 shadow-sm transition-colors">
          <i data-lucide="plus" class="w-4 h-4"></i>
          <span class="hidden sm:inline">New Discussion</span>
        </button>
      </div>
    </div>
  </div>

  <!-- MAIN CONTENT -->
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex flex-col lg:flex-row gap-8">
      
      <!-- LEFT SIDEBAR: NAVIGATION -->
      <div class="hidden lg:block w-64 space-y-6">
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-2">
          <nav class="space-y-1" id="categoryNav">
            <button data-category="All" class="category-filter w-full flex items-center justify-between px-3 py-2 text-sm font-medium rounded-lg transition-colors bg-blue-50 text-blue-700">
              All
            </button>
            <button data-category="Water Supply" class="category-filter w-full flex items-center justify-between px-3 py-2 text-sm font-medium rounded-lg transition-colors text-slate-600 hover:bg-slate-50 hover:text-slate-900">
              Water Supply
            </button>
            <button data-category="Electricity" class="category-filter w-full flex items-center justify-between px-3 py-2 text-sm font-medium rounded-lg transition-colors text-slate-600 hover:bg-slate-50 hover:text-slate-900">
              Electricity
            </button>
            <button data-category="Billing" class="category-filter w-full flex items-center justify-between px-3 py-2 text-sm font-medium rounded-lg transition-colors text-slate-600 hover:bg-slate-50 hover:text-slate-900">
              Billing
            </button>
            <button data-category="General" class="category-filter w-full flex items-center justify-between px-3 py-2 text-sm font-medium rounded-lg transition-colors text-slate-600 hover:bg-slate-50 hover:text-slate-900">
              General
            </button>
          </nav>
        </div>

        <!-- Guidelines Widget -->
        <div class="bg-slate-100 rounded-xl p-4 text-xs text-slate-600">
          <h4 class="font-bold text-slate-700 mb-2 flex items-center gap-2">
            <i data-lucide="alert-circle" class="w-3.5 h-3.5"></i> Guidelines
          </h4>
          <ul class="space-y-2 list-disc pl-4">
            <li>Be respectful to other residents.</li>
            <li>Do not post personal account numbers publicly.</li>
            <li>Search before posting to avoid duplicates.</li>
          </ul>
        </div>
      </div>

      <!-- CENTER: DISCUSSION FEED -->
      <div class="flex-1 space-y-4">
        
        <!-- Search Bar -->
        <div class="relative">
          <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 w-4.5 h-4.5"></i>
          <input 
            type="text" 
            id="searchInput"
            placeholder="Search discussions (e.g. 'billing error', 'meter application')..." 
            class="w-full pl-10 pr-4 py-3 rounded-xl border border-slate-200 shadow-sm focus:ring-2 focus:ring-blue-500 outline-none"
          />
        </div>

        <!-- Sorting Tabs -->
        <div class="flex items-center gap-2 border-b border-slate-200 pb-2 mb-4">
          <button data-sort="newest" class="sort-tab text-sm font-bold text-slate-800 border-b-2 border-slate-800 px-2 py-1">Newest</button>
          <button data-sort="top" class="sort-tab text-sm font-medium text-slate-500 hover:text-slate-700 px-2 py-1">Top Rated</button>
          <button data-sort="unanswered" class="sort-tab text-sm font-medium text-slate-500 hover:text-slate-700 px-2 py-1">Unanswered</button>
        </div>

        <!-- Thread List -->
        <div id="discussionsList">
          <div class="text-center py-8 text-slate-400">
            <i data-lucide="loader-2" class="w-8 h-8 mx-auto animate-spin mb-2"></i>
            <p class="text-sm">Loading discussions...</p>
          </div>
        </div>

        <button id="loadMoreBtn" class="hidden w-full py-3 text-sm text-slate-500 font-medium hover:text-blue-600 hover:bg-slate-100 rounded-lg transition-colors">
          Load more discussions...
        </button>

      </div>

      <!-- RIGHT SIDEBAR: TRENDING -->
      <div class="hidden xl:block w-72 space-y-6">
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-4">
          <h3 class="text-sm font-bold text-slate-800 mb-4 flex items-center gap-2">
            <i data-lucide="trending-up" class="w-4 h-4 text-blue-600"></i> Trending Topics
          </h3>
          <ul class="space-y-3" id="trendingList">
            <li class="text-xs text-slate-600 hover:text-blue-600 cursor-pointer font-medium truncate">
              1. Water interruption schedule
            </li>
            <li class="text-xs text-slate-600 hover:text-blue-600 cursor-pointer font-medium truncate">
              2. Online payment verification delay
            </li>
            <li class="text-xs text-slate-600 hover:text-blue-600 cursor-pointer font-medium truncate">
              3. Where to report broken pipes?
            </li>
            <li class="text-xs text-slate-600 hover:text-blue-600 cursor-pointer font-medium truncate">
              4. New office hours for holidays
            </li>
          </ul>
        </div>
      </div>

    </div>
  </div>

  <!-- New Discussion Modal -->
  <div id="newDiscussionModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
      <div class="p-6 border-b border-slate-200">
        <div class="flex items-center justify-between">
          <h2 class="text-xl font-bold text-slate-800">Start a New Discussion</h2>
          <button id="closeModalBtn" class="text-slate-400 hover:text-slate-600">
            <i data-lucide="x" class="w-6 h-6"></i>
          </button>
        </div>
      </div>
      
      <form id="newDiscussionForm" class="p-6 space-y-4">
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-2">Category</label>
          <select id="discussionCategory" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
            <option value="Water Supply">Water Supply</option>
            <option value="Electricity">Electricity</option>
            <option value="Billing">Billing</option>
            <option value="General">General</option>
          </select>
        </div>
        
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-2">Title</label>
          <input 
            type="text" 
            id="discussionTitle"
            placeholder="What's your question or topic?"
            class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none"
            required
          />
        </div>
        
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-2">Description</label>
          <textarea 
            id="discussionContent"
            rows="6"
            placeholder="Provide more details about your question or topic..."
            class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none"
            required
          ></textarea>
        </div>
        
        <div class="flex gap-3 pt-4">
          <button type="submit" class="flex-1 bg-green-600 hover:bg-green-700 text-white font-bold py-2.5 rounded-lg transition-colors">
            Post Discussion
          </button>
          <button type="button" id="cancelModalBtn" class="px-6 bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium py-2.5 rounded-lg transition-colors">
            Cancel
          </button>
        </div>
      </form>
    </div>
  </div>

  <!-- API Client -->
  <script src="/assets/api-client.js"></script>
  <!-- Dashboard JS -->
  <script src="/assets/dashboard.js?v=20"></script>
  <!-- Discussions JS -->
  <script src="/assets/discussions.js"></script>
</body>
</html>
