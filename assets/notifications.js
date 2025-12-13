/**
 * FixItMati - Notifications Page
 * Real-time notification management with filters and actions
 */

// ============================================
// STATE MANAGEMENT
// ============================================

let allNotifications = [];
let activeFilter = 'All';
let autoRefreshInterval = null;
let currentModalNotification = null;

// ============================================
// NOTIFICATION ICON & COLOR MAPPING
// ============================================

const notificationIcons = {
  urgent: 'alert-triangle',
  billing: 'credit-card',
  update: 'check',
  info: 'info'
};

const categoryIcons = {
  water: 'droplets',
  'electricity': 'zap',
  'power': 'zap',
  service: 'hammer',
  system: 'bell'
};

const iconColors = {
  urgent: 'text-red-600 bg-red-100',
  billing: 'text-green-600 bg-green-100',
  update: 'text-blue-600 bg-blue-100',
  info: 'text-slate-600 bg-slate-100',
  water: 'text-blue-600 bg-blue-100',
  electricity: 'text-amber-600 bg-amber-100',
  power: 'text-amber-600 bg-amber-100',
  service: 'text-slate-600 bg-slate-100',
  system: 'text-purple-600 bg-purple-100'
};

// ============================================
// INITIALIZATION
// ============================================

document.addEventListener('DOMContentLoaded', () => {
  console.log('Notifications page initializing...');
  
  // Setup event listeners
  setupEventListeners();
  
  // Load notifications
  loadNotifications();
  
  // Start auto-refresh (every 30 seconds)
  startAutoRefresh();
  
  console.log('Notifications page initialized');
});

// ============================================
// EVENT LISTENERS
// ============================================

function setupEventListeners() {
  // Filter buttons
  document.querySelectorAll('.filter-btn').forEach(btn => {
    btn.addEventListener('click', (e) => {
      const filter = e.currentTarget.dataset.filter;
      setActiveFilter(filter);
    });
  });
  
  // Mark all as read
  const markAllBtn = document.getElementById('markAllReadBtn');
  if (markAllBtn) {
    markAllBtn.addEventListener('click', markAllAsRead);
  }
  
  // Notification bell in header
  const notificationBtn = document.getElementById('notificationBtn');
  if (notificationBtn) {
    // Already on notifications page, so disable navigation
    notificationBtn.style.cursor = 'default';
    notificationBtn.onclick = null;
  }
  
  // Modal close button
  const closeModalBtn = document.getElementById('closeModalBtn');
  if (closeModalBtn) {
    closeModalBtn.addEventListener('click', closeModal);
  }
  
  // Close modal on backdrop click
  const modal = document.getElementById('notificationModal');
  if (modal) {
    modal.addEventListener('click', (e) => {
      if (e.target === modal) {
        closeModal();
      }
    });
  }
  
  // Preferences modal handlers
  const settingsBtn = document.getElementById('settingsBtn');
  const preferencesModal = document.getElementById('preferencesModal');
  const closePreferencesBtn = document.getElementById('closePreferencesBtn');
  const savePreferencesBtn = document.getElementById('savePreferencesBtn');
  
  if (settingsBtn) {
    settingsBtn.addEventListener('click', openPreferencesModal);
  }
  
  if (closePreferencesBtn) {
    closePreferencesBtn.addEventListener('click', closePreferencesModal);
  }
  
  if (savePreferencesBtn) {
    savePreferencesBtn.addEventListener('click', savePreferences);
  }
  
  if (preferencesModal) {
    preferencesModal.addEventListener('click', (e) => {
      if (e.target === preferencesModal) {
        closePreferencesModal();
      }
    });
  }
  
  // Load saved preferences
  loadPreferences();
  
  console.log('Event listeners setup complete');
}

// ============================================
// DATA LOADING
// ============================================

async function loadNotifications(silent = false) {
  try {
    if (!silent) {
      showLoadingState();
    }
    
    console.log('Loading notifications...');
    
    const response = await ApiClient.request('/notifications', {
      method: 'GET'
    });
    
    console.log('Notifications API response:', response);
    
    if (response.success && response.data) {
      allNotifications = response.data.notifications || [];
      console.log(`Loaded ${allNotifications.length} notifications`);
      
      renderNotifications();
      updateUnreadCount();
    } else {
      throw new Error(response.message || 'Failed to load notifications');
    }
    
  } catch (error) {
    console.error('Error loading notifications:', error);
    
    if (!silent) {
      showErrorState(error.message || 'Failed to load notifications');
    }
  }
}

