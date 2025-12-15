/**
 * Admin Announcements Management - Real-time Database Integration
 */

// Global state
let allAnnouncements = [];
let filteredAnnouncements = [];
let currentFilter = 'All';
let selectedAnnouncement = null;
let currentUser = null;

// =============================================================================
// DOM Elements
// =============================================================================
let elements = {};

function initializeElements() {
  elements = {
    announcementsList: document.getElementById('announcementsList'),
    createAnnouncementBtn: document.getElementById('createAnnouncementBtn'),
    announcementModal: document.getElementById('announcementModal'),
    modalBackdrop: document.getElementById('modalBackdrop'),
    closeModalBtn: document.getElementById('closeModalBtn'),
    announcementForm: document.getElementById('announcementForm'),
    announcementId: document.getElementById('announcementId'),
    announcementTitle: document.getElementById('announcementTitle'),
    announcementCategory: document.getElementById('announcementCategory'),
    announcementType: document.getElementById('announcementType'),
    announcementContent: document.getElementById('announcementContent'),
    pinToDashboard: document.getElementById('pinToDashboard'),
    sendSms: document.getElementById('sendSms'),
    saveDraftBtn: document.getElementById('saveDraftBtn'),
    searchInput: document.getElementById('searchInput'),
    logoutBtn: document.getElementById('logoutBtn'),
    modalTitle: document.getElementById('modalTitle'),
    filterBtns: document.querySelectorAll('.filter-btn'),
    statActiveAlerts: document.getElementById('statActiveAlerts'),
    statTotalReach: document.getElementById('statTotalReach'),
    statUrgentSent: document.getElementById('statUrgentSent'),
    adminName: document.getElementById('adminName'),
    adminRole: document.getElementById('adminRole'),
    adminAvatar: document.getElementById('adminAvatar')
  };
}

// =============================================================================
// Helper Functions
// =============================================================================

function getTypeStyles(type) {
  const styles = {
    'urgent': 'bg-red-100 text-red-700 border-red-200',
    'maintenance': 'bg-amber-100 text-amber-700 border-amber-200',
    'info': 'bg-blue-100 text-blue-700 border-blue-200',
    'news': 'bg-slate-100 text-slate-700 border-slate-200'
  };
  return styles[type] || 'bg-slate-100 text-slate-700';
}

function getStatusBadge(status) {
  const badges = {
    'published': 'bg-green-100 text-green-700',
    'draft': 'bg-slate-200 text-slate-600',
    'archived': 'bg-slate-100 text-slate-400'
  };
  return badges[status] || 'bg-slate-100 text-slate-600';
}

function formatDate(dateString) {
  if (!dateString) return 'N/A';
  const date = new Date(dateString);
  const options = { 
    year: 'numeric', 
    month: 'short', 
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  };
  return date.toLocaleDateString('en-US', options);
}

function showToast(message, type = 'success') {
  const container = document.getElementById('toastContainer');
  if (!container) return;

  const toast = document.createElement('div');
  toast.className = `px-4 py-3 rounded-lg shadow-lg text-white text-sm font-medium animate-in slide-in-from-top-5 ${
    type === 'success' ? 'bg-green-600' : type === 'error' ? 'bg-red-600' : 'bg-blue-600'
  }`;
  toast.textContent = message;

  container.appendChild(toast);

  setTimeout(() => {
    toast.style.animation = 'fade-out 0.3s ease-out';
    setTimeout(() => toast.remove(), 300);
  }, 3000);
}

// =============================================================================
// API Functions
// =============================================================================

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

function updateUserProfile() {
  if (!currentUser) return;
  
  if (elements.adminName && currentUser.first_name && currentUser.last_name) {
    elements.adminName.textContent = `${currentUser.first_name} ${currentUser.last_name}`;
  }
  
  if (elements.adminRole) {
    const roleText = currentUser.role === 'admin' ? 'System Administrator' : 'Staff Member';
    elements.adminRole.textContent = roleText;
  }
  
  if (elements.adminAvatar && currentUser.first_name && currentUser.last_name) {
    const initials = (currentUser.first_name.charAt(0) + currentUser.last_name.charAt(0)).toUpperCase();
    elements.adminAvatar.textContent = initials;
  }
}

async function loadAnnouncements() {
  try {
    const response = await ApiClient.get('/admin/announcements/all');
    
    if (response.success) {
      allAnnouncements = response.data.announcements || [];
      applyFilters();
      updateStats();
    } else {
      showToast('Failed to load announcements', 'error');
    }
  } catch (error) {
    console.error('Error loading announcements:', error);
    showToast('Error loading announcements', 'error');
  }
}

async function createAnnouncement(data, status = 'published') {
  try {
    const announcementData = {
      ...data,
      status: status
    };

    const response = await ApiClient.post('/announcements', announcementData);
    
    if (response.success) {
      showToast(`Announcement ${status === 'draft' ? 'saved as draft' : 'published'} successfully!`, 'success');
      closeModal();
      await loadAnnouncements();
      return true;
    } else {
      showToast(response.message || 'Failed to create announcement', 'error');
      return false;
    }
  } catch (error) {
    console.error('Error creating announcement:', error);
    showToast('Error creating announcement', 'error');
    return false;
  }
}

