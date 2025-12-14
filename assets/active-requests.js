// Active Requests Page JavaScript

// State management
let selectedRequestId = null;
let activeRequests = [];
let autoRefreshInterval = null;

// Initialize the page
async function init() {
    console.log('[Active Requests] Initializing...');
    
    // Check authentication
    const token = localStorage.getItem('auth_token');
    console.log('[Active Requests] Auth token:', token ? 'Present' : 'Missing');
    console.log('[Active Requests] Token value:', token ? token.substring(0, 50) + '...' : 'null');
    
    if (!token) {
        console.error('[Active Requests] No auth token found');
        alert('Your session has expired. Please login again.');
        window.location.replace('/login.php');
        return;
    }
    
    // Load requests initially
    console.log('[Active Requests] Loading requests...');
    await loadRequests(false);
    
    // Attach event listeners
    console.log('[Active Requests] Attaching event listeners');
    attachEventListeners();
    console.log('[Active Requests] Initialization complete');
}

// Show empty state when no requests
function showEmptyState() {
    // Update header
    const panelHeader = document.getElementById('panelStats');
    if (panelHeader) {
        panelHeader.textContent = '0 Active';
    }
    
    const requestsList = document.getElementById('requestsList');
    if (requestsList) {
        requestsList.innerHTML = `
            <div style="padding: 40px 20px; text-align: center; color: #64748b;">
                <svg style="width: 48px; height: 48px; margin: 0 auto 16px; opacity: 0.3;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 2.69l5.66 5.66a8 8 0 1 1-11.31 0z"></path>
                </svg>
                <p style="font-size: 16px; font-weight: 500; margin-bottom: 8px;">No Active Requests</p>
                <p style="font-size: 14px; margin-bottom: 20px;">You don't have any pending or in-progress requests.</p>
                <a href="pages/user/create-request.php" style="display: inline-block; padding: 10px 20px; background: #3b82f6; color: white; border-radius: 8px; text-decoration: none; font-size: 14px; font-weight: 500;">Create New Request</a>
            </div>
        `;
    }
    
    // Hide detail panel when no requests
    const detailPanel = document.getElementById('detailPanel');
    if (detailPanel) {
        detailPanel.style.display = 'none';
    }
}

// SVG Icons as strings
const icons = {
    arrowLeft: '<svg class="icon icon-sm" viewBox="0 0 24 24"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>',
    menu: '<svg class="icon" viewBox="0 0 24 24"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>',
    hammer: '<svg class="icon" viewBox="0 0 24 24"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"></path></svg>',
    bell: '<svg class="icon" viewBox="0 0 24 24"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>',
    filter: '<svg class="icon" viewBox="0 0 24 24"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon></svg>',
    mapPin: '<svg class="icon icon-sm" viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>',
    calendar: '<svg class="icon icon-sm" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>',
    checkCircle: '<svg class="icon icon-xs icon-fill" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>',
    circle: '<svg class="icon icon-xs icon-fill" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"></circle></svg>',
    moreVertical: '<svg class="icon" viewBox="0 0 24 24"><circle cx="12" cy="12" r="1"></circle><circle cx="12" cy="5" r="1"></circle><circle cx="12" cy="19" r="1"></circle></svg>',
    user: '<svg class="icon" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>',
    messageSquare: '<svg class="icon" style="width: 14px; height: 14px;" viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>',
    phone: '<svg class="icon" style="width: 14px; height: 14px;" viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>',
    paperclip: '<svg class="icon" viewBox="0 0 24 24"><path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"></path></svg>',
    send: '<svg class="icon" viewBox="0 0 24 24"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg>',
    droplets: '<svg class="icon icon-xs" viewBox="0 0 24 24"><path d="M12 2.69l5.66 5.66a8 8 0 1 1-11.31 0z"></path></svg>',
    zap: '<svg class="icon icon-xs" viewBox="0 0 24 24"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon></svg>'
};

