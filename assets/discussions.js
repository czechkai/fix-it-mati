/**
 * Community Discussions Page
 * Handles discussion threads, comments, and voting
 */

let allDiscussions = [];
let filteredDiscussions = [];
let currentCategory = 'All';
let currentSort = 'newest';
let autoRefreshInterval = null;
let isLoading = false;

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
  loadDiscussions();
  setupEventListeners();
  
  // Auto-refresh every 30 seconds for real-time updates
  autoRefreshInterval = setInterval(() => {
    loadDiscussions(true); // Silent reload
  }, 30000);
});

// Cleanup on page unload
window.addEventListener('beforeunload', function() {
  if (autoRefreshInterval) {
    clearInterval(autoRefreshInterval);
  }
});

/**
 * Setup event listeners
 */
function setupEventListeners() {
  // Category filters
  const categoryButtons = document.querySelectorAll('.category-filter');
  categoryButtons.forEach(btn => {
    btn.addEventListener('click', handleCategoryChange);
  });

  // Sort tabs
  const sortButtons = document.querySelectorAll('.sort-tab');
  sortButtons.forEach(btn => {
    btn.addEventListener('click', handleSortChange);
  });

  // Search
  const searchInput = document.getElementById('searchInput');
  if (searchInput) {
    searchInput.addEventListener('input', handleSearch);
  }

  // New discussion button
  const newDiscussionBtn = document.getElementById('newDiscussionBtn');
  if (newDiscussionBtn) {
    newDiscussionBtn.addEventListener('click', openNewDiscussionModal);
  }

  // Manual refresh button
  const manualRefreshBtn = document.getElementById('manualRefreshBtn');
  if (manualRefreshBtn) {
    manualRefreshBtn.addEventListener('click', function() {
      const icon = this.querySelector('i[data-lucide]');
      if (icon) {
        icon.classList.add('animate-spin');
      }
      loadDiscussions().then(() => {
        if (icon) {
          setTimeout(() => icon.classList.remove('animate-spin'), 500);
        }
      });
    });
  }

  // Modal close buttons
  const closeModalBtn = document.getElementById('closeModalBtn');
  const cancelModalBtn = document.getElementById('cancelModalBtn');
  if (closeModalBtn) closeModalBtn.addEventListener('click', closeNewDiscussionModal);
  if (cancelModalBtn) cancelModalBtn.addEventListener('click', closeNewDiscussionModal);

  // Form submission
  const newDiscussionForm = document.getElementById('newDiscussionForm');
  if (newDiscussionForm) {
    newDiscussionForm.addEventListener('submit', handleNewDiscussionSubmit);
  }

  // Close modal on background click
  const modal = document.getElementById('newDiscussionModal');
  if (modal) {
    modal.addEventListener('click', function(e) {
      if (e.target === modal) {
        closeNewDiscussionModal();
      }
    });
  }
}

/**
 * Load discussions from API
 */
async function loadDiscussions(silent = false) {
  // Prevent multiple simultaneous loads
  if (isLoading) return;
  
  isLoading = true;
  
  // Show loading state only if not silent refresh
  if (!silent) {
    showLoadingState();
  }
  
  try {
    const response = await ApiClient.get('/discussions');
    
    if (response.success) {
      allDiscussions = response.data || [];
      
      // Add real-time timestamp to each discussion for freshness indicator
      allDiscussions.forEach(d => {
        d._loadedAt = new Date().toISOString();
      });
      
      applyFiltersAndSort();
      
      // Update last update indicator
      const indicator = document.getElementById('lastUpdateIndicator');
      if (indicator) {
        const time = new Date().toLocaleTimeString();
        indicator.textContent = silent ? `Updated ${time}` : `Loaded ${time}`;
      }
      
      // Log success for debugging
      console.log(`✅ Loaded ${allDiscussions.length} discussions at ${new Date().toLocaleTimeString()}`);
    } else {
      if (!silent) {
        showError('Failed to load discussions');
      }
      console.error('API Error:', response.message);
    }
  } catch (error) {
    console.error('Error loading discussions:', error);
    if (!silent) {
      showError('Failed to load discussions. Please check your connection.');
    }
  } finally {
    isLoading = false;
  }
}