// ============================================
// RENDERING
// ============================================

function renderNotifications() {
  const container = document.getElementById('notificationsList');
  if (!container) return;
  
  // Filter notifications based on active filter
  let filteredNotifications = [...allNotifications];
  
  if (activeFilter === 'Unread') {
    filteredNotifications = filteredNotifications.filter(n => !n.is_read);
  } else if (activeFilter === 'Urgent') {
    filteredNotifications = filteredNotifications.filter(n => n.type === 'urgent');
  } else if (activeFilter === 'Billing') {
    filteredNotifications = filteredNotifications.filter(n => n.type === 'billing');
  }
  
  console.log(`Rendering ${filteredNotifications.length} notifications (filter: ${activeFilter})`);
  
  // Show empty state if no notifications
  if (filteredNotifications.length === 0) {
    showEmptyState();
    return;
  }
  
  // Sort by created_at (newest first)
  filteredNotifications.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
  
  // Render each notification
  container.innerHTML = filteredNotifications.map(notification => 
    renderNotificationCard(notification)
  ).join('');
  
  // Reinitialize Lucide icons for the newly rendered content
  lucide.createIcons();
  
  // Add event listeners to action buttons and delete buttons
  attachNotificationEventListeners();
}

function renderNotificationCard(notification) {
  const isUnread = !notification.is_read;
  const icon = categoryIcons[notification.category] || notificationIcons[notification.type] || 'bell';
  const colorClass = iconColors[notification.category] || iconColors[notification.type] || 'text-slate-600 bg-slate-100';
  const timeAgo = getTimeAgo(notification.created_at);
  
  return `
    <div 
      class="notification-card group bg-white rounded-lg p-4 transition-all hover:shadow-md border ${isUnread ? 'border-l-4 border-l-blue-500' : 'border-slate-200'} relative"
      data-notification-id="${notification.id}"
      data-is-read="${notification.is_read ? 'true' : 'false'}"
    >
      <!-- Unread indicator dot -->
      ${isUnread ? '<span class="absolute top-2 right-2 block h-2 w-2 rounded-full bg-blue-500"></span>' : ''}
      
      <!-- Delete button (appears on hover) -->
      <button 
        class="delete-btn absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity bg-white hover:bg-red-50 text-slate-400 hover:text-red-600 p-1.5 rounded-full shadow-sm"
        data-notification-id="${notification.id}"
        title="Delete notification"
      >
        <i data-lucide="x" class="w-4 h-4"></i>
      </button>
      
      <div class="flex gap-3">
        <!-- Icon -->
        <div class="flex-shrink-0">
          <div class="w-10 h-10 rounded-full ${colorClass} flex items-center justify-center">
            <i data-lucide="${icon}" class="w-5 h-5"></i>
          </div>
        </div>
        
        <!-- Content -->
        <div class="flex-1 min-w-0">
          <div class="flex items-start justify-between gap-2 mb-1">
            <h3 class="text-sm font-semibold text-slate-900 ${isUnread ? 'font-bold' : ''}">${escapeHtml(notification.title)}</h3>
            <span class="text-xs text-slate-500 whitespace-nowrap">${timeAgo}</span>
          </div>
          
          <p class="text-sm text-slate-600 mb-3 cursor-pointer hover:text-slate-900 transition-colors" data-message-click="${notification.id}">${escapeHtml(notification.message)}</p>
          
          ${notification.action_label ? `
            <button 
              class="action-btn text-xs font-medium text-blue-600 hover:text-blue-700 hover:bg-blue-50 px-3 py-1.5 rounded-md transition-colors inline-flex items-center gap-1"
              data-notification-id="${notification.id}"
              data-action-url="${notification.action_url || '#'}"
            >
              ${escapeHtml(notification.action_label)}
              <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
            </button>
          ` : ''}
        </div>
      </div>
    </div>
  `;
}

// ============================================
// EVENT HANDLERS
// ============================================

