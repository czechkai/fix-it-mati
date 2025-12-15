/**
 * Admin Technicians Management JavaScript
 * Handles all technician team operations with real-time database updates
 */

// State management
const state = {
  teams: [],
  filteredTeams: [],
  currentFilter: 'All',
  selectedTeam: null,
  searchQuery: ''
};

// DOM Elements
const elements = {
  teamsGrid: document.getElementById('teamsGrid'),
  loadingIndicator: document.getElementById('loadingIndicator'),
  teamDrawer: document.getElementById('teamDrawer'),
  drawerBackdrop: document.getElementById('drawerBackdrop'),
  drawerContent: document.getElementById('drawerContent'),
  drawerBody: document.getElementById('drawerBody'),
  closeDrawerBtn: document.getElementById('closeDrawerBtn'),
  addTeamBtn: document.getElementById('addTeamBtn'),
  addTeamModal: document.getElementById('addTeamModal'),
  addTeamForm: document.getElementById('addTeamForm'),
  modalBackdrop: document.getElementById('modalBackdrop'),
  closeModalBtn: document.getElementById('closeModalBtn'),
  cancelModalBtn: document.getElementById('cancelModalBtn'),
  searchInput: document.getElementById('searchInput'),
  logoutBtn: document.getElementById('logoutBtn'),
  filterBtns: document.querySelectorAll('.filter-btn'),
  statTotalTeams: document.getElementById('statTotalTeams'),
  statAvailable: document.getElementById('statAvailable'),
  statBusy: document.getElementById('statBusy'),
  statOnRoute: document.getElementById('statOnRoute')
};

// Initialize
document.addEventListener('DOMContentLoaded', () => {
  initializeEventListeners();
  loadTeams();
  
  // Auto-refresh every 30 seconds
  setInterval(loadTeams, 30000);
});

// Event Listeners
function initializeEventListeners() {
  // Filter buttons
  elements.filterBtns.forEach(btn => {
    btn.addEventListener('click', () => {
      state.currentFilter = btn.dataset.filter;
      updateFilterButtons();
      filterTeams();
    });
  });

  // Search
  elements.searchInput?.addEventListener('input', (e) => {
    state.searchQuery = e.target.value.toLowerCase();
    filterTeams();
  });

  // Drawer controls
  elements.closeDrawerBtn?.addEventListener('click', closeDrawer);
  elements.drawerBackdrop?.addEventListener('click', closeDrawer);

  // Modal controls
  elements.addTeamBtn?.addEventListener('click', openAddTeamModal);
  elements.closeModalBtn?.addEventListener('click', closeAddTeamModal);
  elements.cancelModalBtn?.addEventListener('click', closeAddTeamModal);
  elements.modalBackdrop?.addEventListener('click', closeAddTeamModal);
  elements.addTeamForm?.addEventListener('submit', handleAddTeam);

  // Logout
  elements.logoutBtn?.addEventListener('click', handleLogout);
}

// Loading indicator functions
function showLoading() {
  elements.loadingIndicator?.classList.remove('hidden');
  elements.teamsGrid?.classList.add('hidden');
}

function hideLoading() {
  elements.loadingIndicator?.classList.add('hidden');
  elements.teamsGrid?.classList.remove('hidden');
}

// API Functions
async function loadTeams() {
  try {
    showLoading();
    
    const token = localStorage.getItem('auth_token');
    
    if (!token) {
      console.error('No auth token found');
      showError('Authentication required');
      hideLoading();
      return;
    }

    const response = await fetch('/api/technicians/all', {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${token}`
      }
    });

    const data = await response.json();
    
    if (data.success) {
      state.teams = Array.isArray(data.data) ? data.data : [];
      filterTeams();
      updateStats();
    } else {
      console.error('API Error:', data.message);
      state.teams = [];
      showError(data.message || 'Failed to load teams');
      filterTeams();
      updateStats();
    }
  } catch (error) {
    console.error('Error loading teams:', error);
    state.teams = [];
    showError('Error loading teams');
    filterTeams();
    updateStats();
  } finally {
    hideLoading();
  }
}

async function addTeam(teamData) {
  try {
    const token = localStorage.getItem('auth_token');

    const response = await fetch('/api/technicians/create', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${token}`
      },
      body: JSON.stringify(teamData)
    });

    const data = await response.json();
    
    if (data.success) {
      showSuccess('Team registered successfully!');
      closeAddTeamModal();
      // Reload teams immediately to show the new team
      await loadTeams();
    } else {
      showError(data.message || 'Failed to register team');
    }
  } catch (error) {
    console.error('Error adding team:', error);
    showError('Error registering team');
  }
}