/**
 * Apply current filters and sorting
 */
function applyFiltersAndSort() {
  // Filter by category
  if (currentCategory === 'All') {
    filteredDiscussions = [...allDiscussions];
  } else {
    filteredDiscussions = allDiscussions.filter(d => d.category === currentCategory);
  }

  // Apply search if active
  const searchInput = document.getElementById('searchInput');
  if (searchInput && searchInput.value.trim()) {
    const query = searchInput.value.toLowerCase();
    filteredDiscussions = filteredDiscussions.filter(d => 
      d.title.toLowerCase().includes(query) ||
      d.content.toLowerCase().includes(query)
    );
  }

  // Sort
  sortDiscussions();
  
  // Display
  displayDiscussions();
}

/**
 * Sort discussions based on current sort option
 */
function sortDiscussions() {
  if (currentSort === 'newest') {
    filteredDiscussions.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
  } else if (currentSort === 'top') {
    filteredDiscussions.sort((a, b) => b.upvotes - a.upvotes);
  } else if (currentSort === 'unanswered') {
    filteredDiscussions = filteredDiscussions.filter(d => !d.is_answered);
    filteredDiscussions.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
  }
}

/**
 * Show loading state
 */
function showLoadingState() {
  const container = document.getElementById('discussionsList');
  if (!container) return;
  
  container.innerHTML = `
    <div class="text-center py-12">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto mb-4"></div>
      <p class="text-slate-500 text-sm">Loading discussions...</p>
    </div>
  `;
}

/**
 * Display discussions in the feed
 */
function displayDiscussions() {
  const container = document.getElementById('discussionsList');
  
  if (!container) return;
  
  if (filteredDiscussions.length === 0) {
    container.innerHTML = `
      <div class="text-center py-12 text-slate-400">
        <i data-lucide="message-square" class="w-16 h-16 mx-auto mb-4"></i>
        <p class="text-sm">No discussions found</p>
        <button onclick="openNewDiscussionModal()" class="mt-4 text-blue-600 hover:text-blue-700 font-medium text-sm">
          Start the conversation
        </button>
      </div>
    `;
    lucide.createIcons();
    return;
  }

  container.innerHTML = filteredDiscussions.map(discussion => {
    const categoryIcon = getCategoryIcon(discussion.category);
    const timeAgo = formatTimeAgo(discussion.created_at);
    
    return `
      <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm hover:border-blue-300 transition-colors cursor-pointer group" onclick="viewDiscussion('${discussion.id}')">
        <div class="flex items-start gap-4">
          
          <!-- Author Avatar -->
          <div class="hidden sm:block">
            <div class="h-10 w-10 rounded-full bg-slate-100 flex items-center justify-center text-slate-400">
              <i data-lucide="user" class="w-5 h-5"></i>
            </div>
          </div>

          <div class="flex-1">
            <div class="flex items-center gap-2 mb-1">
              <h3 class="font-bold text-slate-800 text-lg group-hover:text-blue-600 transition-colors">${escapeHtml(discussion.title)}</h3>
              ${discussion.is_answered ? `
                <span class="bg-green-100 text-green-700 text-[10px] font-bold px-2 py-0.5 rounded-full flex items-center gap-1 border border-green-200">
                  <i data-lucide="check-circle-2" class="w-2.5 h-2.5"></i> Answered
                </span>
              ` : ''}
            </div>
            
            <p class="text-sm text-slate-600 line-clamp-2 mb-3">${escapeHtml(discussion.content)}</p>
            
            <div class="flex items-center gap-4 text-xs text-slate-500">
              <span class="flex items-center gap-1 font-medium text-slate-700">
                ${categoryIcon} ${escapeHtml(discussion.category)}
              </span>
              <span>•</span>
              <span>Posted by ${escapeHtml(discussion.author_name)} ${timeAgo}</span>
              
              ${discussion.is_answered && discussion.answered_by ? `
                <span class="hidden sm:flex items-center gap-1 text-green-600">
                  <i data-lucide="check-circle-2" class="w-3 h-3"></i> Solution by ${escapeHtml(discussion.answered_by)}
                </span>
              ` : ''}
            </div>
          </div>

          <!-- Stats -->
          <div class="flex flex-col items-center gap-2 text-slate-400">
            <button onclick="handleUpvote(event, '${discussion.id}')" class="flex items-center gap-1 bg-slate-50 px-2 py-1 rounded border border-slate-100 min-w-[60px] justify-center hover:bg-blue-50 hover:border-blue-200 transition-colors">
              <i data-lucide="thumbs-up" class="w-3.5 h-3.5"></i>
              <span class="font-bold text-slate-600">${discussion.upvotes}</span>
            </button>
            <div class="flex items-center gap-1">
              <i data-lucide="message-circle" class="w-3.5 h-3.5"></i>
              <span>${discussion.comments_count || 0}</span>
            </div>
          </div>

        </div>
      </div>
    `;
  }).join('');

  lucide.createIcons();
}

