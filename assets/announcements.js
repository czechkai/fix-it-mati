// Announcements Page JavaScript

// State management
let selectedCategory = 'All';
let expandedId = null;
let discussionModal = { isOpen: false, announcementTitle: '' };
let announcements = [];

// Helper function to escape HTML
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Initialize the page
async function init() {
    // Show loading state
    const container = document.getElementById('announcementsList');
    if (container) {
        container.innerHTML = `
            <div class="p-12 text-center">
                <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mb-4"></div>
                <p class="text-slate-600 font-medium">Loading announcements...</p>
            </div>
        `;
    }
    
    try {
        // Load announcements from API
        const response = await ApiClient.get('/announcements');
        announcements = (response.data && Array.isArray(response.data.announcements)) ? response.data.announcements : 
                       (response.data && Array.isArray(response.data)) ? response.data : [];
        
        // If no announcements from API, use mock data
        if (announcements.length === 0) {
            loadMockAnnouncements();
        }
        
        renderAnnouncements();
    } catch (error) {
        console.error('Failed to load announcements:', error);
        UIHelpers.showError('Failed to load announcements. Using demo data.');
        
        // Fallback to mock data
        loadMockAnnouncements();
        renderAnnouncements();
    }
    
    attachEventListeners();
    
    // Initialize Lucide icons
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
}

// Load mock announcements (fallback)
function loadMockAnnouncements() {
    announcements = [
        {
            id: 1,
            title: "URGENT: Scheduled Water Interruption in Brgy. Central",
            category: "Water Supply",
            type: "Urgent",
            created_at: "2023-10-24T10:00:00Z",
            message: `Please be advised of a scheduled maintenance activity on October 28, 2023, from 8:00 AM to 5:00 PM. 
      
This will affect the following areas:
- Main Street Extension
- Purok 4, Brgy. Central
- City Hall Compound

Reason: Replacement of 600mm main valve. Residents are advised to store enough water for consumption.`,
            metadata: {
                author: "Mati Water District",
                priority: "high"
            }
        },
        {
            id: 2,
            title: "Advisory on Illegal Electrical Connections",
            category: "Electricity",
            type: "Warning",
            created_at: "2023-10-18T10:00:00Z",
            message: "The City Government warns against illegal tapping of electrical lines. Violators will face penalties under the Anti-Pilferage of Electricity Act.",
            metadata: {
                author: "Davao Light"
            }
        },
        {
            id: 3,
            title: "New Online Payment Partners: GCash & Maya",
            category: "Billing",
            type: "News",
            created_at: "2023-10-22T10:00:00Z",
            message: "Great news! You can now pay your utility bills directly through the FixItMati app using GCash and Maya. No need to visit the payment center.",
            metadata: {
                author: "City Treasurer's Office"
            }
        },
        {
            id: 4,
            title: "Power Line Maintenance: Purok 2",
            category: "Electricity",
            type: "Maintenance",
            created_at: "2023-10-25T05:00:00Z",
            message: "Routine vegetation clearing near power lines will be conducted. Expect brief power fluctuations.",
            metadata: {
                author: "Davao Light"
            }
        }
    ];
}

// Filter announcements based on selected category
function getFilteredAnnouncements() {
    return announcements.filter(item => {
        if (selectedCategory === 'All') return true;
        
        const category = (item.category || '').toLowerCase();
        const type = (item.type || '').toLowerCase();
        const priority = (item.priority || '').toLowerCase();
        
        // Match category filters
        if (selectedCategory === 'Water Supply' && (category === 'water' || category === 'water supply')) return true;
        if (selectedCategory === 'Electricity' && (category === 'electricity' || category === 'electric')) return true;
        
        // Match type/priority filters
        if (selectedCategory === 'Urgent' && (type === 'urgent' || priority === 'urgent' || priority === 'high')) return true;
        if (selectedCategory === 'News' && type === 'news') return true;
        
        return false;
    });
}

// Get badge styles based on type
function getTypeStyles(type) {
    if (!type) return 'news';
    type = type.toLowerCase();
    switch (type) {
        case 'urgent': return 'urgent';
        case 'maintenance': return 'maintenance';
        case 'news': return 'news';
        case 'warning': return 'warning';
        default: return 'news';
    }
}

// Get icon name based on category
function getIconName(category) {
    if (!category) return 'info';
    category = category.toLowerCase();
    switch (category) {
        case 'water':
        case 'water supply': 
            return 'droplets';
        case 'electricity':
        case 'electric':
            return 'zap';
        case 'urgent':
            return 'alert-circle';
        default: 
            return 'info';
    }
}

// Get icon color based on category
function getIconColor(category) {
    if (!category) return 'text-slate-500';
    category = category.toLowerCase();
    switch (category) {
        case 'water':
        case 'water supply': 
            return 'text-blue-500';
        case 'electricity':
        case 'electric':
            return 'text-amber-500';
        case 'urgent':
            return 'text-red-500';
        default: 
            return 'text-slate-500';
    }
}

// Initialize page
function init() {
    renderFilters();
    renderAnnouncements();
    attachEventListeners();
    
    // Initialize Lucide icons
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
}

// Render filter buttons
function renderFilters() {
    const filterButtons = document.querySelectorAll('.filter-btn');
    filterButtons.forEach(btn => {
        const value = btn.getAttribute('data-value');
        if (value === selectedCategory) {
            btn.classList.add('active');
        } else {
            btn.classList.remove('active');
        }
    });
}

