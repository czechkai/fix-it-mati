/**
 * Admin Dashboard JavaScript
 * Handles all admin dashboard functionality with real-time data
 */

let currentView = 'overview';
let autoRefreshInterval = null;
let currentUser = null;

// Initialize
document.addEventListener('DOMContentLoaded', function() {
  loadCurrentUser();
  setupEventListeners();
  loadDashboardData();
  
  // Auto-refresh every 30 seconds
  autoRefreshInterval = setInterval(() => {
    refreshCurrentView();
  }, 30000);
  
  // Initialize Lucide icons
  lucide.createIcons();
});

// Cleanup
window.addEventListener('beforeunload', function() {
  if (autoRefreshInterval) {
    clearInterval(autoRefreshInterval);
  }
});

/**
 * Load current user data
 */
async function loadCurrentUser() {
  try {
    const response = await ApiClient.get('/auth/me');
    if (response.success) {
      currentUser = response.data;
      updateUserProfile();
    }
  } catch (error) {
    console.error('Error loading user:', error);
  }
}

/**
 * Update user profile in header
 */
function updateUserProfile() {
  if (!currentUser) return;
  
  const adminName = document.getElementById('adminName');
  const adminRole = document.getElementById('adminRole');
  const adminAvatar = document.getElementById('adminAvatar');
  
  if (adminName) {
    adminName.textContent = `${currentUser.first_name} ${currentUser.last_name}`;
  }
  
  if (adminRole) {
    const roleText = currentUser.role === 'admin' ? 'System Administrator' : 'Staff Member';
    adminRole.textContent = roleText;
  }
  
  if (adminAvatar) {
    const initials = (currentUser.first_name.charAt(0) + currentUser.last_name.charAt(0)).toUpperCase();
    adminAvatar.textContent = initials;
  }
}

/**
 * Setup event listeners
 */
function setupEventListeners() {
  // Navigation
  const navItems = document.querySelectorAll('.nav-item');
  navItems.forEach(item => {
    item.addEventListener('click', function() {
      const view = this.getAttribute('data-view');
      switchView(view);
    });
  });
  
  // Logout
  const logoutBtn = document.getElementById('logoutBtn');
  if (logoutBtn) {
    logoutBtn.addEventListener('click', handleLogout);
  }
  
  // Create announcement
  const createAnnouncementBtn = document.getElementById('createAnnouncementBtn');
  if (createAnnouncementBtn) {
    createAnnouncementBtn.addEventListener('click', openAnnouncementModal);
  }
  
  // Announcement form
  const announcementForm = document.getElementById('announcementForm');
  if (announcementForm) {
    announcementForm.addEventListener('submit', handleCreateAnnouncement);
  }
  
  // User search
  const userSearch = document.getElementById('userSearch');
  if (userSearch) {
    userSearch.addEventListener('input', handleUserSearch);
  }
  
  // Global search
  const globalSearch = document.getElementById('globalSearch');
  if (globalSearch) {
    globalSearch.addEventListener('input', handleGlobalSearch);
  }
}

/**
 * Switch view
 */
function switchView(view) {
  currentView = view;
  
  // Hide all views
  const views = document.querySelectorAll('.view-content');
  views.forEach(v => v.classList.add('hidden'));
  
  // Show selected view
  const selectedView = document.getElementById(`view${capitalize(view)}`);
  if (selectedView) {
    selectedView.classList.remove('hidden');
    selectedView.classList.add('animate-in');
  }
  
  // Update navigation
  const navItems = document.querySelectorAll('.nav-item');
  navItems.forEach(item => {
    if (item.getAttribute('data-view') === view) {
      item.classList.add('bg-blue-600', 'text-white', 'shadow-md');
      item.classList.remove('hover:bg-slate-800');
    } else {
      item.classList.remove('bg-blue-600', 'text-white', 'shadow-md');
      item.classList.add('hover:bg-slate-800');
    }
  });
  
  // Load view data
  refreshCurrentView();
  
  // Reinitialize icons
  lucide.createIcons();
}