/**
 * Get category icon
 */
function getCategoryIcon(category) {
  const icons = {
    'Electricity': '<i data-lucide="zap" class="w-3.5 h-3.5 text-amber-500"></i>',
    'Water Supply': '<i data-lucide="droplet" class="w-3.5 h-3.5 text-blue-500"></i>',
    'Billing': '<i data-lucide="credit-card" class="w-3.5 h-3.5 text-green-500"></i>',
    'General': '<i data-lucide="hammer" class="w-3.5 h-3.5 text-slate-500"></i>'
  };
  return icons[category] || icons['General'];
}

/**
 * Handle category change
 */
function handleCategoryChange(e) {
  const category = e.currentTarget.getAttribute('data-category');
  currentCategory = category;
  
  // Update active state
  document.querySelectorAll('.category-filter').forEach(btn => {
    btn.classList.remove('bg-blue-50', 'text-blue-700');
    btn.classList.add('text-slate-600');
  });
  e.currentTarget.classList.add('bg-blue-50', 'text-blue-700');
  e.currentTarget.classList.remove('text-slate-600');
  
  applyFiltersAndSort();
}

/**
 * Handle sort change
 */
function handleSortChange(e) {
  const sort = e.currentTarget.getAttribute('data-sort');
  currentSort = sort;
  
  // Update active state
  document.querySelectorAll('.sort-tab').forEach(btn => {
    btn.classList.remove('font-bold', 'text-slate-800', 'border-b-2', 'border-slate-800');
    btn.classList.add('font-medium', 'text-slate-500');
  });
  e.currentTarget.classList.add('font-bold', 'text-slate-800', 'border-b-2', 'border-slate-800');
  e.currentTarget.classList.remove('font-medium', 'text-slate-500');
  
  applyFiltersAndSort();
}

/**
 * Handle search
 */
function handleSearch(e) {
  applyFiltersAndSort();
}

/**
 * Handle upvote
 */
async function handleUpvote(event, discussionId) {
  event.stopPropagation();
  
  // Provide immediate visual feedback
  const button = event.currentTarget;
  const originalContent = button.innerHTML;
  button.disabled = true;
  button.classList.add('opacity-50', 'cursor-not-allowed');
  
  try {
    const response = await ApiClient.post(`/discussions/${discussionId}/upvote`);
    
    if (response.success) {
      // Update local data immediately
      const discussion = allDiscussions.find(d => d.id === discussionId);
      if (discussion) {
        const oldUpvotes = discussion.upvotes;
        discussion.upvotes = response.data.upvotes;
        discussion.user_upvoted = response.data.user_upvoted;
        
        // Show visual feedback
        const countSpan = button.querySelector('span');
        if (countSpan) {
          countSpan.textContent = discussion.upvotes;
          
          // Animate the change
          if (discussion.upvotes > oldUpvotes) {
            button.classList.add('bg-blue-100', 'border-blue-300');
            setTimeout(() => {
              button.classList.remove('bg-blue-100', 'border-blue-300');
            }, 1000);
          }
        }
        
        applyFiltersAndSort();
        
        console.log(`✅ Upvote ${response.data.user_upvoted ? 'added' : 'removed'} - New count: ${discussion.upvotes}`);
      }
    } else {
      showError(response.message || 'Failed to upvote');
      button.innerHTML = originalContent;
    }
  } catch (error) {
    console.error('Error upvoting:', error);
    showError('Failed to upvote discussion');
    button.innerHTML = originalContent;
  } finally {
    button.disabled = false;
    button.classList.remove('opacity-50', 'cursor-not-allowed');
  }
}

