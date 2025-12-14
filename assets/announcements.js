// Announcements Page JavaScript

// State management
let selectedCategory = 'All';
let expandedId = null;
let discussionModal = { isOpen: false, announcementTitle: '' };
let announcements = [];
let pollingInterval = null;

// Helper function to escape HTML
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Load announcements from API
async function loadAnnouncements() {
    try {
        const response = await ApiClient.get('/announcements');
        const newAnnouncements = (response.data && Array.isArray(response.data.announcements)) ? response.data.announcements : 
                                (response.data && Array.isArray(response.data)) ? response.data : [];
        
        // Only update if there are changes
        if (JSON.stringify(newAnnouncements) !== JSON.stringify(announcements)) {
            announcements = newAnnouncements;
            renderAnnouncements();
        }
        
        return true;
    } catch (error) {
        console.error('Failed to load announcements:', error);
        return false;
    }
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
    
    // Load initial announcements
    const success = await loadAnnouncements();
    
    if (!success || announcements.length === 0) {
        // If no announcements from API, use mock data
        loadMockAnnouncements();
        renderAnnouncements();
    }
    
    attachEventListeners();
    
    // Initialize Lucide icons
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
    
    // Start real-time polling (refresh every 10 seconds)
    startPolling();
}

// Start polling for new announcements
function startPolling() {
    // Clear any existing interval
    if (pollingInterval) {
        clearInterval(pollingInterval);
    }
    
    // Poll every 10 seconds (10000 milliseconds)
    pollingInterval = setInterval(async () => {
        await loadAnnouncements();
    }, 10000);
}

// Stop polling (cleanup)
function stopPolling() {
    if (pollingInterval) {
        clearInterval(pollingInterval);
        pollingInterval = null;
    }
}

// Cleanup on page unload
window.addEventListener('beforeunload', stopPolling);

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
                <div class="announcement-card ${isPinned ? 'pinned' : ''}" data-id="${item.id}" onclick="toggleExpand('${item.id}')">
                    <div class="announcement-header">
                        <div class="announcement-header-content">
                            <div class="announcement-info">
                                <div class="announcement-badges">
                                    <span class="type-badge ${typeClass}">${displayType}</span>
                                    ${isPinned ? '<span class="pinned-badge"><i data-lucide="alert-triangle" style="width: 10px; height: 10px;"></i> Urgent</span>' : ''}
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
                                    <span class="meta-author">By ${author}</span>
                                </div>
                            </div>
                            <button class="expand-btn ${isExpanded ? 'expanded' : ''}" onclick="event.stopPropagation(); toggleExpand('${item.id}')">
                                <i data-lucide="chevron-down" style="width: 20px; height: 20px;"></i>
                            </button>
                        </div>
                    </div>
                    <div class="announcement-body ${isExpanded ? 'expanded' : ''}">
                        <p class="announcement-text">${escapeHtml(item.content || item.message || 'No details available.')}</p>
                        
                        <!-- Comment Form -->
                        <div class="comment-form-section">
                            <div class="comment-info">
                                <i data-lucide="info" style="width: 16px; height: 16px;" class="text-blue-500"></i>
                                <span>Your comment will create a discussion thread for this announcement</span>
                            </div>
                            <form class="comment-form" onsubmit="event.preventDefault(); event.stopPropagation(); submitComment('${item.id}', this);" data-announcement-id="${item.id}">
                                <textarea 
                                    class="comment-textarea" 
                                    name="comment" 
                                    placeholder="Share your thoughts or ask questions about this announcement..." 
                                    rows="3"
                                    onclick="event.stopPropagation();"
                                    required
                                ></textarea>
                                <div class="comment-form-actions">
                                    <button type="submit" class="action-btn primary" onclick="event.stopPropagation();">
                                        <i data-lucide="send" style="width: 16px; height: 16px;"></i>
                                        <span>Post Comment</span>
                                    </button>
                                    <button type="button" class="action-btn secondary" onclick="event.stopPropagation(); shareAnnouncement('${item.id}', '${escapeHtml(item.title)}')">
                                        <i data-lucide="share-2" style="width: 16px; height: 16px;"></i>
                                        <span>Share</span>
                                    </button>
                                </div>
                            </form>
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
    
    // Smooth scroll to expanded card
    if (expandedId) {
        setTimeout(() => {
            const card = document.querySelector(`[data-id="${id}"]`);
            if (card) {
                card.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }
        }, 100);
    }
}

