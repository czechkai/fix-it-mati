/**
 * Service Requests Admin Page - Real-time Database Integration
 */

// Global state
let allTickets = [];
let filteredTickets = [];
let currentFilter = 'All';
let selectedTicket = null;
let technicians = [];
let currentUser = null;

// Initialize on page load
document.addEventListener('DOMContentLoaded', async () => {
  // Check authentication
  const token = localStorage.getItem('auth_token');
  const userData = localStorage.getItem('user');
  
  if (!token) {
    window.location.replace('/login.php');
    return;
  }
  
  if (userData) {
    try {
      const user = JSON.parse(userData);
      if (user.role !== 'admin' && user.role !== 'staff') {
        alert('Access denied. Admin privileges required.');
        window.location.replace('/user-dashboard.php');
        return;
      }
    } catch (e) {
      window.location.replace('/login.php');
      return;
    }
  }
  
  await loadCurrentUser();
  await loadTechnicians();
  await loadTickets();
  setupEventListeners();
  
  // Auto-refresh every 30 seconds
  setInterval(() => {
    loadTickets();
  }, 30000);
  
  // Initialize Lucide icons
  if (typeof lucide !== 'undefined') {
    lucide.createIcons();
  }
});

// Load current user
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

// Update user profile display
function updateUserProfile() {
  if (!currentUser) return;
  
  const adminName = document.getElementById('adminName');
  const adminRole = document.getElementById('adminRole');
  const adminAvatar = document.getElementById('adminAvatar');
  
  if (adminName && currentUser.first_name && currentUser.last_name) {
    adminName.textContent = `${currentUser.first_name} ${currentUser.last_name}`;
  }
  
  if (adminRole) {
    const roleText = currentUser.role === 'admin' ? 'System Administrator' : 'Staff Member';
    adminRole.textContent = roleText;
  }
  
  if (adminAvatar && currentUser.first_name && currentUser.last_name) {
    const initials = (currentUser.first_name.charAt(0) + currentUser.last_name.charAt(0)).toUpperCase();
    adminAvatar.textContent = initials;
  }
}

// Load all technicians
async function loadTechnicians() {
  try {
    // For now, use empty array since admin endpoint is not implemented yet
    // TODO: Implement /api/admin/technicians endpoint
    technicians = [];
  } catch (error) {
    console.error('Error loading technicians:', error);
    technicians = [];
  }
}

// Load all service requests from database
async function loadTickets() {
  try {
    const response = await ApiClient.get('/service-requests/admin');
    // Use the existing /api/requests endpoint
    const response = await ApiClient.get('/requests
      allTickets = response.data || [];
      filterTickets();
      updatePendingCount();
    }
  } catch (error) {
    console.error('Error loading tickets:', error);
    showError('Failed to load service requests');
  }
}

// Filter tickets by status
function filterTickets() {
  if (currentFilter === 'All') {
    filteredTickets = [...allTickets];
  } else {
    filteredTickets = allTickets.filter(t => t.status === currentFilter);
  }
  renderTickets();
}

// Render tickets table
function renderTickets() {
  const tbody = document.getElementById('ticketsTableBody');
  const countEl = document.getElementById('ticketCount');
  
  if (!tbody) return;
  
  countEl.textContent = `Showing ${filteredTickets.length} tickets`;
  
  if (filteredTickets.length === 0) {
    tbody.innerHTML = `
      <tr>
        <td colspan="6" class="px-6 py-12 text-center text-slate-500">
          <div class="flex flex-col items-center gap-3">
            <i data-lucide="inbox" class="w-12 h-12 text-slate-300"></i>
            <p class="font-medium">No service requests found</p>
            <p class="text-xs">Tickets will appear here when citizens submit requests</p>
          </div>
        </td>
      </tr>
    `;
    lucide.createIcons();
    return;
  }
  
  tbody.innerHTML = filteredTickets.map(ticket => {
    const isSelected = selectedTicket && selectedTicket.id === ticket.id;
    const ticketNumber = ticket.ticket_number || 'SR-' + ticket.id;
    const issueType = escapeHtml(ticket.issue_type || 'Service Request');
    const address = escapeHtml(ticket.address || 'N/A');
    const timeAgo = getTimeAgo(ticket.created_at);
    const priority = ticket.priority || 'Medium';
    const assignedToName = ticket.assigned_to_name ? escapeHtml(ticket.assigned_to_name) : null;
    
    return `
      <tr 
        data-ticket-id="${ticket.id}"
        class="cursor-pointer transition-colors ${isSelected ? 'bg-blue-50' : 'hover:bg-slate-50'}"
        onclick="openDrawer(${ticket.id})"
      >
        <td class="px-6 py-4 font-mono text-slate-500 font-medium">${ticketNumber}</td>
        <td class="px-6 py-4">
          <div class="flex items-start gap-3">
            <div class="mt-0.5 p-1.5 bg-slate-100 rounded text-slate-500">
              ${getCategoryIcon(ticket.request_type)}
            </div>
            <div>
              <div class="font-bold text-slate-800">${issueType}</div>
              <div class="text-xs text-slate-500 mt-0.5">${address} • ${timeAgo}</div>
            </div>
          </div>
        </td>
        <td class="px-6 py-4">
          <span class="text-xs font-bold ${getPriorityClass(priority)}">${priority}</span>
        </td>
        <td class="px-6 py-4">
          ${assignedToName ? `
            <div class="flex items-center gap-2">
              <div class="w-6 h-6 rounded-full bg-slate-200 flex items-center justify-center text-[10px] font-bold text-slate-600">
                ${getInitials(assignedToName)}
              </div>
              <span class="text-sm font-medium text-slate-700 truncate max-w-[100px]">${assignedToName}</span>
            </div>
          ` : '<span class="text-xs text-slate-400 italic">Unassigned</span>'}
        </td>
        <td class="px-6 py-4">
          <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-bold border ${getStatusBadge(ticket.status)}">
            ${ticket.status}
          </span>
        </td>
        <td class="px-6 py-4 text-right">
          <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400 ml-auto"></i>
        </td>
      </tr>
    `;
  }).join('');
  
  lucide.createIcons();
}

