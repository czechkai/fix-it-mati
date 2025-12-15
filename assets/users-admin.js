/**
 * Admin Users Management JavaScript
 * Handles all user management operations with real-time database updates
 */

// State management
const state = {
  users: [],
  filteredUsers: [],
  currentFilter: 'All',
  selectedUser: null,
  currentPage: 1,
  usersPerPage: 10,
  searchQuery: ''
};

// DOM Elements
const elements = {
  usersTableBody: document.getElementById('usersTableBody'),
  userDrawer: document.getElementById('userDrawer'),
  drawerBackdrop: document.getElementById('drawerBackdrop'),
  drawerContent: document.getElementById('drawerContent'),
  drawerBody: document.getElementById('drawerBody'),
  closeDrawerBtn: document.getElementById('closeDrawerBtn'),
  searchInput: document.getElementById('searchInput'),
  logoutBtn: document.getElementById('logoutBtn'),
  filterBtns: document.querySelectorAll('.filter-btn'),
  statTotalUsers: document.getElementById('statTotalUsers'),
  statPendingReview: document.getElementById('statPendingReview'),
  statVerifiedMonth: document.getElementById('statVerifiedMonth'),
  tableFooterText: document.getElementById('tableFooterText'),
  prevPageBtn: document.getElementById('prevPageBtn'),
  nextPageBtn: document.getElementById('nextPageBtn')
};

// Initialize
document.addEventListener('DOMContentLoaded', () => {
  initializeEventListeners();
  loadUsers();
  
  // Auto-refresh every 30 seconds
  setInterval(loadUsers, 30000);
});

// Event Listeners
function initializeEventListeners() {
  // Filter buttons
  elements.filterBtns.forEach(btn => {
    btn.addEventListener('click', () => {
      state.currentFilter = btn.dataset.filter;
      updateFilterButtons();
      filterUsers();
    });
  });

  // Search
  elements.searchInput?.addEventListener('input', (e) => {
    state.searchQuery = e.target.value.toLowerCase();
    filterUsers();
  });

  // Drawer controls
  elements.closeDrawerBtn?.addEventListener('click', closeDrawer);
  elements.drawerBackdrop?.addEventListener('click', closeDrawer);

  // Pagination
  elements.prevPageBtn?.addEventListener('click', () => changePage(-1));
  elements.nextPageBtn?.addEventListener('click', () => changePage(1));

  // User row clicks (event delegation)
  elements.usersTableBody?.addEventListener('click', (e) => {
    const row = e.target.closest('.user-row');
    if (row && !e.target.closest('.user-action-btn')) {
      const userId = row.dataset.userId;
      if (userId) {
        openDrawerForUser(userId);
      }
    }
  });

  // Logout
  elements.logoutBtn?.addEventListener('click', handleLogout);
}

// API Functions
async function loadUsers() {
  try {
    const token = localStorage.getItem('auth_token');
    
    if (!token) {
      console.error('No auth token found');
      showError('Not authenticated. Please login.');
      return;
    }
    
    const response = await fetch('/api/users/all', {
      method: 'GET',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json'
      }
    });

    const data = await response.json();
    
    console.log('API Response:', data);

    if (data.success) {
      state.users = data.data || [];
      console.log('Loaded users:', state.users.length);
      filterUsers();
      updateStats();
    } else {
      console.error('API Error:', data.message);
      showError(data.message || 'Failed to load users');
      // Still render empty state
      state.users = [];
      filterUsers();
    }
  } catch (error) {
    console.error('Error loading users:', error);
    showError('Error loading users. Please try again.');
    // Still render empty state
    state.users = [];
    filterUsers();
  }
}

async function verifyUser(userId) {
  if (!confirm('Are you sure you want to verify this user account?')) {
    return;
  }

  try {
    const token = localStorage.getItem('auth_token');
    const response = await fetch(`/api/users/${userId}/verify`, {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json'
      }
    });

    const data = await response.json();

    if (data.success) {
      showSuccess('User account verified successfully!');
      await loadUsers(); // Refresh the list
      
      // If drawer is open for this user, refresh it
      if (state.selectedUser?.id === userId) {
        const updatedUser = state.users.find(u => u.id === userId);
        if (updatedUser) {
          openDrawer(updatedUser);
        }
      }
    } else {
      showError(data.message || 'Failed to verify user');
    }
  } catch (error) {
    console.error('Error verifying user:', error);
    showError('Error verifying user. Please try again.');
  }
}