// Submit comment for announcement
async function submitComment(announcementId, form) {
    const formData = new FormData(form);
    const comment = formData.get('comment');
    
    if (!comment || comment.trim() === '') {
        UIHelpers.showError('Please enter a comment');
        return;
    }
    
    // Show loading state
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<div class="inline-block animate-spin rounded-full h-4 w-4 border-b-2 border-white"></div> Posting...';
    
    try {
        const response = await ApiClient.post('/announcements/comments', {
            announcement_id: announcementId,
            comment: comment.trim()
        });
        
        if (response.success) {
            // Clear the form
            form.reset();
            
            // Restore button
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
            
            // Show modal with options
            showCommentSuccessModal(response.data.discussion_id);
        } else {
            throw new Error(response.message || 'Failed to post comment');
        }
    } catch (error) {
        console.error('Error posting comment:', error);
        UIHelpers.showError(error.message || 'Failed to post comment');
        
        // Restore button
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    }
}

// Show comment success modal
function showCommentSuccessModal(discussionId) {
    // Create modal overlay
    const modal = document.createElement('div');
    modal.className = 'fixed inset-0 z-50 flex items-center justify-center p-4 animate-fade-in';
    modal.innerHTML = `
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeCommentModal()"></div>
        <div class="relative bg-white rounded-xl shadow-2xl max-w-md w-full animate-zoom-in">
            <!-- Header -->
            <div class="p-6 border-b border-slate-100">
                <div class="flex items-center gap-3">
                    <div class="flex-shrink-0 w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                        <i data-lucide="check-circle" class="w-6 h-6 text-green-600"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-slate-900">Comment Posted!</h3>
                        <p class="text-sm text-slate-600">Your comment has been added to the discussion</p>
                    </div>
                </div>
            </div>
            
            <!-- Body -->
            <div class="p-6">
                <p class="text-slate-700 mb-4">
                    Your comment has been successfully posted. Would you like to view the discussion thread or continue browsing announcements?
                </p>
                
                <!-- Actions -->
                <div class="flex flex-col sm:flex-row gap-3">
                    <button 
                        onclick="window.location.href='discussions.php?id=${discussionId}'" 
                        class="flex-1 bg-blue-600 text-white px-4 py-3 rounded-lg font-medium hover:bg-blue-700 transition-colors flex items-center justify-center gap-2">
                        <i data-lucide="message-square" style="width: 18px; height: 18px;"></i>
                        <span>View Discussion</span>
                    </button>
                    <button 
                        onclick="closeCommentModal()" 
                        class="flex-1 bg-slate-100 text-slate-700 px-4 py-3 rounded-lg font-medium hover:bg-slate-200 transition-colors">
                        Stay Here
                    </button>
                </div>
            </div>
        </div>
    `;
    
    modal.id = 'commentSuccessModal';
    document.body.appendChild(modal);
    
    // Initialize Lucide icons in modal
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
    
    // Prevent body scroll
    document.body.style.overflow = 'hidden';
}

// Close comment success modal
function closeCommentModal() {
    const modal = document.getElementById('commentSuccessModal');
    if (modal) {
        modal.remove();
        document.body.style.overflow = '';
    }
}