/**
 * Load dashboard data
 */
async function loadDashboardData() {
  await Promise.all([
    loadStats(),
    loadRecentTickets(),
    loadAnnouncements(),
    loadUsers()
  ]);
}

/**
 * Refresh current view
 */
function refreshCurrentView() {
  switch(currentView) {
    case 'overview':
      loadStats();
      loadRecentTickets();
      break;
    case 'tickets':
      loadAllTickets();
      break;
    case 'announcements':
      loadAnnouncements();
      break;
    case 'billing':
      loadBillingData();
      break;
    case 'technicians':
      loadTechnicians();
      break;
    case 'users':
      loadUsers();
      break;
  }
}

/**
 * Load statistics
 */
async function loadStats() {
  try {
    const response = await ApiClient.get('/requests/statistics');
    if (response.success) {
      const stats = response.data;
      displayStats(stats);
    }
  } catch (error) {
    console.error('Error loading stats:', error);
  }
}

/**
 * Display statistics
 */
function displayStats(stats) {
  const statsGrid = document.getElementById('statsGrid');
  if (!statsGrid) return;
  
  const statsConfig = [
    {
      title: 'Total Requests',
      value: stats.total || 0,
      change: '+12%',
      trend: 'up',
      icon: 'ticket',
      iconColor: 'text-blue-600',
      bg: 'bg-blue-100'
    },
    {
      title: 'Pending Triage',
      value: stats.pending || 0,
      change: '-5%',
      trend: 'down',
      icon: 'alert-circle',
      iconColor: 'text-amber-600',
      bg: 'bg-amber-100'
    },
    {
      title: 'Active Repairs',
      value: stats.in_progress || 0,
      change: '+2%',
      trend: 'up',
      icon: 'hammer',
      iconColor: 'text-purple-600',
      bg: 'bg-purple-100'
    },
    {
      title: 'Resolved (Today)',
      value: stats.resolved_today || 0,
      change: '+8%',
      trend: 'up',
      icon: 'check-circle-2',
      iconColor: 'text-green-600',
      bg: 'bg-green-100'
    }
  ];
  
  statsGrid.innerHTML = statsConfig.map(stat => `
    <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm hover:shadow-md transition-shadow">
      <div class="flex justify-between items-start mb-4">
        <div class="p-2 rounded-lg ${stat.bg}">
          <i data-lucide="${stat.icon}" class="${stat.iconColor} w-6 h-6"></i>
        </div>
        <span class="text-xs font-bold px-2 py-1 rounded-full ${stat.trend === 'up' ? 'bg-green-50 text-green-600' : 'bg-red-50 text-red-600'}">
          ${stat.change}
        </span>
      </div>
      <div class="text-2xl font-bold text-slate-800 mb-1">${stat.value}</div>
      <div class="text-xs text-slate-500 font-medium uppercase tracking-wide">${stat.title}</div>
    </div>
  `).join('');
  
  // Update pending badge
  const pendingBadge = document.getElementById('pendingBadge');
  if (pendingBadge) {
    pendingBadge.textContent = stats.pending || 0;
  }
  
  lucide.createIcons();
}

/**
 * Load recent tickets
 */
async function loadRecentTickets() {
  try {
    const response = await ApiClient.get('/requests?limit=5');
    if (response.success) {
      displayRecentTickets(response.data);
    }
  } catch (error) {
    console.error('Error loading recent tickets:', error);
  }
}

/**
 * Display recent tickets
 */
