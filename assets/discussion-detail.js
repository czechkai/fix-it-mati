/**
 * Discussion Detail Page
 * Handles viewing and interacting with a single discussion thread
 */

let discussionId = null;
let currentDiscussion = null;
let currentUser = null;
let autoRefreshInterval = null;

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
  // Get discussion ID from URL
  const urlParams = new URLSearchParams(window.location.search);
  discussionId = urlParams.get('id');
  
  if (!discussionId) {
    showError();
    return;
  }
  
  // Load current user
  loadCurrentUser();
  
  // Load discussion
  loadDiscussion();
  
  // Setup event listeners
  setupEventListeners();
  
  // Auto-refresh every 15 seconds for new comments
  autoRefreshInterval = setInterval(() => {
    loadDiscussion(true); // Silent reload
  }, 15000);
});

// Cleanup on page unload
window.addEventListener('beforeunload', function() {
  if (autoRefreshInterval) {
    clearInterval(autoRefreshInterval);
  }
});

/**
 * Load current user
 */
async function loadCurrentUser() {
  try {
    const response = await ApiClient.get('/auth/me');
    if (response.success) {
      currentUser = response.data;
    }
  } catch (error) {
    console.error('Error loading user:', error);
  }
}

/**
 * Setup event listeners
 */
function setupEventListeners() {
  // Upvote button
  const upvoteBtn = document.getElementById('upvoteBtn');
  if (upvoteBtn) {
    upvoteBtn.addEventListener('click', handleUpvote);
  }
  
  // Add comment form
  const commentForm = document.getElementById('addCommentForm');
  if (commentForm) {
    commentForm.addEventListener('submit', handleAddComment);
  }
  
  // Delete discussion button
  const deleteBtn = document.getElementById('deleteDiscussionBtn');
  if (deleteBtn) {
    deleteBtn.addEventListener('click', handleDeleteDiscussion);
  }
}

/**
 * Load discussion from API
 */
async function loadDiscussion(silent = false) {
  try {
    const response = await ApiClient.get(`/discussions/${discussionId}`);
    
    if (response.success) {
      currentDiscussion = response.data;
      displayDiscussion();
      displayComments();
      
      if (!silent) {
        document.getElementById('loadingState').classList.add('hidden');
        document.getElementById('discussionContent').classList.remove('hidden');
      }
    } else {
      showError();
    }
  } catch (error) {
    console.error('Error loading discussion:', error);
    showError();
  }
}

/**
 * Display discussion content
 */
function displayDiscussion() {
  if (!currentDiscussion) return;
  
  // Category
  const categoryEl = document.getElementById('discussionCategory');
  if (categoryEl) {
    categoryEl.textContent = currentDiscussion.category;
    categoryEl.className = `text-xs font-bold px-3 py-1 rounded-full ${getCategoryClasses(currentDiscussion.category)}`;
  }
  
  // Answered badge
  const answeredEl = document.getElementById('discussionAnswered');
  if (answeredEl && currentDiscussion.is_answered) {
    answeredEl.classList.remove('hidden');
  }
  
  // Title
  const titleEl = document.getElementById('discussionTitle');
  if (titleEl) {
    titleEl.textContent = currentDiscussion.title;
  }
  
  // Author and time
  const authorEl = document.getElementById('discussionAuthor');
  if (authorEl) {
    authorEl.textContent = currentDiscussion.author_name || 'Anonymous';
  }
  
  const timeEl = document.getElementById('discussionTime');
  if (timeEl) {
    timeEl.textContent = formatTimeAgo(currentDiscussion.created_at);
  }
  
  // Content
  const bodyEl = document.getElementById('discussionBody');
  if (bodyEl) {
    bodyEl.textContent = currentDiscussion.content;
  }
  
  // Upvote count
  const upvoteCountEl = document.getElementById('upvoteCount');
  if (upvoteCountEl) {
    upvoteCountEl.textContent = currentDiscussion.upvotes || 0;
  }
  
  // Comments count
  const commentsCountEl = document.getElementById('commentsCount');
  if (commentsCountEl) {
    const count = currentDiscussion.comments ? currentDiscussion.comments.length : 0;
    commentsCountEl.textContent = count;
  }
  
  // Show delete button if user owns the discussion
  const deleteBtn = document.getElementById('deleteDiscussionBtn');
  if (deleteBtn && currentUser && (currentDiscussion.user_id === currentUser.id || currentUser.role === 'admin')) {
    deleteBtn.classList.remove('hidden');
  }
  
  // Update upvote button state
  const upvoteBtn = document.getElementById('upvoteBtn');
  if (upvoteBtn && currentDiscussion.user_upvoted) {
    upvoteBtn.classList.add('bg-blue-100', 'border-blue-300');
  } else if (upvoteBtn) {
    upvoteBtn.classList.remove('bg-blue-100', 'border-blue-300');
  }
}

/**
 * Display comments
 */