async function suspendUser(userId) {
  const reason = prompt('Enter suspension reason:');
  if (!reason) return;

  try {
    const token = localStorage.getItem('auth_token');
    const response = await fetch(`/api/users/${userId}/suspend`, {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({ reason })
    });

    const data = await response.json();

    if (data.success) {
      showSuccess('User account suspended.');
      await loadUsers();
      closeDrawer();
    } else {
      showError(data.message || 'Failed to suspend user');
    }
  } catch (error) {
    console.error('Error suspending user:', error);
    showError('Error suspending user. Please try again.');
  }
}

async function resetPassword(userId) {
  if (!confirm('This will generate a new temporary password and email it to the user. Continue?')) {
    return;
  }

  try {
    const token = localStorage.getItem('auth_token');
    const response = await fetch(`/api/users/${userId}/reset-password`, {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json'
      }
    });

    const data = await response.json();

    if (data.success) {
      showSuccess('Password reset email sent to user.');
    } else {
      showError(data.message || 'Failed to reset password');
    }
  } catch (error) {
    console.error('Error resetting password:', error);
    showError('Error resetting password. Please try again.');
  }
}

async function deleteUser(userId) {
  if (!confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
    return;
  }

  try {
    const token = localStorage.getItem('auth_token');
    const response = await fetch(`/api/users/${userId}`, {
      method: 'DELETE',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json'
      }
    });

    const data = await response.json();

    if (data.success) {
      showSuccess('User deleted successfully.');
      await loadUsers();
      closeDrawer();
    } else {
      showError(data.message || 'Failed to delete user');
    }
  } catch (error) {
    console.error('Error deleting user:', error);
    showError('Error deleting user. Please try again.');
  }
}

// UI Functions
function filterUsers() {
  let filtered = [...state.users];

  // Apply status filter
  if (state.currentFilter !== 'All') {
    filtered = filtered.filter(u => {
      if (state.currentFilter === 'pending') {
        return u.role === 'pending';
      }
      if (state.currentFilter === 'suspended') {
        return u.role === 'suspended';
      }
      return u.role === state.currentFilter;
    });
  }

  // Apply search filter
  if (state.searchQuery) {
    filtered = filtered.filter(u => {
      const name = (u.full_name || u.first_name + ' ' + u.last_name || '').toLowerCase();
      const email = (u.email || '').toLowerCase();
      const address = (u.address || '').toLowerCase();
      
      return name.includes(state.searchQuery) || 
             email.includes(state.searchQuery) || 
             address.includes(state.searchQuery);
    });
  }

  state.filteredUsers = filtered;
  state.currentPage = 1;
  renderUsers();
}

