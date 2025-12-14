<?php
// Service Addresses page for FixItMati
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Service Addresses - FixItMati</title>
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
        <button id="addAddressBtn" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold px-4 py-2 rounded-lg flex items-center gap-2 shadow-sm transition-colors my-2">
          <i data-lucide="plus" class="w-4 h-4"></i>
          <span class="hidden sm:inline">Add Address</span>
        </button>
      </div>
    </div>
  </div>

  <!-- MAIN CONTENT -->
  <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <!-- Info Banner -->
    <div class="bg-blue-50 border border-blue-100 rounded-xl p-4 mb-8 flex gap-3 items-start">
      <i data-lucide="alert-circle" class="text-blue-600 flex-shrink-0 mt-0.5 w-5 h-5"></i>
      <div>
        <h3 class="text-sm font-bold text-blue-900">Tip: Accurate locations help us find you faster.</h3>
        <p class="text-xs text-blue-800 mt-1 leading-relaxed">
          When adding an address, try to include specific landmarks (e.g., "Green gate," "Near the chapel") to help our field technicians locate you quickly.
        </p>
      </div>
    </div>
    
    <!-- Loading State -->
    <div id="loadingState" class="flex justify-center items-center py-20">
      <div class="text-center">
        <i data-lucide="loader-2" class="w-12 h-12 text-blue-600 animate-spin mx-auto mb-4"></i>
        <p class="text-slate-500">Loading your addresses...</p>
      </div>
    </div>

    <!-- Empty State -->
    <div id="emptyState" class="hidden text-center py-16">
      <div class="max-w-md mx-auto">
        <div class="h-24 w-24 rounded-full bg-slate-100 flex items-center justify-center mx-auto mb-6">
          <i data-lucide="map-pin" class="w-12 h-12 text-slate-400"></i>
        </div>
        <h3 class="text-xl font-bold text-slate-800 mb-2">No Addresses Added Yet</h3>
        <p class="text-slate-500 mb-6">Add your service addresses to help our technicians locate you faster for service requests</p>
        <button id="openModalBtnEmpty" onclick="openAddressModal()" class="bg-blue-600 hover:bg-blue-700 text-white font-bold px-6 py-3 rounded-lg inline-flex items-center gap-2">
          <i data-lucide="plus" class="w-5 h-5"></i>
          Add Your First Address
        </button>
      </div>
    </div>

    <!-- Address Grid -->
    <div id="addressList" class="hidden grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <!-- Addresses will be dynamically loaded here -->
    </div>

  </main>

  <!-- ADD/EDIT ADDRESS MODAL -->
  <div id="addressModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 hidden">
    <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeAddressModal()"></div>
    <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-lg overflow-hidden animate-zoom-in">
      
      <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50">
        <h3 class="font-bold text-slate-800" id="modalTitle">Add New Address</h3>
        <button onclick="closeAddressModal()" class="text-slate-400 hover:text-slate-600">
          <i data-lucide="x" class="w-5 h-5"></i>
        </button>
      </div>

      <form id="addressForm" class="p-6 space-y-4">
        <input type="hidden" id="addressId" value="">
        
        <!-- Label & Type -->
        <div class="flex gap-4">
          <div class="flex-1 space-y-1.5">
            <label class="text-xs font-bold text-slate-500 uppercase">Label</label>
            <input type="text" id="label" placeholder="e.g. Home, Office" required class="w-full border border-slate-200 rounded-lg p-3 text-sm outline-none focus:ring-2 focus:ring-blue-500" />
          </div>
          <div class="w-1/3 space-y-1.5">
            <label class="text-xs font-bold text-slate-500 uppercase">Type</label>
            <select id="type" required class="w-full border border-slate-200 rounded-lg p-3 text-sm outline-none bg-white focus:ring-2 focus:ring-blue-500">
              <option value="Residential">Residential</option>
              <option value="Commercial">Commercial</option>
            </select>
          </div>
        </div>

        <!-- Barangay Dropdown -->
        <div class="space-y-1.5">
          <label class="text-xs font-bold text-slate-500 uppercase">Barangay</label>
          <select id="barangay" required class="w-full border border-slate-200 rounded-lg p-3 text-sm outline-none bg-white focus:ring-2 focus:ring-blue-500">
            <option value="">Select Barangay...</option>
            <option value="Brgy. Central">Brgy. Central</option>
            <option value="Brgy. Dahican">Brgy. Dahican</option>
            <option value="Brgy. Matiao">Brgy. Matiao</option>
            <option value="Brgy. Sainz">Brgy. Sainz</option>
            <option value="Brgy. Tamisan">Brgy. Tamisan</option>
            <option value="Brgy. Badas">Brgy. Badas</option>
            <option value="Brgy. Bobon">Brgy. Bobon</option>
            <option value="Brgy. Cabuaya">Brgy. Cabuaya</option>
            <option value="Brgy. Don Enrique Lopez">Brgy. Don Enrique Lopez</option>
            <option value="Brgy. Don Martin Marundan">Brgy. Don Martin Marundan</option>
            <option value="Brgy. Don Salvador Lopez Sr.">Brgy. Don Salvador Lopez Sr.</option>
            <option value="Brgy. Langka">Brgy. Langka</option>
            <option value="Brgy. Lawigan">Brgy. Lawigan</option>
            <option value="Brgy. Libudon">Brgy. Libudon</option>
            <option value="Brgy. Luban">Brgy. Luban</option>
            <option value="Brgy. Macambol">Brgy. Macambol</option>
            <option value="Brgy. Mayo">Brgy. Mayo</option>
            <option value="Brgy. Sanghay">Brgy. Sanghay</option>
            <option value="Brgy. Tagabakid">Brgy. Tagabakid</option>
            <option value="Brgy. Tagbinonga">Brgy. Tagbinonga</option>
            <option value="Brgy. Taguibo">Brgy. Taguibo</option>
          </select>
        </div>

        <!-- Street Address -->
        <div class="space-y-1.5">
          <label class="text-xs font-bold text-slate-500 uppercase">Street / House No.</label>
          <input type="text" id="street" placeholder="House 123, Purok 1..." required class="w-full border border-slate-200 rounded-lg p-3 text-sm outline-none focus:ring-2 focus:ring-blue-500" />
        </div>

        <!-- Landmarks -->
        <div class="space-y-1.5">
          <label class="text-xs font-bold text-slate-500 uppercase">Landmark / Notes</label>
          <textarea id="details" rows="2" placeholder="Near the yellow store, blue gate..." class="w-full border border-slate-200 rounded-lg p-3 text-sm outline-none focus:ring-2 focus:ring-blue-500 resize-none"></textarea>
        </div>

        <div class="flex items-center gap-2 pt-2">
          <input type="checkbox" id="isDefault" class="w-4 h-4 text-blue-600 rounded focus:ring-blue-500" />
          <label for="isDefault" class="text-sm text-slate-600">Set as default address</label>
        </div>

        <div class="pt-2 flex gap-3">
          <button type="button" onclick="closeAddressModal()" class="flex-1 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold py-3 rounded-xl transition-colors">
            Cancel
          </button>
          <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-xl shadow-sm transition-colors">
            <span id="submitBtnText">Save Address</span>
          </button>
        </div>

      </form>
    </div>
  </div>

  <!-- Floating Action Button (Mobile) -->
  <a href="#" id="openModalBtn2Mobile" onclick="event.preventDefault(); openAddressModal();" class="md:hidden fixed bottom-6 right-6 w-14 h-14 bg-blue-600 text-white rounded-full shadow-lg flex items-center justify-center hover:bg-blue-700 transition-all hover:scale-110 z-40">
    <i data-lucide="plus" class="w-6 h-6"></i>
  </a>

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
  <script src="/assets/api-client.js?v=6"></script>
  <!-- Dashboard JS -->
  <script src="/assets/dashboard.js"></script>
  <!-- Service Addresses Script -->
  <script src="/assets/service-addresses.js"></script>
</body>
</html>