/**
 * View discussion details
 */
function viewDiscussion(discussionId) {
  // TODO: Navigate to discussion detail page
  window.location.href = `discussion-detail.php?id=${discussionId}`;
}

/**
 * Open new discussion modal
 */
function openNewDiscussionModal() {
  const modal = document.getElementById('newDiscussionModal');
  if (modal) {
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
  }
}

/**
 * Close new discussion modal
 */
function closeNewDiscussionModal() {
  const modal = document.getElementById('newDiscussionModal');
  if (modal) {
    modal.classList.add('hidden');
    document.body.style.overflow = '';
    
    // Reset form
    const form = document.getElementById('newDiscussionForm');
    if (form) form.reset();
  }
}

/**
 * Handle new discussion submission
 */
async function handleNewDiscussionSubmit(e) {
  e.preventDefault();
  
  const submitBtn = e.target.querySelector('button[type="submit"]');
  const originalBtnText = submitBtn ? submitBtn.textContent : '';
  
  const category = document.getElementById('discussionCategory').value;
  const title = document.getElementById('discussionTitle').value;
  const content = document.getElementById('discussionContent').value;
  
  if (!title.trim() || !content.trim()) {
    showError('Please fill in all fields');
    return;
  }
  
  // Disable form during submission
  if (submitBtn) {
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i data-lucide="loader-2" class="w-4 h-4 animate-spin"></i> Posting...';
    lucide.createIcons();
  }
  
  try {
    const response = await ApiClient.post('/discussions', {
      category,
      title: title.trim(),
      content: content.trim()
    });
    
    if (response.success) {
      showSuccess('Discussion posted successfully!');
      closeNewDiscussionModal();
      
      // Reload discussions to show the new one
      console.log('✅ New discussion created, reloading...');
      await loadDiscussions();
    } else {
      showError(response.message || 'Failed to create discussion');
      if (submitBtn) {
        submitBtn.disabled = false;
        submitBtn.textContent = originalBtnText;
      }
    }
  } catch (error) {
    console.error('Error creating discussion:', error);
    showError('Failed to create discussion. Please try again.');
    if (submitBtn) {
      submitBtn.disabled = false;
      submitBtn.textContent = originalBtnText;
    }
  }
}

/**
 * Format time ago
 */
function formatTimeAgo(dateString) {
  if (!dateString) return 'recently';
  
  const date = new Date(dateString);
  const now = new Date();
  const seconds = Math.floor((now - date) / 1000);
  
  if (seconds < 60) return 'just now';
  if (seconds < 3600) return `${Math.floor(seconds / 60)} minutes ago`;
  if (seconds < 86400) return `${Math.floor(seconds / 3600)} hours ago`;
  if (seconds < 604800) return `${Math.floor(seconds / 86400)} days ago`;
  if (seconds < 2592000) return `${Math.floor(seconds / 604800)} weeks ago`;
  
  return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
}

/**
 * Escape HTML to prevent XSS
 */
function escapeHtml(text) {
  if (!text) return '';
  const div = document.createElement('div');
  div.textContent = text;
  return div.innerHTML;
}

/**
 * Show success message
 */
function showSuccess(message) {
  alert(message); // TODO: Replace with toast notification
}

/**
 * Show error message
 */
function showError(message) {
  alert(message); // TODO: Replace with toast notification
}