// Render announcements list
function renderAnnouncements() {
    const container = document.getElementById('announcementsList');
    const filtered = getFilteredAnnouncements();
    
    // Update feed title
    const feedTitle = document.getElementById('feedTitle');
    if (feedTitle) {
        feedTitle.textContent = selectedCategory === 'All' ? 'Latest Announcements' : `${selectedCategory} Updates`;
    }
    
    // Update count
    const feedCount = document.getElementById('feedCount');
    if (feedCount) {
        feedCount.textContent = `Showing ${filtered.length} posts`;
    }
    
    if (!container) return;
    
    if (filtered.length === 0) {
        container.innerHTML = `
            <div class="empty-state">
                <i data-lucide="info" class="empty-state-icon" style="width: 32px; height: 32px;"></i>
                <p class="empty-state-text">No updates found for this category.</p>
            </div>
        `;
    } else {
        container.innerHTML = filtered.map(item => {
            const isExpanded = expandedId === item.id;
            const typeClass = getTypeStyles(item.type);
            const iconName = getIconName(item.category);
            const iconColor = getIconColor(item.category);
            
            // Calculate relative time
            const timeAgo = getTimeAgo(item.created_at);
            const formattedDate = UIHelpers.formatDate(item.created_at);
            const isPinned = (item.priority || '').toLowerCase() === 'urgent' || (item.priority || '').toLowerCase() === 'high' || (item.type || '').toLowerCase() === 'urgent';
            const displayType = (item.type || 'news').charAt(0).toUpperCase() + (item.type || 'news').slice(1);
            const author = item.author_name || item.metadata?.author || 'System';
            
            return `
                <div class="announcement-card ${isPinned ? 'pinned' : ''}" data-id="${item.id}">
                    <div class="announcement-header" onclick="toggleExpand('${item.id}')">
                        <div class="announcement-header-content">
                            <div class="announcement-info">
                                <div class="announcement-badges">
                                    <span class="type-badge ${typeClass}">${displayType}</span>
                                    ${isPinned ? '<span class="pinned-badge"><i data-lucide="alert-triangle" style="width: 10px; height: 10px;"></i> Pinned</span>' : ''}
                                    <span class="time-badge"><i data-lucide="clock" style="width: 12px; height: 12px;"></i> ${timeAgo}</span>
                                </div>
                                <h3 class="announcement-title">${item.title}</h3>
                                <div class="announcement-meta">
                                    <div class="announcement-meta-item">
                                        <i data-lucide="${iconName}" class="${iconColor}" style="width: 14px; height: 14px;"></i>
                                        <span>${(item.category || 'General').charAt(0).toUpperCase() + (item.category || 'General').slice(1)}</span>
                                    </div>
                                    <span>•</span>
                                    <span>${formattedDate}</span>
                                    <span>•</span>
                                    <span class="meta-author">${author}</span>
                                </div>
                            </div>
                            <button class="expand-btn ${isExpanded ? 'expanded' : ''}">
                                <i data-lucide="chevron-down" style="width: 16px; height: 16px;"></i>
                            </button>
                        </div>
                    </div>
                    <div class="announcement-body ${isExpanded ? 'expanded' : ''}">
                        <p class="announcement-text">${item.content || item.message || 'No details available.'}</p>
                        <div class="announcement-actions">
                            <button class="action-btn primary" onclick="markAsRead('${item.id}')">
                                <i data-lucide="check-circle-2" style="width: 14px; height: 14px;"></i>
                                Mark as Read
                            </button>
                            <button class="action-btn secondary" onclick="openDiscussion('${escapeHtml(item.title)}')">
                                <i data-lucide="message-square" style="width: 14px; height: 14px;"></i>
                                Discuss
                            </button>
                        </div>
                    </div>
                </div>
            `;
        }).join('');
    }
    
    // Reinitialize Lucide icons
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
}

// Calculate time ago
function getTimeAgo(dateString) {
    const now = new Date();
    const date = new Date(dateString);
    const seconds = Math.floor((now - date) / 1000);
    
    if (seconds < 60) return 'just now';
    if (seconds < 3600) return `${Math.floor(seconds / 60)} minutes ago`;
    if (seconds < 86400) return `${Math.floor(seconds / 3600)} hours ago`;
    if (seconds < 604800) return `${Math.floor(seconds / 86400)} days ago`;
    return UIHelpers.formatDate(dateString);
}

// Toggle expand/collapse announcement
function toggleExpand(id) {
    expandedId = expandedId === id ? null : id;
    renderAnnouncements();
}

// Mark announcement as read
async function markAsRead(id) {
    try {
        await NotificationsAPI.markAsRead(id);
        UIHelpers.showSuccess('Announcement marked as read');
        
        // Remove from list or update visually
        announcements = announcements.filter(a => a.id !== id);
        renderAnnouncements();
    } catch (error) {
        console.error('Failed to mark as read:', error);
        UIHelpers.showError('Failed to mark as read');
    }
}

// Open discussion modal
function openDiscussion(title) {
    alert(`Start discussion about: ${title}`);
    // In production, this would open a modal or redirect to discussion page
}

// Attach event listeners
function attachEventListeners() {
    // Filter buttons
    const filterButtons = document.querySelectorAll('.filter-btn');
    filterButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            selectedCategory = this.getAttribute('data-value');
            renderFilters();
            renderAnnouncements();
        });
    });
}

// Render filter buttons
function renderFilters() {
    const filterButtons = document.querySelectorAll('.filter-btn');
    filterButtons.forEach(btn => {
        const value = btn.getAttribute('data-value');
        if (value === selectedCategory) {
            btn.classList.add('active');
        } else {
            btn.classList.remove('active');
        }
    });
}

// Initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
} else {
    init();
}
