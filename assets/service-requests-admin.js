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

<<<<<<< Updated upstream
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
        UIHelpers.showError('Access denied. Admin privileges required.');
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
=======
// =============================================================================
// FUNCTION DEFINITIONS - All functions defined here before DOMContentLoaded
// =============================================================================
>>>>>>> Stashed changes

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
    // Note: In a full implementation, create a dedicated endpoint
    // For now, we'll populate dropdown with placeholder data
    // The actual assignment will use the technician_id
    technicians = [
      { id: 1, first_name: 'Water', last_name: 'Team', specialization: 'Water Supply', status: 'Available' },
      { id: 2, first_name: 'Electric', last_name: 'Team', specialization: 'Electrical', status: 'Available' },
      { id: 3, first_name: 'Maintenance', last_name: 'Team', specialization: 'General', status: 'Available' }
    ];
  } catch (error) {
    console.error('Error loading technicians:', error);
    technicians = [];
  }
}

// Load all service requests from database
async function loadTickets() {
  console.log('=== loadTickets() called ===');
  const tbody = document.getElementById('ticketsTableBody');
  
  if (!tbody) {
    console.error('Table body element not found!');
    return;
  }
  
  // Show loading state
  tbody.innerHTML = `
    <tr>
      <td colspan="6" class="px-6 py-12 text-center text-slate-500">
        <div class="flex flex-col items-center gap-3">
          <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
          <p>Loading service requests...</p>
        </div>
      </td>
    </tr>
  `;
  
  try {
<<<<<<< Updated upstream
    const response = await ApiClient.get('/service-requests/admin');
    if (response.success) {
      allTickets = response.data || [];
=======
    console.log('Fetching from /api/requests...');
    
    // Use the existing /api/requests endpoint
    const response = await ApiClient.get('/requests');
    
    console.log('API Response:', response);
    
    // Handle the response structure
    // API returns: { success: true, message: "Success", data: { success: true, requests: [...], count: N } }
    if (response && response.success && response.data) {
      const data = response.data;
      console.log('Data object:', data);
      
      // Extract the requests array
      if (data.requests && Array.isArray(data.requests)) {
        allTickets = data.requests;
        console.log(`âœ“ Loaded ${allTickets.length} service requests`);
      } else if (Array.isArray(data)) {
        // Fallback: data might be the array directly
        allTickets = data;
        console.log(`âœ“ Loaded ${allTickets.length} service requests (direct array)`);
      } else {
        console.error('Expected requests array not found in data:', data);
        allTickets = [];
      }
      
>>>>>>> Stashed changes
      filterTickets();
      updatePendingCount();
    } else if (response && response.requests && Array.isArray(response.requests)) {
      // Fallback: requests might be at top level
      allTickets = response.requests;
      console.log(`âœ“ Loaded ${allTickets.length} service requests (top level)`);
      filterTickets();
      updatePendingCount();
    } else {
      console.error('Unexpected response format:', response);
      allTickets = [];
      filterTickets();
    }
  } catch (error) {
    console.error('=== ERROR loading tickets ===');
    console.error('Error:', error);
    console.error('Message:', error.message);
    console.error('Response:', error.response);
    console.error('Stack:', error.stack);
    
    allTickets = [];
    
    if (tbody) {
      tbody.innerHTML = `
        <tr>
          <td colspan="6" class="px-6 py-12 text-center text-slate-500">
            <div class="flex flex-col items-center gap-3">
              <i data-lucide="alert-circle" class="w-12 h-12 text-red-300"></i>
              <p class="font-medium text-red-600">Failed to load service requests</p>
              <p class="text-xs text-slate-600">${error.message || 'Please check your connection and try again'}</p>
              <button onclick="window.loadTickets()" class="mt-3 px-4 py-2 bg-blue-600 text-white rounded-lg text-sm hover:bg-blue-700">
                Retry
              </button>
            </div>
          </td>
        </tr>
      `;
      if (typeof lucide !== 'undefined') {
        lucide.createIcons();
      }
    }
  }
  
  console.log('=== loadTickets() finished ===');
}

// Filter tickets by status
function filterTickets() {
  console.log(`Filtering tickets by: ${currentFilter}`);
  console.log(`Total tickets: ${allTickets.length}`);
  
  if (currentFilter === 'All') {
    filteredTickets = [...allTickets];
  } else {
    filteredTickets = allTickets.filter(t => t.status === currentFilter);
  }
  
  console.log(`Filtered tickets: ${filteredTickets.length}`);
  renderTickets();
}

// Render tickets table
function renderTickets() {
  console.log('=== renderTickets() called ===');
  console.log('Filtered tickets count:', filteredTickets.length);
  
  const tbody = document.getElementById('ticketsTableBody');
  const countEl = document.getElementById('ticketCount');
  
  if (!tbody) {
    console.error('Table body element not found in renderTickets!');
    return;
  }
  
  if (countEl) {
    countEl.textContent = `Showing ${filteredTickets.length} tickets`;
  }
  
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
    if (typeof lucide !== 'undefined') {
      lucide.createIcons();
    }
    return;
  }
  
  console.log('Rendering tickets:', filteredTickets.length);
  console.log('Rendering ticket IDs:', filteredTickets.map(t => t.id));
  
  tbody.innerHTML = filteredTickets.map(ticket => {
    const isSelected = selectedTicket && selectedTicket.id === ticket.id;
    const ticketNumber = ticket.ticket_number || 'SR-' + ticket.id;
    const issueType = escapeHtml(ticket.issue_type || 'Service Request');
    const address = escapeHtml(ticket.address || 'N/A');
    const timeAgo = getTimeAgo(ticket.created_at);
    const priority = ticket.priority || 'Medium';
    const assignedToName = ticket.assigned_to_name ? escapeHtml(ticket.assigned_to_name) : null;
    
    console.log(`Rendering row for ticket ID: ${ticket.id}, ticket_number: ${ticketNumber}`);
    
    return `
      <tr 
        data-ticket-id="${ticket.id}"
        class="cursor-pointer transition-all duration-150 ${isSelected ? 'bg-blue-50 border-l-4 border-l-blue-600' : 'hover:bg-slate-50 hover:shadow-sm'}"
        style="${isSelected ? 'box-shadow: inset 0 0 0 1px rgb(37 99 235 / 0.1);' : ''}"
      >
        <td class="px-6 py-4 font-mono text-slate-500 font-medium">${ticketNumber}</td>
        <td class="px-6 py-4">
          <div class="flex items-start gap-3">
            <div class="mt-0.5 p-1.5 bg-slate-100 rounded text-slate-500">
              ${getCategoryIcon(ticket.request_type)}
            </div>
            <div>
              <div class="font-bold text-slate-800">${issueType}</div>
              <div class="text-xs text-slate-500 mt-0.5">${address} â€¢ ${timeAgo}</div>
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
  
  // Re-initialize Lucide icons after rendering
  if (typeof lucide !== 'undefined') {
    lucide.createIcons();
  }
  
  // Attach click handlers to all rows after rendering
  attachRowClickHandlers();
}

// Attach click handlers to table rows
function attachRowClickHandlers() {
  const rows = document.querySelectorAll('tr[data-ticket-id]');
  console.log(`Attaching click handlers to ${rows.length} rows`);
  
  rows.forEach(row => {
    // Remove any existing listeners by cloning
    const newRow = row.cloneNode(true);
    row.parentNode.replaceChild(newRow, row);
    
    // Add click handler
    newRow.addEventListener('click', function(e) {
      const ticketId = this.getAttribute('data-ticket-id');
      console.log('Row clicked, ticket ID:', ticketId, 'Type:', typeof ticketId);
      console.log('Current allTickets:', allTickets.length, 'tickets');
      
      if (ticketId) {
        openDrawer(ticketId);
      } else {
        console.error('Invalid ticket ID:', ticketId);
        showToast('Invalid ticket ID', 'error');
      }
    });
    
    // Add hover effect
    newRow.style.cursor = 'pointer';
  });
}

// Update pending count badge
function updatePendingCount() {
  const pendingCount = allTickets.filter(t => t.status === 'Pending').length;
  const badge = document.getElementById('pendingBadge');
  if (badge) {
    badge.textContent = pendingCount;
  }
}

// Store scroll position to maintain place in list
let savedScrollPosition = 0;

// Open ticket drawer
async function openDrawer(ticketId) {
  console.log('========================================');
  console.log('Opening drawer for ticket ID:', ticketId, 'Type:', typeof ticketId);
  console.log('Total tickets in array:', allTickets.length);
  console.log('Available ticket IDs:', allTickets.map(t => `${t.id} (${typeof t.id})`).join(', '));
  
  // Try to find with both number and string comparison
  let ticket = allTickets.find(t => t.id == ticketId); // Use == for loose comparison
  
  if (!ticket) {
    console.error('❌ Ticket not found with ID:', ticketId);
    console.error('Attempted to find in tickets:', allTickets.map(t => ({ id: t.id, type: typeof t.id })));
    showToast('Ticket not found. Please refresh the page.', 'error');
    return;
  }
  
  console.log('✓ Ticket found:', ticket);
  
  selectedTicket = ticket;
  
  // Save current scroll position
  const mainContent = document.querySelector('main');
  if (mainContent) {
    savedScrollPosition = mainContent.scrollTop;
  }
  
  // Update drawer content
  document.getElementById('drawerTicketId').textContent = ticket.ticket_number || 'SR-' + ticket.id;
  document.getElementById('drawerIssueTitle').textContent = ticket.issue_type || 'Service Request';
  document.getElementById('drawerDescription').textContent = ticket.description || 'No description provided';
  
  // Display category (request_type from database: water, electricity, etc)
  const category = ticket.request_type || 'General';
  document.getElementById('drawerCategory').textContent = category.charAt(0).toUpperCase() + category.slice(1);
  
  document.getElementById('drawerCitizen').textContent = ticket.citizen_name || 'Unknown';
  document.getElementById('drawerDate').textContent = formatDate(ticket.created_at);
  document.getElementById('drawerLocation').textContent = ticket.address || 'N/A';
  
  const statusBadge = document.getElementById('drawerStatusBadge');
  statusBadge.textContent = ticket.status;
  statusBadge.className = `text-xs font-bold px-2 py-1 rounded border ${getStatusBadge(ticket.status)}`;
  
  // Load technicians dropdown
  const techSelect = document.getElementById('technicianSelect');
  techSelect.innerHTML = '<option value="">Select Technician...</option>' + 
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
  
  // Show drawer with animation
  const drawer = document.getElementById('ticketDrawer');
  const panel = document.getElementById('drawerPanel');
  const backdrop = document.getElementById('drawerBackdrop');
  
  console.log('Drawer elements found:', { drawer: !!drawer, panel: !!panel, backdrop: !!backdrop });
  
  if (drawer) {
    drawer.classList.remove('hidden');
    console.log('Drawer opened successfully');
    
    // Trigger animation by removing and re-adding animation classes
    requestAnimationFrame(() => {
      if (panel) {
        panel.classList.remove('drawer-slide-in');
        void panel.offsetWidth; // Force reflow
        panel.classList.add('drawer-slide-in');
      }
      if (backdrop) {
        backdrop.classList.remove('drawer-backdrop-in');
        void backdrop.offsetWidth; // Force reflow
        backdrop.classList.add('drawer-backdrop-in');
      }
    });
  }
  
  // Re-initialize Lucide icons
  if (typeof lucide !== 'undefined') {
    lucide.createIcons();
  }
  
  renderTickets(); // Re-render to update selected state
}

// Close drawer with smooth animation
function closeDrawer() {
  const drawer = document.getElementById('ticketDrawer');
  const panel = document.getElementById('drawerPanel');
  
  // Add slide-out animation
  if (panel) {
    panel.style.animation = 'slide-in-right 0.2s ease-in reverse';
  }
  
  // Wait for animation to complete before hiding
  setTimeout(() => {
    selectedTicket = null;
    if (drawer) {
      drawer.classList.add('hidden');
    }
    if (panel) {
      panel.style.animation = ''; // Reset animation
    }
    renderTickets(); // Re-render to update selected state
    
    // Restore scroll position
    const mainContent = document.querySelector('main');
    if (mainContent && savedScrollPosition > 0) {
      mainContent.scrollTop = savedScrollPosition;
    }
  }, 200);
}

// Update priority
async function updatePriority() {
  if (!selectedTicket) return;
  
  const priority = document.getElementById('prioritySelect').value;
  if (!priority) {
    showToast('Please select a priority level', 'error');
    return;
  }
  
  try {
    // Use the existing /api/requests/{id} PATCH endpoint
    const response = await ApiClient.request(`/requests/${selectedTicket.id}`, {
      method: 'PATCH',
      body: JSON.stringify({ priority: priority })
    });
    
    if (response.success) {
      // Store the ticket ID before refreshing
      const ticketId = selectedTicket.id;
      
      showSuccess(`Priority updated to ${priority}`);
      
      // Refresh data from database to get latest state
      await loadTickets();
      
      // Reopen drawer with the same ticket ID after data loads
      setTimeout(() => {
        console.log('Reopening drawer after priority update for ticket:', ticketId);
        openDrawer(ticketId);
      }, 100);
    }
  } catch (error) {
    console.error('Error updating priority:', error);
    showError(error.message || 'Failed to update priority');
  }
}

// Update assignment
async function updateAssignment() {
  if (!selectedTicket) return;
  
  const techId = document.getElementById('technicianSelect').value;
  if (!techId) {
<<<<<<< Updated upstream
    UIHelpers.showError('Please select a technician');
=======
    showToast('Please select a technician', 'error');
>>>>>>> Stashed changes
    return;
  }
  
  try {
<<<<<<< Updated upstream
    const response = await ApiClient.put(`/service-requests/${selectedTicket.id}/assign`, {
      technician_id: techId
=======
    // Use the existing /api/requests/{id}/assign endpoint
    const response = await ApiClient.post(`/requests/${selectedTicket.id}/assign`, {
      technician_id: parseInt(techId)
>>>>>>> Stashed changes
    });
    if (response.success) {
      // Store the ticket ID before refreshing
      const ticketId = selectedTicket.id;
      
      // Find technician name
      const tech = technicians.find(t => t.id == techId);
      const techName = tech ? (tech.name || `${tech.first_name} ${tech.last_name}`) : 'Technician';
      
      showSuccess(`Assigned to ${techName}`);
      
      // Refresh data from database to get latest state
      await loadTickets();
<<<<<<< Updated upstream
      openDrawer(selectedTicket.id); // Refresh drawer
    } else {
      showError(response.message || 'Failed to assign technician');
=======
      
      setTimeout(() => {
        console.log('Reopening drawer after assignment update for ticket:', ticketId);
        openDrawer(ticketId);
      }, 100);
>>>>>>> Stashed changes
    }
  } catch (error) {
    console.error('Error assigning technician:', error);
    showError(error.message || 'Failed to assign technician');
  }
}

// Update status
async function updateStatus() {
  if (!selectedTicket) return;
  
  const status = document.getElementById('statusSelect').value;
  if (!status) {
    showToast('Please select a status', 'error');
    return;
  }
  
  // If marking as resolved, use the markResolved function
  if (status === 'Resolved') {
    markResolved();
    return;
  }
  
  try {
    // Use the existing /api/requests/{id} PATCH endpoint
    const response = await ApiClient.request(`/requests/${selectedTicket.id}`, {
      method: 'PATCH',
      body: JSON.stringify({ status: status })
    });
    
    if (response.success) {
      // Store the ticket ID before refreshing
      const ticketId = selectedTicket.id;
      
      showSuccess(`Status updated to ${status}`);
      
      // Refresh data from database to get latest state
      await loadTickets();
      
      setTimeout(() => {
        console.log('Reopening drawer after status update for ticket:', ticketId);
        openDrawer(ticketId);
      }, 100);
    }
  } catch (error) {
    console.error('Error updating status:', error);
    showError(error.message || 'Failed to update status');
  }
}

// Mark as resolved
async function markResolved() {
  if (!selectedTicket) return;
  
<<<<<<< Updated upstream
  const ok = await UIHelpers.confirm({
    title: 'Mark as Resolved',
    message: 'Mark this ticket as resolved?',
    confirmText: 'Mark Resolved',
    cancelText: 'Cancel',
    variant: 'primary'
  });
  if (!ok) return;
=======
  if (!confirm(`Mark ticket ${selectedTicket.ticket_number || 'SR-' + selectedTicket.id} as resolved?\n\nThis will close the ticket and notify the citizen.`)) return;
  
>>>>>>> Stashed changes
  try {
    // Use the existing /api/requests/{id}/complete endpoint
    const response = await ApiClient.post(`/requests/${selectedTicket.id}/complete`, {
      completion_notes: 'Marked as resolved by admin'
    });
    
    if (response.success) {
      // Store the ticket ID before refreshing
      const ticketId = selectedTicket.id;
      
      showSuccess('✓ Ticket marked as resolved');
      
      // Refresh data from database to get latest state
      await loadTickets();
<<<<<<< Updated upstream
      openDrawer(selectedTicket.id); // Refresh drawer
    } else {
      showError(response.message || 'Failed to update status');
=======
      
      setTimeout(() => {
        console.log('Reopening drawer after marking resolved for ticket:', ticketId);
        openDrawer(ticketId);
      }, 100);
>>>>>>> Stashed changes
    }
  } catch (error) {
    console.error('Error updating status:', error);
    showError(error.message || 'Failed to update status');
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
  console.log('Setting up event listeners...');
  
  // Filter buttons
  const filterButtons = document.querySelectorAll('.filter-btn');
  console.log(`Found ${filterButtons.length} filter buttons`);
  
  filterButtons.forEach(btn => {
    btn.addEventListener('click', function() {
      console.log('Filter clicked:', this.dataset.status);
      
      document.querySelectorAll('.filter-btn').forEach(b => {
        b.classList.remove('bg-slate-800', 'text-white', 'border-slate-800', 'active');
        b.classList.add('bg-white', 'text-slate-600', 'border-slate-200');
      });
      this.classList.remove('bg-white', 'text-slate-600', 'border-slate-200');
      this.classList.add('bg-slate-800', 'text-white', 'border-slate-800', 'active');
      
      currentFilter = this.dataset.status;
      console.log('Current filter set to:', currentFilter);
      filterTickets();
    });
  });
  
  // Drawer close
  document.getElementById('closeDrawer')?.addEventListener('click', (e) => {
    e.preventDefault();
    e.stopPropagation();
    closeDrawer();
  });
  document.getElementById('drawerBackdrop')?.addEventListener('click', (e) => {
    e.preventDefault();
    e.stopPropagation();
    closeDrawer();
  });
  
  // Table row clicks handled by attachRowClickHandlers() after each render
  
  // Drawer actions
  document.getElementById('updatePriorityBtn')?.addEventListener('click', updatePriority);
  document.getElementById('updateStatusBtn')?.addEventListener('click', updateStatus);
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
  
  // Export CSV
  document.getElementById('exportBtn')?.addEventListener('click', exportToCSV);
  
  // Manual ticket button (placeholder)
  document.getElementById('manualTicketBtn')?.addEventListener('click', () => {
    showToast('Manual ticket creation feature coming soon!', 'info');
  });
  
  // Logout
  document.getElementById('logoutBtn')?.addEventListener('click', async () => {
    const ok = await UIHelpers.confirm({
      title: 'Logout',
      message: 'Are you sure you want to logout?',
      confirmText: 'Logout',
      cancelText: 'Cancel',
      variant: 'danger'
    });
    if (ok) {
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
  
  // Notification button dropdown
  const notificationBtn = document.getElementById('notificationBtn');
  const notificationDropdown = document.getElementById('notificationDropdown');
  
  if (notificationBtn && notificationDropdown) {
    notificationBtn.addEventListener('click', (e) => {
      e.stopPropagation();
      // Close profile dropdown if open
      const profileDropdown = document.getElementById('profileDropdown');
      if (profileDropdown && !profileDropdown.classList.contains('hidden')) {
        profileDropdown.classList.add('hidden');
      }
      // Toggle notification dropdown
      notificationDropdown.classList.toggle('hidden');
      // Re-initialize icons
      if (typeof lucide !== 'undefined') {
        lucide.createIcons();
      }
    });
  }
  
  // Profile dropdown
  const profileDropdownBtn = document.getElementById('profileDropdownBtn');
  const profileDropdown = document.getElementById('profileDropdown');
  
  if (profileDropdownBtn && profileDropdown) {
    profileDropdownBtn.addEventListener('click', (e) => {
      e.stopPropagation();
      // Close notification dropdown if open
      if (notificationDropdown && !notificationDropdown.classList.contains('hidden')) {
        notificationDropdown.classList.add('hidden');
      }
      // Toggle profile dropdown
      profileDropdown.classList.toggle('hidden');
      // Update dropdown info with current user
      if (currentUser) {
        const dropdownName = document.getElementById('dropdownName');
        const dropdownRole = document.getElementById('dropdownRole');
        if (dropdownName && currentUser.first_name && currentUser.last_name) {
          dropdownName.textContent = `${currentUser.first_name} ${currentUser.last_name}`;
        }
        if (dropdownRole) {
          const roleText = currentUser.role === 'admin' ? 'System Administrator' : 'Staff Member';
          dropdownRole.textContent = roleText;
        }
      }
      // Re-initialize icons
      if (typeof lucide !== 'undefined') {
        lucide.createIcons();
      }
    });
  }
  
  // Header logout button
  document.getElementById('logoutBtnHeader')?.addEventListener('click', async () => {
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
  
  // Close dropdowns when clicking outside
  document.addEventListener('click', (e) => {
    if (notificationDropdown && !notificationDropdown.classList.contains('hidden')) {
      if (!notificationBtn?.contains(e.target) && !notificationDropdown.contains(e.target)) {
        notificationDropdown.classList.add('hidden');
      }
    }
    if (profileDropdown && !profileDropdown.classList.contains('hidden')) {
      if (!profileDropdownBtn?.contains(e.target) && !profileDropdown.contains(e.target)) {
        profileDropdown.classList.add('hidden');
      }
    }
  });
  
  // Keyboard shortcuts
  document.addEventListener('keydown', (e) => {
    // ESC to close drawer
    if (e.key === 'Escape') {
      const drawer = document.getElementById('ticketDrawer');
      if (drawer && !drawer.classList.contains('hidden')) {
        closeDrawer();
      }
      // Also close dropdowns
      if (notificationDropdown && !notificationDropdown.classList.contains('hidden')) {
        notificationDropdown.classList.add('hidden');
      }
      if (profileDropdown && !profileDropdown.classList.contains('hidden')) {
        profileDropdown.classList.add('hidden');
      }
    }
  });
}

// Export to CSV
function exportToCSV() {
  if (filteredTickets.length === 0) {
    showToast('No data to export', 'error');
    return;
  }
  
  try {
    const headers = ['Ticket ID', 'Issue Type', 'Description', 'Priority', 'Status', 'Assigned To', 'Citizen', 'Address', 'Date Created'];
    const rows = filteredTickets.map(ticket => [
      ticket.ticket_number || 'SR-' + ticket.id,
      ticket.issue_type || '',
      ticket.description || '',
      ticket.priority || 'Medium',
      ticket.status || 'Pending',
      ticket.assigned_to_name || 'Unassigned',
      ticket.citizen_name || '',
      ticket.address || '',
      formatDate(ticket.created_at)
    ]);
    
    let csvContent = headers.join(',') + '\n';
    rows.forEach(row => {
      csvContent += row.map(cell => `"${String(cell).replace(/"/g, '""')}"`).join(',') + '\n';
    });
    
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);
    link.setAttribute('href', url);
    link.setAttribute('download', `service-requests-${new Date().toISOString().split('T')[0]}.csv`);
    link.style.visibility = 'hidden';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    
    showToast(`Exported ${filteredTickets.length} tickets to CSV`, 'success');
  } catch (error) {
    console.error('Export error:', error);
    showToast('Failed to export CSV', 'error');
  }
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
    case 'Pending': return 'bg-yellow-100 text-yellow-700 border-yellow-300';
    case 'In Progress': return 'bg-blue-100 text-blue-700 border-blue-300';
    case 'Resolved': return 'bg-green-100 text-green-700 border-green-300';
    default: return 'bg-slate-100 text-slate-700 border-slate-300';
  }
}

