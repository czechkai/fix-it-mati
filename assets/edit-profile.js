// Edit Profile functionality for FixItMati
(function() {
  let currentUser = null;

  // Load user data on page load
  async function loadUserProfile() {
    try {
      const response = await ApiClient.get('/auth/me');
      if (response.success && response.data) {
        currentUser = response.data;
        populateForm(currentUser);
      } else {
        console.error('API response not successful:', response);
        showToast('Failed to load profile data', 'error');
      }
    } catch (error) {
      console.error('Error loading user profile:', error);
      showToast('Failed to load profile data', 'error');
    }
  }

  // Populate form with user data
  function populateForm(user) {
    // Personal details
    const firstName = document.getElementById('firstName');
    const lastName = document.getElementById('lastName');
    const email = document.getElementById('email');
    const phone = document.getElementById('phone');
    const address = document.getElementById('address');
    
    if (firstName) firstName.value = user.first_name || '';
    if (lastName) lastName.value = user.last_name || '';
    if (email) email.value = user.email || '';
    if (phone) phone.value = user.phone || '';
    if (address) address.value = user.address || '';

    // Profile display
    const displayName = document.getElementById('profileDisplayName');
    const location = document.getElementById('profileLocation');
    
    if (displayName) {
      displayName.textContent = `${user.first_name || ''} ${user.last_name || ''}`.trim() || 'User';
    }
    if (location) {
      location.textContent = user.address ? user.address.split(',')[0] : 'N/A';
    }
    
    // Member since
    const memberSinceEl = document.getElementById('memberSince');
    if (user.created_at) {
      const date = new Date(user.created_at);
      const monthYear = date.toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
      memberSinceEl.textContent = monthYear;
    } else {
      memberSinceEl.textContent = 'N/A';
    }

    // Update profile avatar
    const initials = `${user.first_name?.[0] || ''}${user.last_name?.[0] || ''}`.toUpperCase();
    if (initials) {
      document.getElementById('profileAvatarLarge').textContent = initials;
      document.getElementById('profileBtn').textContent = initials;
    }

    // Initialize Lucide icons
    lucide.createIcons();
  }

  // Save changes
  async function saveProfile() {
    const saveBtn = document.getElementById('saveChangesBtn');
    const successMessage = document.getElementById('successMessage');
    
    // Get form data
    const personalData = {
      first_name: document.getElementById('firstName').value.trim(),
      last_name: document.getElementById('lastName').value.trim(),
      email: document.getElementById('email').value.trim(),
      phone: document.getElementById('phone').value.trim(),
      address: document.getElementById('address').value.trim()
    };

    // Validate
    if (!personalData.first_name || !personalData.last_name) {
      showToast('First name and last name are required', 'error');
      return;
    }

    // Check if password change is requested
    const currentPassword = document.getElementById('currentPassword').value;
    const newPassword = document.getElementById('newPassword').value;
    const confirmPassword = document.getElementById('confirmPassword').value;

    if (currentPassword || newPassword || confirmPassword) {
      if (!currentPassword) {
        showToast('Current password is required to change password', 'error');
        return;
      }
      if (!newPassword || !confirmPassword) {
        showToast('Please fill in both new password fields', 'error');
        return;
      }
      if (newPassword !== confirmPassword) {
        showToast('New passwords do not match', 'error');
        return;
      }
      if (newPassword.length < 6) {
        showToast('New password must be at least 6 characters', 'error');
        return;
      }

      personalData.current_password = currentPassword;
      personalData.new_password = newPassword;
    }

    // Show loading state
    const originalText = saveBtn.innerHTML;
    saveBtn.disabled = true;
    saveBtn.innerHTML = '<i data-lucide="loader-2" class="w-4 h-4 animate-spin"></i> <span class="hidden sm:inline">Saving...</span>';
    lucide.createIcons();

    try {
      const response = await ApiClient.put('/auth/profile', personalData);
      
      if (response.success) {
        // Show success message
        successMessage.classList.remove('hidden');
        lucide.createIcons();
        
        // Clear password fields
        document.getElementById('currentPassword').value = '';
        document.getElementById('newPassword').value = '';
        document.getElementById('confirmPassword').value = '';

        // Update current user
        currentUser = { ...currentUser, ...personalData };
        populateForm(currentUser);

        // Hide success message after 3 seconds
        setTimeout(() => {
          successMessage.classList.add('hidden');
        }, 3000);

        showToast('Profile updated successfully', 'success');
      } else {
        showToast(response.message || 'Failed to update profile', 'error');
      }
    } catch (error) {
      console.error('Error updating profile:', error);
      showToast(error.message || 'Failed to update profile', 'error');
    } finally {
      // Restore button
      saveBtn.disabled = false;
      saveBtn.innerHTML = originalText;
      lucide.createIcons();
    }
  }

  // Show toast notification
  function showToast(message, type = 'info') {
    // Create toast element
    const toast = document.createElement('div');
    toast.className = `fixed bottom-6 right-6 px-6 py-3 rounded-xl shadow-lg flex items-center gap-3 z-50 animate-in slide-in-from-bottom-5 ${
      type === 'success' ? 'bg-green-50 border border-green-200 text-green-700' :
      type === 'error' ? 'bg-red-50 border border-red-200 text-red-700' :
      'bg-blue-50 border border-blue-200 text-blue-700'
    }`;
    
    const icon = type === 'success' ? 'check-circle-2' : type === 'error' ? 'x-circle' : 'info';
    toast.innerHTML = `
      <i data-lucide="${icon}" class="w-5 h-5"></i>
      <span class="text-sm font-medium">${message}</span>
    `;
    
    document.body.appendChild(toast);
    lucide.createIcons();
    
    // Remove after 3 seconds
    setTimeout(() => {
      toast.remove();
    }, 3000);
  }

  // Initialize when DOM is ready
  function init() {
    // Event listeners
    const saveBtn = document.getElementById('saveChangesBtn');
    const personalForm = document.getElementById('personalDetailsForm');
    const securityForm = document.getElementById('securityForm');
    
    if (saveBtn) {
      saveBtn.addEventListener('click', saveProfile);
    } else {
      console.error('Save button not found');
    }

    if (personalForm) {
      personalForm.addEventListener('submit', (e) => {
        e.preventDefault();
        saveProfile();
      });
    } else {
      console.error('Personal details form not found');
    }

    if (securityForm) {
      securityForm.addEventListener('submit', (e) => {
        e.preventDefault();
        saveProfile();
      });
    } else {
      console.error('Security form not found');
    }

    // Load user data
    loadUserProfile();
    lucide.createIcons();
  }

  // Wait for DOM to be ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
