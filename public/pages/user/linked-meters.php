<?php
// Simple PHP template for FixItMati Linked Meters Page
// This page uses the standard header and footer from user-dashboard.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Linked Meters - FixItMati</title>
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
          <a href="/user-dashboard.php" class="flex items-center gap-2 cursor-pointer hover:opacity-80 transition-opacity">
            <div class="bg-blue-600 p-1.5 rounded-lg">
              <i data-lucide="hammer" class="text-white w-5 h-5"></i>
            </div>
            <span class="text-xl font-bold tracking-tight text-blue-900">FixItMati</span>
          </a>
        </div>
        <!-- Right Actions -->
        <div class="flex items-center gap-3">
          <a href="/user-dashboard.php" class="p-2 text-slate-500 hover:bg-slate-100 rounded-full cursor-pointer transition-colors" title="Go to Dashboard">
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
        <nav class="flex -mb-px items-center space-x-8">
          <div class="flex items-center gap-2 py-4 border-b-2 border-blue-600">
            <a href="/user-dashboard.php" class="text-slate-500 hover:text-blue-600 transition-colors" title="Back to Dashboard">
              <i data-lucide="arrow-left" class="w-4 h-4"></i>
            </a>
            <h1 class="text-lg font-bold text-slate-800">Linked Meters</h1>
          </div>
        </nav>
        <button id="openModalBtn" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold px-4 py-2 rounded-lg flex items-center gap-2 shadow-sm transition-colors my-2">
          <i data-lucide="plus" class="w-4 h-4"></i>
          <span class="hidden sm:inline">Link New Meter</span>
        </button>
      </div>
    </div>
  </div>

  <!-- MAIN CONTENT -->
  <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <!-- Statistics Summary -->
    <div id="statsSection" class="hidden grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
      <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl p-5 text-white shadow-sm">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-xs text-blue-100 uppercase font-medium mb-1">Total Meters</p>
            <h3 id="totalMeters" class="text-3xl font-bold">0</h3>
          </div>
          <div class="h-12 w-12 bg-white/20 rounded-lg flex items-center justify-center">
            <i data-lucide="gauge" class="w-6 h-6"></i>
          </div>
        </div>
      </div>
      <div class="bg-gradient-to-br from-cyan-500 to-blue-500 rounded-xl p-5 text-white shadow-sm">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-xs text-cyan-100 uppercase font-medium mb-1">Water Meters</p>
            <h3 id="waterMeters" class="text-3xl font-bold">0</h3>
          </div>
          <div class="h-12 w-12 bg-white/20 rounded-lg flex items-center justify-center">
            <i data-lucide="droplets" class="w-6 h-6"></i>
          </div>
        </div>
      </div>
      <div class="bg-gradient-to-br from-amber-500 to-orange-500 rounded-xl p-5 text-white shadow-sm">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-xs text-amber-100 uppercase font-medium mb-1">Electric Meters</p>
            <h3 id="electricMeters" class="text-3xl font-bold">0</h3>
          </div>
          <div class="h-12 w-12 bg-white/20 rounded-lg flex items-center justify-center">
            <i data-lucide="zap" class="w-6 h-6"></i>
          </div>
        </div>
      </div>
    </div>

    <!-- Info Banner -->
    <div class="bg-blue-50 border border-blue-100 rounded-xl p-4 mb-8 flex gap-3 items-start">
      <i data-lucide="alert-circle" class="text-blue-600 flex-shrink-0 mt-0.5 w-5 h-5"></i>
      <div>
        <h3 class="text-sm font-bold text-blue-900">Why link your meters?</h3>
        <p class="text-xs text-blue-800 mt-1 leading-relaxed">
          Linking your physical meters allows you to view digital bills, track historical consumption, and pay directly within the app.
          You can link multiple meters for different properties.
        </p>
      </div>
    </div>

    <!-- Loading State -->
    <div id="loadingState" class="flex justify-center items-center py-20">
      <div class="text-center">
        <i data-lucide="loader-2" class="w-12 h-12 text-blue-600 animate-spin mx-auto mb-4"></i>
        <p class="text-slate-500">Loading your meters...</p>
      </div>
    </div>

    <!-- Empty State -->
    <div id="emptyState" class="hidden text-center py-16">
      <div class="max-w-md mx-auto">
        <div class="h-24 w-24 rounded-full bg-slate-100 flex items-center justify-center mx-auto mb-6">
          <i data-lucide="gauge" class="w-12 h-12 text-slate-400"></i>
        </div>
        <h3 class="text-xl font-bold text-slate-800 mb-2">No Meters Linked Yet</h3>
        <p class="text-slate-500 mb-6">Link your water and electricity meters to view bills and track consumption</p>
        <button id="openModalBtnEmpty" class="bg-blue-600 hover:bg-blue-700 text-white font-bold px-6 py-3 rounded-lg inline-flex items-center gap-2">
          <i data-lucide="plus" class="w-5 h-5"></i>
          Link Your First Meter
        </button>
      </div>
    </div>

    <!-- Filter Section -->
    <div id="filterSection" class="hidden mb-6 flex flex-col sm:flex-row gap-3 items-center justify-between">
      <div class="flex gap-2 w-full sm:w-auto">
        <button id="filterAll" class="filter-btn flex-1 sm:flex-none px-4 py-2 rounded-lg text-sm font-medium transition-colors bg-blue-600 text-white">
          <span class="flex items-center gap-2 justify-center">
            <i data-lucide="gauge" class="w-4 h-4"></i> All (<span id="countAll">0</span>)
          </span>
        </button>
        <button id="filterWater" class="filter-btn flex-1 sm:flex-none px-4 py-2 rounded-lg text-sm font-medium transition-colors bg-white border border-slate-200 text-slate-600 hover:bg-slate-50">
          <span class="flex items-center gap-2 justify-center">
            <i data-lucide="droplets" class="w-4 h-4"></i> Water (<span id="countWater">0</span>)
          </span>
        </button>
        <button id="filterElectricity" class="filter-btn flex-1 sm:flex-none px-4 py-2 rounded-lg text-sm font-medium transition-colors bg-white border border-slate-200 text-slate-600 hover:bg-slate-50">
          <span class="flex items-center gap-2 justify-center">
            <i data-lucide="zap" class="w-4 h-4"></i> Electric (<span id="countElectric">0</span>)
          </span>
        </button>
      </div>
      <div class="relative w-full sm:w-64">
        <input id="searchInput" type="text" placeholder="Search meters..." class="w-full pl-10 pr-4 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none">
        <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
      </div>
    </div>

    <!-- Meter Grid -->
    <div id="metersGrid" class="hidden grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <!-- Meters will be dynamically loaded here -->
    </div>
  </main>

  <!-- Floating Action Button (Mobile) -->
  <a href="#" id="openModalBtn2Mobile" class="md:hidden fixed bottom-6 right-6 w-14 h-14 bg-blue-600 text-white rounded-full shadow-lg flex items-center justify-center hover:bg-blue-700 transition-all hover:scale-110 z-40">
    <i data-lucide="plus" class="w-6 h-6"></i>
  </a>

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
        <a href="/user-dashboard.php" class="block px-3 py-2 text-base font-medium text-slate-600 hover:bg-slate-50 rounded-md">Dashboard</a>
        <a href="/active-requests.php" class="block px-3 py-2 text-base font-medium text-slate-600 hover:bg-slate-50 rounded-md">My Requests</a>
        <a href="/announcements.php" class="block px-3 py-2 text-base font-medium text-slate-600 hover:bg-slate-50 rounded-md">Announcements</a>
        <a href="/payments.php" class="block px-3 py-2 text-base font-medium text-slate-600 hover:bg-slate-50 rounded-md">Payments</a>
        <a href="#" class="block px-3 py-2 text-base font-medium text-slate-600 hover:bg-slate-50 rounded-md">Help Center</a>
      </nav>
    </div>
  </div>

  <!-- ADD METER MODAL --> 
  <div id="addMeterModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 hidden">
    <div id="modalBackdrop" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
    <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-md max-h-[90vh] overflow-y-auto">

      <div class="px-5 py-3 border-b border-slate-100 flex justify-between items-center bg-slate-50">
        <h3 class="font-bold text-slate-800 text-base">Link New Meter</h3>
        <button id="closeModalBtn" class="text-slate-400 hover:text-slate-600">
          <i data-lucide="x" class="w-5 h-5"></i>
        </button>
      </div>

      <form id="linkMeterForm" class="p-5 space-y-3">
        <!-- Meter Type -->
        <div class="space-y-1.5">
          <label class="text-xs font-bold text-slate-500 uppercase">Meter Type</label>
          <div class="flex gap-2">
            <label class="flex-1 flex items-center gap-2 border-2 border-slate-200 rounded-lg p-2.5 cursor-pointer hover:border-blue-400 transition-colors has-[:checked]:border-blue-500 has-[:checked]:bg-blue-50">
              <input type="radio" name="meter_type" value="water" class="text-blue-600" required />
              <i data-lucide="droplets" class="w-4 h-4 text-blue-600"></i>
              <span class="text-sm font-medium">Water</span>
            </label>
            <label class="flex-1 flex items-center gap-2 border-2 border-slate-200 rounded-lg p-2.5 cursor-pointer hover:border-amber-400 transition-colors has-[:checked]:border-amber-500 has-[:checked]:bg-amber-50">
              <input type="radio" name="meter_type" value="electricity" class="text-amber-600" required />
              <i data-lucide="zap" class="w-4 h-4 text-amber-600"></i>
              <span class="text-sm font-medium">Electricity</span>
            </label>
          </div>
        </div>

        <!-- Provider Select -->
        <div class="space-y-1.5">
          <label class="text-xs font-bold text-slate-500 uppercase">Utility Provider</label>
          <div class="relative">
            <select id="providerInput" name="provider" class="w-full appearance-none border border-slate-200 rounded-lg p-2.5 text-sm font-medium text-slate-700 focus:ring-2 focus:ring-blue-500 outline-none bg-white" required>
              <option value="">Select Provider...</option>
              <option value="Mati Water District">Mati Water District</option>
              <option value="Davao Oriental Electric Cooperative (DORECO)">Davao Oriental Electric Cooperative (DORECO)</option>
            </select>
            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-slate-500">
              <svg class="h-4 w-4 fill-current" viewBox="0 0 20 20"><path d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/></svg>
            </div>
          </div>
        </div>

        <!-- Account Number -->
        <div class="space-y-1.5">
          <label class="text-xs font-bold text-slate-500 uppercase">Account / Meter Number</label>
          <input id="accountNumberInput" name="account_number" type="text" placeholder="e.g., 123-456-789 or 12345678" class="w-full border border-slate-200 rounded-lg p-2.5 text-sm font-mono focus:ring-2 focus:ring-blue-500 outline-none" required />
          <div id="accountNumberHelp" class="text-[10px] text-slate-500 leading-tight">
            <div class="font-medium mb-0.5">Format Guide:</div>
            <div class="flex flex-col gap-0.5">
              <span><strong>Water:</strong> 9-12 digits (e.g., 123456789 or 123-456-789)</span>
              <span><strong>Electric:</strong> 8-15 characters (e.g., 12345678 or ABC-123-4567)</span>
            </div>
          </div>
        </div>

        <!-- Account Holder Name -->
        <div class="space-y-1.5">
          <label class="text-xs font-bold text-slate-500 uppercase">Account Holder Full Name</label>
          <input id="accountHolderInput" name="account_holder_name" type="text" placeholder="As shown on bill" class="w-full border border-slate-200 rounded-lg p-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none" required />
        </div>

        <!-- Alias -->
        <div class="space-y-1.5">
          <label class="text-xs font-bold text-slate-500 uppercase">Save As (Optional)</label>
          <div class="flex gap-2">
            <button type="button" class="alias-preset flex-1 border border-slate-200 rounded-lg py-1.5 text-xs font-medium hover:bg-slate-50 flex items-center justify-center gap-1" data-alias="Home">
              <i data-lucide="home" class="w-3 h-3"></i> Home
            </button>
            <button type="button" class="alias-preset flex-1 border border-slate-200 rounded-lg py-1.5 text-xs font-medium hover:bg-slate-50 flex items-center justify-center gap-1" data-alias="Business">
              <i data-lucide="building-2" class="w-3 h-3"></i> Business
            </button>
          </div>
          <input id="aliasInput" name="alias" type="text" placeholder="e.g., Main Office, Vacation Home" class="w-full border border-slate-200 rounded-lg p-2.5 text-sm mt-1.5 focus:ring-2 focus:ring-blue-500 outline-none" />
        </div>

        <!-- Address -->
        <div class="space-y-1.5">
          <label class="text-xs font-bold text-slate-500 uppercase">Service Address (Optional)</label>
          <textarea id="addressInput" name="address" rows="2" placeholder="Property address" class="w-full border border-slate-200 rounded-lg p-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none resize-none"></textarea>
        </div>

        <div class="pt-3">
          <button id="submitBtn" type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 rounded-lg shadow-md shadow-blue-200 transition-all flex items-center justify-center gap-2">
            <span id="submitBtnText">Verify & Link Meter</span>
            <span id="loader" class="hidden">
              <i data-lucide="loader-2" class="w-4.5 h-4.5 animate-spin"></i>
            </span>
          </button>
        </div>
      </form>
    </div>
  </div>

  <!-- API Client -->
  <script src="/assets/api-client.js"></script>
  <!-- Dashboard JS -->
  <script src="/assets/dashboard.js"></script>
  <!-- Linked Meters JS -->
  <script src="/assets/linked-meters.js"></script>
</body>
</html>