function renderUsers() {
  const startIndex = (state.currentPage - 1) * state.usersPerPage;
  const endIndex = startIndex + state.usersPerPage;
  const paginatedUsers = state.filteredUsers.slice(startIndex, endIndex);

  if (!elements.usersTableBody) {
    console.error('Table body element not found');
    return;
  }

  if (paginatedUsers.length === 0) {
    const message = state.users.length === 0 
      ? 'No users in database' 
      : 'No users match your filters';
    
    elements.usersTableBody.innerHTML = `
      <tr>
        <td colspan="6" class="px-6 py-12 text-center text-slate-500">
          <div class="flex flex-col items-center gap-2">
            <i data-lucide="users" class="w-12 h-12 text-slate-300"></i>
            <p class="font-medium">${message}</p>
            <p class="text-xs">Total users in database: ${state.users.length}</p>
          </div>
        </td>
      </tr>
    `;
    lucide.createIcons();
    
    // Update footer
    if (elements.tableFooterText) {
      elements.tableFooterText.textContent = `Showing 0 users`;
    }
    return;
  }

  elements.usersTableBody.innerHTML = paginatedUsers.map(user => {
    const name = user.full_name || `${user.first_name || ''} ${user.last_name || ''}`.trim() || 'Unnamed User';
    const initial = name.charAt(0).toUpperCase();
    const statusBadge = getStatusBadge(user);
    const metersCount = user.linked_meters || 0;

    return `
      <tr 
        class="hover:bg-slate-50 transition-colors group cursor-pointer user-row"
        data-user-id="${user.id}"
      >
        <td class="px-6 py-4">
          <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-full bg-slate-200 flex items-center justify-center font-bold text-slate-500 text-xs">
              ${initial}
            </div>
            <div>
              <div class="font-bold text-slate-800">${escapeHtml(name)}</div>
              <div class="text-[10px] text-slate-400 font-mono">${escapeHtml(user.account_number || user.id)}</div>
            </div>
          </div>
        </td>
        <td class="px-6 py-4">
          <div class="text-slate-600">${escapeHtml(user.email || '')}</div>
          <div class="text-xs text-slate-400">${escapeHtml(user.phone || 'N/A')}</div>
        </td>
        <td class="px-6 py-4 text-slate-600">${escapeHtml(user.address || 'N/A')}</td>
        <td class="px-6 py-4">
          <div class="flex gap-1">
            ${metersCount > 0 ? 
              `<span class="bg-blue-50 text-blue-600 px-2 py-0.5 rounded text-xs font-bold">${metersCount} Account${metersCount > 1 ? 's' : ''}</span>` :
              `<span class="text-xs text-slate-400">None</span>`
            }
          </div>
        </td>
        <td class="px-6 py-4">
          <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-bold border ${statusBadge.class}">${statusBadge.label}</span>
        </td>
        <td class="px-6 py-4 text-right">
          <button 
            class="text-slate-400 hover:text-blue-600 p-1 rounded hover:bg-blue-50 transition-colors user-action-btn"
            data-user-id="${user.id}"
          >
            <i data-lucide="more-vertical" class="w-4 h-4"></i>
          </button>
        </td>
      </tr>
    `;
  }).join('');

  // Re-initialize Lucide icons for new content
  lucide.createIcons();

  // Update footer text
  elements.tableFooterText.textContent = `Showing ${startIndex + 1}-${Math.min(endIndex, state.filteredUsers.length)} of ${state.filteredUsers.length} users`;

  // Update pagination buttons
  elements.prevPageBtn.disabled = state.currentPage === 1;
  elements.nextPageBtn.disabled = endIndex >= state.filteredUsers.length;

  // Reinitialize Lucide icons
  lucide.createIcons();
}

function getStatusBadge(user) {
  if (user.role === 'suspended') {
    return { label: 'Suspended', class: 'bg-red-100 text-red-700 border-red-200' };
  }
  if (user.role === 'pending') {
    return { label: 'Pending', class: 'bg-amber-100 text-amber-700 border-amber-200' };
  }
  if (user.role === 'customer') {
    return { label: 'Verified', class: 'bg-green-100 text-green-700 border-green-200' };
  }
  if (user.role === 'admin' || user.role === 'staff') {
    return { label: 'Admin', class: 'bg-blue-100 text-blue-700 border-blue-200' };
  }
  if (user.role === 'technician') {
    return { label: 'Technician', class: 'bg-purple-100 text-purple-700 border-purple-200' };
  }
  return { label: user.role || 'Active', class: 'bg-slate-100 text-slate-700 border-slate-200' };
}

function openDrawerForUser(userId) {
  console.log('Opening drawer for user:', userId);
  const user = state.users.find(u => u.id === userId);
  if (user) {
    console.log('User found:', user);
    openDrawer(user);
  } else {
    console.error('User not found:', userId);
  }
}