async function updateTeamStatus(teamId, status) {
  try {
    const token = localStorage.getItem('auth_token');

    const response = await fetch(`/api/technicians/${teamId}/status`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${token}`
      },
      body: JSON.stringify({ status })
    });

    const data = await response.json();
    
    if (data.success) {
      showSuccess('Team status updated');
      loadTeams();
    } else {
      showError(data.message || 'Failed to update status');
    }
  } catch (error) {
    console.error('Error updating status:', error);
    showError('Error updating team status');
  }
}

async function deactivateTeam(teamId) {
  if (!confirm('Are you sure you want to deactivate this team?')) {
    return;
  }

  try {
    const token = localStorage.getItem('auth_token');

    const response = await fetch(`/api/technicians/${teamId}`, {
      method: 'DELETE',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${token}`
      }
    });

    const data = await response.json();
    
    if (data.success) {
      showSuccess('Team deactivated successfully');
      closeDrawer();
      loadTeams();
    } else {
      showError(data.message || 'Failed to deactivate team');
    }
  } catch (error) {
    console.error('Error deactivating team:', error);
    showError('Error deactivating team');
  }
}

// UI Functions
function filterTeams() {
  // Ensure state.teams is an array
  if (!Array.isArray(state.teams)) {
    state.teams = [];
  }
  
  state.filteredTeams = state.teams.filter(team => {
    const matchesFilter = state.currentFilter === 'All' || team.department === state.currentFilter;
    const matchesSearch = !state.searchQuery || 
      team.name.toLowerCase().includes(state.searchQuery) ||
      team.lead.toLowerCase().includes(state.searchQuery) ||
      team.status.toLowerCase().includes(state.searchQuery);
    
    return matchesFilter && matchesSearch;
  });

  renderTeams();
}

