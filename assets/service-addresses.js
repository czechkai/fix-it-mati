/**
 * Service Addresses Management
 * Real-time database interactions for service addresses
 */

// Initialize Lucide icons
document.addEventListener('DOMContentLoaded', () => {
  lucide.createIcons();
  loadAddresses();
});

// Global variables
let currentEditId = null;

/**
 * Load all addresses for the current user
 */
async function loadAddresses() {
  try {
    showLoading();
    
    const response = await ApiClient.get('/service-addresses');
    
    if (response.success) {
      renderAddresses(response.data.addresses);
    } else {
      showError('Failed to load addresses: ' + (response.message || 'Unknown error'));
    }
  } catch (error) {
    console.error('Error loading addresses:', error);
    showError('Failed to load addresses');
  }
}

/**
 * Render addresses list
 */
function renderAddresses(addresses) {
  const loadingState = document.getElementById('loadingState');
  const addressList = document.getElementById('addressList');
  const emptyState = document.getElementById('emptyState');
  
  loadingState.classList.add('hidden');
  
  if (!addresses || addresses.length === 0) {
    addressList.classList.add('hidden');
    emptyState.classList.remove('hidden');
    lucide.createIcons();
    return;
  }
  
  emptyState.classList.add('hidden');
  addressList.classList.remove('hidden');
  
  addressList.innerHTML = addresses.map(addr => createAddressCard(addr)).join('');
  
  // Reinitialize icons
  lucide.createIcons();
}

/**
 * Create HTML for an address card
 */
function createAddressCard(addr) {
  const icon = getAddressIcon(addr.label);
  const isDefault = addr.is_default;
  
  return `
    <div class="bg-white rounded-xl border transition-all duration-200 relative overflow-hidden group animate-fade-in ${
      isDefault 
        ? 'border-blue-500 shadow-md ring-1 ring-blue-500/20' 
        : 'border-slate-200 shadow-sm hover:border-blue-300 hover:shadow-md'
    }">
      <div class="p-5 flex items-start gap-4">
        
        <!-- Icon Box -->
        <div class="p-3 rounded-lg flex-shrink-0 ${
          isDefault ? 'bg-blue-100 text-blue-600' : 'bg-slate-100 text-slate-500 group-hover:bg-blue-50 group-hover:text-blue-500'
        }">
          <i data-lucide="${icon}" class="w-5 h-5"></i>
        </div>

        <!-- Content -->
        <div class="flex-1 min-w-0">
          <div class="flex items-center gap-2 mb-1 flex-wrap">
            <h3 class="font-bold text-slate-800 text-base">${escapeHtml(addr.label)}</h3>
            ${isDefault ? `
              <span class="bg-blue-100 text-blue-700 text-[10px] font-bold px-2 py-0.5 rounded-full border border-blue-200 flex items-center gap-1">
                <i data-lucide="check" class="w-3 h-3"></i> Default
              </span>
            ` : ''}
            <span class="text-xs text-slate-400 border border-slate-200 px-1.5 rounded uppercase">
              ${escapeHtml(addr.type)}
            </span>
          </div>
          
          <p class="text-sm text-slate-600 font-medium">${escapeHtml(addr.street)}</p>
          <p class="text-sm text-slate-500">${escapeHtml(addr.barangay)}, Mati City</p>
          
          ${addr.details ? `
            <div class="flex items-center gap-1 mt-2 text-xs text-slate-400">
              <i data-lucide="navigation" class="w-3 h-3"></i>
              <span>Note: ${escapeHtml(addr.details)}</span>
            </div>
          ` : ''}
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-col gap-2">
          ${!isDefault ? `
            <button 
              onclick="setDefaultAddress('${addr.id}')"
              class="p-2 text-slate-400 hover:text-yellow-600 hover:bg-yellow-50 rounded-lg transition-colors"
              title="Set as Default"
            >
              <i data-lucide="star" class="w-4 h-4"></i>
            </button>
          ` : ''}
          <button 
            onclick="editAddress('${addr.id}')"
            class="p-2 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors"
            title="Edit"
          >
            <i data-lucide="edit-2" class="w-4 h-4"></i>
          </button>
        </div>

      </div>
      
      <!-- Footer -->
      <div class="border-t border-slate-50 bg-slate-50/50 px-5 py-2 flex justify-between items-center text-xs text-slate-500">
        <span>Added ${formatDate(addr.created_at)}</span>
        <button 
          onclick="confirmDelete('${addr.id}', '${escapeHtml(addr.label)}')"
          class="text-red-500 hover:text-red-700 hover:underline flex items-center gap-1 font-medium"
        >
          <i data-lucide="trash-2" class="w-3 h-3"></i> Remove
        </button>
      </div>
    </div>
  `;
}

/**
 * Get icon based on label
 */
function getAddressIcon(label) {
  const lowerLabel = label.toLowerCase();
  if (lowerLabel.includes('home') || lowerLabel.includes('house')) return 'home';
  if (lowerLabel.includes('office') || lowerLabel.includes('work')) return 'briefcase';
  return 'map-pin';
}

/**
 * Open modal for adding new address
 */
function openAddressModal() {
  currentEditId = null;
  document.getElementById('modalTitle').textContent = 'Add New Address';
  document.getElementById('submitBtnText').textContent = 'Save Address';
  document.getElementById('addressForm').reset();
  document.getElementById('addressId').value = '';
  document.getElementById('addressModal').classList.remove('hidden');
  lucide.createIcons();
}

/**
 * Close address modal
 */