function attachNotificationEventListeners() {
  // Mark as read on card click (except when clicking action/delete buttons)
  document.querySelectorAll('.notification-card').forEach(card => {
    card.addEventListener('click', async (e) => {
      // Don't mark as read if clicking on action or delete button
      if (e.target.closest('.action-btn') || e.target.closest('.delete-btn')) {
        return;
      }
      
      const notificationId = card.dataset.notificationId;
      const isRead = card.dataset.isRead === 'true';
      
      if (!isRead) {
        await markAsRead(notificationId);
      }
    });
  });
  
  // Action buttons
  document.querySelectorAll('.action-btn').forEach(btn => {
    btn.addEventListener('click', async (e) => {
      e.stopPropagation();
      const notificationId = btn.dataset.notificationId;
      const actionUrl = btn.dataset.actionUrl;
      
      // Mark as read before navigating
      await markAsRead(notificationId);
      
      // Navigate to action URL
      if (actionUrl && actionUrl !== '#') {
        window.location.href = actionUrl;
      }
    });
  });
  
  // Delete buttons
  document.querySelectorAll('.delete-btn').forEach(btn => {
    btn.addEventListener('click', async (e) => {
      e.stopPropagation();
      const notificationId = btn.dataset.notificationId;
      await deleteNotification(notificationId);
    });
  });
  
  // Message click handlers
  document.querySelectorAll('[data-message-click]').forEach(element => {
    element.addEventListener('click', (e) => {
      e.stopPropagation();
      const notificationId = element.dataset.messageClick;
      const notification = allNotifications.find(n => n.id === notificationId);
      if (notification) {
        openModal(notification);
      }
    });
  });
}

// ============================================
// FILTER MANAGEMENT
// ============================================

function setActiveFilter(filter) {
  activeFilter = filter;
  
  // Update button styles
  document.querySelectorAll('.filter-btn').forEach(btn => {
    if (btn.dataset.filter === filter) {
      btn.className = 'filter-btn whitespace-nowrap px-4 py-2 rounded-full text-sm font-medium transition-colors border bg-slate-800 text-white border-slate-800';
    } else {
      btn.className = 'filter-btn whitespace-nowrap px-4 py-2 rounded-full text-sm font-medium transition-colors border bg-white text-slate-600 border-slate-200 hover:bg-slate-50';
    }
  });
  
  // Re-render with new filter
  renderNotifications();
  
  console.log(`Filter changed to: ${filter}`);
}

// ============================================
// NOTIFICATION ACTIONS
// ============================================

async function markAsRead(notificationId) {
  try {
    console.log(`Marking notification ${notificationId} as read...`);
    
    // Optimistic update
    const notification = allNotifications.find(n => n.id === notificationId);
    if (notification) {
      notification.is_read = true;
    }
    renderNotifications();
    updateUnreadCount();
    
    const response = await ApiClient.request(`/notifications/${notificationId}/read`, {
      method: 'PUT'
    });
    
    if (!response.success) {
      // Revert on failure
      if (notification) {
        notification.is_read = false;
      }
      renderNotifications();
      updateUnreadCount();
      throw new Error(response.message || 'Failed to mark as read');
    }
    
    console.log(`Notification ${notificationId} marked as read`);
    
  } catch (error) {
    console.error('Error marking notification as read:', error);
  }
}

async function markAllAsRead() {
  try {
    console.log('Marking all notifications as read...');
    
    // Optimistic update
    const previousState = allNotifications.map(n => ({ ...n }));
    allNotifications.forEach(n => n.is_read = true);
    renderNotifications();
    updateUnreadCount();
    
    const response = await ApiClient.request('/notifications/mark-all-read', {
      method: 'POST'
    });
    
    if (!response.success) {
      // Revert on failure
      allNotifications = previousState;
      renderNotifications();
      updateUnreadCount();
      throw new Error(response.message || 'Failed to mark all as read');
    }
    
    console.log('All notifications marked as read');
    
  } catch (error) {
    console.error('Error marking all as read:', error);
    alert('Failed to mark all notifications as read. Please try again.');
  }
}