function renderTeams() {
  if (!elements.teamsGrid) return;

  if (state.filteredTeams.length === 0) {
    elements.teamsGrid.innerHTML = `
      <div class="col-span-full flex flex-col items-center justify-center py-12">
        <i data-lucide="users" class="w-16 h-16 text-slate-300 mb-4"></i>
        <p class="text-slate-500 font-medium">No teams found</p>
        <p class="text-xs text-slate-400 mt-1">Try adjusting your filters</p>
      </div>
    `;
    lucide.createIcons();
    return;
  }

  elements.teamsGrid.innerHTML = state.filteredTeams.map(team => {
    const initial = team.name.charAt(5) || 'T';
    const statusBadge = getStatusBadge(team.status);
    const deptIcon = getDeptIcon(team.department);

    return `
      <div 
        class="team-card bg-white rounded-xl border transition-all duration-200 cursor-pointer group hover:shadow-md ${
          state.selectedTeam?.id === team.id ? 'border-blue-500 ring-1 ring-blue-500' : 'border-slate-200 hover:border-blue-300'
        }"
        data-team-id="${team.id}"
      >
        <div class="p-5">
          
          <!-- Card Header -->
          <div class="flex justify-between items-start mb-4">
            <div class="flex items-center gap-3">
              <div class="h-10 w-10 rounded-full bg-slate-100 flex items-center justify-center font-bold text-slate-600 text-sm border border-slate-200">
                ${initial}
              </div>
              <div>
                <h3 class="font-bold text-slate-800">${escapeHtml(team.name)}</h3>
                <div class="flex items-center gap-1 text-xs text-slate-500">
                  ${deptIcon} ${escapeHtml(team.department)} Dept.
                </div>
              </div>
            </div>
            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase border ${statusBadge.class}">
              ${statusBadge.label}
            </span>
          </div>

          <!-- Details -->
          <div class="space-y-3 mb-4">
            <div class="flex justify-between text-sm">
              <span class="text-slate-500 flex items-center gap-2">
                <i data-lucide="users" class="w-3.5 h-3.5"></i> Members
              </span>
              <span class="font-medium text-slate-800">${team.members} Staff</span>
            </div>
            <div class="flex justify-between text-sm">
              <span class="text-slate-500 flex items-center gap-2">
                <i data-lucide="user" class="w-3.5 h-3.5"></i> Team Lead
              </span>
              <span class="font-medium text-slate-800">${escapeHtml(team.lead)}</span>
            </div>
            <div class="flex justify-between text-sm">
              <span class="text-slate-500 flex items-center gap-2">
                <i data-lucide="map-pin" class="w-3.5 h-3.5"></i> Location
              </span>
              <span class="font-medium text-slate-800 truncate max-w-[140px] text-right">${escapeHtml(team.location || 'N/A')}</span>
            </div>
          </div>

          <!-- Current Task -->
          <div class="pt-4 border-t border-slate-100">
            <p class="text-xs font-bold text-slate-400 uppercase mb-1">Current Assignment</p>
            ${(team.status || '').toLowerCase() === 'available' ? `
              <p class="text-sm text-slate-400 italic">No active task</p>
            ` : `
              <div class="flex items-start gap-2 bg-slate-50 p-2 rounded text-sm text-slate-700">
                <i data-lucide="briefcase" class="w-3.5 h-3.5 mt-0.5 text-blue-500 shrink-0"></i>
                <span class="line-clamp-1">${escapeHtml(team.current_task || 'In Progress')}</span>
              </div>
            `}
          </div>

        </div>
      </div>
    `;
  }).join('');

  // Re-initialize Lucide icons
  lucide.createIcons();

  // Add click handlers to team cards
  document.querySelectorAll('.team-card').forEach(card => {
    card.addEventListener('click', () => {
      const teamId = card.dataset.teamId;
      const team = state.teams.find(t => t.id === teamId);
      if (team) {
        openDrawer(team);
      }
    });
  });
}

