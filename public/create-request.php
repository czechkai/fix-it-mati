<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Service Request - FixItMati</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="min-h-screen bg-slate-50 font-sans text-slate-800">
    <!-- HEADER -->
    <header class="bg-white border-b border-slate-200 sticky top-0 z-30">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center gap-4">
                    <a href="user-dashboard.php" class="flex items-center gap-2">
                        <div class="bg-blue-600 p-1.5 rounded-lg">
                            <i data-lucide="hammer" class="text-white w-5 h-5"></i>
                        </div>
                        <span class="text-xl font-bold tracking-tight text-blue-900">FixItMati</span>
                    </a>
                </div>
                <div class="flex items-center gap-3">
                    <div class="relative p-2 text-slate-500 hover:bg-slate-100 rounded-full cursor-pointer">
                        <i data-lucide="bell" class="w-5 h-5"></i>
                    </div>
                    <div class="h-8 w-8 rounded-full bg-gradient-to-tr from-blue-500 to-cyan-400 border-2 border-white shadow-sm cursor-pointer"></div>
                </div>
            </div>
        </div>
    </header>

    <!-- MAIN CONTENT -->
    <main class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        <!-- Breadcrumb -->
        <div class="flex items-center gap-2 mb-6">
            <a href="user-dashboard.php" class="flex items-center gap-1 text-slate-500 hover:text-blue-600 transition-colors text-sm font-medium">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                Back to Dashboard
            </a>
        </div>

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

    <script src="assets/api-client.js?v=6"></script>
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
                // user_id will be set from authenticated session on server
            };
            
            UIHelpers.showLoading(submitBtn, 'Submitting...');
            
            try {
                const result = await RequestsAPI.create(requestData);
                UIHelpers.hideLoading(submitBtn);
                
                createdRequestId = result.id || result.data?.id;
                
                // Show success modal
                const modal = document.getElementById('successModal');
                const refSpan = document.getElementById('requestRef');
                refSpan.textContent = `SR-${new Date().getFullYear()}-${createdRequestId || Math.floor(Math.random() * 10000)}`;
                modal.classList.remove('hidden');
                
                lucide.createIcons();
                
            } catch (error) {
                UIHelpers.hideLoading(submitBtn);
                UIHelpers.showError(`Failed to submit request: ${error.message}`);
            }
        }

        function viewRequest() {
            if (createdRequestId) {
                window.location.href = `active-requests.php?id=${createdRequestId}`;
            } else {
                window.location.href = 'active-requests.php';
            }
        }
    </script>
</body>
</html>
