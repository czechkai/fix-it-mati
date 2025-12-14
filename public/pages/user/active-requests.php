<?php
// Authentication check
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Active Requests - FixItMati</title>
    <!-- Check authentication client-side -->
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
    <link rel="stylesheet" href="/assets/active-requests.css">
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

    <div class="page-container">
        <div class="main-content">

            <div class="layout-container">
                
                <!-- LEFT PANEL: LIST OF REQUESTS -->
                <div class="requests-panel">
                    <div class="panel-header">
                        <div class="panel-header-text">
                            <h2>Your Tickets</h2>
                            <p id="panelStats">Loading...</p>
                        </div>
                        <button class="filter-btn" id="filterBtn">
                            <svg class="icon" viewBox="0 0 24 24">
                                <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
                            </svg>
                        </button>
                    </div>
                    
                    <div class="requests-list" id="requestsList">
                        <div style="padding: 40px 20px; text-align: center; color: #94a3b8;">
                            <div style="width: 40px; height: 40px; margin: 0 auto 16px; border: 3px solid #e2e8f0; border-top-color: #3b82f6; border-radius: 50%; animation: spin 0.8s linear infinite;"></div>
                            <p style="font-size: 14px;">Loading requests...</p>
                        </div>
                    </div>
                    <style>
                        @keyframes spin { to { transform: rotate(360deg); } }
                    </style>
                </div>

                <!-- RIGHT PANEL: DETAILED TRACKING VIEW -->
                <div class="detail-panel" id="detailPanel" style="display: none;">
                    
                    <!-- Detail Header -->
                    <div class="detail-header">
                        <div class="detail-header-top">
                            <div class="detail-title-section">
                                <div class="detail-title-group">
                                    <h1 id="detailTitle"></h1>
                                    <span class="detail-status-badge" id="detailStatus"></span>
                                </div>
                                <div class="detail-meta">
                                    <div class="detail-meta-item" id="detailLocation"></div>
                                    <div class="detail-meta-item" id="detailDate"></div>
                                </div>
                            </div>
                            <button class="more-btn" id="moreBtn">
                                <svg class="icon" viewBox="0 0 24 24">
                                    <circle cx="12" cy="12" r="1"></circle>
                                    <circle cx="12" cy="5" r="1"></circle>
                                    <circle cx="12" cy="19" r="1"></circle>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Split Content: Timeline & Info -->
                    <div class="detail-content">
                        <div class="detail-layout">
                            
                            <!-- Timeline Section -->
                            <div class="timeline-section">
                                <h3 class="section-title">Progress Tracker</h3>
                                <div class="timeline" id="timeline"></div>
                            </div>

                            <!-- Details Sidebar -->
                            <div class="details-sidebar">
                                
                                <!-- Assigned Tech Card -->
                                <div class="info-card">
                                    <h4 class="info-card-title">Assigned Technician</h4>
                                    <div class="technician-info">
                                        <div class="technician-avatar">
                                            <svg class="icon" viewBox="0 0 24 24">
                                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                                <circle cx="12" cy="7" r="4"></circle>
                                            </svg>
                                        </div>
                                        <div class="technician-details">
                                            <p id="technicianName"></p>
                                            <p id="organizationName"></p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Description Block -->
                                <div>
                                    <h4 class="info-card-title">Issue Description</h4>
                                    <p class="description-box" id="issueDescription"></p>
                                </div>
                                
                                <!-- Attachments -->
                                <div>
                                    <h4 class="info-card-title">Attachments</h4>
                                    <div class="attachments-grid">
                                        <div class="attachment-box">
                                            <svg class="icon" viewBox="0 0 24 24">
                                                <path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"></path>
                                            </svg>
                                        </div>
                                        <div class="attachment-image">
                                            <div class="attachment-image-overlay"></div>
                                            <img src="https://via.placeholder.com/64" alt="Evidence">
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                    <!-- Comment Input Footer -->
                    <div class="comment-footer">
                        <div class="comment-form">
                            <input 
                                type="text" 
                                id="messageInput"
                                placeholder="Type a message to support..." 
                                class="comment-input"
                            >
                            <button class="send-btn" id="sendBtn">
                                <svg class="icon" viewBox="0 0 24 24">
                                    <line x1="22" y1="2" x2="11" y2="13"></line>
                                    <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                                </svg>
                            </button>
                        </div>
                    </div>

                </div>
            </div>
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

    <script src="/assets/api-client.js?v=7"></script>
    <script src="/assets/dashboard.js?v=20"></script>
    <script src="/assets/active-requests.js?v=12"></script>
    <script>lucide.createIcons();</script>
</body>
</html>
