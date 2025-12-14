<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payments & Billing - FixItMati</title>
    <!-- Authentication check and cross-tab sync -->
    <script src="/assets/auth-check.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link rel="stylesheet" href="/assets/style.css">
</head>
<body class="min-h-screen bg-slate-50 font-sans text-slate-800">
    <!-- HEADER -->
    <header class="bg-white border-b border-slate-200 sticky top-0 z-30">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
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
                <div class="flex items-center gap-3">
                    <a href="/user-dashboard.php" class="p-2 text-slate-500 hover:bg-slate-100 rounded-full cursor-pointer transition-colors" title="Go to Dashboard">
                        <i data-lucide="home" class="w-5 h-5"></i>
                    </a>
                    <div class="relative p-2 text-slate-500 hover:bg-slate-100 rounded-full cursor-pointer" id="notificationBtn">
                        <i data-lucide="bell" class="w-5 h-5"></i>
                        <span class="absolute top-1.5 right-1.5 block h-2 w-2 rounded-full bg-red-500 ring-2 ring-white" id="notificationDot"></span>
                    </div>
                    <div class="relative">
                        <div class="h-8 w-8 rounded-full bg-gradient-to-tr from-blue-500 to-cyan-400 border-2 border-white shadow-sm cursor-pointer" id="profileBtn"></div>
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
                        <h1 class="text-lg font-bold text-slate-800">Payments & Billing</h1>
                    </div>
                </nav>
            </div>
        </div>
    </div>

    <!-- MAIN PAYMENTS CONTENT -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        <div class="flex flex-col lg:flex-row gap-8">
            
            <!-- LEFT COLUMN: Current Bills & Total -->
            <div class="flex-1 space-y-6">
                
                <!-- User Payment Information Card -->
                <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl p-6 shadow-lg text-white relative overflow-hidden">
                    <div class="absolute top-0 right-0 p-6 opacity-10">
                        <i data-lucide="wallet" class="w-32 h-32"></i>
                    </div>
                    <div class="relative z-10">
                        <p class="text-blue-100 text-sm font-medium mb-3">User Payment Information</p>
                        <div class="bg-white/15 backdrop-blur-sm rounded-lg p-4 mb-4 space-y-3">
                            <div class="flex justify-between items-center">
                                <span class="text-blue-100 text-sm font-medium">Amount Due:</span>
                                <span class="text-white text-lg font-bold" id="totalAmount">Loading...</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-blue-100 text-sm font-medium">Due Date:</span>
                                <span class="text-white text-base font-semibold" id="dueDate">Loading...</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-blue-100 text-sm font-medium">Account Status:</span>
                                <span class="bg-amber-400 text-amber-900 px-3 py-1 rounded-md text-xs font-bold" id="accountStatus">Loading...</span>
                            </div>
                        </div>
                        
                        <div class="flex gap-3">
                            <button class="bg-white text-blue-700 hover:bg-blue-50 px-6 py-2.5 rounded-lg text-sm font-bold shadow-sm transition-colors flex items-center gap-2" id="payAllBtn">
                                Pay Bill Now
                                <i data-lucide="chevron-right" class="w-4 h-4"></i>
                            </button>
                            <button class="bg-blue-700 hover:bg-blue-600 text-white border border-blue-500 px-4 py-2.5 rounded-lg text-sm font-medium transition-colors" id="statementBtn">
                                View Statement
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Bill Breakdown List -->
                <div>
                    <h3 class="text-slate-800 font-bold mb-4 flex items-center gap-2">
                        <i data-lucide="file-text" class="w-5 h-5 text-slate-400"></i>
                        Current Charges
                    </h3>
                    <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden divide-y divide-slate-100" id="billsList">
                        <!-- Bills will be dynamically inserted here by JavaScript -->
                    </div>
                </div>

            </div>

            <!-- RIGHT COLUMN: Methods & History -->
            <div class="w-full lg:w-96 space-y-6">
                
                <!-- Accepted Payment Methods Widget -->
                <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm">
                    <h4 class="text-sm font-bold text-slate-800 mb-3">Accepted Payment Methods</h4>
                    <div class="flex gap-2">
                        <button class="h-10 flex-1 bg-blue-50 rounded border border-blue-100 flex items-center justify-center text-xs font-bold text-blue-800 cursor-pointer hover:bg-blue-100 transition-colors" id="gcashBtn">
                            GCash
                        </button>
                        <button class="h-10 flex-1 bg-green-50 rounded border border-green-100 flex items-center justify-center text-xs font-bold text-green-800 cursor-pointer hover:bg-green-100 transition-colors" id="mayaBtn">
                            Maya
                        </button>
                        <button class="h-10 flex-1 bg-slate-50 rounded border border-slate-200 flex items-center justify-center text-xs font-bold text-slate-600 cursor-pointer hover:bg-slate-100 transition-colors" id="cardBtn">
                            Card
                        </button>
                    </div>
                </div>

                <!-- Transaction History Widget -->
                <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
                    <div class="p-4 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
                        <h4 class="text-sm font-bold text-slate-800">Recent Transactions</h4>
                        <button class="text-xs text-blue-600 hover:underline" id="viewAllBtn">View All</button>
                    </div>
                    <div class="divide-y divide-slate-100" id="transactionList">
                        <!-- Transactions will be dynamically inserted here by JavaScript -->
                    </div>
                </div>

                <!-- Help/Support Box -->
                <div class="bg-blue-50 rounded-xl p-4 flex gap-3 items-start border border-blue-100">
                    <i data-lucide="alert-circle" class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5"></i>
                    <div>
                        <h5 class="text-sm font-bold text-blue-900">Need help with your bill?</h5>
                        <p class="text-xs text-blue-800 mt-1">If you notice an irregularity in your consumption reading, please submit a "Billing Inquiry" request immediately.</p>
                        <button class="text-xs font-bold text-blue-700 mt-2 hover:underline" id="reportBtn">Report an issue &rarr;</button>
                    </div>
                </div>

            </div>
        </div>
    </main>

    <script src="assets/api-client.js?v=6"></script>
    <script src="assets/payments.js?v=3"></script>
    <script>lucide.createIcons();</script>

    <!-- Payment Modal -->
    <div id="paymentModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4" style="display: none;">
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full overflow-hidden">
            <!-- Modal Header -->
            <div class="bg-gradient-to-r from-blue-500 to-blue-600 p-6">
                <div class="flex justify-between items-center">
                    <h3 class="text-xl font-bold text-white">Select Payment Method</h3>
                    <button id="closeModal" class="text-white hover:bg-white/20 rounded-full p-2 transition-colors">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>
            </div>

            <!-- Modal Body -->
            <div class="p-6">
                <div class="mb-6">
                    <div class="flex justify-between items-baseline mb-2">
                        <span class="text-slate-600 text-sm">Total Amount:</span>
                        <span class="text-2xl font-bold text-slate-800" id="modalTotalAmount">₱0.00</span>
                    </div>
                    <p class="text-slate-500 text-xs">Choose your preferred payment method below</p>
                </div>

                <!-- Payment Options -->
                <div class="space-y-3">
                    <!-- GCash -->
                    <button class="payment-option-btn w-full flex items-center gap-4 p-4 border-2 border-slate-200 rounded-xl hover:border-blue-500 hover:bg-blue-50 transition-all" data-gateway="gcash">
                        <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0">
                            <span class="text-blue-600 font-bold text-sm">G</span>
                        </div>
                        <div class="text-left flex-1">
                            <div class="font-bold text-slate-800">GCash</div>
                            <div class="text-xs text-slate-500">Pay via GCash mobile wallet</div>
                        </div>
                        <i data-lucide="chevron-right" class="w-5 h-5 text-slate-400"></i>
                    </button>

                    <!-- Maya -->
                    <button class="payment-option-btn w-full flex items-center gap-4 p-4 border-2 border-slate-200 rounded-xl hover:border-green-500 hover:bg-green-50 transition-all" data-gateway="maya">
                        <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center flex-shrink-0">
                            <span class="text-green-600 font-bold text-sm">M</span>
                        </div>
                        <div class="text-left flex-1">
                            <div class="font-bold text-slate-800">Maya</div>
                            <div class="text-xs text-slate-500">Pay via Maya (PayMaya)</div>
                        </div>
                        <i data-lucide="chevron-right" class="w-5 h-5 text-slate-400"></i>
                    </button>

                    <!-- Card -->
                    <button class="payment-option-btn w-full flex items-center gap-4 p-4 border-2 border-slate-200 rounded-xl hover:border-purple-500 hover:bg-purple-50 transition-all" data-gateway="card">
                        <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center flex-shrink-0">
                            <i data-lucide="credit-card" class="w-6 h-6 text-purple-600"></i>
                        </div>
                        <div class="text-left flex-1">
                            <div class="font-bold text-slate-800">Card</div>
                            <div class="text-xs text-slate-500">Credit or Debit Card</div>
                        </div>
                        <i data-lucide="chevron-right" class="w-5 h-5 text-slate-400"></i>
                    </button>
                </div>

                <!-- Loading State -->
                <div id="paymentProcessing" class="hidden mt-6 text-center">
                    <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                    <p class="text-sm text-slate-600 mt-2">Processing payment...</p>
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
                <a href="/user-dashboard.php" class="block px-3 py-2 text-base font-medium text-slate-600 hover:bg-slate-50 rounded-md">Dashboard</a>
                <a href="/active-requests.php" class="block px-3 py-2 text-base font-medium text-slate-600 hover:bg-slate-50 rounded-md">My Requests</a>
                <a href="/announcements.php" class="block px-3 py-2 text-base font-medium text-slate-600 hover:bg-slate-50 rounded-md">Announcements</a>
                <a href="/payments.php" class="block px-3 py-2 text-base font-medium text-slate-600 hover:bg-slate-50 rounded-md">Payments</a>
                <a href="#" class="block px-3 py-2 text-base font-medium text-slate-600 hover:bg-slate-50 rounded-md">Help Center</a>
            </nav>
        </div>
    </div>

    <script src="/assets/api-client.js"></script>
    <script src="/assets/dashboard.js"></script>
    <script src="/assets/payments.js"></script>
    <script>lucide.createIcons();</script>
</body>
</html>