function closeAddressModal() {
  document.getElementById('addressModal').classList.add('hidden');
  currentEditId = null;
}

/**
 * Edit an address
 */
async function editAddress(id) {
  try {
    const response = await ApiClient.get(`/service-addresses/${id}`);
    
    if (response.success) {
      const addr = response.data.address;
      currentEditId = id;
      
      document.getElementById('modalTitle').textContent = 'Edit Address';
      document.getElementById('submitBtnText').textContent = 'Update Address';
      document.getElementById('addressId').value = id;
      document.getElementById('label').value = addr.label;
      document.getElementById('type').value = addr.type;
      document.getElementById('barangay').value = addr.barangay;
      document.getElementById('street').value = addr.street;
      document.getElementById('details').value = addr.details || '';
      document.getElementById('isDefault').checked = addr.is_default;
      
      document.getElementById('addressModal').classList.remove('hidden');
      lucide.createIcons();
    }
  } catch (error) {
    console.error('Error loading address:', error);
    showError('Failed to load address details');
  }
}

/**
 * Handle form submission
 */
document.getElementById('addressForm').addEventListener('submit', async (e) => {
  e.preventDefault();
  
  const formData = {
    label: document.getElementById('label').value.trim(),
    type: document.getElementById('type').value,
    barangay: document.getElementById('barangay').value,
    street: document.getElementById('street').value.trim(),
    details: document.getElementById('details').value.trim(),
    is_default: document.getElementById('isDefault').checked
  };
  
  // Validation
  if (!formData.label || !formData.barangay || !formData.street) {
    showError('Please fill in all required fields');
    return;
  }
  
  try {
    const submitBtn = e.target.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<span class="flex items-center gap-2"><div class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div>Saving...</span>';
    submitBtn.disabled = true;
    
    let response;
    
    if (currentEditId) {
      // Update existing address
      response = await ApiClient.put(`/service-addresses/${currentEditId}`, formData);
    } else {
      // Create new address
      response = await ApiClient.post('/service-addresses', formData);
    }
    
    if (response.success) {
      closeAddressModal();
      showSuccess(currentEditId ? 'Address updated successfully!' : 'Address added successfully!');
      loadAddresses();
    } else {
      showError(response.message || 'Failed to save address');
      submitBtn.innerHTML = originalText;
      submitBtn.disabled = false;
    }
  } catch (error) {
    console.error('Error saving address:', error);
    showError('Failed to save address');
    const submitBtn = e.target.querySelector('button[type="submit"]');
    submitBtn.innerHTML = document.getElementById('submitBtnText').textContent;
    submitBtn.disabled = false;
  }
});

/**
 * Set address as default
 */
async function setDefaultAddress(id) {
  try {
    const response = await ApiClient.patch(`/service-addresses/${id}/set-default`);
    
    if (response.success) {
      showSuccess('Default address updated!');
      loadAddresses();
    } else {
      showError(response.message || 'Failed to set default address');
    }
  } catch (error) {
    console.error('Error setting default:', error);
    showError('Failed to set default address');
  }
}

/**
 * Confirm and delete address
 */
function confirmDelete(id, label) {
  if (confirm(`Are you sure you want to delete "${label}"?\n\nThis action cannot be undone.`)) {
    deleteAddress(id);
  }
}

/**
 * Delete an address
 */
async function deleteAddress(id) {
  try {
    const response = await ApiClient.delete(`/service-addresses/${id}`);
    
    if (response.success) {
      showSuccess('Address deleted successfully!');
      loadAddresses();
    } else {
      showError(response.message || 'Failed to delete address');
    }
  } catch (error) {
    console.error('Error deleting address:', error);
    showError('Failed to delete address');
  }
}

/**
 * Show loading state
 */
function showLoading() {
  document.getElementById('loadingState').classList.remove('hidden');
  document.getElementById('addressList').classList.add('hidden');
  document.getElementById('emptyState').classList.add('hidden');
}

/**
 * Show error message
 */
function showError(message) {
  alert('❌ ' + message);
}

/**
 * Show success message
 */
function showSuccess(message) {
  // Create success toast
  const toast = document.createElement('div');
  toast.className = 'fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg flex items-center gap-2 z-50 animate-fade-in';
  toast.innerHTML = `
    <i data-lucide="check-circle" class="w-5 h-5"></i>
    <span>${escapeHtml(message)}</span>
  `;
  document.body.appendChild(toast);
  lucide.createIcons();
  
  setTimeout(() => {
    toast.style.opacity = '0';
    toast.style.transform = 'translateY(-10px)';
    toast.style.transition = 'all 0.3s ease-out';
    setTimeout(() => toast.remove(), 300);
  }, 3000);
}

/**
 * Escape HTML to prevent XSS
 */
function escapeHtml(text) {
  const div = document.createElement('div');
  div.textContent = text;
  return div.innerHTML;
}

/**
 * Format date
 */
function formatDate(dateString) {
  const date = new Date(dateString);
  const now = new Date();
  const diffTime = Math.abs(now - date);
  const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
  
  if (diffDays === 0) return 'Today';
  if (diffDays === 1) return 'Yesterday';
  if (diffDays < 7) return `${diffDays} days ago`;
  if (diffDays < 30) return `${Math.floor(diffDays / 7)} weeks ago`;
  if (diffDays < 365) return `${Math.floor(diffDays / 30)} months ago`;
  return `${Math.floor(diffDays / 365)} years ago`;
}

// Add event listener for "Add Address" button
document.getElementById('addAddressBtn').addEventListener('click', openAddressModal);