function openDrawer(user) {
  console.log('openDrawer called for:', user);
  state.selectedUser = user;
  const name = user.full_name || `${user.first_name || ''} ${user.last_name || ''}`.trim() || 'Unnamed User';
  const initial = name.charAt(0).toUpperCase();
  const statusBadge = getStatusBadge(user);

  document.getElementById('drawerAvatar').textContent = initial;
  document.getElementById('drawerUserName').textContent = name;
  document.getElementById('drawerUserId').textContent = user.account_number || user.id;

  elements.drawerBody.innerHTML = `
    <!-- Profile Summary -->
    <div class="grid grid-cols-2 gap-4">
      <div class="col-span-2 p-4 bg-slate-50 rounded-xl border border-slate-100 flex items-center justify-between">
        <div class="flex items-center gap-2">
          <i data-lucide="shield" class="w-4 h-4 text-slate-400"></i>
          <span class="text-sm font-medium text-slate-600">Account Status</span>
        </div>
        <span class="px-2 py-0.5 rounded text-xs font-bold border ${statusBadge.class}">${statusBadge.label}</span>
      </div>
      
      <div class="space-y-1">
        <label class="text-xs font-bold text-slate-400 uppercase flex items-center gap-1">
          <i data-lucide="mail" class="w-3 h-3"></i> Email
        </label>
        <p class="text-sm font-medium text-slate-800">${escapeHtml(user.email || 'N/A')}</p>
      </div>
      <div class="space-y-1">
        <label class="text-xs font-bold text-slate-400 uppercase flex items-center gap-1">
          <i data-lucide="phone" class="w-3 h-3"></i> Phone
        </label>
        <p class="text-sm font-medium text-slate-800">${escapeHtml(user.phone || 'N/A')}</p>
      </div>
      <div class="space-y-1 col-span-2">
        <label class="text-xs font-bold text-slate-400 uppercase flex items-center gap-1">
          <i data-lucide="map-pin" class="w-3 h-3"></i> Address
        </label>
        <p class="text-sm font-medium text-slate-800">${escapeHtml(user.address || 'N/A')}</p>
      </div>
      <div class="space-y-1">
        <label class="text-xs font-bold text-slate-400 uppercase">Account Number</label>
        <p class="text-sm font-medium text-slate-800 font-mono">${escapeHtml(user.account_number || 'Not assigned')}</p>
      </div>
      <div class="space-y-1">
        <label class="text-xs font-bold text-slate-400 uppercase">Joined</label>
        <p class="text-sm font-medium text-slate-800">${formatDate(user.created_at)}</p>
      </div>
    </div>

    <div class="border-t border-slate-100 pt-6">
      <h3 class="text-sm font-bold text-slate-800 mb-3 flex items-center gap-2">
        <i data-lucide="zap" class="w-4 h-4 text-amber-500"></i> Linked Utilities
      </h3>
      ${(user.linked_meters || 0) > 0 ? `
        <div class="space-y-2">
          <div class="p-3 border border-slate-200 rounded-lg flex justify-between items-center bg-white">
            <div class="flex items-center gap-3">
              <div class="bg-blue-100 p-1.5 rounded text-blue-600">
                <i data-lucide="droplets" class="w-4 h-4"></i>
              </div>
              <div>
                <p class="text-xs font-bold text-slate-700">Water Service</p>
                <p class="text-[10px] text-slate-500">Active utility account</p>
              </div>
            </div>
            <span class="text-[10px] bg-green-50 text-green-600 px-2 py-0.5 rounded font-bold">Active</span>
          </div>
        </div>
      ` : `
        <div class="text-sm text-slate-500 italic bg-slate-50 p-3 rounded text-center">No meters linked yet.</div>
      `}
    </div>

    <div class="border-t border-slate-100 pt-6">
      <h3 class="text-sm font-bold text-slate-800 mb-3 flex items-center gap-2">
        <i data-lucide="history" class="w-4 h-4 text-blue-600"></i> Account Information
      </h3>
      <div class="space-y-2 text-sm">
        <div class="flex justify-between py-2 border-b border-slate-100">
          <span class="text-slate-500">Total Service Requests</span>
          <span class="font-bold text-slate-800">${user.total_requests || 0}</span>
        </div>
        <div class="flex justify-between py-2 border-b border-slate-100">
          <span class="text-slate-500">Open Tickets</span>
          <span class="font-bold text-slate-800">${user.open_tickets || 0}</span>
        </div>
        <div class="flex justify-between py-2">
          <span class="text-slate-500">Last Activity</span>
          <span class="font-bold text-slate-800">${formatDate(user.updated_at || user.created_at)}</span>
        </div>
      </div>
    </div>

    <!-- Actions -->
    <div class="pt-6 flex gap-3 mt-auto">
      ${statusBadge.label === 'Pending' ? `
        <button 
          onclick="verifyUser('${user.id}')"
          class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-2.5 rounded-lg text-sm transition-colors shadow-sm flex items-center justify-center gap-2"
        >
          <i data-lucide="check-circle-2" class="w-4 h-4"></i> Verify Account
        </button>
      ` : `
        <button 
          onclick="resetPassword('${user.id}')"
          class="flex-1 bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 font-bold py-2.5 rounded-lg text-sm transition-colors"
        >
          Reset Password
        </button>
        ${statusBadge.label !== 'Suspended' ? `
          <button 
            onclick="suspendUser('${user.id}')"
            class="flex-1 bg-white border border-red-200 text-red-600 hover:bg-red-50 font-bold py-2.5 rounded-lg text-sm transition-colors"
          >
            Suspend
          </button>
        ` : `
          <button 
            onclick="deleteUser('${user.id}')"
            class="flex-1 bg-red-600 hover:bg-red-700 text-white font-bold py-2.5 rounded-lg text-sm transition-colors"
          >
            <i data-lucide="trash-2" class="w-4 h-4 inline"></i> Delete
          </button>
        `}
      `}
    </div>
  `;

  console.log('Showing drawer...');
  elements.userDrawer.classList.remove('hidden');
  lucide.createIcons();
  console.log('Drawer opened, classList:', elements.userDrawer.classList.toString());
}