function displayRecentTickets(tickets) {
  const tbody = document.getElementById('recentTicketsBody');
  if (!tbody) return;
  
  if (tickets.length === 0) {
    tbody.innerHTML = `
      <tr>
        <td colspan="5" class="px-5 py-8 text-center text-slate-400">
          No service requests found
        </td>
      </tr>
    `;
    return;
  }
  
  tbody.innerHTML = tickets.map(ticket => `
    <tr class="hover:bg-slate-50 transition-colors group">
      <td class="px-5 py-3 font-mono text-slate-500">${ticket.ticket_number || 'N/A'}</td>
      <td class="px-5 py-3">
        <div class="font-medium text-slate-800">${escapeHtml(ticket.description || 'No description')}</div>
        <div class="text-xs text-slate-500">${escapeHtml(ticket.service_type || 'General')} • ${escapeHtml(ticket.address || 'No address')}</div>
      </td>
      <td class="px-5 py-3 text-xs ${getPriorityColor(ticket.priority || 'Medium')}">${ticket.priority || 'Medium'}</td>
      <td class="px-5 py-3">
        <span class="px-2 py-0.5 rounded-full text-xs font-bold border ${getStatusBadge(ticket.status)}">${ticket.status}</span>
      </td>
      <td class="px-5 py-3 text-right">
        <button onclick="viewTicketDetails('${ticket.id}')" class="text-slate-400 hover:text-blue-600 p-1 rounded hover:bg-blue-50">
          <i data-lucide="more-vertical" class="w-4 h-4"></i>
        </button>
      </td>
    </tr>
  `).join('');
  
  lucide.createIcons();
}

/**
 * Load all tickets
 */
async function loadAllTickets() {
  try {
    const response = await ApiClient.get('/requests');
    if (response.success) {
      displayAllTickets(response.data);
    }
  } catch (error) {
    console.error('Error loading all tickets:', error);
  }
}

/**
 * Display all tickets
 */
function displayAllTickets(tickets) {
  const tbody = document.getElementById('allTicketsBody');
  if (!tbody) return;
  
  if (tickets.length === 0) {
    tbody.innerHTML = `
      <tr>
        <td colspan="9" class="px-5 py-8 text-center text-slate-400">
          No service requests found
        </td>
      </tr>
    `;
    return;
  }
  
  tbody.innerHTML = tickets.map(ticket => `
    <tr class="hover:bg-slate-50 transition-colors">
      <td class="px-5 py-3 font-mono text-slate-500">${ticket.ticket_number || 'N/A'}</td>
      <td class="px-5 py-3 font-medium text-slate-800">${escapeHtml(ticket.requester_name || 'Unknown')}</td>
      <td class="px-5 py-3 text-slate-600">${escapeHtml(ticket.service_type || 'General')}</td>
      <td class="px-5 py-3 text-slate-800">${escapeHtml(ticket.description || 'No description')}</td>
      <td class="px-5 py-3 text-slate-600">${escapeHtml(ticket.address || 'N/A')}</td>
      <td class="px-5 py-3 text-xs ${getPriorityColor(ticket.priority || 'Medium')}">${ticket.priority || 'Medium'}</td>
      <td class="px-5 py-3">
        <span class="px-2 py-0.5 rounded-full text-xs font-bold border ${getStatusBadge(ticket.status)}">${ticket.status}</span>
      </td>
      <td class="px-5 py-3 text-slate-500 text-xs">${formatDate(ticket.created_at)}</td>
      <td class="px-5 py-3 text-right">
        <button onclick="viewTicketDetails('${ticket.id}')" class="text-slate-400 hover:text-blue-600 p-1">
          <i data-lucide="more-vertical" class="w-4 h-4"></i>
        </button>
      </td>
    </tr>
  `).join('');
  
  lucide.createIcons();
}

/**
 * Load announcements
 */
async function loadAnnouncements() {
  try {
    const response = await ApiClient.get('/announcements');
    if (response.success) {
      displayAnnouncements(response.data);
    }
  } catch (error) {
    console.error('Error loading announcements:', error);
  }
}

/**
 * Display announcements
 */
