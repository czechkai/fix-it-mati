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
    
    try {
        loadingState.classList.remove('hidden');
        emptyState.classList.add('hidden');
        metersGrid.classList.add('hidden');
        
        const response = await ApiClient.get('/linked-meters');
        
        if (response.success) {
            const meters = response.data.meters;
            
            if (meters.length === 0) {
                loadingState.classList.add('hidden');
                emptyState.classList.remove('hidden');
            } else {
                renderMeters(meters);
                loadingState.classList.add('hidden');
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

// Render meters
function renderMeters(meters) {
    const metersGrid = document.getElementById('metersGrid');
    
    // Clear existing meters
    metersGrid.innerHTML = '';
    
    // Render each meter
    meters.forEach(meter => {
        const styles = getMeterStyles(meter.meter_type);
        const status = meter.status === 'active' ? 
            '<div class="px-2 py-1 bg-green-100 text-green-700 text-[10px] font-bold uppercase rounded-full flex items-center gap-1"><i data-lucide="check-circle-2" class="w-2.5 h-2.5"></i> Active</div>' :
            '<div class="px-2 py-1 bg-slate-100 text-slate-700 text-[10px] font-bold uppercase rounded-full flex items-center gap-1"><i data-lucide="pause-circle" class="w-2.5 h-2.5"></i> Inactive</div>';
        
        const meterCard = `
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm hover:shadow-md transition-all group relative overflow-hidden">
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
                            </div>
                        </div>
                        ${status}
                    </div>
                    <div class="space-y-3 mb-6">
                        <div class="flex justify-between py-2 border-b border-slate-50">
                            <span class="text-xs text-slate-400 font-medium uppercase">Account No.</span>
                            <span class="text-sm font-mono text-slate-700 bg-slate-100 px-2 rounded">${meter.account_number}</span>
                        </div>
                        <div class="flex justify-between py-2 border-b border-slate-50">
                            <span class="text-xs text-slate-400 font-medium uppercase">Last Bill</span>
                            <span class="text-sm font-bold text-slate-900">${formatCurrency(meter.last_bill_amount)}</span>
                        </div>
                        ${meter.address ? `
                        <div class="flex items-start gap-2 pt-1">
                            <span class="text-xs text-slate-400 font-medium uppercase mt-0.5">Address</span>
                            <span class="text-sm text-slate-600 text-right flex-1">${meter.address}</span>
                        </div>
                        ` : ''}
                    </div>
                    <div class="flex gap-2">
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
    
    modalTitle.textContent = 'Link New Meter';
    form.reset();
    modal.classList.remove('hidden');
}

// Close modal
function closeMeterModal() {
    const modal = document.getElementById('addMeterModal');
    modal.classList.add('hidden');
    currentEditMeterId = null;
}

// Edit meter
async function editMeter(meterId) {
    try {
        const response = await ApiClient.get(`/linked-meters/${meterId}`);
        
        if (response.success) {
            const meter = response.data.meter;
            currentEditMeterId = meterId;
            
            // Populate form
            document.querySelector(`input[name="meter_type"][value="${meter.meter_type}"]`).checked = true;
            document.getElementById('providerInput').value = meter.provider;
            document.getElementById('accountNumberInput').value = meter.account_number;
            document.getElementById('accountNumberInput').readOnly = true; // Can't change account number
            document.getElementById('accountHolderInput').value = meter.account_holder_name;
            document.getElementById('accountHolderInput').readOnly = true; // Can't change holder name
            document.getElementById('aliasInput').value = meter.alias || '';
            document.getElementById('addressInput').value = meter.address || '';
            
            // Update modal title
            const modal = document.getElementById('addMeterModal');
            const modalTitle = modal.querySelector('h3');
            modalTitle.textContent = 'Edit Meter Details';
            
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

// Handle form submission
async function handleMeterSubmit(event) {
    event.preventDefault();
    
    const form = event.target;
    const submitBtn = document.getElementById('submitBtn');
    const submitBtnText = document.getElementById('submitBtnText');
    const loader = document.getElementById('loader');
    
    // Get form data
    const formData = new FormData(form);
    const data = {
        meter_type: formData.get('meter_type'),
        provider: formData.get('provider'),
        account_number: formData.get('account_number'),
        account_holder_name: formData.get('account_holder_name'),
        alias: formData.get('alias') || null,
        address: formData.get('address') || null
    };
    
    // Start loading
    submitBtn.disabled = true;
    submitBtnText.textContent = currentEditMeterId ? 'Updating...' : 'Verifying...';
    loader.classList.remove('hidden');
    lucide.createIcons();
    
    try {
        let response;
        
        if (currentEditMeterId) {
            // Update existing meter
            response = await ApiClient.put(`/linked-meters/${currentEditMeterId}`, data);
        } else {
            // Create new meter
            response = await ApiClient.post('/linked-meters', data);
        }
        
        if (response.success) {
            showToast(currentEditMeterId ? 'Meter updated successfully' : 'Meter linked successfully', 'success');
            closeMeterModal();
            loadMeters();
        } else {
            throw new Error(response.message || 'Operation failed');
        }
    } catch (error) {
        console.error('Error submitting meter:', error);
        showToast(error.message || 'Operation failed', 'error');
    } finally {
        submitBtn.disabled = false;
        submitBtnText.textContent = 'Verify & Link Meter';
        loader.classList.add('hidden');
    }
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    // Render all lucide icons
    lucide.createIcons();
    
    // Load meters
    loadMeters();
    
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
});