function closeDrawer() {
  elements.userDrawer.classList.add('hidden');
  state.selectedUser = null;
}

function openAddUserModal() {
  elements.addUserModal.classList.remove('hidden');
  lucide.createIcons();
}

function closeAddUserModal() {
  elements.addUserModal.classList.add('hidden');
  elements.addUserForm.reset();
}

function updateFilterButtons() {
  elements.filterBtns.forEach(btn => {
    if (btn.dataset.filter === state.currentFilter) {
      btn.className = 'filter-btn px-3 py-1.5 rounded-lg text-xs font-medium transition-colors border bg-slate-800 text-white border-slate-800';
    } else {
      btn.className = 'filter-btn px-3 py-1.5 rounded-lg text-xs font-medium transition-colors border bg-white text-slate-600 border-slate-200 hover:bg-slate-50';
    }
  });
}

function updateStats() {
  const totalUsers = state.users.length;
  const pendingUsers = state.users.filter(u => u.role === 'pending').length;
  
  // Calculate verified this month
  const now = new Date();
  const thisMonth = new Date(now.getFullYear(), now.getMonth(), 1);
  const verifiedThisMonth = state.users.filter(u => {
    const createdDate = new Date(u.created_at);
    return createdDate >= thisMonth && u.role === 'customer';
  }).length;

  elements.statTotalUsers.textContent = totalUsers.toLocaleString();
  elements.statPendingReview.textContent = pendingUsers.toLocaleString();
  elements.statVerifiedMonth.textContent = `+${verifiedThisMonth}`;
}

function changePage(direction) {
  const maxPage = Math.ceil(state.filteredUsers.length / state.usersPerPage);
  state.currentPage = Math.max(1, Math.min(state.currentPage + direction, maxPage));
  renderUsers();
}

function formatDate(dateString) {
  if (!dateString) return 'N/A';
  const date = new Date(dateString);
  const options = { year: 'numeric', month: 'short', day: 'numeric' };
  return date.toLocaleDateString('en-US', options);
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

function showSuccess(message) {
  showToast(message, 'success');
}

function showError(message) {
  showToast(message, 'error');
}

function showToast(message, type = 'info') {
  const toast = document.createElement('div');
  const bgColor = type === 'success' ? 'bg-green-500' : type === 'error' ? 'bg-red-500' : 'bg-blue-500';
  
  toast.className = `${bgColor} text-white px-6 py-3 rounded-lg shadow-lg flex items-center gap-3 animate-in`;
  toast.innerHTML = `
    <i data-lucide="${type === 'success' ? 'check-circle-2' : 'alert-circle'}" class="w-5 h-5"></i>
    <span class="font-medium">${message}</span>
  `;
  
  document.getElementById('toastContainer').appendChild(toast);
  lucide.createIcons();
  
  setTimeout(() => {
    toast.style.opacity = '0';
    toast.style.transform = 'translateX(100%)';
    toast.style.transition = 'all 0.3s ease-out';
    setTimeout(() => toast.remove(), 300);
  }, 3000);
}

function handleLogout() {
  if (confirm('Are you sure you want to logout?')) {
    localStorage.removeItem('auth_token');
    localStorage.removeItem('user');
    window.location.href = '/login.php';
  }
}

// Make functions globally accessible for onclick handlers
window.openDrawerForUser = openDrawerForUser;
window.verifyUser = verifyUser;
window.suspendUser = suspendUser;
window.resetPassword = resetPassword;
window.deleteUser = deleteUser;