async function updateAnnouncement(id, data) {
  try {
    const response = await ApiClient.put(`/announcements/${id}`, data);
    
    if (response.success) {
      showToast('Announcement updated successfully!', 'success');
      closeModal();
      await loadAnnouncements();
      return true;
    } else {
      showToast(response.message || 'Failed to update announcement', 'error');
      return false;
    }
  } catch (error) {
    console.error('Error updating announcement:', error);
    showToast('Error updating announcement', 'error');
    return false;
  }
}

async function deleteAnnouncement(id) {
  if (!confirm('Are you sure you want to delete this announcement? This action cannot be undone.')) {
    return;
  }

  try {
    const response = await ApiClient.delete(`/announcements/${id}`);
    
    if (response.success) {
      showToast('Announcement deleted successfully!', 'success');
      await loadAnnouncements();
    } else {
      showToast(response.message || 'Failed to delete announcement', 'error');
    }
  } catch (error) {
    console.error('Error deleting announcement:', error);
    showToast('Error deleting announcement', 'error');
  }
}

// =============================================================================
// UI Functions
// =============================================================================

function applyFilters() {
  if (currentFilter === 'All') {
    filteredAnnouncements = [...allAnnouncements];
  } else {
    filteredAnnouncements = allAnnouncements.filter(a => a.status === currentFilter);
  }
  
  renderAnnouncements();
}

function updateStats() {
  // Active alerts (published)
  const activeCount = allAnnouncements.filter(a => a.status === 'published').length;
  if (elements.statActiveAlerts) {
    elements.statActiveAlerts.textContent = activeCount;
  }

  // Total reach (sum of views)
  const totalViews = allAnnouncements.reduce((sum, a) => sum + (parseInt(a.views) || 0), 0);
  if (elements.statTotalReach) {
    elements.statTotalReach.textContent = totalViews >= 1000 
      ? `${(totalViews / 1000).toFixed(1)}k` 
      : totalViews;
  }

  // Urgent sent (count urgent type)
  const urgentCount = allAnnouncements.filter(a => a.type === 'urgent' && a.status === 'published').length;
  if (elements.statUrgentSent) {
    elements.statUrgentSent.textContent = urgentCount;
  }
}

function renderAnnouncements() {
  if (!elements.announcementsList) return;

  if (filteredAnnouncements.length === 0) {
    elements.announcementsList.innerHTML = `
      <div class="p-12 text-center text-slate-500">
        <div class="flex flex-col items-center gap-3">
          <i data-lucide="inbox" class="w-12 h-12 text-slate-300"></i>
          <p>No announcements found</p>
        </div>
      </div>
    `;
    lucide.createIcons();
    return;
  }

  elements.announcementsList.innerHTML = filteredAnnouncements.map(announcement => `
    <div class="p-5 hover:bg-slate-50 transition-colors group">
      <div class="flex justify-between items-start gap-4">
        <div class="flex-1">
          <div class="flex items-center gap-2 mb-2">
            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase border ${getTypeStyles(announcement.type)}">
              ${announcement.type || 'info'}
            </span>
            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase ${getStatusBadge(announcement.status)}">
              ${announcement.status || 'draft'}
            </span>
          </div>
          <h3 class="text-lg font-bold text-slate-800 mb-1">${announcement.title}</h3>
          <p class="text-sm text-slate-600 mb-3 max-w-2xl">${announcement.content.substring(0, 150)}${announcement.content.length > 150 ? '...' : ''}</p>
          <div class="flex items-center gap-4 text-xs text-slate-400">
            <span class="flex items-center gap-1">
              <i data-lucide="clock" class="w-3 h-3"></i> ${formatDate(announcement.created_at)}
            </span>
            <span class="flex items-center gap-1">
              <i data-lucide="eye" class="w-3 h-3"></i> ${announcement.views || 0} Views
            </span>
            <span class="font-medium text-slate-500">${announcement.category} Dept.</span>
          </div>
        </div>
        
        <!-- Actions -->
        <div class="flex items-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
          <button 
            onclick="editAnnouncement('${announcement.id}')" 
            class="p-2 border border-slate-200 rounded text-slate-500 hover:text-blue-600 hover:bg-white" 
            title="Edit"
          >
            <i data-lucide="edit-2" class="w-4 h-4"></i>
          </button>
          <button 
            onclick="handleDeleteAnnouncement('${announcement.id}')" 
            class="p-2 border border-slate-200 rounded text-slate-500 hover:text-red-600 hover:bg-white" 
            title="Delete"
          >
            <i data-lucide="trash-2" class="w-4 h-4"></i>
          </button>
        </div>
      </div>
    </div>
  `).join('');

  lucide.createIcons();
}

// =============================================================================
// Modal Functions
// =============================================================================