async function deleteNotification(notificationId) {
  try {
    console.log(`Deleting notification ${notificationId}...`);
    
    // Optimistic update
    const previousNotifications = [...allNotifications];
    allNotifications = allNotifications.filter(n => n.id !== notificationId);
    renderNotifications();
    updateUnreadCount();
    
    const response = await ApiClient.request(`/notifications/${notificationId}`, {
      method: 'DELETE'
    });
    
    if (!response.success) {
      // Revert on failure
      allNotifications = previousNotifications;
      renderNotifications();
      updateUnreadCount();
      throw new Error(response.message || 'Failed to delete notification');
    }
    
    console.log(`Notification ${notificationId} deleted`);
    
  } catch (error) {
    console.error('Error deleting notification:', error);
    alert('Failed to delete notification. Please try again.');
  }
}

// ============================================
// UI STATE MANAGEMENT
// ============================================

function showLoadingState() {
  const container = document.getElementById('notificationsList');
  if (!container) return;
  
  container.innerHTML = `
    <div class="text-center py-16">
      <div style="width: 48px; height: 48px; margin: 0 auto 16px; border: 3px solid #e2e8f0; border-top-color: #3b82f6; border-radius: 50%; animation: spin 1s linear infinite;"></div>
      <p style="font-size: 16px; font-weight: 500; color: #64748b;">Loading notifications...</p>
    </div>
  `;
}

function showEmptyState() {
  const container = document.getElementById('notificationsList');
  if (!container) return;
  
  let message = 'No notifications yet';
  let description = 'You\'re all caught up! Check back later for updates.';
  
  if (activeFilter === 'Unread') {
    message = 'No unread notifications';
    description = 'You\'ve read all your notifications.';
  } else if (activeFilter === 'Urgent') {
    message = 'No urgent notifications';
    description = 'You have no urgent items requiring immediate attention.';
  } else if (activeFilter === 'Billing') {
    message = 'No billing notifications';
    description = 'No billing-related notifications at this time.';
  }
  
  container.innerHTML = `
    <div class="text-center py-16">
      <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-slate-100 flex items-center justify-center">
        <i data-lucide="bell-off" class="w-8 h-8 text-slate-400"></i>
      </div>
      <h3 class="text-lg font-semibold text-slate-900 mb-2">${message}</h3>
      <p class="text-sm text-slate-500">${description}</p>
    </div>
  `;
  
  lucide.createIcons();
}

function showErrorState(message) {
  const container = document.getElementById('notificationsList');
  if (!container) return;
  
  container.innerHTML = `
    <div class="text-center py-16">
      <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-red-100 flex items-center justify-center">
        <i data-lucide="alert-circle" class="w-8 h-8 text-red-600"></i>
      </div>
      <h3 class="text-lg font-semibold text-slate-900 mb-2">Failed to load notifications</h3>
      <p class="text-sm text-slate-500 mb-4">${escapeHtml(message)}</p>
      <button 
        onclick="loadNotifications()" 
        class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors"
      >
        Try again
      </button>
    </div>
  `;
  
  lucide.createIcons();
}

function updateUnreadCount() {
  const unreadCount = allNotifications.filter(n => !n.is_read).length;
  
  // Update badge in sub-header
  const badge = document.getElementById('newCountBadge');
  if (badge) {
    if (unreadCount > 0) {
      badge.textContent = unreadCount;
      badge.classList.remove('hidden');
    } else {
      badge.classList.add('hidden');
    }
  }
  
  // Update notification dot in header
  const dot = document.getElementById('notificationDot');
  if (dot) {
    if (unreadCount > 0) {
      dot.classList.remove('hidden');
    } else {
      dot.classList.add('hidden');
    }
  }
  
  console.log(`Unread count: ${unreadCount}`);
}

// ============================================
// AUTO-REFRESH
// ============================================

function startAutoRefresh() {
  // Clear any existing interval
  if (autoRefreshInterval) {
    clearInterval(autoRefreshInterval);
  }
  
  // Refresh every 30 seconds
  autoRefreshInterval = setInterval(() => {
    console.log('Auto-refreshing notifications...');
    loadNotifications(true); // Silent reload
  }, 30000);
  
  console.log('Auto-refresh started (30 seconds interval)');
}

// Stop auto-refresh when page is hidden (browser optimization)
document.addEventListener('visibilitychange', () => {
  if (document.hidden) {
    if (autoRefreshInterval) {
      clearInterval(autoRefreshInterval);
      console.log('Auto-refresh paused (page hidden)');
    }
  } else {
    startAutoRefresh();
    loadNotifications(true);
    console.log('Auto-refresh resumed (page visible)');
  }
});