function displayAnnouncements(announcements) {
  const tbody = document.getElementById('announcementsBody');
  if (!tbody) return;
  
  if (announcements.length === 0) {
    tbody.innerHTML = `
      <tr>
        <td colspan="6" class="px-5 py-8 text-center text-slate-400">
          No announcements found. Create one to get started.
        </td>
      </tr>
    `;
    return;
  }
  
  tbody.innerHTML = announcements.map(ann => `
    <tr class="hover:bg-slate-50">
      <td class="px-5 py-3 font-medium text-slate-800">${escapeHtml(ann.title)}</td>
      <td class="px-5 py-3 text-slate-500">${escapeHtml(ann.category || 'General')}</td>
      <td class="px-5 py-3">
        <span class="px-2 py-0.5 rounded text-xs font-bold ${ann.priority === 'urgent' ? 'bg-red-100 text-red-600' : 'bg-blue-100 text-blue-600'}">
          ${ann.priority || 'info'}
        </span>
      </td>
      <td class="px-5 py-3 text-slate-500">${formatDate(ann.created_at)}</td>
      <td class="px-5 py-3">
        <span class="px-2 py-0.5 bg-green-100 text-green-700 rounded-full text-xs font-bold">
          ${ann.status === 'published' ? 'Published' : 'Draft'}
        </span>
      </td>
      <td class="px-5 py-3 text-right">
        <button onclick="deleteAnnouncement('${ann.id}')" class="text-slate-400 hover:text-red-600 p-1">
          <i data-lucide="trash-2" class="w-4 h-4"></i>
        </button>
      </td>
    </tr>
  `).join('');
  
  lucide.createIcons();
}

/**
 * Load billing data
 */
async function loadBillingData() {
  try {
    const response = await ApiClient.get('/payments/history');
    if (response.success) {
      displayBillingStats(response.data);
      displayTransactions(response.data);
    }
  } catch (error) {
    console.error('Error loading billing data:', error);
  }
}

/**
 * Display billing stats
 */
