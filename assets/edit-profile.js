// Edit Profile functionality for FixItMati
(function() {
  let currentUser = null;
  let profileImageData = null; // Store the base64 image data

  let uploadedFile = null; // Store the actual file object

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
    console.log('Populating form with user data:', user);
    
    // Personal details
    const firstName = document.getElementById('firstName');
    const lastName = document.getElementById('lastName');
    const email = document.getElementById('email');
    const phone = document.getElementById('phone');
    const address = document.getElementById('address');
    
    if (firstName) {
      firstName.value = user.first_name || '';
      console.log('Set first name to:', user.first_name);
    } else {
      console.error('firstName input not found');
    }
    
    if (lastName) {
      lastName.value = user.last_name || '';
      console.log('Set last name to:', user.last_name);
    } else {
      console.error('lastName input not found');
    }
    
    if (email) email.value = user.email || '';
    if (phone) phone.value = user.phone || '';
    if (address) address.value = user.address || '';

    // Profile display (in left sidebar)
    const displayName = document.getElementById('profileDisplayName');
    const location = document.getElementById('profileLocation');
    
    if (displayName) {
      // Build full name, handling empty values gracefully
      const firstName = (user.first_name || '').trim();
      const lastName = (user.last_name || '').trim();
      const fullName = `${firstName} ${lastName}`.trim();
      
      // Display the full name, or format email username if no name
      if (fullName) {
        displayName.textContent = fullName;
      } else if (user.email) {
        // Extract username from email and format it nicely
        const username = user.email.split('@')[0];
        // Replace dots, underscores, hyphens with spaces and capitalize each word
        const formattedName = username
          .replace(/[._-]/g, ' ')
          .split(' ')
          .map(word => word.charAt(0).toUpperCase() + word.slice(1).toLowerCase())
          .join(' ');
        displayName.textContent = formattedName;
      } else {
        displayName.textContent = 'User';
      }
      console.log('Set display name to:', displayName.textContent);
    }
    if (location) {
      location.textContent = user.address ? user.address.split(',')[0] : 'N/A';
    }
    
    // Member since
    const memberSinceEl = document.getElementById('memberSince');
    if (memberSinceEl && user.created_at) {
      const date = new Date(user.created_at);
      const monthYear = date.toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
      memberSinceEl.textContent = monthYear;
    } else if (memberSinceEl) {
      memberSinceEl.textContent = 'N/A';
    }

    // Update profile avatar initials or image
    const initials = `${user.first_name?.[0] || ''}${user.last_name?.[0] || ''}`.toUpperCase();
    if (initials) {
      const profileBtn = document.getElementById('profileBtn');
      const profileAvatarLarge = document.getElementById('profileAvatarLarge');
      
      if (profileBtn) profileBtn.textContent = initials;
      if (profileAvatarLarge) profileAvatarLarge.textContent = initials;
    }

    // If user has profile image, display it
    if (user.profile_image) {
      const profileImage = document.getElementById('profileImage');
      const placeholder = document.getElementById('profileImagePlaceholder');
      if (profileImage && placeholder) {
        // If it's a data URL, use as-is, otherwise construct API path
        let imageSrc;
        if (user.profile_image.startsWith('data:')) {
          imageSrc = user.profile_image;
        } else {
          // Extract just the filename if full path is provided
          const filename = user.profile_image.includes('/') || user.profile_image.includes('\\\\')
            ? user.profile_image.split(/[\\/]/).pop()
            : user.profile_image;
          imageSrc = '/api/uploads/profiles/' + filename;
        }
        profileImage.src = imageSrc;
        profileImage.classList.remove('hidden');
        placeholder.classList.add('hidden');
      }
    }

    // Initialize Lucide icons
    lucide.createIcons();
  }

  // Handle profile image upload
  function setupImageUpload() {
    const avatarContainer = document.getElementById('profileAvatarContainer');
    const fileInput = document.getElementById('profileImageInput');
    
    if (avatarContainer && fileInput) {
      avatarContainer.addEventListener('click', () => {
        fileInput.click();
      });

      fileInput.addEventListener('change', (e) => {
        const file = e.target.files[0];
        if (file) {
          // Validate file type
          if (!file.type.startsWith('image/')) {
            showToast('Please select an image file', 'error');
            return;
          }

          // Validate file size (max 5MB)
          if (file.size > 5 * 1024 * 1024) {
            showToast('Image size should be less than 5MB', 'error');
            return;
          }

          // Store the actual file
          uploadedFile = file;
          
          // Display preview
          const reader = new FileReader();
          reader.onload = (event) => {
            const imgSrc = event.target.result;
            
            const profileImage = document.getElementById('profileImage');
            const placeholder = document.getElementById('profileImagePlaceholder');
            
            if (profileImage && placeholder) {
              profileImage.src = imgSrc;
              profileImage.classList.remove('hidden');
              placeholder.classList.add('hidden');
              
              showToast('Profile image updated (Remember to click Save Changes)', 'success');
            }
          };
          reader.readAsDataURL(file);
        }
      });
    }
  }

  // Save changes
  async function saveProfile() {
    const saveBtn = document.getElementById('saveChangesBtn');
    const successMessage = document.getElementById('successMessage');
    
    // Create FormData to handle file upload
    const formData = new FormData();
    
    // Add personal data
    formData.append('first_name', document.getElementById('firstName').value.trim());
    formData.append('last_name', document.getElementById('lastName').value.trim());
    formData.append('email', document.getElementById('email').value.trim());
    formData.append('phone', document.getElementById('phone').value.trim());
    formData.append('address', document.getElementById('address').value.trim());

    // Include profile image file if it was changed
    if (uploadedFile) {
      formData.append('profile_image', uploadedFile);
    }

    // Validate email (required field)
    const email = formData.get('email');
    if (!email) {
      showToast('Email is required', 'error');
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

      formData.append('current_password', currentPassword);
      formData.append('new_password', newPassword);
      formData.append('confirm_password', confirmPassword);
    }

    // Show loading state
    const originalText = saveBtn.innerHTML;
    saveBtn.disabled = true;
    saveBtn.innerHTML = '<i data-lucide="loader-2" class="w-4 h-4 animate-spin"></i> <span class="hidden sm:inline">Saving...</span>';
    lucide.createIcons();

    try {
      // Get auth token
      const token = localStorage.getItem('auth_token');
      
      // Add method override for PUT (standard pattern for file uploads)
      formData.append('_method', 'PUT');
      
      // Debug: Log FormData contents
      console.log('FormData contents:');
      for (let [key, value] of formData.entries()) {
        if (value instanceof File) {
          console.log(key, ':', value.name, value.size, 'bytes');
        } else {
          console.log(key, ':', value);
        }
      }
      
      // Use POST with multipart form data for file upload
      const response = await fetch('/api/auth/profile', {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${token}`
        },
        body: formData
      });
      
      // Check if response is JSON
      const contentType = response.headers.get('content-type');
      if (!contentType || !contentType.includes('application/json')) {
        // Server returned HTML error, likely a PHP error
        const errorText = await response.text();
        console.error('Server returned non-JSON response:', errorText);
        showToast('Server error occurred. Check console for details.', 'error');
        return;
      }
      
      const result = await response.json();
      
      console.log('Server response:', result);
      
      if (result.success) {
        // Show success message
        successMessage.classList.remove('hidden');
        lucide.createIcons();
        
        // Clear password fields
        document.getElementById('currentPassword').value = '';
        document.getElementById('newPassword').value = '';
        document.getElementById('confirmPassword').value = '';

        // Reset uploaded file flag
        uploadedFile = null;

        // Reload user data from API to get the updated info including profile_image
        const userResponse = await ApiClient.get('/auth/me');
        if (userResponse.success && userResponse.data) {
          currentUser = userResponse.data;
          // Update localStorage so other pages have the latest data
          localStorage.setItem('user', JSON.stringify(currentUser));
          populateForm(currentUser);
          // Trigger profile sync event to navbar on all tabs
          localStorage.setItem('profile_updated_event', Date.now().toString());
        }

        // Hide success message after 3 seconds
        setTimeout(() => {
          successMessage.classList.add('hidden');
        }, 3000);

        showToast('Profile updated successfully', 'success');
      } else {
        showToast(result.message || 'Failed to update profile', 'error');
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

    // Setup profile image upload
    setupImageUpload();

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
