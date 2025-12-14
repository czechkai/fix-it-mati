// Basic interactions for FixItMati Dashboard
(function(){
  const mobileMenuBtn = document.getElementById('mobileMenuBtn');
  const mobileDrawer = document.getElementById('mobileDrawer');
  const tabsNav = document.getElementById('tabsNav');

  // Load profile image/initials on every page
  function loadProfileDisplay() {
    const user = localStorage.getItem('user');
    if (!user) return;

    try {
      const userData = JSON.parse(user);
      const profileName = document.getElementById('profileName');
      const profileEmail = document.getElementById('profileEmail');
      const profileAvatarLarge = document.getElementById('profileAvatarLarge');
      const profileBtn = document.getElementById('profileBtn');
      
      // Build display name
      const firstName = (userData.first_name || '').trim();
      const lastName = (userData.last_name || '').trim();
      let displayName = `${firstName} ${lastName}`.trim();
      
      if (!displayName && userData.email) {
        // Format email username
        const username = userData.email.split('@')[0];
        displayName = username.replace(/[._-]/g, ' ').split(' ')
          .map(word => word.charAt(0).toUpperCase() + word.slice(1).toLowerCase())
          .join(' ');
      }
      
      if (profileName) profileName.textContent = displayName || userData.email;
      if (profileEmail) profileEmail.textContent = userData.email;
      
      // Handle profile image or initials
      if (userData.profile_image) {
        // Display profile image - handle both file paths and base64
        let imageSrc;
        if (userData.profile_image.startsWith('data:')) {
          imageSrc = userData.profile_image;
        } else {
          // Extract just the filename if full path is provided
          const filename = userData.profile_image.includes('/') || userData.profile_image.includes('\\')
            ? userData.profile_image.split(/[\\/]/).pop()
            : userData.profile_image;
          imageSrc = '/api/uploads/profiles/' + filename;
        }
        
        if (profileAvatarLarge) {
          profileAvatarLarge.innerHTML = `<img src="${imageSrc}" class="w-full h-full object-cover rounded-full" alt="Profile" />`;
        }
        if (profileBtn) {
          profileBtn.innerHTML = `<img src="${imageSrc}" class="w-full h-full object-cover rounded-full" alt="Profile" />`;
        }
      } else {
        // Display initials
        const initials = displayName.split(' ').map(n => n[0]).join('').toUpperCase().substring(0, 2);
        if (profileAvatarLarge) profileAvatarLarge.textContent = initials;
        if (profileBtn) profileBtn.textContent = initials;
      }
    } catch (error) {
      console.error('Error loading profile display:', error);
    }
  }

  // Run profile loader when DOM is ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', loadProfileDisplay);
  } else {
    loadProfileDisplay();
  }

  function openDrawer(){
    if (!mobileDrawer) return;
    mobileDrawer.classList.remove('hidden');
    mobileDrawer.classList.add('show');
    // swap icon
    if (mobileMenuBtn) mobileMenuBtn.innerHTML = '<i data-lucide="x" class="w-6 h-6"></i>';
    lucide.createIcons();
  }
  function closeDrawer(){
    if (!mobileDrawer) return;
    mobileDrawer.classList.add('hidden');
    mobileDrawer.classList.remove('show');
    if (mobileMenuBtn) mobileMenuBtn.innerHTML = '<i data-lucide="menu" class="w-6 h-6"></i>';
    lucide.createIcons();
  }

  if (mobileMenuBtn && mobileDrawer){
    mobileMenuBtn.addEventListener('click', () => {
      if (mobileDrawer.classList.contains('hidden')) openDrawer();
      else closeDrawer();
    });
    mobileDrawer.querySelectorAll('[data-close]').forEach(el => {
      el.addEventListener('click', closeDrawer);
    });
  }

  // Tabs: navigate to different pages
  if (tabsNav){
    tabsNav.querySelectorAll('button[data-tab]').forEach(btn => {
      btn.addEventListener('click', () => {
        const tab = btn.getAttribute('data-tab');
        
        // Navigate to appropriate page
        switch(tab) {
          case 'dashboard':
            window.location.href = 'pages/user/user-dashboard.php';
            break;
          case 'my requests':
            window.location.href = 'pages/user/active-requests.php';
            break;
          case 'announcements':
            window.location.href = 'announcements.php';
            break;
          case 'discussions':
            // Would navigate to discussions page when implemented
            alert('Discussions feature coming soon!');
            break;
          case 'payments':
            window.location.href = 'payments.php';
            break;
        }
      });
    });
  }

  // Sort Dropdown functionality
  const sortBtn = document.getElementById('sortBtn');
  const sortDropdown = document.getElementById('sortDropdown');

  if (sortBtn && sortDropdown) {
    // Toggle dropdown on button click
    sortBtn.addEventListener('click', (e) => {
      e.stopPropagation();
      sortDropdown.classList.toggle('hidden');
      lucide.createIcons();
    });

    // Handle sort option clicks
    sortDropdown.querySelectorAll('.sort-option').forEach(option => {
      option.addEventListener('click', (e) => {
        e.stopPropagation();
        const sortType = option.dataset.sort;
        const sortText = option.querySelector('span').textContent;
        
        // Update button text to show selected sort
        const btnText = sortBtn.childNodes[0];
        btnText.textContent = sortText + ' ';
        
        // Close dropdown
        sortDropdown.classList.add('hidden');
        
        // Here you would implement actual sorting logic
        console.log('Sorting by:', sortType);
        
        // Visual feedback - highlight selected option
        sortDropdown.querySelectorAll('.sort-option').forEach(opt => {
          opt.classList.remove('bg-blue-50', 'text-blue-700');
        });
        option.classList.add('bg-blue-50', 'text-blue-700');
      });
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', () => {
      sortDropdown.classList.add('hidden');
    });
  }
})();
