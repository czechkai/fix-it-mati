<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Service Request - FixItMati</title>
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
                    <div class="flex items-center gap-2">
                        <div class="bg-blue-600 p-1.5 rounded-lg">
                            <i data-lucide="hammer" class="text-white w-5 h-5"></i>
                        </div>
                        <span class="text-xl font-bold tracking-tight text-blue-900">FixItMati</span>
                    </div>
                </div>
                <div class="hidden md:flex flex-1 max-w-lg mx-8 relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i data-lucide="search" class="h-5 w-5 text-slate-400"></i>
                    </div>
                    <input type="text" class="block w-full pl-10 pr-3 py-2 border border-slate-200 rounded-full leading-5 bg-slate-50 placeholder-slate-400 focus:outline-none focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition duration-150 ease-in-out" placeholder="Search requests, announcements, or help articles..." />
                    <div class="absolute inset-y-0 right-0 pr-2 flex items-center">
                        <span class="text-xs border border-slate-200 rounded px-1.5 py-0.5 text-slate-400">/</span>
                    </div>
                </div>
                <div class="flex items-center gap-3">
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
                <nav class="flex -mb-px space-x-8">
                    <a href="user-dashboard.php" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm flex items-center gap-2 transition-colors border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300">
                        <i data-lucide="arrow-left" class="w-4 h-4"></i> Back to Dashboard
                    </a>
                </nav>
            </div>
        </div>
    </div>

    <!-- MAIN CONTENT -->
    <main class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        <!-- Form Card -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 md:p-8">
            <div class="flex items-center gap-3 mb-6">
                <div class="bg-blue-100 p-3 rounded-lg">
                    <i data-lucide="plus-circle" class="w-6 h-6 text-blue-600"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-slate-800">Create Service Request</h1>
                    <p class="text-sm text-slate-500">Report an issue or request assistance</p>
                </div>
            </div>

            <form id="createRequestForm" class="space-y-6">
                
                <!-- Category Selection -->
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-3">Service Category *</label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="category-option">
                            <input type="radio" name="category" value="water" class="peer hidden" required>
                            <div class="p-4 border-2 border-slate-200 rounded-lg cursor-pointer transition-all hover:border-blue-300 peer-checked:border-blue-600 peer-checked:bg-blue-50 flex flex-col items-center gap-2">
                                <i data-lucide="droplets" class="w-8 h-8 text-blue-500"></i>
                                <span class="text-sm font-medium text-slate-700">Water</span>
                            </div>
                        </label>
                        <label class="category-option">
                            <input type="radio" name="category" value="electricity" class="peer hidden">
                            <div class="p-4 border-2 border-slate-200 rounded-lg cursor-pointer transition-all hover:border-blue-300 peer-checked:border-blue-600 peer-checked:bg-blue-50 flex flex-col items-center gap-2">
                                <i data-lucide="zap" class="w-8 h-8 text-yellow-500"></i>
                                <span class="text-sm font-medium text-slate-700">Electricity</span>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Title -->
                <div>
                    <label for="title" class="block text-sm font-semibold text-slate-700 mb-2">Issue Title *</label>
                    <input 
                        type="text" 
                        id="title" 
                        name="title" 
                        required
                        placeholder="e.g., Leaking water pipe on Main Street"
                        class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all"
                    >
                </div>

                <!-- Location -->
                <div>
                    <label for="location" class="block text-sm font-semibold text-slate-700 mb-2">Location *</label>
                    <div class="relative">
                        <i data-lucide="map-pin" class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400"></i>
                        <input 
                            type="text" 
                            id="location" 
                            name="location" 
                            required
                            placeholder="Street, Barangay, Mati City"
                            class="w-full pl-11 pr-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all"
                        >
                    </div>
                </div>

                <!-- Description -->
                <div>
                    <label for="description" class="block text-sm font-semibold text-slate-700 mb-2">Description *</label>
                    <textarea 
                        id="description" 
                        name="description" 
                        rows="5" 
                        required
                        placeholder="Please describe the issue in detail. Include any relevant information that will help us address your concern."
                        class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all resize-none"
                    ></textarea>
                    <p class="mt-1 text-xs text-slate-500">Minimum 20 characters</p>
                </div>

                <!-- Priority -->
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-3">Priority Level</label>
                    <div class="flex gap-3">
                        <label class="flex-1">
                            <input type="radio" name="priority" value="low" class="peer hidden">
                            <div class="p-3 border-2 border-slate-200 rounded-lg cursor-pointer transition-all hover:border-green-300 peer-checked:border-green-600 peer-checked:bg-green-50 text-center">
                                <span class="text-sm font-medium text-slate-700">Low</span>
                            </div>
                        </label>
                        <label class="flex-1">
                            <input type="radio" name="priority" value="medium" class="peer hidden" checked>
                            <div class="p-3 border-2 border-slate-200 rounded-lg cursor-pointer transition-all hover:border-blue-300 peer-checked:border-blue-600 peer-checked:bg-blue-50 text-center">
                                <span class="text-sm font-medium text-slate-700">Medium</span>
                            </div>
                        </label>
                        <label class="flex-1">
                            <input type="radio" name="priority" value="high" class="peer hidden">
                            <div class="p-3 border-2 border-slate-200 rounded-lg cursor-pointer transition-all hover:border-red-300 peer-checked:border-red-600 peer-checked:bg-red-50 text-center">
                                <span class="text-sm font-medium text-slate-700">High</span>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Submit Buttons -->
                <div class="flex gap-3 pt-4 border-t border-slate-200">
                    <button 
                        type="button" 
                        onclick="window.location.href='user-dashboard.php'"
                        class="flex-1 px-6 py-3 border border-slate-300 text-slate-700 font-semibold rounded-lg hover:bg-slate-50 transition-colors"
                    >
                        Cancel
                    </button>
                    <button 
                        type="submit" 
                        id="submitBtn"
                        class="flex-1 px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition-colors flex items-center justify-center gap-2"
                    >
                        <i data-lucide="send" class="w-4 h-4"></i>
                        Submit Request
                    </button>
                </div>

            </form>
        </div>

        <!-- Success Modal -->
        <div id="successModal" class="hidden fixed inset-0 bg-slate-900/50 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-xl p-8 max-w-md w-full shadow-2xl animate-fade-in">
                <div class="text-center">
                    <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i data-lucide="check-circle-2" class="w-10 h-10 text-green-600"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-slate-800 mb-2">Request Submitted!</h3>
                    <p class="text-slate-600 mb-1">Your service request has been received.</p>
                    <p class="text-sm text-slate-500 mb-6">Reference: <span id="requestRef" class="font-mono font-semibold"></span></p>
                    <div class="flex gap-3">
                        <button 
                            onclick="window.location.href='user-dashboard.php'" 
                            class="flex-1 px-4 py-2 border border-slate-300 text-slate-700 font-medium rounded-lg hover:bg-slate-50"
                        >
                            Go to Dashboard
                        </button>
                        <button 
                            onclick="viewRequest()" 
                            class="flex-1 px-4 py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700"
                        >
                            View Request
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </main>

    <script src="assets/api-client.js?v=7"></script>
    <script>
        let createdRequestId = null;

        document.addEventListener('DOMContentLoaded', () => {
            lucide.createIcons();
            
            const form = document.getElementById('createRequestForm');
            form.addEventListener('submit', handleSubmit);
            
            // Check if user is logged in
            const token = sessionStorage.getItem('auth_token');
            if (!token) {
                window.location.href = 'login.php';
            }
        });

        async function handleSubmit(e) {
            e.preventDefault();
            
            const submitBtn = document.getElementById('submitBtn');
            const formData = new FormData(e.target);
            
            // Validate description length
            const description = formData.get('description');
            if (description.length < 20) {
                alert('Please provide a more detailed description (at least 20 characters)');
                return;
            }
            
            // Prepare request data
            const requestData = {
                title: formData.get('title'),
                description: description,
                category: formData.get('category'),
                priority: formData.get('priority') || 'normal',
                location: formData.get('location')
            };
            
            // Disable button and show loading
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<svg class="animate-spin w-4 h-4" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg><span class="ml-2">Submitting...</span>';
            
            try {
                const result = await ApiClient.post('/requests', requestData);
                
                if (result.success) {
                    createdRequestId = result.data?.id || result.data?.request?.id;
                    
                    // Show success modal
                    const modal = document.getElementById('successModal');
                    const refSpan = document.getElementById('requestRef');
                    refSpan.textContent = `#${createdRequestId ? createdRequestId.substring(0, 8) : Math.floor(Math.random() * 10000)}`;
                    modal.classList.remove('hidden');
                    
                    lucide.createIcons();
                } else {
                    throw new Error(result.error || 'Failed to submit request');
                }
                
            } catch (error) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i data-lucide="send" class="w-4 h-4"></i><span>Submit Request</span>';
                lucide.createIcons();
                
                console.error('Submission error:', error);
                alert(`Failed to submit request: ${error.message || 'Unknown error'}`);
            }
        }

        function viewRequest() {
            window.location.href = 'active-requests.php';
        }
    </script>

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
    <script src="/assets/dashboard.js"></script>
</body>
</html>
