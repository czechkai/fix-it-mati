// Active Requests Page JavaScript

// State management
let selectedRequestId = null;
let activeRequests = [];

// Initialize the page
async function init() {
    try {
        // Load requests from API
        const response = await RequestsAPI.getAll({ status: 'active,in-progress,pending' });
        activeRequests = response.data || [];
        
        // Select first request if available
        if (activeRequests.length > 0) {
            selectedRequestId = activeRequests[0].id;
        }
        
        renderRequestsList();
        renderDetailView();
    } catch (error) {
        console.error('Failed to load requests:', error);
        UIHelpers.showError('Failed to load requests. Using demo data.');
        
        // Fallback to mock data
        loadMockData();
        renderRequestsList();
        renderDetailView();
    }
    
    attachEventListeners();
}

// Fallback mock data
function loadMockData() {
    activeRequests = [
        {
            id: 101,
            title: "Leaking Pipe - Main Street Extension",
            category: "water",
            status: "in-progress",
            created_at: "2023-10-20T10:00:00Z",
            location: "123 Main St. Ext, Brgy. Central, Mati City",
            description: "Severe water leak observed near the sidewalk. Water is pooling on the road. Suspected main line burst.",
            assigned_to: "Juan Dela Cruz (Team A)",
            estimated_completion: "2023-10-24T17:00:00Z",
            timeline: [
                { status: "Request Submitted", date: "Oct 20, 10:00 AM", completed: true },
                { status: "Received by Water Dept", date: "Oct 20, 10:15 AM", completed: true },
                { status: "Technician Assigned", date: "Oct 21, 08:30 AM", completed: true },
                { status: "Inspection in Progress", date: "Oct 21, 09:45 AM", completed: true, current: true },
                { status: "Repair Scheduled", date: "Pending", completed: false },
                { status: "Issue Resolved", date: "Pending", completed: false }
            ]
        },
        {
            id: 102,
            title: "No Electricity - Purok 4",
            category: "electricity",
            status: "pending",
            created_at: "2023-10-22T08:00:00Z",
            location: "Purok 4, Brgy. Dahican",
            description: "Whole street has no power since last night's storm.",
            assigned_to: null,
            estimated_completion: null,
            timeline: [
                { status: "Request Submitted", date: "Oct 22, 08:00 AM", completed: true },
                { status: "Pending Review", date: "Oct 22, 08:05 AM", completed: true, current: true },
                { status: "Technician Assigned", date: "Pending", completed: false },
                { status: "Issue Resolved", date: "Pending", completed: false }
            ]
        }
    ];
    selectedRequestId = 101;
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

// Initialize the page
function init() {
    renderRequestsList();
    renderDetailView();
    attachEventListeners();
}

// Render the requests list
function renderRequestsList() {
    const listContainer = document.getElementById('requestsList');
    if (!listContainer) return;
    
    listContainer.innerHTML = activeRequests.map(req => {
        const isActive = selectedRequestId === req.id;
        const categoryIcon = req.category === 'water' ? '<svg class="icon icon-xs" viewBox="0 0 24 24"><path d="M12 2.69l5.66 5.66a8 8 0 1 1-11.31 0z"></path></svg>' : 
                             '<svg class="icon icon-xs" viewBox="0 0 24 24"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon></svg>';
        
        const statusMap = {
            'pending': 'status-pending',
            'in-progress': 'status-in-progress',
            'completed': 'status-completed'
        };
        const statusClass = statusMap[req.status] || 'status-pending';
        
        const statusDisplay = req.status.replace('-', ' ').replace(/\b\w/g, l => l.toUpperCase());
        const categoryDisplay = req.category.charAt(0).toUpperCase() + req.category.slice(1);
        
        return `
            <div class="request-card ${isActive ? 'active' : ''}" data-request-id="${req.id}">
                <div class="request-card-header">
                    <span class="ticket-number">SR-2023-${req.id}</span>
                    <span class="status-badge ${statusClass}">${statusDisplay}</span>
                </div>
                <h3 class="request-title">${req.title}</h3>
                <div class="request-meta">
                    ${categoryIcon}
                    <span>${categoryDisplay}</span>
                    <span>•</span>
                    <span>${new Date(req.created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}</span>
                </div>
            </div>
        `;
    }).join('');
}

// Render the detail view
function renderDetailView() {
    const currentRequest = activeRequests.find(r => r.id === selectedRequestId);
    if (!currentRequest) return;
    
    const mapIcon = '<svg class="icon icon-sm" viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>';
    const calendarIcon = '<svg class="icon icon-sm" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>';
    
    // Update header
    const titleEl = document.getElementById('detailTitle');
    const statusEl = document.getElementById('detailStatus');
    const locationEl = document.getElementById('detailLocation');
    const dateEl = document.getElementById('detailDate');
    
    if (titleEl) titleEl.textContent = currentRequest.title;
    if (statusEl) statusEl.textContent = currentRequest.status.replace('-', ' ').replace(/\b\w/g, l => l.toUpperCase());
    if (locationEl) locationEl.innerHTML = `${mapIcon} ${currentRequest.location}`;
    if (dateEl) dateEl.innerHTML = `${calendarIcon} Filed: ${new Date(currentRequest.created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}`;
    
    // Update timeline
    const timelineContainer = document.getElementById('timeline');
    if (timelineContainer && currentRequest.timeline) {
        const checkCircle = '<svg class="icon icon-xs icon-fill" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>';
        const circle = '<svg class="icon icon-xs icon-fill" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"></circle></svg>';
        
        timelineContainer.innerHTML = currentRequest.timeline.map((step, index) => {
            let dotClass = 'pending';
            let dotIcon = circle;
            let contentClass = '';
            
            if (step.completed) {
                dotClass = 'completed';
                dotIcon = checkCircle;
            } else if (step.current) {
                dotClass = 'current';
                dotIcon = circle;
            } else {
                contentClass = 'faded';
            }
            
            const noteHtml = step.current ? `
                <div class="timeline-note">
                    Technician is currently assessing the damage at the site. Please keep lines open.
                </div>
            ` : '';
            
            return `
                <div class="timeline-item ${contentClass}">
                    <div class="timeline-dot ${dotClass}">
                        ${dotIcon}
                    </div>
                    <div class="timeline-content">
                        <span class="timeline-status">${step.status}</span>
                        <span class="timeline-date">${step.date}</span>
                        ${noteHtml}
                    </div>
                </div>
            `;
        }).join('');
    }
    
    // Update assigned technician
    const techName = document.getElementById('technicianName');
    if (techName) techName.textContent = currentRequest.assigned_to || 'Unassigned';
    
    // Update description
    const descEl = document.getElementById('issueDescription');
    if (descEl) descEl.textContent = currentRequest.description;
}

// Attach event listeners
function attachEventListeners() {
    // Request card clicks
    document.getElementById('requestsList').addEventListener('click', function(e) {
        const card = e.target.closest('.request-card');
        if (card) {
            const requestId = parseInt(card.getAttribute('data-request-id'));
            if (requestId !== selectedRequestId) {
                selectedRequestId = requestId;
                renderRequestsList();
                renderDetailView();
            }
        }
    });
    
    // Menu button (mobile)
    document.getElementById('menuBtn').addEventListener('click', function() {
        // Toggle mobile menu
        alert('Toggle mobile menu');
    });
    
    // Filter button
    document.getElementById('filterBtn').addEventListener('click', function() {
        // Open filter modal
        alert('Open filter modal');
    });
    
    // Notification button
    document.getElementById('notificationBtn').addEventListener('click', function() {
        // Open notifications
        alert('Open notifications');
    });
    
    // More options button
    document.getElementById('moreBtn').addEventListener('click', function() {
        // Open options menu
        alert('Open options menu');
    });
    
    // Message button
    document.getElementById('messageBtn').addEventListener('click', function() {
        alert('Open message dialog');
    });
    
    // Call button
    document.getElementById('callBtn').addEventListener('click', function() {
        alert('Initiate call');
    });
    
    // Send message button
    document.getElementById('sendBtn').addEventListener('click', function() {
        const input = document.getElementById('messageInput');
        const message = input.value.trim();
        if (message) {
            alert('Message sent: ' + message);
            input.value = '';
        }
    });
    
    // Message input - send on Enter
    document.getElementById('messageInput').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            document.getElementById('sendBtn').click();
        }
    });
}

// Initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
} else {
    init();
}