// Render the requests list
function renderRequestsList() {
    const listContainer = document.getElementById('requestsList');
    if (!listContainer) return;
    
    // Update panel header counts
    const panelHeader = document.getElementById('panelStats');
    if (panelHeader) {
        const activeCount = activeRequests.length;
        panelHeader.textContent = `${activeCount} Active`;
    }
    
    if (activeRequests.length === 0) {
        return; // Empty state already handled
    }
    
    listContainer.innerHTML = activeRequests.map(req => {
        const isActive = selectedRequestId === req.id;
        const categoryIcon = req.category === 'water' ? 
            '<svg class="icon icon-xs" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2.69l5.66 5.66a8 8 0 1 1-11.31 0z"></path></svg>' : 
            '<svg class="icon icon-xs" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon></svg>';
        
        const statusMap = {
            'pending': 'status-pending',
            'in_progress': 'status-in-progress',
            'completed': 'status-completed',
            'cancelled': 'status-cancelled'
        };
        const statusClass = statusMap[req.status] || 'status-pending';
        
        const statusDisplay = req.status.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase());
        const categoryDisplay = req.category.charAt(0).toUpperCase() + req.category.slice(1);
        
        const formattedDate = formatDate(req.created_at);
        
        return `
            <div class="request-card ${isActive ? 'active' : ''}" data-request-id="${req.id}">
                <div class="request-card-header">
                    <span class="ticket-number">#${req.id.substring(0, 8)}</span>
                    <span class="status-badge ${statusClass}">${statusDisplay}</span>
                </div>
                <h3 class="request-title">${escapeHtml(req.title)}</h3>
                <div class="request-meta">
                    ${categoryIcon}
                    <span>${categoryDisplay}</span>
                    <span>•</span>
                    <span>${formattedDate}</span>
                </div>
            </div>
        `;
    }).join('');
}

// Helper function to format dates
function formatDate(dateString) {
    const date = new Date(dateString);
    const options = { month: 'short', day: 'numeric', year: 'numeric' };
    return date.toLocaleDateString('en-US', options);
}

// Helper function to escape HTML
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Render the detail view
function renderDetailView() {
    const currentRequest = activeRequests.find(r => r.id === selectedRequestId);
    if (!currentRequest) return;
    
    // Show the detail panel
    const detailPanel = document.getElementById('detailPanel');
    if (detailPanel) {
        detailPanel.style.display = 'flex';
    }
    
    const mapIcon = '<svg class="icon icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>';
    const calendarIcon = '<svg class="icon icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>';
    
    // Update header
    const titleEl = document.getElementById('detailTitle');
    const statusEl = document.getElementById('detailStatus');
    const locationEl = document.getElementById('detailLocation');
    const dateEl = document.getElementById('detailDate');
    
    if (titleEl) titleEl.textContent = currentRequest.title;
    if (statusEl) {
        statusEl.textContent = currentRequest.status.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase());
        statusEl.className = 'detail-status-badge';
    }
    if (locationEl) locationEl.innerHTML = `${mapIcon} ${escapeHtml(currentRequest.location || 'Not specified')}`;
    if (dateEl) dateEl.innerHTML = `${calendarIcon} Filed: ${formatDate(currentRequest.created_at)}`;
    
    // Update timeline - Generate timeline based on status
    const timelineContainer = document.getElementById('timeline');
    if (timelineContainer) {
        const checkCircle = '<svg class="icon icon-xs icon-fill" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>';
        const circle = '<svg class="icon icon-xs icon-fill" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle></svg>';
        
        // Generate timeline based on request status
        const timeline = generateTimeline(currentRequest);
        
        timelineContainer.innerHTML = timeline.map((item, index) => {
            const icon = item.completed ? checkCircle : circle;
            const itemClass = item.current ? 'timeline-item current' : 'timeline-item' + (item.completed ? ' completed' : '');
            
            return `
                <div class="${itemClass}">
                    <div class="timeline-marker">${icon}</div>
                    <div class="timeline-content">
                        <p class="timeline-text">${item.status}</p>
                        <p class="timeline-date">${item.date}</p>
                    </div>
                </div>
            `;
        }).join('');
    }
    
    // Update assigned technician
    const techName = document.getElementById('technicianName');
    if (techName) {
        if (currentRequest.assigned_technician_name) {
            techName.textContent = currentRequest.assigned_technician_name;
        } else {
            techName.textContent = 'Awaiting Assignment';
        }
    }
    
    // Update organization based on category
    const orgName = document.getElementById('organizationName');
    if (orgName) {
        const category = currentRequest.category?.toLowerCase();
        if (category === 'water') {
            orgName.textContent = 'Mati Water District';
        } else if (category === 'electricity') {
            orgName.textContent = 'Davao Oriental Electric Cooperative (DORECO)';
        } else {
            orgName.textContent = 'Municipal Engineering Office';
        }
    }
    
    // Update description
    const descEl = document.getElementById('issueDescription');
    if (descEl) descEl.textContent = currentRequest.description || 'No description provided.';
}

