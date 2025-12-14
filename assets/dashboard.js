// Dashboard functionality for FixItMati
(function(){
  let currentCategory = 'all';
  let currentSort = 'newest';
  let allRequests = [];
  let allAnnouncements = [];

  const mobileMenuBtn = document.getElementById('mobileMenuBtn');
  const mobileDrawer = document.getElementById('mobileDrawer');
  const tabsNav = document.getElementById('tabsNav');
  const globalSearch = document.getElementById('globalSearch');

  function openDrawer(){
    if (!mobileDrawer) return;
    mobileDrawer.classList.remove('hidden');
    mobileDrawer.classList.add('show');
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

  // Notification button navigation
  const notificationBtn = document.getElementById('notificationBtn');
  if (notificationBtn) {
    notificationBtn.addEventListener('click', () => {
      window.location.href = window.location.pathname.includes('/public/') ? 'notifications.php' : 'public/notifications.php';
    });
  }

  // Tabs navigation
  if (tabsNav){
    tabsNav.querySelectorAll('button[data-tab]').forEach(btn => {
      btn.addEventListener('click', () => {
        const tab = btn.getAttribute('data-tab');
        
        switch(tab) {
          case 'dashboard':
            window.location.href = window.location.pathname.includes('/public/') ? 'pages/user/user-dashboard.php' : 'public/pages/user/user-dashboard.php';
            break;
          case 'my requests':
            window.location.href = window.location.pathname.includes('/public/') ? 'pages/user/active-requests.php' : 'public/pages/user/active-requests.php';
            break;
          case 'announcements':
            window.location.href = window.location.pathname.includes('/public/') ? 'announcements.php' : 'public/announcements.php';
            break;
          case 'discussions':
            window.location.href = window.location.pathname.includes('/public/') ? 'discussions.php' : 'public/discussions.php';
            break;
          case 'payments':
            window.location.href = window.location.pathname.includes('/public/') ? 'payments.php' : 'public/payments.php';
            break;
        }
      });
    });
  }

  // Category filtering
  const categoryFilters = document.querySelectorAll('.category-filter');
  categoryFilters.forEach(filter => {
    filter.addEventListener('click', function() {
      const category = this.dataset.category;
      currentCategory = category;
      
      // Update UI - reset all filters first
      categoryFilters.forEach(f => {
        f.classList.remove('bg-blue-50', 'text-blue-700', 'border-l-4', 'border-blue-600');
        f.classList.add('text-slate-600');
        const span = f.querySelector('span');
        if (span) {
          span.classList.remove('text-blue-600');
          span.classList.add('text-slate-400');
        }
      });
      
      // Apply active state to clicked filter
      this.classList.add('bg-blue-50', 'text-blue-700', 'border-l-4', 'border-blue-600');
      this.classList.remove('text-slate-600');
      const span = this.querySelector('span');
      if (span) {
        span.classList.add('text-blue-600');
        span.classList.remove('text-slate-400');
      }
      
      // Filter and sort requests
      filterAndSortRequests();
    });
  });

  // Search functionality
  if (globalSearch) {
    globalSearch.addEventListener('input', function(e) {
      filterAndSortRequests(e.target.value.toLowerCase());
    });
  }

  // Sort dropdown
  const sortBtn = document.getElementById('sortBtn');
  const sortDropdown = document.getElementById('sortDropdown');

  if (sortBtn && sortDropdown) {
    sortBtn.addEventListener('click', (e) => {
      e.stopPropagation();
      sortDropdown.classList.toggle('hidden');
      lucide.createIcons();
    });

    sortDropdown.querySelectorAll('.sort-option').forEach(option => {
      option.addEventListener('click', (e) => {
        e.stopPropagation();
        const sortType = option.dataset.sort;
        
        // Handle category sorts differently
        if (sortType === 'water' || sortType === 'electricity') {
          currentCategory = sortType;
          // Update category filter UI
          categoryFilters.forEach(f => {
            if (f.dataset.category === sortType) {
              f.click();
            }
          });
        } else {
          currentSort = sortType;
          filterAndSortRequests();
        }
        
        sortDropdown.classList.add('hidden');
        
        // Visual feedback
        sortDropdown.querySelectorAll('.sort-option').forEach(opt => {
          opt.classList.remove('bg-blue-50', 'text-blue-700');
        });
        option.classList.add('bg-blue-50', 'text-blue-700');
      });
    });

    document.addEventListener('click', () => {
      if (sortDropdown) sortDropdown.classList.add('hidden');
    });
  }

  // New Discussion button
  const newDiscussionBtn = document.querySelector('[class*="bg-green-600"]');
  if (newDiscussionBtn && newDiscussionBtn.textContent.includes('New Discussion')) {
    newDiscussionBtn.addEventListener('click', () => {
      alert('Create New Discussion feature coming soon!');
    });
  }

  // Load dashboard data
  async function loadDashboardData() {
    try {
      // Load requests
      const requestsData = await ApiClient.requests.getAll();
      allRequests = (requestsData.data && requestsData.data.requests) || [];
      
      // Load announcements
      const announcementsData = await ApiClient.get('/announcements');
      allAnnouncements = (announcementsData.data && announcementsData.data.announcements) || announcementsData.data || [];
      
      // Load payment data for Total Amount Due card
      try {
        const paymentsData = await ApiClient.get('/payments/current');
        if (paymentsData.success && paymentsData.data) {
          const totalDue = paymentsData.data.total_due || 0;
          const dueDate = paymentsData.data.bills && paymentsData.data.bills.length > 0 
            ? paymentsData.data.bills[0].due_date 
            : null;
          updatePaymentCard(totalDue, dueDate);
        }
      } catch (error) {
        console.error('Error loading payment data:', error);
      }
      
      // Update stats cards
      updateStatsCards();
      
      // Update announcements card
      updateAnnouncementsCard();
      
      // Display requests
      filterAndSortRequests();
      
      // Display announcements
      displayAnnouncements();
      
    } catch (error) {
      console.error('Error loading dashboard data:', error);
      
      // Check if it's an authentication error
      if (error.message && (error.message.includes('Unauthorized') || error.message.includes('Not authenticated') || error.message.includes('401'))) {
        // Redirect to login
        window.location.href = window.location.pathname.includes('/public/') ? 'pages/auth/login.php' : 'public/pages/auth/login.php';
        return;
      }
      
      // Show message to user
      const container = document.querySelector('.bg-white.border.border-slate-200.rounded-lg.shadow-sm.divide-y');
      if (container) {
        const children = Array.from(container.children);
        children.slice(1).forEach(child => child.remove());
        container.insertAdjacentHTML('beforeend', `
          <div class="px-4 py-8 text-center text-slate-500">
            <i data-lucide="alert-circle" class="w-12 h-12 mx-auto mb-2 text-amber-400"></i>
            <p>Unable to load requests. Please try again.</p>
          </div>
        `);
        lucide.createIcons();
      }
    }
  }

  function updateStatsCards() {
    const activeRequests = allRequests.filter(r => r.status === 'pending' || r.status === 'in_progress');
    const resolvedRequests = allRequests.filter(r => r.status === 'completed');
    
    // Update Active Requests card
    const activeCard = document.querySelector('[href="active-requests.php"] .text-2xl');
    if (activeCard) {
      activeCard.textContent = activeRequests.length;
      activeCard.setAttribute('data-loading', 'false');
      activeCard.classList.remove('loading-skeleton');
      
      // Update subtitle
      const activeSubtitle = activeCard.parentElement.querySelector('.text-xs');
      if (activeSubtitle) {
        activeSubtitle.textContent = activeRequests.length === 1 ? 'In Progress' : `${activeRequests.length} In Progress`;
      }
    }
    
    // Update Resolved Issues card
    const resolvedCards = document.querySelectorAll('.text-2xl');
    if (resolvedCards[3]) {
      resolvedCards[3].textContent = resolvedRequests.length;
      resolvedCards[3].setAttribute('data-loading', 'false');
      resolvedCards[3].classList.remove('loading-skeleton');
      
      // Update subtitle
      const resolvedSubtitle = resolvedCards[3].parentElement.querySelector('.text-xs');
      if (resolvedSubtitle) {
        resolvedSubtitle.textContent = 'This Year';
      }
    }
  }

  function updateAnnouncementsCard() {
    // Update Announcements card with real count
    const announcementCards = document.querySelectorAll('[href="announcements.php"]');
    announcementCards.forEach(card => {
      const countElement = card.querySelector('.text-2xl');
      const subtitleElement = card.querySelector('.text-xs.opacity-80');
      
      if (countElement) {
        countElement.setAttribute('data-loading', 'false');
        countElement.classList.remove('loading-skeleton');
        
        if (allAnnouncements.length > 0) {
          // Count urgent/new announcements (within last 7 days)
          const sevenDaysAgo = new Date();
          sevenDaysAgo.setDate(sevenDaysAgo.getDate() - 7);
          
          const newAnnouncements = allAnnouncements.filter(a => {
            const createdDate = new Date(a.created_at);
            return createdDate > sevenDaysAgo || a.priority === 'urgent';
          });
          
          const count = newAnnouncements.length;
          countElement.textContent = count > 0 ? `${count} New` : 'No New';
          
          // Update subtitle with latest announcement info
          if (subtitleElement && newAnnouncements.length > 0) {
            const latest = newAnnouncements[0];
            const shortTitle = latest.title.length > 20 ? latest.title.substring(0, 20) + '...' : latest.title;
            subtitleElement.textContent = shortTitle;
          } else if (subtitleElement) {
            subtitleElement.textContent = 'Check announcements';
          }
        } else {
          countElement.textContent = 'No New';
          if (subtitleElement) {
            subtitleElement.textContent = 'Check announcements';
          }
        }
      }
    });
  }

  function updatePaymentCard(totalDue, dueDate) {
    // Update amount in Total Amount Due card on dashboard
    const paymentCards = document.querySelectorAll('[href="payments.php"]');
    paymentCards.forEach(card => {
      const amountElement = card.querySelector('.text-2xl');
      const dueDateElement = card.querySelector('.text-xs.opacity-80');
      
      if (amountElement) {
        amountElement.setAttribute('data-loading', 'false');
        amountElement.classList.remove('loading-skeleton');
        amountElement.textContent = `₱${totalDue.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',')}`;
      }
      
      if (dueDateElement && dueDate) {
        const formattedDate = new Date(dueDate).toLocaleDateString('en-US', { 
          month: 'short', 
          day: 'numeric', 
          year: 'numeric' 
        });
        dueDateElement.textContent = `Due: ${formattedDate}`;
      } else if (dueDateElement) {
        dueDateElement.textContent = 'No pending bills';
      }
    });
  }

  function filterAndSortRequests(searchTerm = '') {
    let filtered = [...allRequests];
    
    // Filter by category
    if (currentCategory !== 'all') {
      filtered = filtered.filter(r => r.category === currentCategory);
    }
    
    // Filter by search term
    if (searchTerm) {
      filtered = filtered.filter(r => 
        (r.title && r.title.toLowerCase().includes(searchTerm)) ||
        (r.description && r.description.toLowerCase().includes(searchTerm)) ||
        (r.location && r.location.toLowerCase().includes(searchTerm))
      );
    }
    
    // Sort
    if (currentSort === 'newest') {
      filtered.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
    } else if (currentSort === 'oldest') {
      filtered.sort((a, b) => new Date(a.created_at) - new Date(b.created_at));
    }
    
    // Display filtered requests
    displayRequests(filtered);
  }

  function displayRequests(requests) {
    const container = document.getElementById('requestsContent');
    const loadingState = document.getElementById('requestsLoadingState');
    const countElement = document.getElementById('requestsCount');
    
    if (!container) return;
    
    // Hide loading state and show content
    if (loadingState) loadingState.classList.add('hidden');
    container.classList.remove('hidden');
    
    // Update count
    if (countElement) {
      countElement.textContent = requests.length > 0 ? `Showing ${Math.min(requests.length, 3)} of ${allRequests.length}` : 'No requests';
    }
    
    // Clear content
    container.innerHTML = '';
    
    if (requests.length === 0) {
      container.innerHTML = `
        <div class="px-4 py-8 text-center text-slate-500">
          <i data-lucide="inbox" class="w-12 h-12 mx-auto mb-2 text-slate-300"></i>
          <p class="mb-2">No requests found</p>
          ${currentCategory !== 'all' ? `<button class="text-sm text-blue-600 hover:underline" onclick="document.querySelector('[data-category=\'all\']').click()">Show all requests</button>` : ''}
        </div>
      `;
      lucide.createIcons();
      return;
    }
    
    // Show only first 3 requests
    const displayRequests = requests.slice(0, 3);
    
    displayRequests.forEach(request => {
      const statusColors = {
        pending: 'bg-yellow-100 text-yellow-800',
        in_progress: 'bg-blue-100 text-blue-800',
        completed: 'bg-green-100 text-green-800',
        cancelled: 'bg-red-100 text-red-800'
      };
      
      const categoryIcons = {
        water: 'droplets',
        electricity: 'zap'
      };
      
      const html = `
        <div class="p-4 hover:bg-slate-50 transition-colors cursor-pointer request-item" data-id="${request.id}">
          <div class="flex items-start gap-3">
            <div class="flex-shrink-0 w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center">
              <i data-lucide="${categoryIcons[request.category] || 'file-text'}" class="w-5 h-5 text-blue-600"></i>
            </div>
            <div class="flex-1 min-w-0">
              <div class="flex items-start justify-between gap-2">
                <h4 class="text-sm font-semibold text-slate-900 truncate">${request.title || 'Service Request'}</h4>
                <span class="text-xs px-2 py-1 rounded-full whitespace-nowrap ${statusColors[request.status] || statusColors.pending}">
                  ${request.status.replace('_', ' ')}
                </span>
              </div>
              <p class="text-sm text-slate-600 mt-1 line-clamp-2">${request.description || 'No description'}</p>
              <div class="flex items-center gap-4 mt-2 text-xs text-slate-500">
                <span class="flex items-center gap-1">
                  <i data-lucide="calendar" class="w-3 h-3"></i>
                  ${new Date(request.created_at).toLocaleDateString()}
                </span>
                <span class="flex items-center gap-1">
                  <i data-lucide="map-pin" class="w-3 h-3"></i>
                  ${request.location || 'N/A'}
                </span>
              </div>
            </div>
          </div>
        </div>
      `;
      
      container.insertAdjacentHTML('beforeend', html);
    });
    
    lucide.createIcons();
    
    // Add click handlers to requests
    document.querySelectorAll('.request-item').forEach(item => {
      item.addEventListener('click', () => {
        const requestId = item.dataset.id;
        const basePath = window.location.pathname.includes('/public/') ? '' : 'public/';
        window.location.href = `${basePath}active-requests.php?id=${requestId}`;
      });
    });
  }

  function displayAnnouncements() {
    const container = document.getElementById('announcementsContent');
    const loadingState = document.getElementById('announcementsLoadingState');
    
    if (!container) return;
    
    // Hide loading state and show content
    if (loadingState) loadingState.classList.add('hidden');
    container.classList.remove('hidden');
    
    // Clear existing
    container.innerHTML = '';
    
    if (allAnnouncements.length === 0) {
      container.innerHTML = `
        <div class="p-4 text-center text-slate-500">
          <p class="text-sm">No announcements available</p>
        </div>
      `;
      return;
    }
    
    // Show latest 2 announcements
    const latest = allAnnouncements.slice(0, 2);
    latest.forEach(announcement => {
      const priorityColors = {
        urgent: 'bg-red-100 text-red-600',
        high: 'bg-orange-100 text-orange-600',
        normal: 'bg-blue-100 text-blue-600',
        low: 'bg-gray-100 text-gray-600'
      };
      
      const timeAgo = getTimeAgo(announcement.created_at);
      
      const html = `
        <div class="p-3 hover:bg-slate-50 transition-colors cursor-pointer announcement-item" data-id="${announcement.id}">
          <div class="flex items-center justify-between mb-1">
            <span class="text-[10px] font-bold px-1.5 py-0.5 rounded uppercase ${priorityColors[announcement.priority] || priorityColors.normal}">
              ${announcement.priority || 'News'}
            </span>
            <span class="text-xs text-slate-400">${timeAgo}</span>
          </div>
          <h4 class="text-sm font-medium text-slate-800 mb-1 leading-snug">${announcement.title}</h4>
          <p class="text-xs text-slate-500 line-clamp-2">${announcement.content}</p>
        </div>
      `;
      
      container.insertAdjacentHTML('beforeend', html);
    });
    
    lucide.createIcons();
    
    // Add click handlers to show modal
    document.querySelectorAll('.announcement-item').forEach(item => {
      item.addEventListener('click', () => {
        const announcementId = item.dataset.id;
        const announcement = allAnnouncements.find(a => a.id === announcementId);
        if (announcement) {
          showAnnouncementModal(announcement);
        }
      });
    });
    
    // View all announcements link
    const viewAllLink = document.querySelector('[href="#"]');
    if (viewAllLink && viewAllLink.textContent.includes('View all announcements')) {
      viewAllLink.href = window.location.pathname.includes('/public/') ? 'announcements.php' : 'public/announcements.php';
    }
  }

  function showAnnouncementModal(announcement) {
    // Create modal
    const modal = document.createElement('div');
    modal.className = 'fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4';
    modal.innerHTML = `
      <div class="bg-white rounded-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto shadow-2xl animate-fade-in">
        <div class="sticky top-0 bg-white border-b border-slate-200 px-6 py-4 flex items-center justify-between">
          <h2 class="text-xl font-bold text-slate-900">Announcement</h2>
          <button class="close-modal p-2 hover:bg-slate-100 rounded-lg transition-colors">
            <i data-lucide="x" class="w-5 h-5"></i>
          </button>
        </div>
        <div class="p-6">
          <div class="flex items-center gap-2 mb-4">
            <span class="text-xs font-bold px-2 py-1 rounded uppercase ${announcement.priority === 'urgent' ? 'bg-red-100 text-red-600' : 'bg-blue-100 text-blue-600'}">
              ${announcement.priority || 'News'}
            </span>
            <span class="text-sm text-slate-500">${new Date(announcement.created_at).toLocaleString()}</span>
          </div>
          <h3 class="text-2xl font-bold text-slate-900 mb-4">${announcement.title}</h3>
          <div class="prose prose-sm max-w-none text-slate-700">
            ${announcement.content}
          </div>
        </div>
        <div class="border-t border-slate-200 px-6 py-4 bg-slate-50">
          <button class="close-modal w-full py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium">
            Close
          </button>
        </div>
      </div>
    `;
    
    document.body.appendChild(modal);
    lucide.createIcons();
    
    // Close handlers
    modal.querySelectorAll('.close-modal').forEach(btn => {
      btn.addEventListener('click', () => {
        modal.remove();
      });
    });
    
    modal.addEventListener('click', (e) => {
      if (e.target === modal) {
        modal.remove();
      }
    });
  }

  function getTimeAgo(dateString) {
    const now = new Date();
    const date = new Date(dateString);
    const seconds = Math.floor((now - date) / 1000);
    
    if (seconds < 60) return 'Just now';
    if (seconds < 3600) return `${Math.floor(seconds / 60)} minutes ago`;
    if (seconds < 86400) return `${Math.floor(seconds / 3600)} hours ago`;
    if (seconds < 604800) return `${Math.floor(seconds / 86400)} days ago`;
    return date.toLocaleDateString();
  }

  // Function to load profile display - called on every page
  async function loadProfileDisplay() {
    console.log('[Profile Display] Function called');
    
    // First, refresh user data from API to ensure we have latest profile_image
    const token = sessionStorage.getItem('auth_token');
    if (token) {
      try {
        console.log('[Profile Display] Refreshing user data from API...');
        const response = await fetch('/api/auth/me', {
          headers: { 'Authorization': 'Bearer ' + token }
        });
        if (response.ok) {
          const data = await response.json();
          if (data.success && data.data) {
            sessionStorage.setItem('user', JSON.stringify(data.data));
            console.log('[Profile Display] User data refreshed from API');
          }
        }
      } catch (error) {
        console.log('[Profile Display] Could not refresh from API:', error.message);
      }
    }
    
    const user = sessionStorage.getItem('user');
    if (!user) {
      console.log('[Profile Display] No user in sessionStorage');
      return;
    }

    try {
      const userData = JSON.parse(user);
      console.log('[Profile Display] User data:', userData);
      console.log('[Profile Display] Profile image:', userData.profile_image);
      
      const profileName = document.getElementById('profileName');
      const profileEmail = document.getElementById('profileEmail');
      const profileAvatarLarge = document.getElementById('profileAvatarLarge');
      const profileBtn = document.getElementById('profileBtn');
      
      console.log('[Profile Display] Elements found:', {
        profileName: !!profileName,
        profileEmail: !!profileEmail,
        profileAvatarLarge: !!profileAvatarLarge,
        profileBtn: !!profileBtn
      });
      
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
        console.log('[Profile Display] Has profile image, processing...');
        // Display profile image - handle both file paths and base64
        let imageSrc;
        if (userData.profile_image.startsWith('data:')) {
          imageSrc = userData.profile_image;
          console.log('[Profile Display] Using base64 image');
        } else {
          // Extract just the filename if full path is provided
          const filename = userData.profile_image.includes('/') || userData.profile_image.includes('\\')
            ? userData.profile_image.split(/[\\/]/).pop()
            : userData.profile_image;
          imageSrc = '/api/uploads/profiles/' + filename;
          console.log('[Profile Display] Image URL:', imageSrc);
        }
        
        if (profileAvatarLarge) {
          profileAvatarLarge.innerHTML = `<img src="${imageSrc}" class="w-full h-full object-cover rounded-full" alt="Profile" />`;
          console.log('[Profile Display] Updated profileAvatarLarge');
        }
        if (profileBtn) {
          profileBtn.innerHTML = `<img src="${imageSrc}" class="w-full h-full object-cover rounded-full" alt="Profile" />`;
          console.log('[Profile Display] Updated profileBtn');
        }
      } else {
        console.log('[Profile Display] No profile image, using initials');
        // Display initials
        const initials = displayName.split(' ').map(n => n[0]).join('').toUpperCase().substring(0, 2);
        if (profileAvatarLarge) profileAvatarLarge.textContent = initials;
        if (profileBtn) profileBtn.textContent = initials;
        console.log('[Profile Display] Set initials:', initials);
      }
      console.log('[Profile Display] Completed successfully');
    } catch (error) {
      console.error('[Profile Display] Error:', error);
    }
  }

  // Call profile loader immediately
  console.log('[Dashboard.js] Calling loadProfileDisplay immediately');
  loadProfileDisplay();

  // Profile dropdown functionality
  const profileBtn = document.getElementById('profileBtn');
  const profileDropdown = document.getElementById('profileDropdown');
  const logoutBtn = document.getElementById('logoutBtn');
  const profileEditBtn = document.getElementById('profileEditBtn');
  const serviceAddressesBtn = document.getElementById('serviceAddressesBtn');
  const linkedMetersBtn = document.getElementById('linkedMetersBtn');
  const helpSupportBtn = document.getElementById('helpSupportBtn');

  if (profileBtn && profileDropdown) {
    // Toggle dropdown
    profileBtn.addEventListener('click', (e) => {
      e.stopPropagation();
      profileDropdown.classList.toggle('hidden');
      lucide.createIcons();
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', (e) => {
      if (!profileDropdown.contains(e.target) && e.target !== profileBtn) {
        profileDropdown.classList.add('hidden');
      }
    });
  }

  // Logout functionality
  if (logoutBtn) {
    logoutBtn.addEventListener('click', async () => {
      if (confirm('Are you sure you want to logout?')) {
        try {
          // Call API logout to destroy backend session
          await ApiClient.auth.logout();
        } catch (error) {
          console.error('Logout error:', error);
          // Fallback: clear storage and redirect manually
          sessionStorage.clear();
          localStorage.clear();
          window.location.href = window.location.pathname.includes('/public/') ? 'pages/auth/login.php' : 'public/pages/auth/login.php';
        }
      }
    });
  }

  // Profile edit button
  if (profileEditBtn) {
    profileEditBtn.addEventListener('click', () => {
      // Close dropdown
      profileDropdown.classList.add('hidden');
      
      // Redirect to edit profile page
      window.location.href = 'edit-profile.php';
    });
  }

  // Service Addresses button
  if (serviceAddressesBtn) {
    serviceAddressesBtn.addEventListener('click', () => {
      profileDropdown.classList.add('hidden');
      window.location.href = 'service-addresses.php';
    });
  }

  // Linked Meters button
  if (linkedMetersBtn) {
    linkedMetersBtn.addEventListener('click', () => {
      profileDropdown.classList.add('hidden');
      window.location.href = window.location.pathname.includes('/public/') ? 'linked-meters.php' : 'public/linked-meters.php';
    });
  }

  // Help & Support button
  if (helpSupportBtn) {
    helpSupportBtn.addEventListener('click', () => {
      window.location.href = 'help-support.php';
    });
  }

  // Settings button (deprecated - remove if exists)
  const profileSettingsBtn = document.getElementById('profileSettingsBtn');
  if (profileSettingsBtn) {
    profileSettingsBtn.addEventListener('click', () => {
      alert('Settings page coming soon!');
    });
  }

  // Initialize lucide icons when available
  if (typeof lucide !== 'undefined') {
    lucide.createIcons();
  } else {
    // Wait for lucide to load
    window.addEventListener('load', () => {
      if (typeof lucide !== 'undefined') {
        lucide.createIcons();
      }
    });
  }
  
  // Load data on page load
  if (window.location.pathname.includes('user-dashboard.php')) {
    // Check if authenticated before loading data
    const token = sessionStorage.getItem('auth_token');
    if (token) {
      loadDashboardData();
    } else {
      console.warn('No auth token found, skipping data load');
      window.location.replace(window.location.pathname.includes('/public/') ? 'pages/auth/login.php' : 'public/pages/auth/login.php');
    }
  }
})();