function openDrawer(team) {
  state.selectedTeam = team;
  const initial = team.name.charAt(5) || 'T';
  const statusBadge = getStatusBadge(team.status);
  const deptIcon = getDeptIcon(team.department);

  document.getElementById('drawerTeamInitial').textContent = initial;
  document.getElementById('drawerTeamName').textContent = team.name;
  document.getElementById('drawerTeamId').textContent = team.id;

  elements.drawerBody.innerHTML = `
    <!-- Status Section -->
    <div class="flex items-center justify-between p-4 bg-slate-50 rounded-xl border border-slate-100">
      <div>
        <p class="text-xs font-bold text-slate-500 uppercase">Current Status</p>
        <span class="inline-block mt-1 px-2.5 py-0.5 rounded text-xs font-bold border ${statusBadge.class}">
          ${statusBadge.label}
        </span>
      </div>
      <div>
        <p class="text-xs font-bold text-slate-500 uppercase text-right">Performance</p>
        <div class="flex items-center gap-1 mt-1 justify-end">
          <i data-lucide="star" class="w-3.5 h-3.5 text-yellow-500 fill-yellow-500"></i>
          <span class="font-bold text-slate-800">${team.rating || '4.5'}/5.0</span>
        </div>
      </div>
    </div>

    <!-- Team Info -->
    <div class="space-y-4">
      <h4 class="text-sm font-bold text-slate-800 border-b border-slate-100 pb-2">Team Information</h4>
      <div class="grid grid-cols-2 gap-4 text-sm">
        <div>
          <p class="text-slate-500 text-xs">Department</p>
          <p class="font-medium text-slate-800 flex items-center gap-1 mt-0.5">
            ${deptIcon} ${team.department === 'Electric' ? 'DORECO' : escapeHtml(team.department)}
          </p>
        </div>
        <div>
          <p class="text-slate-500 text-xs">Team Leader</p>
          <p class="font-medium text-slate-800 mt-0.5">${escapeHtml(team.lead)}</p>
        </div>
        <div>
          <p class="text-slate-500 text-xs">Contact Number</p>
          <p class="font-medium text-slate-800 mt-0.5 flex items-center gap-1">
            <i data-lucide="phone" class="w-3 h-3"></i> ${escapeHtml(team.contact_number || 'N/A')}
          </p>
        </div>
        <div>
          <p class="text-slate-500 text-xs">Total Resolved</p>
          <p class="font-medium text-slate-800 mt-0.5 flex items-center gap-1">
            <i data-lucide="check-circle-2" class="w-3 h-3 text-green-600"></i> ${team.tickets_resolved || 0} Tickets
          </p>
        </div>
      </div>
    </div>

    <!-- Current Task Details -->
    <div class="space-y-3">
      <h4 class="text-sm font-bold text-slate-800 border-b border-slate-100 pb-2">Current Assignment</h4>
      ${(team.status || '').toLowerCase() === 'available' ? `
        <div class="bg-green-50 p-4 rounded-lg text-center border border-green-100">
          <i data-lucide="check-circle-2" class="w-6 h-6 mx-auto text-green-600 mb-2"></i>
          <p class="text-green-800 font-bold text-sm">Team is Ready</p>
          <p class="text-green-600 text-xs mt-1">Assign a pending ticket to this team.</p>
          <button class="mt-3 bg-white text-green-700 border border-green-200 px-4 py-2 rounded-lg text-xs font-bold shadow-sm hover:bg-green-100 w-full">
            Assign Ticket
          </button>
        </div>
      ` : `
        <div class="bg-white border border-slate-200 p-4 rounded-lg shadow-sm">
          <div class="flex justify-between items-start mb-2">
            <span class="bg-slate-100 text-slate-600 font-mono text-[10px] px-1.5 py-0.5 rounded">${team.current_ticket || 'N/A'}</span>
            <span class="text-[10px] text-slate-400">${formatTime(team.updated_at)}</span>
          </div>
          <p class="font-bold text-slate-800 text-sm mb-1">${escapeHtml(team.current_task || 'In Progress')}</p>
          <div class="flex items-center gap-1 text-xs text-slate-500 mb-3">
            <i data-lucide="map-pin" class="w-3 h-3"></i> ${escapeHtml(team.location || 'N/A')}
          </div>
          <div class="flex gap-2">
            <button class="flex-1 bg-white border border-slate-200 text-slate-600 py-1.5 rounded text-xs font-bold hover:bg-slate-50">View Ticket</button>
            <button class="flex-1 bg-white border border-slate-200 text-slate-600 py-1.5 rounded text-xs font-bold hover:bg-slate-50">Contact Team</button>
          </div>
        </div>
      `}
    </div>

    <!-- Admin Actions -->
    <div class="pt-4 flex gap-3 mt-auto border-t border-slate-100">
      <button onclick="openEditTeamModal('${team.id}')" class="flex-1 bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 font-bold py-2.5 rounded-lg text-sm transition-colors">
        Edit Details
      </button>
      <button onclick="deactivateTeam('${team.id}')" class="flex-1 bg-white border border-red-200 text-red-600 hover:bg-red-50 font-bold py-2.5 rounded-lg text-sm transition-colors">
        Deactivate
      </button>
    </div>
  `;

  elements.teamDrawer.classList.remove('hidden');
  lucide.createIcons();
}

function closeDrawer() {
  elements.teamDrawer.classList.add('hidden');
  state.selectedTeam = null;
  renderTeams(); // Re-render to update selection state
}

function openAddTeamModal() {
  elements.addTeamModal.classList.remove('hidden');
  elements.addTeamForm.reset();
  lucide.createIcons();
}

function closeAddTeamModal() {
  elements.addTeamModal.classList.add('hidden');
}