// Share announcement - show modal with social options
function shareAnnouncement(id, title) {
    const announcement = announcements.find(a => a.id === id);
    const baseUrl = window.location.origin + '/public/announcements.php?id=' + id;
    const excerpt = announcement ? (announcement.content || announcement.message || '').substring(0, 150) : '';
    
    // Create share modal
    const modal = document.createElement('div');
    modal.className = 'fixed inset-0 z-50 flex items-center justify-center p-4 animate-fade-in';
    modal.innerHTML = `
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeShareModal()"></div>
        <div class="relative bg-white rounded-xl shadow-2xl max-w-lg w-full animate-zoom-in">
            <!-- Header -->
            <div class="flex items-center justify-between p-6 border-b border-slate-100">
                <div class="flex items-center gap-3">
                    <div class="flex-shrink-0 w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                        <i data-lucide="share-2" class="w-5 h-5 text-blue-600"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-slate-900">Share Announcement</h3>
                </div>
                <button onclick="closeShareModal()" class="text-slate-400 hover:text-slate-600 transition-colors">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            
            <!-- Body -->
            <div class="p-6">
                <p class="text-sm text-slate-600 mb-4 font-medium">${escapeHtml(title)}</p>
                
                <!-- Personal Message -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-slate-700 mb-2">Add your message (optional)</label>
                    <textarea 
                        id="shareMessage" 
                        class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                        rows="3"
                        placeholder="Share your thoughts about this announcement..."></textarea>
                </div>
                
                <!-- Share Options -->
                <div class="space-y-3">
                    <button onclick="shareToFacebook('${id}', '${escapeHtml(title)}', '${baseUrl}')" 
                        class="w-full flex items-center gap-3 px-4 py-3 bg-[#1877F2] text-white rounded-lg hover:bg-[#166FE5] transition-colors">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                        </svg>
                        <span class="font-medium">Share on Facebook</span>
                    </button>
                    
                    <button onclick="shareToTwitter('${id}', '${escapeHtml(title)}', '${baseUrl}')" 
                        class="w-full flex items-center gap-3 px-4 py-3 bg-[#1DA1F2] text-white rounded-lg hover:bg-[#1A8CD8] transition-colors">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/>
                        </svg>
                        <span class="font-medium">Share on Twitter</span>
                    </button>
                    
                    <button onclick="copyShareLink('${baseUrl}')" 
                        class="w-full flex items-center gap-3 px-4 py-3 bg-slate-100 text-slate-700 rounded-lg hover:bg-slate-200 transition-colors">
                        <i data-lucide="link" class="w-5 h-5"></i>
                        <span class="font-medium">Copy Link</span>
                    </button>
                </div>
            </div>
        </div>
    `;
    
    modal.id = 'shareModal';
    document.body.appendChild(modal);
    
    // Initialize Lucide icons
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
    
    // Prevent body scroll
    document.body.style.overflow = 'hidden';
}

// Close share modal
function closeShareModal() {
    const modal = document.getElementById('shareModal');
    if (modal) {
        modal.remove();
        document.body.style.overflow = '';
    }
}

// Share to Facebook
function shareToFacebook(id, title, url) {
    const message = document.getElementById('shareMessage')?.value || '';
    const announcement = announcements.find(a => a.id === id);
    const announcementExcerpt = announcement ? (announcement.content || announcement.message || '').substring(0, 200) : '';
    
    // Build the complete message with all info
    let fullMessage = '';
    
    // Add user's personal message if provided
    if (message.trim()) {
        fullMessage += message.trim() + '\n\n';
    }
    
    // Add announcement info
    fullMessage += `📢 Announcement from FixItMati\n\n`;
    fullMessage += `${title}\n\n`;
    
    if (announcementExcerpt) {
        fullMessage += `${announcementExcerpt}${announcementExcerpt.length >= 200 ? '...' : ''}\n\n`;
    }
    
    fullMessage += `Read full announcement:\n${url}`;
    
    // Copy the message to clipboard first
    navigator.clipboard.writeText(fullMessage).then(() => {
        // Show instruction modal
        showFacebookShareInstructions(url, fullMessage);
    }).catch(() => {
        // If clipboard fails, still show the dialog
        showFacebookShareInstructions(url, fullMessage);
    });
    
    closeShareModal();
}

