/**
 * Linked Meters Management
 * Real-time meter linking and management with database integration
 */

let currentEditMeterId = null;

// Format currency
function formatCurrency(amount) {
    if (!amount) return 'N/A';
    return '₱' + parseFloat(amount).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

// Format date
function formatDate(dateString) {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
}

// Get meter icon and colors
function getMeterStyles(type) {
    if (type === 'water') {
        return {
            icon: 'droplets',
            colorClass: 'blue',
            borderColor: 'bg-blue-500',
            iconBg: 'bg-blue-100',
            iconColor: 'text-blue-600'
        };
    } else {
        return {
            icon: 'zap',
            colorClass: 'amber',
            borderColor: 'bg-amber-500',
            iconBg: 'bg-amber-100',
            iconColor: 'text-amber-600'
        };
    }
}

// Load all meters
async function loadMeters() {
    const loadingState = document.getElementById('loadingState');
    const emptyState = document.getElementById('emptyState');
    const metersGrid = document.getElementById('metersGrid');
    const statsSection = document.getElementById('statsSection');
    const filterSection = document.getElementById('filterSection');
    
    try {
        loadingState.classList.remove('hidden');
        emptyState.classList.add('hidden');
        metersGrid.classList.add('hidden');
        statsSection.classList.add('hidden');
        if (filterSection) filterSection.classList.add('hidden');
        
        const response = await ApiClient.get('/linked-meters');
        
        if (response.success) {
            const meters = response.data.meters;
            allMeters = meters; // Store globally for filtering
            
            if (meters.length === 0) {
                loadingState.classList.add('hidden');
                emptyState.classList.remove('hidden');
            } else {
                // Update statistics
                updateStatistics(meters);
                
                // Reset filter and apply
                currentFilter = 'all';
                applyFilters();
                
                // Show content
                loadingState.classList.add('hidden');
                statsSection.classList.remove('hidden');
                if (filterSection) {
                    filterSection.classList.remove('hidden');
                    lucide.createIcons();
                }
                metersGrid.classList.remove('hidden');
            }
        } else {
            throw new Error(response.message || 'Failed to load meters');
        }
    } catch (error) {
        console.error('Error loading meters:', error);
        loadingState.classList.add('hidden');
        showToast('Failed to load meters', 'error');
    }
}

// Update statistics
function updateStatistics(meters) {
    const totalMeters = meters.length;
    const waterMeters = meters.filter(m => m.meter_type === 'water').length;
    const electricMeters = meters.filter(m => m.meter_type === 'electricity').length;
    
    document.getElementById('totalMeters').textContent = totalMeters;
    document.getElementById('waterMeters').textContent = waterMeters;
    document.getElementById('electricMeters').textContent = electricMeters;
}

// Render meters
function renderMeters(meters) {
    const metersGrid = document.getElementById('metersGrid');
    
    // Clear existing meters
    metersGrid.innerHTML = '';
    
    // Render each meter
    meters.forEach(meter => {
        const styles = getMeterStyles(meter.meter_type);
        const isActive = meter.status === 'active';
        const status = isActive ? 
            '<div class="px-2 py-1 bg-green-100 text-green-700 text-[10px] font-bold uppercase rounded-full flex items-center gap-1"><i data-lucide="check-circle-2" class="w-2.5 h-2.5"></i> Active</div>' :
            '<div class="px-2 py-1 bg-slate-100 text-slate-700 text-[10px] font-bold uppercase rounded-full flex items-center gap-1"><i data-lucide="pause-circle" class="w-2.5 h-2.5"></i> Inactive</div>';
        
        // Format account holder name
        const accountHolder = meter.account_holder_name || 'N/A';
        
        // Calculate days since last bill
        let lastBillInfo = 'N/A';
        if (meter.last_bill_date) {
            const lastBillDate = new Date(meter.last_bill_date);
            const today = new Date();
            const daysDiff = Math.floor((today - lastBillDate) / (1000 * 60 * 60 * 24));
            lastBillInfo = `${formatDate(meter.last_bill_date)} (${daysDiff} days ago)`;
        }
        
        const meterCard = `
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm hover:shadow-md transition-all group relative overflow-hidden ${!isActive ? 'opacity-70' : ''}">
                <div class="h-1.5 w-full ${styles.borderColor}"></div>
                <div class="p-5">
                    <div class="flex justify-between items-start mb-4">
                        <div class="flex items-center gap-3">
                            <div class="p-2.5 rounded-lg ${styles.iconBg} ${styles.iconColor}">
                                <i data-lucide="${styles.icon}" class="w-6 h-6"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-slate-800">${meter.alias || 'My Meter'}</h3>
                                <p class="text-xs text-slate-500">${meter.provider}</p>
                                <p class="text-[10px] text-slate-400 mt-0.5 capitalize">${meter.meter_type} Meter</p>
                            </div>
                        </div>
                        ${status}
                    </div>
                    <div class="space-y-2 mb-5">
                        <div class="flex justify-between py-2 border-b border-slate-50">
                            <span class="text-xs text-slate-400 font-medium uppercase">Account No.</span>
                            <span class="text-sm font-mono text-slate-700 bg-slate-100 px-2 py-0.5 rounded">${meter.account_number}</span>
                        </div>
                        <div class="flex justify-between py-2 border-b border-slate-50">
                            <span class="text-xs text-slate-400 font-medium uppercase">Account Holder</span>
                            <span class="text-sm text-slate-700 text-right">${accountHolder}</span>
                        </div>
                        <div class="flex justify-between py-2 border-b border-slate-50">
                            <span class="text-xs text-slate-400 font-medium uppercase">Last Bill</span>
                            <span class="text-sm font-bold text-slate-900">${formatCurrency(meter.last_bill_amount)}</span>
                        </div>
                        ${meter.last_bill_date ? `
                        <div class="flex justify-between py-2 border-b border-slate-50">
                            <span class="text-xs text-slate-400 font-medium uppercase">Bill Date</span>
                            <span class="text-xs text-slate-600">${formatDate(meter.last_bill_date)}</span>
                        </div>
                        ` : ''}
                        ${meter.last_reading ? `
                        <div class="flex justify-between py-2 border-b border-slate-50">
                            <span class="text-xs text-slate-400 font-medium uppercase">Last Reading</span>
                            <span class="text-sm text-slate-700 font-mono">${meter.last_reading}</span>
                        </div>
                        ` : ''}
                        ${meter.address ? `
                        <div class="pt-2">
                            <span class="text-xs text-slate-400 font-medium uppercase block mb-1">Address</span>
                            <span class="text-xs text-slate-600 block">${meter.address}</span>
                        </div>
                        ` : ''}
                    </div>
                    <div class="flex gap-2">
                        <button onclick="toggleMeterStatus('${meter.id}', '${meter.status}')" class="py-2 px-3 border ${isActive ? 'border-amber-200 text-amber-600 hover:bg-amber-50' : 'border-green-200 text-green-600 hover:bg-green-50'} rounded-lg transition-colors" title="${isActive ? 'Deactivate' : 'Activate'} Meter">
                            <i data-lucide="${isActive ? 'pause' : 'play'}" class="w-4 h-4"></i>
                        </button>
                        <button onclick="editMeter('${meter.id}')" class="flex-1 py-2 border border-slate-200 rounded-lg text-xs font-bold text-slate-600 hover:bg-slate-50 flex items-center justify-center gap-2 transition-colors">
                            <i data-lucide="edit-2" class="w-3.5 h-3.5"></i> Edit
                        </button>
                        <button onclick="deleteMeter('${meter.id}')" class="py-2 px-3 border border-red-100 rounded-lg text-red-500 hover:bg-red-50 transition-colors" title="Unlink Meter">
                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;
        
        metersGrid.insertAdjacentHTML('beforeend', meterCard);
    });
    
    // Add the "Add New" card
    const addNewCard = `
        <button id="openModalBtnGrid" class="border-2 border-dashed border-slate-200 rounded-xl p-6 flex flex-col items-center justify-center text-slate-400 hover:border-blue-400 hover:text-blue-500 hover:bg-blue-50/50 transition-all min-h-[280px] group">
            <div class="h-12 w-12 rounded-full bg-slate-100 flex items-center justify-center mb-3 group-hover:bg-blue-100 transition-colors"> 
                <i data-lucide="plus" class="w-6 h-6"></i>
            </div>
            <span class="font-bold text-sm">Link Another Meter</span>
            <span class="text-xs mt-1 opacity-70">Add water or electric accounts</span>
        </button>
    `;
    
    metersGrid.insertAdjacentHTML('beforeend', addNewCard);
    
    // Re-render icons
    lucide.createIcons();
    
    // Re-attach event listeners
    const openModalBtnGrid = document.getElementById('openModalBtnGrid');
    if (openModalBtnGrid) {
        openModalBtnGrid.addEventListener('click', openMeterModal);
    }
}

// Show toast notification
function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    const bgColor = type === 'success' ? 'bg-green-500' : 'bg-red-500';
    toast.className = `fixed bottom-4 right-4 ${bgColor} text-white px-6 py-3 rounded-lg shadow-lg z-50 flex items-center gap-2`;
    toast.innerHTML = `
        <i data-lucide="${type === 'success' ? 'check-circle' : 'alert-circle'}" class="w-5 h-5"></i>
        <span>${message}</span>
    `;
    document.body.appendChild(toast);
    lucide.createIcons();
    
    setTimeout(() => {
        toast.remove();
    }, 3000);
}

// Open modal
function openMeterModal() {
    currentEditMeterId = null;
    const modal = document.getElementById('addMeterModal');
    const form = document.getElementById('linkMeterForm');
    const modalTitle = modal.querySelector('h3');
    const submitBtnText = document.getElementById('submitBtnText');
    
    modalTitle.textContent = 'Link New Meter';
    submitBtnText.textContent = 'Verify & Link Meter';
    form.reset();
    
    // Reset provider select to default
    const providerInput = document.getElementById('providerInput');
    if (providerInput) {
        providerInput.value = '';
    }
    
    // Enable all inputs
    document.querySelectorAll('input[name="meter_type"]').forEach(input => {
        input.disabled = false;
        input.parentElement.classList.remove('opacity-50', 'cursor-not-allowed');
    });
    
    const accountNumberInput = document.getElementById('accountNumberInput');
    accountNumberInput.readOnly = false;
    accountNumberInput.classList.remove('bg-slate-50', 'cursor-not-allowed');
    
    const accountHolderInput = document.getElementById('accountHolderInput');
    accountHolderInput.readOnly = false;
    accountHolderInput.classList.remove('bg-slate-50', 'cursor-not-allowed');
    
    modal.classList.remove('hidden');
}

// Close modal
function closeMeterModal() {
    const modal = document.getElementById('addMeterModal');
    const form = document.getElementById('linkMeterForm');
    
    modal.classList.add('hidden');
    currentEditMeterId = null;
    form.reset();
    
    // Re-enable all inputs for next time
    document.querySelectorAll('input[name="meter_type"]').forEach(input => {
        input.disabled = false;
        input.parentElement.classList.remove('opacity-50', 'cursor-not-allowed');
    });
    
    const accountNumberInput = document.getElementById('accountNumberInput');
    accountNumberInput.readOnly = false;
    accountNumberInput.classList.remove('bg-slate-50', 'cursor-not-allowed');
    
    const accountHolderInput = document.getElementById('accountHolderInput');
    accountHolderInput.readOnly = false;
    accountHolderInput.classList.remove('bg-slate-50', 'cursor-not-allowed');
}

// Edit meter
async function editMeter(meterId) {
    try {
        const response = await ApiClient.get(`/linked-meters/${meterId}`);
        
        if (response.success) {
            const meter = response.data.meter;
            currentEditMeterId = meterId;
            
            // Populate form
            const meterTypeInput = document.querySelector(`input[name="meter_type"][value="${meter.meter_type}"]`);
            if (meterTypeInput) {
                meterTypeInput.checked = true;
                // Disable meter type selection in edit mode
                document.querySelectorAll('input[name="meter_type"]').forEach(input => {
                    input.disabled = true;
                    input.parentElement.classList.add('opacity-50', 'cursor-not-allowed');
                });
            }
            
            document.getElementById('providerInput').value = meter.provider;
            
            const accountNumberInput = document.getElementById('accountNumberInput');
            accountNumberInput.value = meter.account_number;
            accountNumberInput.readOnly = true;
            accountNumberInput.classList.add('bg-slate-50', 'cursor-not-allowed');
            
            const accountHolderInput = document.getElementById('accountHolderInput');
            accountHolderInput.value = meter.account_holder_name;
            accountHolderInput.readOnly = true;
            accountHolderInput.classList.add('bg-slate-50', 'cursor-not-allowed');
            
            document.getElementById('aliasInput').value = meter.alias || '';
            document.getElementById('addressInput').value = meter.address || '';
            
            // Update modal title and button
            const modal = document.getElementById('addMeterModal');
            const modalTitle = modal.querySelector('h3');
            const submitBtnText = document.getElementById('submitBtnText');
            
            modalTitle.textContent = 'Edit Meter Details';
            submitBtnText.textContent = 'Update Meter';
            
            modal.classList.remove('hidden');
        } else {
            throw new Error(response.message || 'Failed to load meter');
        }
    } catch (error) {
        console.error('Error loading meter:', error);
        showToast('Failed to load meter details', 'error');
    }
}

// Delete meter
async function deleteMeter(meterId) {
    const ok = await UIHelpers.confirm({
        title: 'Unlink Meter',
        message: 'Are you sure you want to unlink this meter?',
        confirmText: 'Unlink',
        cancelText: 'Cancel',
        variant: 'danger'
    });
    if (!ok) return;
    
    try {
        const response = await ApiClient.delete(`/linked-meters/${meterId}`);
        
        if (response.success) {
            showToast('Meter unlinked successfully', 'success');
            loadMeters();
        } else {
            throw new Error(response.message || 'Failed to unlink meter');
        }
    } catch (error) {
        console.error('Error deleting meter:', error);
        showToast(error.message || 'Failed to unlink meter', 'error');
    }
}

// Validate account number format based on meter type and provider
function validateAccountNumber(accountNumber, meterType, provider) {
    if (!accountNumber || !meterType || !provider) {
        return { valid: false, message: 'Please fill in all required fields' };
    }
    
    accountNumber = accountNumber.trim();
    
    // Water meter validation (Mati Water District)
    if (meterType === 'water' && provider.includes('Mati Water')) {
        // 9-12 digits, can have dashes
        const waterPattern = /^[\d\-]{9,15}$/;
        if (!waterPattern.test(accountNumber)) {
            return { 
                valid: false, 
                message: 'Water account number should be 9-12 digits (e.g., 123456789 or 123-456-789)' 
            };
        }
        // Check that it has at least 9 actual digits
        const digitsOnly = accountNumber.replace(/\-/g, '');
        if (digitsOnly.length < 9 || digitsOnly.length > 12) {
            return { 
                valid: false, 
                message: 'Water account number should contain 9-12 digits' 
            };
        }
    }
    
    // Electric meter validation (DORECO)
    if (meterType === 'electricity' && provider.includes('DORECO')) {
        // 8-15 alphanumeric characters, can have dashes
        const electricPattern = /^[A-Za-z0-9\-]{8,20}$/;
        if (!electricPattern.test(accountNumber)) {
            return { 
                valid: false, 
                message: 'Electric account number should be 8-15 characters (e.g., 12345678 or ABC-123-4567)' 
            };
        }
        // Check that it has at least 8 actual alphanumeric characters
        const charsOnly = accountNumber.replace(/\-/g, '');
        if (charsOnly.length < 8 || charsOnly.length > 15) {
            return { 
                valid: false, 
                message: 'Electric account number should contain 8-15 alphanumeric characters' 
            };
        }
    }
    
    return { valid: true, message: '' };
}

// Handle form submission
async function handleMeterSubmit(event) {
    event.preventDefault();
    
    const form = event.target;
    const submitBtn = document.getElementById('submitBtn');
    const submitBtnText = document.getElementById('submitBtnText');
    const loader = document.getElementById('loader');
    
    // Get form data directly from DOM elements (more reliable than FormData)
    const meterTypeChecked = document.querySelector('input[name="meter_type"]:checked');
    const providerSelect = document.getElementById('providerInput');
    const accountNumberInput = document.getElementById('accountNumberInput');
    const accountHolderInput = document.getElementById('accountHolderInput');
    const aliasInput = document.getElementById('aliasInput');
    const addressInput = document.getElementById('addressInput');
    
    const data = {
        meter_type: meterTypeChecked ? meterTypeChecked.value : null,
        provider: providerSelect ? providerSelect.value : null,
        account_number: accountNumberInput ? accountNumberInput.value : null,
        account_holder_name: accountHolderInput ? accountHolderInput.value : null,
        alias: aliasInput && aliasInput.value ? aliasInput.value : null,
        address: addressInput && addressInput.value ? addressInput.value : null,
        status: 'active'
    };
    
    // Validate account number format (only for new meters, not edits)
    if (!currentEditMeterId) {
        const validation = validateAccountNumber(data.account_number, data.meter_type, data.provider);
        if (!validation.valid) {
            showToast(validation.message, 'error');
            return;
        }
    }
    
    // Debug: Log collected data
    console.log('=== Form Data Collected ===');
    console.log('Meter Type:', data.meter_type);
    console.log('Provider:', data.provider);
    console.log('Account Number:', data.account_number);
    console.log('Account Holder:', data.account_holder_name);
    console.log('Alias:', data.alias);
    console.log('Address:', data.address);
    
    // Validate required fields
    if (!data.meter_type) {
        showToast('Please select a meter type', 'error');
        console.error('Validation failed: meter_type missing');
        return;
    }
    
    if (!data.provider || data.provider === '') {
        showToast('Please select a utility provider', 'error');
        console.error('Validation failed: provider missing or empty. Value:', data.provider);
        return;
    }
    
    if (!data.account_number || data.account_number.trim() === '') {
        showToast('Please enter an account number', 'error');
        return;
    }
    
    if (!data.account_holder_name || data.account_holder_name.trim() === '') {
        showToast('Please enter the account holder name', 'error');
        return;
    }
    
    // Trim whitespace from text fields
    data.account_number = data.account_number.trim();
    data.account_holder_name = data.account_holder_name.trim();
    if (data.alias) data.alias = data.alias.trim();
    if (data.address) data.address = data.address.trim();
    
    // Log data for debugging
    console.log('=== About to Submit ===');
    console.log('Full data object:', JSON.stringify(data, null, 2));
    console.log('Provider value:', data.provider);
    console.log('Provider type:', typeof data.provider);
    console.log('Provider is empty?:', !data.provider || data.provider === '');
    console.log('Is edit mode:', !!currentEditMeterId);
    
    // Start loading
    submitBtn.disabled = true;
    submitBtnText.textContent = currentEditMeterId ? 'Updating...' : 'Verifying...';
    loader.classList.remove('hidden');
    lucide.createIcons();
    
    try {
        let response;
        
        if (currentEditMeterId) {
            // Update existing meter - only send updatable fields
            const updateData = {
                provider: data.provider,
                alias: data.alias,
                address: data.address,
                status: data.status || 'active'
            };
            console.log('Updating meter:', currentEditMeterId);
            console.log('Update payload:', JSON.stringify(updateData, null, 2));
            response = await ApiClient.put(`/linked-meters/${currentEditMeterId}`, updateData);
        } else {
            // Create new meter - send all fields
            console.log('Creating new meter');
            console.log('Create payload:', JSON.stringify(data, null, 2));
            response = await ApiClient.post('/linked-meters', data);
        }
        
        if (response.success) {
            showToast(currentEditMeterId ? 'Meter updated successfully' : 'Meter linked successfully', 'success');
            closeMeterModal();
            form.reset();
            loadMeters();
        } else {
            throw new Error(response.message || 'Operation failed');
        }
    } catch (error) {
        console.error('Error submitting meter:', error);
        showToast(error.message || 'Operation failed', 'error');
    } finally {
        submitBtn.disabled = false;
        submitBtnText.textContent = currentEditMeterId ? 'Update Meter' : 'Verify & Link Meter';
        loader.classList.add('hidden');
    }
}

// Toggle meter status (activate/deactivate)
async function toggleMeterStatus(meterId, currentStatus) {
    const newStatus = currentStatus === 'active' ? 'inactive' : 'active';
    
    try {
        const response = await ApiClient.put(`/linked-meters/${meterId}`, {
            status: newStatus
        });
        
        if (response.success) {
            showToast(`Meter ${newStatus === 'active' ? 'activated' : 'deactivated'} successfully`, 'success');
            loadMeters();
        } else {
            throw new Error(response.message || 'Failed to update status');
        }
    } catch (error) {
        console.error('Error toggling status:', error);
        showToast(error.message || 'Failed to update status', 'error');
    }
}

// View meter details (expanded view)
function viewMeterDetails(meterId) {
    // This could open a detailed modal with consumption history, bills, etc.
    // For now, we'll just load the edit form
    editMeter(meterId);
}

// Store all meters globally for filtering
let allMeters = [];
let currentFilter = 'all';

// Apply filters
function applyFilters() {
    const searchTerm = document.getElementById('searchInput')?.value.toLowerCase() || '';
    
    let filtered = allMeters;
    
    // Apply type filter
    if (currentFilter !== 'all') {
        filtered = filtered.filter(m => m.meter_type === currentFilter);
    }
    
    // Apply search filter
    if (searchTerm) {
        filtered = filtered.filter(m => {
            return (
                m.alias?.toLowerCase().includes(searchTerm) ||
                m.account_number?.toLowerCase().includes(searchTerm) ||
                m.provider?.toLowerCase().includes(searchTerm) ||
                m.account_holder_name?.toLowerCase().includes(searchTerm) ||
                m.address?.toLowerCase().includes(searchTerm)
            );
        });
    }
    
    // Update counts
    document.getElementById('countAll').textContent = allMeters.length;
    document.getElementById('countWater').textContent = allMeters.filter(m => m.meter_type === 'water').length;
    document.getElementById('countElectric').textContent = allMeters.filter(m => m.meter_type === 'electricity').length;
    
    renderMeters(filtered);
}

// Set filter
function setFilter(filter) {
    currentFilter = filter;
    
    // Update button states
    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.classList.remove('bg-blue-600', 'text-white');
        btn.classList.add('bg-white', 'border', 'border-slate-200', 'text-slate-600', 'hover:bg-slate-50');
    });
    
    const activeBtn = document.getElementById(`filter${filter.charAt(0).toUpperCase() + filter.slice(1)}`);
    if (activeBtn) {
        activeBtn.classList.remove('bg-white', 'border', 'border-slate-200', 'text-slate-600', 'hover:bg-slate-50');
        activeBtn.classList.add('bg-blue-600', 'text-white');
    }
    
    applyFilters();
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    // Render all lucide icons
    lucide.createIcons();
    
    // Load meters
    loadMeters();
    
    // Real-time account number validation
    const accountNumberInput = document.getElementById('accountNumberInput');
    const accountNumberHelp = document.getElementById('accountNumberHelp');
    
    if (accountNumberInput && accountNumberHelp) {
        accountNumberInput.addEventListener('input', function() {
            const meterTypeChecked = document.querySelector('input[name="meter_type"]:checked');
            const providerSelect = document.getElementById('providerInput');
            
            if (meterTypeChecked && providerSelect && providerSelect.value && this.value) {
                const validation = validateAccountNumber(this.value, meterTypeChecked.value, providerSelect.value);
                
                if (!validation.valid) {
                    this.classList.add('border-red-300', 'focus:ring-red-500');
                    this.classList.remove('border-slate-200', 'focus:ring-blue-500');
                    accountNumberHelp.classList.add('text-red-600');
                    accountNumberHelp.classList.remove('text-slate-500');
                    accountNumberHelp.innerHTML = '<div class="font-medium">⚠️ ' + validation.message + '</div>';
                } else {
                    this.classList.remove('border-red-300', 'focus:ring-red-500');
                    this.classList.add('border-green-300', 'focus:ring-green-500');
                    accountNumberHelp.classList.remove('text-red-600');
                    accountNumberHelp.classList.add('text-green-600');
                    accountNumberHelp.innerHTML = '<div class="font-medium">✓ Valid account number format</div>';
                }
            } else {
                // Reset to default state
                this.classList.remove('border-red-300', 'focus:ring-red-500', 'border-green-300', 'focus:ring-green-500');
                this.classList.add('border-slate-200', 'focus:ring-blue-500');
                accountNumberHelp.classList.remove('text-red-600', 'text-green-600');
                accountNumberHelp.classList.add('text-slate-500');
                accountNumberHelp.innerHTML = `
                    <div class="font-medium mb-0.5">Format Guide:</div>
                    <div class="flex flex-col gap-0.5">
                        <span><strong>Water:</strong> 9-12 digits (e.g., 123456789 or 123-456-789)</span>
                        <span><strong>Electric:</strong> 8-15 characters (e.g., 12345678 or ABC-123-4567)</span>
                    </div>
                `;
            }
        });
    }
    
    // Modal controls
    const modal = document.getElementById('addMeterModal');
    const openModalBtns = [
        document.getElementById('openModalBtn'),
        document.getElementById('openModalBtn2'),
        document.getElementById('openModalBtn2Mobile'),
        document.getElementById('openModalBtnEmpty')
    ].filter(btn => btn);
    
    const closeModalBtns = [
        document.getElementById('closeModalBtn'),
        document.getElementById('modalBackdrop')
    ];
    
    openModalBtns.forEach(btn => {
        if (btn) btn.addEventListener('click', openMeterModal);
    });
    
    closeModalBtns.forEach(btn => {
        if (btn) btn.addEventListener('click', closeMeterModal);
    });
    
    // Form submission
    const linkMeterForm = document.getElementById('linkMeterForm');
    linkMeterForm.addEventListener('submit', handleMeterSubmit);
    
    // Alias preset buttons
    const aliasInput = document.getElementById('aliasInput');
    const aliasPresets = document.querySelectorAll('.alias-preset');
    
    aliasPresets.forEach(button => {
        button.addEventListener('click', () => {
            aliasInput.value = button.dataset.alias;
        });
    });
    
    // Filter buttons
    const filterAll = document.getElementById('filterAll');
    const filterWater = document.getElementById('filterWater');
    const filterElectricity = document.getElementById('filterElectricity');
    
    if (filterAll) filterAll.addEventListener('click', () => setFilter('all'));
    if (filterWater) filterWater.addEventListener('click', () => setFilter('water'));
    if (filterElectricity) filterElectricity.addEventListener('click', () => setFilter('electricity'));
    
    // Search input
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('input', applyFilters);
    }
});