function displayComments() {
  const commentsList = document.getElementById('commentsList');
  if (!commentsList) return;
  
  const comments = currentDiscussion.comments || [];
  
  if (comments.length === 0) {
    commentsList.innerHTML = `
      <p class="text-slate-400 text-sm text-center py-4">No comments yet. Be the first to comment!</p>
    `;
    return;
  }
  
  commentsList.innerHTML = comments.map(comment => {
    const isSolution = comment.is_solution;
    const isOwner = currentUser && (currentDiscussion.user_id === currentUser.id || currentUser.role === 'admin');
    
    return `
      <div class="border border-slate-200 rounded-lg p-4 ${isSolution ? 'bg-green-50 border-green-200' : ''}">
        <div class="flex items-start gap-3">
          <div class="h-10 w-10 rounded-full bg-slate-100 flex items-center justify-center text-slate-400 flex-shrink-0">
            <i data-lucide="user" class="w-5 h-5"></i>
          </div>
          <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2 mb-1">
              <p class="font-semibold text-slate-800">${escapeHtml(comment.author_name || 'Anonymous')}</p>
              ${comment.author_role === 'admin' || comment.author_role === 'staff' ? `
                <span class="bg-blue-100 text-blue-700 text-xs font-bold px-2 py-0.5 rounded">
                  ${comment.author_role === 'admin' ? 'Admin' : 'Staff'}
                </span>
              ` : ''}
              ${isSolution ? `
                <span class="bg-green-600 text-white text-xs font-bold px-2 py-0.5 rounded flex items-center gap-1">
                  <i data-lucide="check-circle-2" class="w-3 h-3"></i> Solution
                </span>
              ` : ''}
              <span class="text-xs text-slate-500">${formatTimeAgo(comment.created_at)}</span>
            </div>
            <p class="text-slate-700 text-sm whitespace-pre-wrap">${escapeHtml(comment.content)}</p>
            ${!isSolution && isOwner && !currentDiscussion.is_answered ? `
              <button 
                onclick="markAsSolution('${comment.id}')" 
                class="mt-2 text-xs text-green-600 hover:text-green-700 font-medium flex items-center gap-1"
              >
                <i data-lucide="check-circle-2" class="w-3 h-3"></i>
                Mark as solution
              </button>
            ` : ''}
          </div>
        </div>
      </div>
    `;
  }).join('');
  
  // Re-initialize Lucide icons
  lucide.createIcons();
}

/**
 * Handle upvote
 */
async function handleUpvote() {
  const button = document.getElementById('upvoteBtn');
  if (!button) return;
  
  const originalContent = button.innerHTML;
  button.disabled = true;
  button.classList.add('opacity-50');
  
  try {
    const response = await ApiClient.post(`/discussions/${discussionId}/upvote`);
    
    if (response.success) {
      currentDiscussion.upvotes = response.data.upvotes;
      currentDiscussion.user_upvoted = response.data.user_upvoted;
      displayDiscussion();
      
      console.log(`✅ Upvote ${response.data.user_upvoted ? 'added' : 'removed'}`);
    } else {
      alert(response.message || 'Failed to upvote');
    }
  } catch (error) {
    console.error('Error upvoting:', error);
    alert('Failed to upvote discussion');
  } finally {
    button.disabled = false;
    button.classList.remove('opacity-50');
  }
}

/**
 * Handle add comment
 */
async function handleAddComment(e) {
  e.preventDefault();
  
  const contentInput = document.getElementById('commentContent');
  if (!contentInput) return;
  
  const content = contentInput.value.trim();
  if (!content) {
    alert('Please enter a comment');
    return;
  }
  
  const submitBtn = e.target.querySelector('button[type="submit"]');
  const originalText = submitBtn ? submitBtn.textContent : '';
  
  if (submitBtn) {
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i data-lucide="loader-2" class="w-4 h-4 animate-spin inline"></i> Posting...';
    lucide.createIcons();
  }
  
  try {
    const response = await ApiClient.post(`/discussions/${discussionId}/comments`, {
      content: content
    });
    
    if (response.success) {
      contentInput.value = '';
      await loadDiscussion(); // Reload to show new comment
      console.log('✅ Comment added successfully');
    } else {
      alert(response.message || 'Failed to add comment');
    }
  } catch (error) {
    console.error('Error adding comment:', error);
    alert('Failed to add comment');
  } finally {
    if (submitBtn) {
      submitBtn.disabled = false;
      submitBtn.textContent = originalText;
    }
  }
}

/**
 * Mark comment as solution
 */
async function markAsSolution(commentId) {
  if (!confirm('Mark this comment as the solution?')) {
    return;
  }
  
  try {
    const response = await ApiClient.post(`/discussions/${discussionId}/comments/${commentId}/mark-solution`);
    
    if (response.success) {
      await loadDiscussion(); // Reload to show solution badge
      console.log('✅ Comment marked as solution');
    } else {
      alert(response.message || 'Failed to mark solution');
    }
  } catch (error) {
    console.error('Error marking solution:', error);
    alert('Failed to mark solution');
  }
}

/**
 * Handle delete discussion
 */
async function handleDeleteDiscussion() {
  if (!confirm('Are you sure you want to delete this discussion? This action cannot be undone.')) {
    return;
  }
  
  try {
    const response = await ApiClient.delete(`/discussions/${discussionId}`);
    
    if (response.success) {
      alert('Discussion deleted successfully');
      window.location.href = 'discussions.php';
    } else {
      alert(response.message || 'Failed to delete discussion');
    }
  } catch (error) {
    console.error('Error deleting discussion:', error);
    alert('Failed to delete discussion');
  }
}

/**
 * Get category classes
 */
function getCategoryClasses(category) {
  const classes = {
    'Electricity': 'bg-amber-100 text-amber-700',
    'Water Supply': 'bg-blue-100 text-blue-700',
    'Billing': 'bg-green-100 text-green-700',
    'General': 'bg-slate-100 text-slate-700'
  };
  return classes[category] || classes['General'];
}

/**
 * Show error state
 */
function showError() {
  document.getElementById('loadingState').classList.add('hidden');
  document.getElementById('errorState').classList.remove('hidden');
  
  if (autoRefreshInterval) {
    clearInterval(autoRefreshInterval);
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