// Show Facebook share instructions
function showFacebookShareInstructions(url, message) {
    const modal = document.createElement('div');
    modal.className = 'fixed inset-0 z-50 flex items-center justify-center p-4 animate-fade-in';
    modal.innerHTML = `
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeFacebookInstructionsModal()"></div>
        <div class="relative bg-white rounded-xl shadow-2xl max-w-lg w-full animate-zoom-in">
            <div class="flex items-center justify-between p-6 border-b border-slate-100">
                <div class="flex items-center gap-3">
                    <div class="flex-shrink-0 w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-[#1877F2]" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-slate-900">Share on Facebook</h3>
                </div>
                <button onclick="closeFacebookInstructionsModal()" class="text-slate-400 hover:text-slate-600 transition-colors">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            
            <div class="p-6">
                <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-4">
                    <div class="flex items-start gap-2">
                        <i data-lucide="check-circle" class="w-5 h-5 text-green-600 flex-shrink-0 mt-0.5"></i>
                        <p class="text-sm text-green-800 font-medium">Message copied to clipboard!</p>
                    </div>
                </div>
                
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                    <h4 class="font-semibold text-blue-900 mb-2 text-sm">📋 Your message to share:</h4>
                    <div class="bg-white rounded p-3 text-xs text-slate-700 max-h-32 overflow-y-auto border border-blue-100">
                        <pre class="whitespace-pre-wrap font-sans">${escapeHtml(message)}</pre>
                    </div>
                </div>
                
                <div class="space-y-3 mb-4">
                    <h4 class="font-semibold text-slate-900 text-sm">How to share:</h4>
                    <ol class="space-y-2 text-sm text-slate-700">
                        <li class="flex gap-2">
                            <span class="flex-shrink-0 w-5 h-5 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center text-xs font-bold">1</span>
                            <span>Click "Open Facebook" below</span>
                        </li>
                        <li class="flex gap-2">
                            <span class="flex-shrink-0 w-5 h-5 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center text-xs font-bold">2</span>
                            <span>Click on "What's on your mind?" or create a new post</span>
                        </li>
                        <li class="flex gap-2">
                            <span class="flex-shrink-0 w-5 h-5 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center text-xs font-bold">3</span>
                            <span>Paste (Ctrl+V) the copied message</span>
                        </li>
                        <li class="flex gap-2">
                            <span class="flex-shrink-0 w-5 h-5 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center text-xs font-bold">4</span>
                            <span>The link will show a preview automatically</span>
                        </li>
                    </ol>
                </div>
                
                <div class="flex gap-3">
                    <button onclick="window.open('https://www.facebook.com', 'facebook'); closeFacebookInstructionsModal();" 
                        class="flex-1 bg-[#1877F2] text-white px-4 py-3 rounded-lg font-medium hover:bg-[#166FE5] transition-colors flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                        </svg>
                        <span>Open Facebook</span>
                    </button>
                    <button onclick="copyMessageAgain('${escapeHtml(message).replace(/'/g, "\\'")}');" 
                        class="px-4 py-3 bg-slate-100 text-slate-700 rounded-lg hover:bg-slate-200 transition-colors">
                        <i data-lucide="copy" class="w-5 h-5"></i>
                    </button>
                </div>
            </div>
        </div>
    `;
    
    modal.id = 'facebookInstructionsModal';
    document.body.appendChild(modal);
    
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
    
    document.body.style.overflow = 'hidden';
}

// Close Facebook instructions modal
function closeFacebookInstructionsModal() {
    const modal = document.getElementById('facebookInstructionsModal');
    if (modal) {
        modal.remove();
        document.body.style.overflow = '';
    }
}

// Copy message again
function copyMessageAgain(message) {
    navigator.clipboard.writeText(message).then(() => {
        UIHelpers.showSuccess('Message copied again!');
    }).catch(() => {
        UIHelpers.showError('Failed to copy message');
    });
}

// Share to Twitter
function shareToTwitter(id, title, url) {
    const message = document.getElementById('shareMessage')?.value || '';
    const shareText = message ? encodeURIComponent(message + '\n\n') : '';
    const tweetText = `${shareText}${encodeURIComponent(title)}\n\nFrom FixItMati`;
    const shareUrl = encodeURIComponent(url);
    
    // Twitter share URL
    const twitterUrl = `https://twitter.com/intent/tweet?text=${tweetText}&url=${shareUrl}`;
    
    window.open(twitterUrl, 'twitter-share', 'width=580,height=600');
    closeShareModal();
}

// Copy share link
function copyShareLink(url) {
    const message = document.getElementById('shareMessage')?.value || '';
    const fullText = message 
        ? `${message}\n\nCheck out this announcement from FixItMati:\n${url}`
        : `Check out this announcement from FixItMati:\n${url}`;
    
    navigator.clipboard.writeText(fullText).then(() => {
        UIHelpers.showSuccess('Link copied to clipboard!');
        closeShareModal();
    }).catch(() => {
        UIHelpers.showError('Failed to copy link');
    });
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
