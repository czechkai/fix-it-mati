// Announcements Page JavaScript

// State management
let selectedCategory = 'All';
let expandedId = 1;
let discussionModal = { isOpen: false, announcementTitle: '' };

// Mock Data
const announcements = [
    {
        id: 1,
        title: "URGENT: Scheduled Water Interruption in Brgy. Central",
        category: "Water Supply",
        type: "Urgent",
        date: "Oct 24, 2023",
        time: "2 hours ago",
        author: "Mati Water District",
        content: `Please be advised of a scheduled maintenance activity on October 28, 2023, from 8:00 AM to 5:00 PM. 
      
This will affect the following areas:
- Main Street Extension
- Purok 4, Brgy. Central
- City Hall Compound

Reason: Replacement of 600mm main valve. Residents are advised to store enough water for consumption.`,
        pinned: true
    },
    {
        id: 2,
        title: "Advisory on Illegal Electrical Connections",
        category: "Electricity",
        type: "Warning",
        date: "Oct 18, 2023",
        time: "1 week ago",
        author: "Davao Light",
        content: "The City Government warns against illegal tapping of electrical lines. Violators will face penalties under the Anti-Pilferage of Electricity Act.",
        pinned: false
    },
    {
        id: 3,
        title: "New Online Payment Partners: GCash & Maya",
        category: "Billing",
        type: "News",
        date: "Oct 22, 2023",
        time: "2 days ago",
        author: "City Treasurer's Office",
        content: "Great news! You can now pay your utility bills directly through the FixItMati app using GCash and Maya. No need to visit the payment center.",
        pinned: false
    },
    {
        id: 4,
        title: "Power Line Maintenance: Purok 2",
        category: "Electricity",
        type: "Maintenance",
        date: "Oct 25, 2023",
        time: "5 hours ago",
        author: "Davao Light",
        content: "Routine vegetation clearing near power lines will be conducted. Expect brief power fluctuations.",
        pinned: false
    }
];

// Filter announcements based on selected category
function getFilteredAnnouncements() {
    return announcements.filter(item => {
        if (selectedCategory === 'All') return true;
        if (selectedCategory === 'Water Supply' && item.category === 'Water Supply') return true;
        if (selectedCategory === 'Electricity' && item.category === 'Electricity') return true;
        if (['Urgent', 'News'].includes(selectedCategory) && item.type === selectedCategory) return true;
        return false;
    });
}

// Get badge styles based on type
function getTypeStyles(type) {
    switch (type) {
        case 'Urgent': return 'urgent';
        case 'Maintenance': return 'maintenance';
        case 'News': return 'news';
        case 'Warning': return 'warning';
        default: return '';
    }
}

// Get icon name based on category
function getIconName(category) {
    switch (category) {
        case 'Water Supply': return 'droplets';
        case 'Electricity': return 'zap';
        default: return 'info';
    }
}