// ============================================
// MODAL FUNCTIONS
// ============================================

function openModal(notification) {
  currentModalNotification = notification;
  
  const modal = document.getElementById('notificationModal');
  const modalIcon = document.getElementById('modalIcon');
  const modalTitle = document.getElementById('modalTitle');
  const modalMessage = document.getElementById('modalMessage');
  const modalTime = document.getElementById('modalTime');
  const modalFooter = document.getElementById('modalFooter');
  
  if (!modal) return;
  
  // Set icon and color
  const icon = categoryIcons[notification.category] || notificationIcons[notification.type] || 'bell';
  const colorClass = iconColors[notification.category] || iconColors[notification.type] || 'text-slate-600 bg-slate-100';
  
  modalIcon.className = `w-10 h-10 rounded-full flex items-center justify-center ${colorClass}`;
  modalIcon.innerHTML = `<i data-lucide="${icon}" class="w-5 h-5"></i>`;
  
  // Set content
  modalTitle.textContent = notification.title;
  modalMessage.textContent = notification.message;
  modalTime.textContent = getTimeAgo(notification.created_at);
  
  // Clear and set footer action button
  modalFooter.innerHTML = '';
  
  if (notification.action_label && notification.action_url) {
    const actionBtn = document.createElement('button');
    actionBtn.className = 'px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors flex items-center gap-2';
    actionBtn.innerHTML = `
      ${escapeHtml(notification.action_label)}
      <i data-lucide="arrow-right" class="w-4 h-4"></i>
    `;
    actionBtn.addEventListener('click', async () => {
      if (!notification.is_read) {
        await markAsRead(notification.id);
      }
      window.location.href = notification.action_url;
    });
    modalFooter.appendChild(actionBtn);
  }
  
  const closeBtn = document.createElement('button');
  closeBtn.className = 'px-4 py-2 text-slate-600 text-sm font-medium rounded-lg hover:bg-slate-100 transition-colors';
  closeBtn.textContent = 'Close';
  closeBtn.addEventListener('click', closeModal);
  modalFooter.appendChild(closeBtn);
  
  // Show modal
  modal.classList.remove('hidden');
  
  // Reinitialize icons
  lucide.createIcons();
  
  // Mark as read if unread
  if (!notification.is_read) {
    markAsRead(notification.id);
  }
}

function closeModal() {
  const modal = document.getElementById('notificationModal');
  if (modal) {
    modal.classList.add('hidden');
  }
  currentModalNotification = null;
}

// Close modal on Escape key
document.addEventListener('keydown', (e) => {
  if (e.key === 'Escape') {
    closeModal();
    closePreferencesModal();
  }
});

// ============================================
// PREFERENCES MODAL FUNCTIONS
// ============================================

function openPreferencesModal() {
  const modal = document.getElementById('preferencesModal');
  if (modal) {
    modal.classList.remove('hidden');
    lucide.createIcons();
  }
}

function closePreferencesModal() {
  const modal = document.getElementById('preferencesModal');
  if (modal) {
    modal.classList.add('hidden');
  }
}

async function loadPreferences() {
  try {
    // Try to load from API first
    const response = await ApiClient.get('/settings');
    
    if (response.success && response.data && response.data.notification_preferences) {
      const preferences = response.data.notification_preferences;
      
      // Set toggle states
      const urgentToggle = document.getElementById('urgentToggle');
      const billingToggle = document.getElementById('billingToggle');
      const serviceToggle = document.getElementById('serviceToggle');
      const systemToggle = document.getElementById('systemToggle');
      
      if (urgentToggle) urgentToggle.checked = preferences.urgent !== false;
      if (billingToggle) billingToggle.checked = preferences.billing !== false;
      if (serviceToggle) serviceToggle.checked = preferences.service !== false;
      if (systemToggle) systemToggle.checked = preferences.system !== false;
      
      console.log('Loaded preferences from API:', preferences);
    }
  } catch (error) {
    console.error('Error loading preferences:', error);
    
    // Fallback to localStorage
    const preferences = JSON.parse(localStorage.getItem('notificationPreferences') || '{}');
    
    const urgentToggle = document.getElementById('urgentToggle');
    const billingToggle = document.getElementById('billingToggle');
    const serviceToggle = document.getElementById('serviceToggle');
    const systemToggle = document.getElementById('systemToggle');
    
    if (urgentToggle) urgentToggle.checked = preferences.urgent !== false;
    if (billingToggle) billingToggle.checked = preferences.billing !== false;
    if (serviceToggle) serviceToggle.checked = preferences.service !== false;
    if (systemToggle) systemToggle.checked = preferences.system || false;
  }
}