function displayBillingStats(data) {
  const transactions = data || [];
  
  // Calculate stats
  const totalRevenue = transactions
    .filter(t => t.status === 'completed')
    .reduce((sum, t) => sum + parseFloat(t.amount || 0), 0);
    
  const pendingCount = transactions.filter(t => t.status === 'pending').length;
  const successfulCount = transactions.filter(t => t.status === 'completed').length;
  const successRate = transactions.length > 0 ? Math.round((successfulCount / transactions.length) * 100) : 0;
  
  // Update DOM
  const totalRevenueEl = document.getElementById('totalRevenue');
  if (totalRevenueEl) {
    totalRevenueEl.textContent = `₱${totalRevenue.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
  }
  
  const pendingPaymentsEl = document.getElementById('pendingPayments');
  if (pendingPaymentsEl) {
    pendingPaymentsEl.textContent = pendingCount;
  }
  
  const successfulTransactionsEl = document.getElementById('successfulTransactions');
  if (successfulTransactionsEl) {
    successfulTransactionsEl.textContent = successfulCount;
  }
  
  const successRateEl = document.getElementById('successRate');
  if (successRateEl) {
    successRateEl.textContent = `${successRate}% success rate`;
  }
}

/**
 * Display transactions
 */
function displayTransactions(transactions) {
  const tbody = document.getElementById('transactionsBody');
  if (!tbody) return;
  
  if (!transactions || transactions.length === 0) {
    tbody.innerHTML = `
      <tr>
        <td colspan="6" class="px-5 py-8 text-center text-slate-400">
          No transactions found
        </td>
      </tr>
    `;
    return;
  }
  
  tbody.innerHTML = transactions.slice(0, 10).map(trx => `
    <tr class="hover:bg-slate-50">
      <td class="px-5 py-3 font-mono text-slate-500">${trx.transaction_id || 'N/A'}</td>
      <td class="px-5 py-3 font-medium text-slate-800">${escapeHtml(trx.user_name || 'Unknown')}</td>
      <td class="px-5 py-3 text-slate-600">${escapeHtml(trx.payment_type || 'Payment')}</td>
      <td class="px-5 py-3 font-bold text-slate-800">₱${parseFloat(trx.amount || 0).toFixed(2)}</td>
      <td class="px-5 py-3 text-slate-500">${formatDate(trx.created_at)}</td>
      <td class="px-5 py-3">
        <span class="px-2 py-0.5 rounded-full text-xs font-bold border ${getStatusBadge(trx.status)}">${trx.status}</span>
      </td>
    </tr>
  `).join('');
  
  lucide.createIcons();
}

/**
 * Load technicians
 */
async function loadTechnicians() {
  const grid = document.getElementById('techniciansGrid');
  if (!grid) return;
  
  // Mock data for now - can be connected to API later
  const technicians = [
    { id: 1, name: 'Team Alpha', head: 'Engr. Santos', dept: 'Water District', status: 'Busy', currentTask: 'SR-8821' },
    { id: 2, name: 'Team Bravo', head: 'Engr. Reyes', dept: 'Davao Light', status: 'Available', currentTask: '-' },
    { id: 3, name: 'Team Charlie', head: 'Foreman Lito', dept: 'Public Works', status: 'On Route', currentTask: 'SR-8819' }
  ];
  
  grid.innerHTML = technicians.map(tech => `
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5 hover:shadow-md transition-shadow">
      <div class="flex justify-between items-start mb-4">
        <div class="h-10 w-10 rounded-full bg-slate-100 flex items-center justify-center font-bold text-slate-600">
          ${tech.name.charAt(5)}
        </div>
        <span class="px-2 py-0.5 rounded text-xs font-bold ${getStatusBadge(tech.status)}">${tech.status}</span>
      </div>
      <h3 class="font-bold text-slate-800">${tech.name}</h3>
      <p class="text-sm text-slate-500">${tech.head} • ${tech.dept}</p>
      <div class="mt-4 pt-4 border-t border-slate-100 flex justify-between items-center text-xs">
        <span class="text-slate-400">Current Assignment</span>
        <span class="font-mono bg-slate-50 px-2 py-1 rounded text-slate-600">${tech.currentTask}</span>
      </div>
    </div>
  `).join('');
}

/**
 * Load users
 */
async function loadUsers() {
  try {
    // This would need an admin endpoint to list all users
    // For now, we'll show a message
    const tbody = document.getElementById('usersBody');
    if (!tbody) return;
    
    tbody.innerHTML = `
      <tr>
        <td colspan="5" class="px-5 py-8 text-center text-slate-400">
          User management coming soon. Contact system administrator.
        </td>
      </tr>
    `;
  } catch (error) {
    console.error('Error loading users:', error);
  }
}

/**
 * Handle logout
 */
async function handleLogout() {
  if (!confirm('Are you sure you want to logout?')) return;
  
  try {
    await ApiClient.post('/auth/logout');
  } catch (error) {
    console.error('Logout error:', error);
  } finally {
    sessionStorage.clear();
    window.location.href = 'login.php';
  }
}

/**
 * Open announcement modal
 */
function openAnnouncementModal() {
  const modal = document.getElementById('announcementModal');
  if (modal) {
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
  }
  lucide.createIcons();
}

/**
 * Close announcement modal
 */
function closeAnnouncementModal() {
  const modal = document.getElementById('announcementModal');
  if (modal) {
    modal.classList.add('hidden');
    document.body.style.overflow = '';
    document.getElementById('announcementForm').reset();
  }
}

/**
 * Handle create announcement
 */
async function handleCreateAnnouncement(e) {
  e.preventDefault();
  
  const formData = new FormData(e.target);
  const data = {
    title: formData.get('title'),
    category: formData.get('category'),
    priority: formData.get('priority'),
    content: formData.get('content'),
    status: 'published'
  };
  
  try {
    const response = await ApiClient.post('/announcements', data);
    if (response.success) {
      alert('Announcement published successfully!');
      closeAnnouncementModal();
      loadAnnouncements();
    } else {
      alert('Failed to create announcement: ' + (response.message || 'Unknown error'));
    }
  } catch (error) {
    console.error('Error creating announcement:', error);
    alert('Failed to create announcement. Please try again.');
  }
}

/**
 * Delete announcement
 */
async function deleteAnnouncement(id) {
  if (!confirm('Are you sure you want to delete this announcement?')) return;
  
  try {
    const response = await ApiClient.delete(`/announcements/${id}`);
    if (response.success) {
      alert('Announcement deleted successfully!');
      loadAnnouncements();
    } else {
      alert('Failed to delete announcement: ' + (response.message || 'Unknown error'));
    }
  } catch (error) {
    console.error('Error deleting announcement:', error);
    alert('Failed to delete announcement. Please try again.');
  }
}

/**
 * View ticket details
 */
function viewTicketDetails(ticketId) {
  // Navigate to request detail page
  window.location.href = `request-detail.php?id=${ticketId}`;
}

/**
 * Handle user search
 */
function handleUserSearch(e) {
  const query = e.target.value.toLowerCase();
  // Implementation for user search filtering
  console.log('Searching users:', query);
}

/**
 * Handle global search
 */
function handleGlobalSearch(e) {
  const query = e.target.value.toLowerCase();
  // Implementation for global search
  console.log('Global search:', query);
}

/**
 * Helper: Get status badge class
 */
function getStatusBadge(status) {
  const badges = {
    'pending': 'bg-red-100 text-red-700 border-red-200',
    'pending_review': 'bg-red-100 text-red-700 border-red-200',
    'in_progress': 'bg-blue-100 text-blue-700 border-blue-200',
    'assigned': 'bg-blue-100 text-blue-700 border-blue-200',
    'completed': 'bg-green-100 text-green-700 border-green-200',
    'resolved': 'bg-green-100 text-green-700 border-green-200',
    'Resolved': 'bg-green-100 text-green-700 border-green-200',
    'Success': 'bg-green-100 text-green-700 border-green-200',
    'Verified': 'bg-green-100 text-green-700 border-green-200',
    'Published': 'bg-green-100 text-green-700 border-green-200',
    'Available': 'bg-green-100 text-green-700 border-green-200',
    'Busy': 'bg-red-100 text-red-700 border-red-200',
    'On Route': 'bg-blue-100 text-blue-700 border-blue-200'
  };
  return badges[status] || 'bg-slate-100 text-slate-700 border-slate-200';
}

/**
 * Helper: Get priority color
 */
function getPriorityColor(priority) {
  const colors = {
    'Critical': 'text-red-600 font-bold',
    'High': 'text-orange-600 font-medium',
    'Medium': 'text-blue-600',
    'Low': 'text-slate-500'
  };
  return colors[priority] || 'text-slate-500';
}

/**
 * Helper: Format date
 */
function formatDate(dateString) {
  if (!dateString) return 'N/A';
  
  const date = new Date(dateString);
  const now = new Date();
  const diff = now - date;
  const hours = Math.floor(diff / 3600000);
  const days = Math.floor(diff / 86400000);
  
  if (hours < 1) return 'Just now';
  if (hours < 24) return `${hours} hrs ago`;
  if (days < 7) return `${days} days ago`;
  
  return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
}

/**
 * Helper: Capitalize
 */
function capitalize(str) {
  return str.charAt(0).toUpperCase() + str.slice(1);
}

/**
 * Helper: Escape HTML
 */
function escapeHtml(text) {
  if (!text) return '';
  const div = document.createElement('div');
  div.textContent = text;
  return div.innerHTML;
}