// Get icon color based on category
function getIconColor(category) {
    switch (category) {
        case 'Water Supply': return 'text-blue-500';
        case 'Electricity': return 'text-amber-500';
        default: return 'text-slate-500';
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
    feedTitle.textContent = selectedCategory === 'All' ? 'Latest Announcements' : `${selectedCategory} Updates`;
    
    // Update count
    const feedCount = document.getElementById('feedCount');
    feedCount.textContent = `Showing ${filtered.length} posts`;
    
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
            
            return `
                <div class="announcement-card ${item.pinned ? 'pinned' : ''}" data-id="${item.id}">
                    <div class="announcement-header" onclick="toggleExpand(${item.id})">
                        <div class="announcement-header-content">
                            <div class="announcement-info">
                                <div class="announcement-badges">
                                    <span class="type-badge ${typeClass}">${item.type}</span>
                                    ${item.pinned ? '<span class="pinned-badge"><i data-lucide="alert-triangle" style="width: 10px; height: 10px;"></i> Pinned</span>' : ''}
                                    <span class="time-badge"><i data-lucide="clock" style="width: 12px; height: 12px;"></i> ${item.time}</span>
                                </div>
                                <h3 class="announcement-title">${item.title}</h3>
                                <div class="announcement-meta">
                                    <span class="announcement-author">
                                        <i data-lucide="${iconName}" class="${iconColor}" style="width: 16px; height: 16px;"></i>
                                        ${item.author}
                                    </span>
                                    <span>•</span>
                                    <span>${item.date}</span>
                                </div>
                            </div>
                            <div class="chevron-icon">
                                <i data-lucide="${isExpanded ? 'chevron-up' : 'chevron-down'}" style="width: 20px; height: 20px;"></i>
                            </div>
                        </div>
                    </div>
                    ${isExpanded ? `
                        <div class="announcement-body animate-slide-in">
                            <div class="announcement-content-wrapper">
                                <div class="announcement-content">${item.content}</div>
                                <div class="discussion-area">
                                    <div class="discussion-info">
                                        <i data-lucide="info" class="discussion-info-icon" style="width: 16px; height: 16px;"></i>
                                        <p class="discussion-info-text">
                                            <strong>Comments are turned off.</strong><br/>
                                            Have a question or concern about this advisory? Start a thread in the community discussions.
                                        </p>
                                    </div>
                                    <button class="discuss-btn" onclick="openDiscussionModal('${item.title.replace(/'/g, "\\'")}')">
                                        <i data-lucide="message-square" style="width: 14px; height: 14px;"></i> Discuss in Community
                                    </button>
                                </div>
                            </div>
                        </div>
                    ` : ''}
                </div>
            `;
        }).join('');
    }
    
    // Reinitialize Lucide icons
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
}

// Toggle expand/collapse announcement
function toggleExpand(id) {
    expandedId = expandedId === id ? null : id;
    renderAnnouncements();
}

// Open discussion modal
function openDiscussionModal(title) {
    discussionModal = { isOpen: true, announcementTitle: title };
    renderModal();
}

// Close discussion modal
function closeDiscussionModal() {
    discussionModal = { isOpen: false, announcementTitle: '' };
    renderModal();
}

// Submit discussion
function submitDiscussion() {
    const textarea = document.getElementById('discussionMessage');
    const message = textarea ? textarea.value.trim() : '';
    
    if (message) {
        alert('Your query has been posted to the Discussions tab!');
        closeDiscussionModal();
    } else {
        alert('Please enter your message.');
    }
}

// Render modal
function renderModal() {
    const modalContainer = document.getElementById('discussionModal');
    
    if (discussionModal.isOpen) {
        modalContainer.innerHTML = `
            <div class="modal-overlay animate-zoom-in">
                <div class="modal-backdrop" onclick="closeDiscussionModal()"></div>
                <div class="modal-content">
                    <div class="modal-header">
                        <h3 class="modal-title">
                            <i data-lucide="message-square" class="text-green-600" style="width: 18px; height: 18px;"></i>
                            Start New Discussion
                        </h3>
                        <button class="modal-close" onclick="closeDiscussionModal()">
                            <i data-lucide="x" style="width: 20px; height: 20px;"></i>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="modal-field">
                            <label class="modal-label">Topic Reference</label>
                            <div class="modal-reference">
                                Re: ${discussionModal.announcementTitle}
                            </div>
                        </div>
                        <div class="modal-field">
                            <label class="modal-label">Your Query / Message</label>
                            <textarea 
                                id="discussionMessage"
                                rows="4"
                                class="modal-textarea"
                                placeholder="e.g., Will this water interruption affect Purok 5 as well?"
                            ></textarea>
                            <p class="modal-hint">This will be posted publicly in the Discussions tab.</p>
                        </div>
                        <div class="modal-actions">
                            <button class="modal-btn modal-btn-cancel" onclick="closeDiscussionModal()">
                                Cancel
                            </button>
                            <button class="modal-btn modal-btn-submit" onclick="submitDiscussion()">
                                <i data-lucide="send" style="width: 16px; height: 16px;"></i> Post Discussion
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        modalContainer.classList.remove('hidden');
        
        // Reinitialize icons
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    } else {
        modalContainer.innerHTML = '';
        modalContainer.classList.add('hidden');
    }
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

// Make functions globally accessible
window.toggleExpand = toggleExpand;
window.openDiscussionModal = openDiscussionModal;
window.closeDiscussionModal = closeDiscussionModal;
window.submitDiscussion = submitDiscussion;

// Initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
} else {
    init();
}