async function savePreferences() {
  const preferences = {
    urgent: document.getElementById('urgentToggle')?.checked || false,
    billing: document.getElementById('billingToggle')?.checked || false,
    service: document.getElementById('serviceToggle')?.checked || false,
    system: document.getElementById('systemToggle')?.checked || false
  };
  
  const saveBtn = document.getElementById('savePreferencesBtn');
  
  try {
    // Show loading state
    if (saveBtn) {
      const originalText = saveBtn.textContent;
      saveBtn.textContent = 'Saving...';
      saveBtn.disabled = true;
    }
    
    // Save to API
    const response = await ApiClient.put('/settings', {
      notification_preferences: preferences
    });
    
    if (response.success) {
      // Also save to localStorage as backup
      localStorage.setItem('notificationPreferences', JSON.stringify(preferences));
      
      // Show success message
      if (saveBtn) {
        saveBtn.textContent = '✓ Preferences Saved!';
        saveBtn.classList.add('bg-green-600', 'hover:bg-green-700');
        saveBtn.classList.remove('bg-blue-600', 'hover:bg-blue-700');
        
        setTimeout(() => {
          saveBtn.textContent = 'Save Preferences';
          saveBtn.classList.remove('bg-green-600', 'hover:bg-green-700');
          saveBtn.classList.add('bg-blue-600', 'hover:bg-blue-700');
          saveBtn.disabled = false;
          closePreferencesModal();
        }, 1500);
      }
      
      console.log('Notification preferences saved to database:', preferences);
    } else {
      throw new Error(response.message || 'Failed to save preferences');
    }
  } catch (error) {
    console.error('Error saving preferences:', error);
    
    // Show error message
    if (saveBtn) {
      saveBtn.textContent = '✗ Failed to save';
      saveBtn.classList.add('bg-red-600', 'hover:bg-red-700');
      saveBtn.classList.remove('bg-blue-600', 'hover:bg-blue-700');
      
      setTimeout(() => {
        saveBtn.textContent = 'Save Preferences';
        saveBtn.classList.remove('bg-red-600', 'hover:bg-red-700');
        saveBtn.classList.add('bg-blue-600', 'hover:bg-blue-700');
        saveBtn.disabled = false;
      }, 2000);
    }
    
    alert('Failed to save preferences. Please try again.');
  }
}

// ============================================
// UTILITY FUNCTIONS
// ============================================

function getTimeAgo(dateString) {
  const now = new Date();
  const date = new Date(dateString);
  const seconds = Math.floor((now - date) / 1000);
  
  if (seconds < 60) return 'Just now';
  
  const minutes = Math.floor(seconds / 60);
  if (minutes < 60) return `${minutes}m ago`;
  
  const hours = Math.floor(minutes / 60);
  if (hours < 24) return `${hours}h ago`;
  
  const days = Math.floor(hours / 24);
  if (days < 7) return `${days}d ago`;
  
  const weeks = Math.floor(days / 7);
  if (weeks < 4) return `${weeks}w ago`;
  
  const months = Math.floor(days / 30);
  if (months < 12) return `${months}mo ago`;
  
  const years = Math.floor(days / 365);
  return `${years}y ago`;
}

function escapeHtml(text) {
  const map = {
    '&': '&amp;',
    '<': '&lt;',
    '>': '&gt;',
    '"': '&quot;',
    "'": '&#039;'
  };
  return text.replace(/[&<>"']/g, m => map[m]);
}

// Add CSS animation for spinner
const style = document.createElement('style');
style.textContent = `
  @keyframes spin {
    to { transform: rotate(360deg); }
  }
  .no-scrollbar::-webkit-scrollbar {
    display: none;
  }
  .no-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
  }
`;
document.head.appendChild(style);