function getPriorityClass(priority) {
  switch(priority) {
    case 'Critical': return 'text-red-700 font-bold bg-red-100 border border-red-200 px-2.5 py-1 rounded-md';
    case 'High': return 'text-orange-700 font-bold bg-orange-100 border border-orange-200 px-2.5 py-1 rounded-md';
    case 'Medium': return 'text-yellow-700 font-semibold bg-yellow-100 border border-yellow-200 px-2.5 py-1 rounded-md';
    case 'Low': return 'text-green-700 font-medium bg-green-100 border border-green-200 px-2.5 py-1 rounded-md';
    default: return 'text-slate-600 font-medium bg-slate-100 border border-slate-200 px-2.5 py-1 rounded-md';
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
<<<<<<< Updated upstream
  UIHelpers.showSuccess(message);
}

function showError(message) {
  UIHelpers.showError(message);
=======
  showToast(message, 'success');
}

function showError(message) {
  showToast(message, 'error');
>>>>>>> Stashed changes
}

// Toast notification system
function showToast(message, type = 'info') {
  console.log('Showing toast:', message, type);
  
  // Remove any existing toast
  const existingToast = document.getElementById('customToast');
  if (existingToast) {
    existingToast.remove();
  }
  
  // Create toast element
  const toast = document.createElement('div');
  toast.id = 'customToast';
  toast.className = `fixed top-4 right-4 flex items-center gap-3 px-4 py-3 rounded-lg shadow-lg border`;
  toast.style.cssText = 'z-index: 100; animation: slide-in-from-top-5 0.3s ease-out;';
  
  // Set colors based on type
  if (type === 'success') {
    toast.className += ' bg-green-50 border-green-200 text-green-800';
    toast.innerHTML = `
      <i data-lucide="check-circle" class="w-5 h-5 text-green-600"></i>
      <span class="font-medium">${escapeHtml(message)}</span>
    `;
  } else if (type === 'error') {
    toast.className += ' bg-red-50 border-red-200 text-red-800';
    toast.innerHTML = `
      <i data-lucide="alert-circle" class="w-5 h-5 text-red-600"></i>
      <span class="font-medium">${escapeHtml(message)}</span>
    `;
  } else {
    toast.className += ' bg-blue-50 border-blue-200 text-blue-800';
    toast.innerHTML = `
      <i data-lucide="info" class="w-5 h-5 text-blue-600"></i>
      <span class="font-medium">${escapeHtml(message)}</span>
    `;
  }
  
  // Add close button
  const closeBtn = document.createElement('button');
  closeBtn.className = 'ml-2 text-current opacity-50 hover:opacity-100';
  closeBtn.innerHTML = '<i data-lucide="x" class="w-4 h-4"></i>';
  closeBtn.onclick = () => toast.remove();
  toast.appendChild(closeBtn);
  
  // Add to body
  document.body.appendChild(toast);
  
  // Initialize icons
  if (typeof lucide !== 'undefined') {
    lucide.createIcons();
  }
  
  // Auto-remove after 3 seconds
  setTimeout(() => {
    if (toast && toast.parentElement) {
      toast.style.animation = 'fade-out 0.2s ease-out';
      setTimeout(() => toast.remove(), 200);
    }
  }, 3000);
}

// =============================================================================
// INITIALIZATION - Runs when DOM is ready
// =============================================================================

document.addEventListener('DOMContentLoaded', async () => {
  console.log('========================================');
  console.log('Service Requests Admin - Page Loaded');
  console.log('========================================');
  console.log('ApiClient available:', typeof ApiClient !== 'undefined');
  console.log('Lucide available:', typeof lucide !== 'undefined');
  
  // Check authentication
  const token = localStorage.getItem('auth_token');
  const userData = localStorage.getItem('user');
  
  console.log('Auth token exists:', !!token);
  console.log('User data exists:', !!userData);
  
  if (!token) {
    console.log('No auth token found, redirecting to login');
    window.location.replace('/login.php');
    return;
  }
  
  if (userData) {
    try {
      const user = JSON.parse(userData);
      console.log('User:', user);
      
      if (user.role !== 'admin' && user.role !== 'staff') {
        alert('Access denied. Admin privileges required.');
        window.location.replace('/user-dashboard.php');
        return;
      }
    } catch (e) {
      console.error('Error parsing user data:', e);
      window.location.replace('/login.php');
      return;
    }
  }
  
  console.log('========================================');
  console.log('Authentication passed, loading data...');
  
  await loadCurrentUser();
  await loadTechnicians();
  await loadTickets();
  
  console.log('========================================');
  console.log('Initial Data Load Complete');
  console.log('Total tickets loaded:', allTickets.length);
  console.log('Ticket IDs:', allTickets.map(t => ({ id: t.id, type: typeof t.id, ticket_number: t.ticket_number })));
  console.log('First 3 tickets:', allTickets.slice(0, 3));
  console.log('========================================');
  
  setupEventListeners();
  
  // Auto-refresh every 30 seconds
  setInterval(() => {
    loadTickets();
  }, 30000);
  
  // Initialize Lucide icons
  console.log('Initializing Lucide icons...');
  if (typeof lucide !== 'undefined') {
    lucide.createIcons();
    console.log('Lucide icons initialized');
  } else {
    console.error('Lucide library not loaded!');
  }
  
  // Expose global functions after they're defined
  window.loadTickets = loadTickets;
  window.openDrawer = openDrawer;
});