// Update pending count badge
function updatePendingCount() {
  const pendingCount = allTickets.filter(t => t.status === 'Pending').length;
  const badge = document.getElementById('pendingBadge');
  if (badge) {
    badge.textContent = pendingCount;
  }
}

// Open ticket drawer
async function openDrawer(ticketId) {
  const ticket = allTickets.find(t => t.id === ticketId);
  if (!ticket) return;
  
  selectedTicket = ticket;
  
  // Update drawer content
  document.getElementById('drawerTicketId').textContent = ticket.ticket_number || 'SR-' + ticket.id;
  document.getElementById('drawerIssueTitle').textContent = ticket.issue_type || 'Service Request';
  document.getElementById('drawerDescription').textContent = ticket.description || 'No description provided';
  document.getElementById('drawerCitizen').textContent = ticket.citizen_name || 'Unknown';
  document.getElementById('drawerDate').textContent = formatDate(ticket.created_at);
  document.getElementById('drawerLocation').textContent = ticket.address || 'N/A';
  
  const statusBadge = document.getElementById('drawerStatusBadge');
  statusBadge.textContent = ticket.status;
  statusBadge.className = `text-xs font-bold px-2 py-0.5 rounded border ${getStatusBadge(ticket.status)}`;
  
  // Load technicians dropdown
  const techSelect = document.getElementById('technicianSelect');
  techSelect.innerHTML = '<option value="">Select Team...</option>' + 
    technicians.map(t => {
      const techName = t.name || `${t.first_name} ${t.last_name}`;
      const status = t.status || 'Available';
      const selected = ticket.assigned_to == t.id ? 'selected' : '';
      return `<option value="${t.id}" ${selected}>${escapeHtml(techName)} (${status})</option>`;
    }).join('');
  
  // Show/hide resolved button
  const resolvedBtn = document.getElementById('markResolvedBtn');
  if (ticket.status === 'Resolved') {
    resolvedBtn.style.display = 'none';
  } else {
    resolvedBtn.style.display = 'block';
  }
  
  // Show drawer
  document.getElementById('ticketDrawer').classList.remove('hidden');
  lucide.createIcons();
  renderTickets(); // Re-render to update selected state
}

// Close drawer
function closeDrawer() {
  selectedTicket = null;
  document.getElementById('ticketDrawer').classList.add('hidden');
  renderTickets(); // Re-render to update selected state
}

