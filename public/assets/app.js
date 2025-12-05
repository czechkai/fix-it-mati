// Basic interactions for FixItMati Dashboard
(function(){
  const mobileMenuBtn = document.getElementById('mobileMenuBtn');
  const mobileDrawer = document.getElementById('mobileDrawer');
  const tabsNav = document.getElementById('tabsNav');

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
            window.location.href = 'user-dashboard.php';
            break;
          case 'my requests':
            window.location.href = 'active-requests.php';
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