function openModal(announcement = null) {
  if (!elements.announcementModal) return;

  selectedAnnouncement = announcement;

  if (announcement) {
    // Edit mode
    elements.modalTitle.textContent = 'Edit Announcement';
    elements.announcementId.value = announcement.id;
    elements.announcementTitle.value = announcement.title;
    elements.announcementCategory.value = announcement.category;
    elements.announcementType.value = announcement.type;
    elements.announcementContent.value = announcement.content;
  } else {
    // Create mode
    elements.modalTitle.textContent = 'Compose Announcement';
    elements.announcementForm.reset();
    elements.announcementId.value = '';
  }

  elements.announcementModal.classList.remove('hidden');
  lucide.createIcons();
}

function closeModal() {
  if (!elements.announcementModal) return;
  elements.announcementModal.classList.add('hidden');
  elements.announcementForm.reset();
  selectedAnnouncement = null;
}

// =============================================================================
// Event Handlers
// =============================================================================

function handleFormSubmit(e) {
  e.preventDefault();

  const formData = {
    title: elements.announcementTitle.value.trim(),
    content: elements.announcementContent.value.trim(),
    category: elements.announcementCategory.value,
    type: elements.announcementType.value
  };

  // Validate required fields
  if (!formData.title) {
    showToast('Please enter a title', 'error');
    elements.announcementTitle.focus();
    return;
  }

  if (!formData.content) {
    showToast('Please enter content', 'error');
    elements.announcementContent.focus();
    return;
  }

  const announcementId = elements.announcementId.value;

  if (announcementId) {
    // Update existing
    updateAnnouncement(announcementId, { ...formData, status: 'published' });
  } else {
    // Create new
    createAnnouncement(formData, 'published');
  }
}

function handleSaveDraft() {
  const formData = {
    title: elements.announcementTitle.value.trim(),
    content: elements.announcementContent.value.trim(),
    category: elements.announcementCategory.value,
    type: elements.announcementType.value
  };

  // Validate required fields
  if (!formData.title) {
    showToast('Please enter a title', 'error');
    elements.announcementTitle.focus();
    return;
  }

  if (!formData.content) {
    showToast('Please enter content', 'error');
    elements.announcementContent.focus();
    return;
  }

  const announcementId = elements.announcementId.value;

  if (announcementId) {
    updateAnnouncement(announcementId, { ...formData, status: 'draft' });
  } else {
    createAnnouncement(formData, 'draft');
  }
}

function handleFilterChange(e) {
  const filterBtn = e.target.closest('.filter-btn');
  if (!filterBtn) return;

  currentFilter = filterBtn.dataset.filter;

  // Update active button
  elements.filterBtns.forEach(btn => {
    if (btn === filterBtn) {
      btn.classList.add('bg-slate-800', 'text-white');
      btn.classList.remove('text-slate-600', 'hover:bg-slate-50');
    } else {
      btn.classList.remove('bg-slate-800', 'text-white');
      btn.classList.add('text-slate-600', 'hover:bg-slate-50');
    }
  });

  applyFilters();
}

function handleSearch(e) {
  const searchTerm = e.target.value.toLowerCase();
  
  if (searchTerm.trim() === '') {
    applyFilters();
    return;
  }

  filteredAnnouncements = allAnnouncements.filter(announcement => {
    return announcement.title.toLowerCase().includes(searchTerm) ||
           announcement.content.toLowerCase().includes(searchTerm) ||
           announcement.category.toLowerCase().includes(searchTerm);
  });

  renderAnnouncements();
}

function handleLogout() {
  if (confirm('Are you sure you want to logout?')) {
    localStorage.removeItem('auth_token');
    localStorage.removeItem('user');
    window.location.href = '/login.php';
  }
}

// Make functions globally accessible for onclick handlers
window.editAnnouncement = function(id) {
  const announcement = allAnnouncements.find(a => a.id === id);
  if (announcement) {
    openModal(announcement);
  }
};

window.handleDeleteAnnouncement = deleteAnnouncement;

// =============================================================================
// Event Listeners
// =============================================================================

function initializeEventListeners() {
  // Create button
  elements.createAnnouncementBtn?.addEventListener('click', () => openModal());

  // Modal controls
  elements.closeModalBtn?.addEventListener('click', closeModal);
  elements.modalBackdrop?.addEventListener('click', closeModal);

  // Form submit
  elements.announcementForm?.addEventListener('submit', handleFormSubmit);

  // Save draft
  elements.saveDraftBtn?.addEventListener('click', handleSaveDraft);

  // Filter buttons
  elements.filterBtns.forEach(btn => {
    btn.addEventListener('click', handleFilterChange);
  });

  // Search
  elements.searchInput?.addEventListener('input', handleSearch);

  // Logout
  elements.logoutBtn?.addEventListener('click', handleLogout);

  // Escape key to close modal
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && !elements.announcementModal.classList.contains('hidden')) {
      closeModal();
    }
  });
}

// =============================================================================
// Initialize
// =============================================================================

document.addEventListener('DOMContentLoaded', async () => {
  initializeElements();
  initializeEventListeners();
  await loadCurrentUser();
  await loadAnnouncements();
  
  // Auto-refresh every 30 seconds
  setInterval(loadAnnouncements, 30000);
});