// Update assignment
async function updateAssignment() {
  if (!selectedTicket) return;
  
  const techId = document.getElementById('technicianSelect').value;
  if (!techId) {
    alert('Please select a technician');
    return;
  }
  
  tr// Use the existing /api/requests/{id} endpoint to update
    const response = await ApiClient.put(`/requests/${selectedTicket.id}`, {
      assigned_to= await ApiClient.put(`/service-requests/${selectedTicket.id}/assign`, {
      technician_id: techId
    });
    
    if (response.success) {
      showSuccess('Technician assigned successfully');
      await loadTickets();
      openDrawer(selectedTicket.id); // Refresh drawer
    }
  } catch (error) {
    console.error('Error assigning technician:', error);
    showError('Failed to assign technician');
  }
}

// Mark as resolved
async function markResolved() {
  if (!selectedTicket) return;
  
  if (!confirm('Mark this ticket as resolved?')) return;
  // Use the existing /api/requests/{id} endpoint to update status
    const response = await ApiClient.put(`/requests/${selectedTicket.id}
  try {
    const response = await ApiClient.put(`/service-requests/${selectedTicket.id}/status`, {
      status: 'Resolved'
    });
    
    if (response.success) {
      showSuccess('Ticket marked as resolved');
      await loadTickets();
      openDrawer(selectedTicket.id); // Refresh drawer
    }
  } catch (error) {
    console.error('Error updating status:', error);
    showError('Failed to update status');
  }
}

// Add note
async function addNote() {
  if (!selectedTicket) return;
  
  const input = document.getElementById('newNoteInput');
  const note = input.value.trim();
  
  if (!note) return;
  
  try {
    // Implement API endpoint for adding notes
    showSuccess('Note added');
    input.value = '';
  } catch (error) {
    console.error('Error adding note:', error);
    showError('Failed to add note');
  }
}

// Setup event listeners
function setupEventListeners() {
  // Filter buttons
  document.querySelectorAll('.filter-btn').forEach(btn => {
    btn.addEventListener('click', function() {
      document.querySelectorAll('.filter-btn').forEach(b => {
        b.classList.remove('bg-slate-800', 'text-white', 'border-slate-800', 'active');
        b.classList.add('bg-white', 'text-slate-600', 'border-slate-200');
      });
      this.classList.remove('bg-white', 'text-slate-600', 'border-slate-200');
      this.classList.add('bg-slate-800', 'text-white', 'border-slate-800', 'active');
      
      currentFilter = this.dataset.status;
      filterTickets();
    });
  });
  
  // Drawer close
  document.getElementById('closeDrawer')?.addEventListener('click', closeDrawer);
  document.getElementById('drawerBackdrop')?.addEventListener('click', closeDrawer);
  
  // Drawer actions
  document.getElementById('updateAssignmentBtn')?.addEventListener('click', updateAssignment);
  document.getElementById('markResolvedBtn')?.addEventListener('click', markResolved);
  document.getElementById('sendNoteBtn')?.addEventListener('click', addNote);
  document.getElementById('newNoteInput')?.addEventListener('keypress', (e) => {
    if (e.key === 'Enter') addNote();
  });
  
  // Search
  document.getElementById('searchInput')?.addEventListener('input', function(e) {
    const query = e.target.value.toLowerCase();
    if (!query) {
      filterTickets();
      return;
    }
    
    filteredTickets = allTickets.filter(t => 
      (t.ticket_number && t.ticket_number.toLowerCase().includes(query)) ||
      (t.citizen_name && t.citizen_name.toLowerCase().includes(query)) ||
      (t.issue_type && t.issue_type.toLowerCase().includes(query)) ||
      (t.assigned_to_name && t.assigned_to_name.toLowerCase().includes(query))
    );
    renderTickets();
  });
  
  // Logout
  document.getElementById('logoutBtn')?.addEventListener('click', async () => {
    if (confirm('Are you sure you want to logout?')) {
      try {
        await ApiClient.auth.logout();
      } catch (error) {
        console.error('Logout error:', error);
        localStorage.removeItem('auth_token');
        localStorage.removeItem('user');
        localStorage.removeItem('remember_me');
        localStorage.setItem('logout_event', Date.now().toString());
        window.location.href = '/login.php';
      }
    }
  });
}

// Helper functions
function getCategoryIcon(type) {
  if (type === 'Water' || type === 'water') {
    return '<i data-lucide="droplets" class="w-4 h-4 text-blue-500"></i>';
  } else if (type === 'Electric' || type === 'electrical' || type === 'electricity') {
    return '<i data-lucide="zap" class="w-4 h-4 text-amber-500"></i>';
  }
  return '<i data-lucide="hammer" class="w-4 h-4 text-slate-500"></i>';
}

function getStatusBadge(status) {
  switch(status) {
    case 'Pending': return 'bg-red-100 text-red-700 border-red-200';
    case 'In Progress': return 'bg-blue-100 text-blue-700 border-blue-200';
    case 'Resolved': return 'bg-green-100 text-green-700 border-green-200';
    default: return 'bg-slate-100 text-slate-700 border-slate-200';
  }
}

function getPriorityClass(priority) {
  switch(priority) {
    case 'Critical': return 'text-red-600 font-bold bg-red-50 px-2 py-0.5 rounded';
    case 'High': return 'text-orange-600 font-medium bg-orange-50 px-2 py-0.5 rounded';
    case 'Medium': return 'text-blue-600 bg-blue-50 px-2 py-0.5 rounded';
    default: return 'text-slate-500 bg-slate-50 px-2 py-0.5 rounded';
  }
}

function getTimeAgo(dateString) {
  if (!dateString) return 'Unknown';
  const date = new Date(dateString);
  const now = new Date();
  const diff = Math.floor((now - date) / 1000); // seconds
  
  if (diff < 60) return 'Just now';
  if (diff < 3600) return Math.floor(diff / 60) + ' mins ago';
  if (diff < 86400) return Math.floor(diff / 3600) + ' hrs ago';
  if (diff < 604800) return Math.floor(diff / 86400) + ' days ago';
  return formatDate(dateString);
}

function formatDate(dateString) {
  if (!dateString) return 'N/A';
  const date = new Date(dateString);
  return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
}

function getInitials(name) {
  if (!name) return '??';
  const parts = name.split(' ');
  if (parts.length >= 2) {
    return (parts[0].charAt(0) + parts[1].charAt(0)).toUpperCase();
  }
  return name.substring(0, 2).toUpperCase();
}

function escapeHtml(text) {
  const div = document.createElement('div');
  div.textContent = text;
  return div.innerHTML;
}

function showSuccess(message) {
  alert(message);
}

function showError(message) {
  alert('Error: ' + message);
}