// Generate timeline based on request status
function generateTimeline(request) {
    const timeline = [];
    const createdDate = formatDate(request.created_at);
    const updatedDate = request.updated_at ? formatDate(request.updated_at) : createdDate;
    
    // Request submitted - always completed
    timeline.push({
        status: 'Request Submitted',
        date: createdDate,
        completed: true,
        current: false
    });
    
    if (request.status === 'pending') {
        timeline.push({
            status: 'Pending Review',
            date: createdDate,
            completed: true,
            current: true
        });
        timeline.push({
            status: 'Technician Assignment',
            date: 'Pending',
            completed: false,
            current: false
        });
        timeline.push({
            status: 'In Progress',
            date: 'Pending',
            completed: false,
            current: false
        });
        timeline.push({
            status: 'Issue Resolved',
            date: 'Pending',
            completed: false,
            current: false
        });
    } else if (request.status === 'in_progress') {
        timeline.push({
            status: 'Technician Assigned',
            date: updatedDate,
            completed: true,
            current: false
        });
        timeline.push({
            status: 'Work in Progress',
            date: updatedDate,
            completed: true,
            current: true
        });
        timeline.push({
            status: 'Issue Resolved',
            date: 'Pending',
            completed: false,
            current: false
        });
    } else if (request.status === 'completed') {
        timeline.push({
            status: 'Technician Assigned',
            date: updatedDate,
            completed: true,
            current: false
        });
        timeline.push({
            status: 'Work Completed',
            date: updatedDate,
            completed: true,
            current: false
        });
        timeline.push({
            status: 'Issue Resolved',
            date: request.completed_at ? formatDate(request.completed_at) : updatedDate,
            completed: true,
            current: true
        });
    } else if (request.status === 'cancelled') {
        timeline.push({
            status: 'Request Cancelled',
            date: updatedDate,
            completed: true,
            current: true
        });
    }
    
    return timeline;
}

// Attach event listeners
function attachEventListeners() {
    // Request card clicks
    const requestsList = document.getElementById('requestsList');
    if (requestsList) {
        requestsList.addEventListener('click', function(e) {
            const card = e.target.closest('.request-card');
            if (card) {
                const requestId = card.getAttribute('data-request-id');
                if (requestId !== selectedRequestId) {
                    selectedRequestId = requestId;
                    renderRequestsList();
                    renderDetailView();
                }
            }
        });
    }
    
    // Menu button (mobile)
    const menuBtn = document.getElementById('menuBtn');
    if (menuBtn) {
        menuBtn.addEventListener('click', function() {
            // Would open mobile menu
            console.log('Open mobile menu');
        });
    }
    
    // Filter button
    const filterBtn = document.getElementById('filterBtn');
    if (filterBtn) {
        filterBtn.addEventListener('click', function() {
            // Would open filter options
            console.log('Open filter modal');
        });
    }
    
    // Notification button
    const notificationBtn = document.getElementById('notificationBtn');
    if (notificationBtn) {
        notificationBtn.addEventListener('click', function() {
            window.location.href = 'announcements.php';
        });
    }
    
    // More options button
    const moreBtn = document.getElementById('moreBtn');
    if (moreBtn) {
        moreBtn.addEventListener('click', function() {
            const menu = `
Request Actions:
1. View Details
2. Add Comment  
3. Request Update
4. Cancel Request

Select an option:`;
            const choice = prompt(menu);
            if (choice) {
                handleRequestAction(choice);
            }
        });
    }
    
    // Send message button
    const sendBtn = document.getElementById('sendBtn');
    const messageInput = document.getElementById('messageInput');
    if (sendBtn && messageInput) {
        sendBtn.addEventListener('click', function() {
            const message = messageInput.value.trim();
            if (message) {
                alert(`Message sent: "${message}"\n\nYour message has been forwarded to the assigned technician.`);
                messageInput.value = '';
            } else {
                alert('Please enter a message');
            }
        });
        
        // Also send on Enter key
        messageInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                sendBtn.click();
            }
        });
    }
}