async function handleAddTeam(e) {
  e.preventDefault();
  
  const teamData = {
    name: document.getElementById('teamName').value,
    department: document.getElementById('teamDepartment').value,
    lead: document.getElementById('teamLead').value,
    members: parseInt(document.getElementById('teamMembers').value),
    contact_number: document.getElementById('teamContact').value,
    status: 'Available'
  };

  await addTeam(teamData);
}

function updateFilterButtons() {
  elements.filterBtns.forEach(btn => {
    if (btn.dataset.filter === state.currentFilter) {
      btn.className = 'filter-btn px-4 py-1.5 rounded-lg text-sm font-medium transition-all bg-slate-800 text-white shadow-sm';
    } else {
      btn.className = 'filter-btn px-4 py-1.5 rounded-lg text-sm font-medium transition-all text-slate-500 hover:text-slate-900 hover:bg-slate-50';
    }
  });
}

function updateStats() {
  // Ensure state.teams is an array
  if (!Array.isArray(state.teams)) {
    state.teams = [];
  }
  
  const totalTeams = state.teams.length;
  const availableTeams = state.teams.filter(t => (t.status || '').toLowerCase() === 'available').length;
  const busyTeams = state.teams.filter(t => (t.status || '').toLowerCase() === 'busy').length;
  const onRouteTeams = state.teams.filter(t => (t.status || '').toLowerCase() === 'on_route').length;

  if (elements.statTotalTeams) elements.statTotalTeams.textContent = totalTeams;
  if (elements.statAvailable) elements.statAvailable.textContent = availableTeams;
  if (elements.statBusy) elements.statBusy.textContent = busyTeams;
  if (elements.statOnRoute) elements.statOnRoute.textContent = onRouteTeams;
}

// Helper Functions
function getStatusBadge(status) {
  const statusLower = (status || '').toLowerCase();
  switch(statusLower) {
    case 'available':
      return { label: 'Available', class: 'bg-green-100 text-green-700 border-green-200' };
    case 'busy':
      return { label: 'Busy', class: 'bg-red-100 text-red-700 border-red-200' };
    case 'on_route':
    case 'on route':
      return { label: 'On Route', class: 'bg-blue-100 text-blue-700 border-blue-200' };
    case 'off_duty':
    case 'off duty':
      return { label: 'Off Duty', class: 'bg-slate-100 text-slate-500 border-slate-200' };
    default:
      return { label: status, class: 'bg-slate-100 text-slate-700 border-slate-200' };
  }
}

function getDeptIcon(dept) {
  if (dept === 'Water') {
    return '<i data-lucide="droplets" class="w-4 h-4 text-blue-500 inline"></i>';
  } else if (dept === 'Electric') {
    return '<i data-lucide="zap" class="w-4 h-4 text-amber-500 inline"></i>';
  }
  return '<i data-lucide="briefcase" class="w-4 h-4 text-slate-500 inline"></i>';
}

function escapeHtml(text) {
  if (!text) return '';
  const map = {
    '&': '&amp;',
    '<': '&lt;',
    '>': '&gt;',
    '"': '&quot;',
    "'": '&#039;'
  };
  return text.replace(/[&<>"']/g, m => map[m]);
}

function formatTime(dateString) {
  if (!dateString) return 'N/A';
  const date = new Date(dateString);
  const now = new Date();
  const diffMs = now - date;
  const diffHours = Math.floor(diffMs / (1000 * 60 * 60));
  
  if (diffHours < 1) {
    const diffMins = Math.floor(diffMs / (1000 * 60));
    return `${diffMins} mins ago`;
  } else if (diffHours < 24) {
    return `${diffHours} hrs ago`;
  } else {
    const diffDays = Math.floor(diffHours / 24);
    return `${diffDays} days ago`;
  }
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
window.openEditTeamModal = openEditTeamModal;
window.deactivateTeam = deactivateTeam;

function openEditTeamModal(teamId) {
  // TODO: Implement edit modal
  showError('Edit functionality coming soon');
}