// Handle request actions
function handleRequestAction(choice) {
    const currentRequest = activeRequests.find(r => r.id === selectedRequestId);
    if (!currentRequest) return;
    
    switch(choice) {
        case '1':
            alert(`Request Details:\n\nID: ${currentRequest.id}\nTitle: ${currentRequest.title}\nStatus: ${currentRequest.status}\nCategory: ${currentRequest.category}\nLocation: ${currentRequest.location}`);
            break;
        case '2':
            const comment = prompt('Enter your comment:');
            if (comment) {
                alert('Comment added successfully!');
            }
            break;
        case '3':
            alert('Update request sent to technician. You will be notified of any changes.');
            break;
        case '4':
            if (confirm('Are you sure you want to cancel this request?')) {
                alert('Request cancelled successfully.');
                window.location.href = 'user-dashboard.php';
            }
            break;
    }
}

// Initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        init();
        startAutoRefresh();
    });
} else {
    init();
    startAutoRefresh();
}

// Start auto-refresh for real-time updates
function startAutoRefresh() {
    // Auto-refresh every 30 seconds
    autoRefreshInterval = setInterval(async () => {
        await loadRequests(true); // Silent reload without showing loading state
    }, 30000);
}

// Load requests from API (can be silent for auto-refresh)
async function loadRequests(silent = false) {
    try {
        const token = localStorage.getItem('auth_token');
        if (!token) {
            window.location.replace('/login.php');
            return;
        }

        // Show loading state only if not silent
        if (!silent) {
            const requestsList = document.getElementById('requestsList');
            if (requestsList) {
                requestsList.innerHTML = `
                    <div style="padding: 40px 20px; text-align: center; color: #64748b;">
                        <div style="width: 48px; height: 48px; margin: 0 auto 16px; border: 3px solid #e2e8f0; border-top-color: #3b82f6; border-radius: 50%; animation: spin 1s linear infinite;"></div>
                        <p style="font-size: 16px; font-weight: 500;">Loading requests...</p>
                    </div>
                `;
            }
        }

        // Load requests from API
        const response = await ApiClient.requests.getAll();
        
        console.log('API Response:', response);
        console.log('Response.data:', response.data);
        console.log('Response.data.requests:', response.data?.requests);
        console.log('Is Array?:', Array.isArray(response.data?.requests));
        
        // Check if we have a valid response with requests array
        if (response.success && response.data) {
            const requestsArray = response.data.requests || [];
            
            console.log('Requests array:', requestsArray);
            console.log('Requests count:', requestsArray.length);
            
            // Store previous selected ID
            const previouslySelectedId = selectedRequestId;
            
            // Filter for active requests (pending, in_progress)
            activeRequests = requestsArray.filter(r => 
                r.status === 'pending' || r.status === 'in_progress'
            );
            
            console.log('Active requests filtered:', activeRequests.length);
            console.log('Active requests:', activeRequests);
            
            // Maintain selection if still exists, otherwise select first
            if (activeRequests.length > 0) {
                const stillExists = activeRequests.find(r => r.id === previouslySelectedId);
                selectedRequestId = stillExists ? previouslySelectedId : activeRequests[0].id;
                renderRequestsList();
                renderDetailView();
            } else {
                selectedRequestId = null;
                showEmptyState();
            }
        } else {
            console.error('Invalid response structure:', response);
            if (!silent) {
                showEmptyState();
            }
        }
    } catch (error) {
        console.error('Failed to load requests:', error);
        if (!silent) {
            showEmptyState();
        }
    }
}

// Cleanup on page unload
window.addEventListener('beforeunload', function() {
    if (autoRefreshInterval) {
        clearInterval(autoRefreshInterval);
    }
});
