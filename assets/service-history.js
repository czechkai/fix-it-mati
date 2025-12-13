/**
 * Service History / Resolved Issues Page
 * Handles viewing, rating, and managing resolved service requests
 */

let resolvedIssues = [];
let currentIssue = null;
let filteredIssues = [];
let currentDateFilter = 30; // Default: Last 30 days

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
  loadResolvedIssues();
  setupEventListeners();
});

/**
 * Setup event listeners
 */
function setupEventListeners() {
  // Search functionality
  const searchInput = document.getElementById('searchInput');
  if (searchInput) {
    searchInput.addEventListener('input', handleSearch);
  }

  // Filter button
  const filterBtn = document.getElementById('filterBtn');
  if (filterBtn) {
    filterBtn.addEventListener('click', toggleFilterMenu);
  }

  // Date filter button
  const dateFilterBtn = document.getElementById('dateFilterBtn');
  if (dateFilterBtn) {
    dateFilterBtn.addEventListener('click', toggleDateFilterDropdown);
  }

  // Date filter options
  const dateFilterOptions = document.querySelectorAll('.date-filter-option');
  dateFilterOptions.forEach(option => {
    option.addEventListener('click', handleDateFilterChange);
  });

  // Close dropdowns when clicking outside
  document.addEventListener('click', function(e) {
    const dateDropdown = document.getElementById('dateFilterDropdown');
    const dateBtn = document.getElementById('dateFilterBtn');
    if (dateDropdown && !dateDropdown.contains(e.target) && !dateBtn.contains(e.target)) {
      dateDropdown.classList.add('hidden');
    }
  });
}

/**
 * Load resolved issues from API
 */
async function loadResolvedIssues() {
  try {
    const response = await ApiClient.get('/requests/resolved');
    
    if (response.success) {
      resolvedIssues = response.data || [];
      applyDateFilter();
      
      // Automatically select first issue
      if (filteredIssues.length > 0) {
        selectIssue(filteredIssues[0].id);
      }
    } else {
      showError('Failed to load service history');
    }
  } catch (error) {
    console.error('Error loading resolved issues:', error);
    showError('Failed to load service history');
  }
}

/**
 * Display list of issues in the left sidebar
 */
function displayIssuesList() {
  const listContainer = document.getElementById('issuesList');
  
  if (!listContainer) return;
  
  if (filteredIssues.length === 0) {
    listContainer.innerHTML = `
      <div class="text-center py-8 text-slate-400">
        <i data-lucide="file-x-2" class="w-12 h-12 mx-auto mb-2"></i>
        <p class="text-sm">No resolved issues found</p>
      </div>
    `;
    lucide.createIcons();
    return;
  }

  listContainer.innerHTML = filteredIssues.map(issue => {
    const isSelected = currentIssue && currentIssue.id === issue.id;
    const categoryIcon = getCategoryIcon(issue.category);
    const categoryColor = getCategoryColor(issue.category);
    
    return `
      <div 
        class="issue-card p-4 rounded-lg border border-slate-200 cursor-pointer transition-all hover:shadow-md ${isSelected ? 'bg-green-50 border-green-300' : 'bg-white'}"
        data-issue-id="${issue.id}"
        onclick="selectIssue('${issue.id}')"
      >
        <div class="flex items-start gap-3">
          <div class="p-2 rounded-lg ${categoryColor}">
            <i data-lucide="${categoryIcon}" class="w-4 h-4"></i>
          </div>
          <div class="flex-1 min-w-0">
            <div class="flex items-start justify-between gap-2 mb-1">
              <h3 class="font-semibold text-sm text-slate-800 truncate">#${issue.ticket_number}</h3>
              ${renderRating(issue.rating)}
            </div>
            <p class="text-sm text-slate-600 mb-2 line-clamp-2">${escapeHtml(issue.title)}</p>
            <div class="flex items-center justify-between text-xs text-slate-500">
              <span class="flex items-center gap-1">
                <i data-lucide="calendar" class="w-3 h-3"></i>
                ${formatDate(issue.resolved_at)}
              </span>
              <span class="flex items-center gap-1 text-green-600">
                <i data-lucide="check-circle-2" class="w-3 h-3"></i>
                Resolved
              </span>
            </div>
          </div>
        </div>
      </div>
    `;
  }).join('');

  lucide.createIcons();
}

/**
 * Select and display an issue in detail view
 */
async function selectIssue(issueId) {
  try {
    const response = await ApiClient.get(`/requests/${issueId}`);
    
    if (response.success) {
      currentIssue = response.data;
      displayIssueDetail();
      // Refresh list to show selected state
      displayIssuesList();
    } else {
      showError('Failed to load issue details');
    }
  } catch (error) {
    console.error('Error loading issue details:', error);
    showError('Failed to load issue details');
  }
}

/**
 * Display issue details in right panel
 */
function displayIssueDetail() {
  if (!currentIssue) return;

  const detailView = document.getElementById('detailView');
  if (!detailView) return;

  const categoryIcon = getCategoryIcon(currentIssue.category);
  const categoryColor = getCategoryColor(currentIssue.category);
  const hasRating = currentIssue.rating && currentIssue.rating > 0;

  detailView.innerHTML = `
    <!-- Header -->
    <div class="p-6 border-b border-slate-200">
      <div class="flex items-start justify-between mb-4">
        <div>
          <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-green-100 text-green-700 text-xs font-medium mb-2">
            <i data-lucide="check-circle-2" class="w-3 h-3"></i>
            Resolved
          </span>
          <h2 class="text-2xl font-bold text-slate-800 mt-2">#${currentIssue.ticket_number}</h2>
          <p class="text-slate-600 mt-1">${escapeHtml(currentIssue.title)}</p>
        </div>
        <div class="p-3 rounded-lg ${categoryColor}">
          <i data-lucide="${categoryIcon}" class="w-6 h-6"></i>
        </div>
      </div>

      <div class="grid grid-cols-2 gap-4">
        <div class="flex items-center gap-2 text-sm text-slate-600">
          <i data-lucide="calendar" class="w-4 h-4"></i>
          <span>Resolved: ${formatDateTime(currentIssue.resolved_at)}</span>
        </div>
        <div class="flex items-center gap-2 text-sm text-slate-600">
          <i data-lucide="user-check" class="w-4 h-4"></i>
          <span>By: ${escapeHtml(currentIssue.resolved_by || 'Technician')}</span>
        </div>
        <div class="flex items-center gap-2 text-sm text-slate-600">
          <i data-lucide="map-pin" class="w-4 h-4"></i>
          <span>${escapeHtml(currentIssue.location || currentIssue.address || 'N/A')}</span>
        </div>
        <div class="flex items-center gap-2 text-sm text-slate-600">
          <i data-lucide="tag" class="w-4 h-4"></i>
          <span>${escapeHtml(currentIssue.category)}</span>
        </div>
      </div>
    </div>

    <!-- Scrollable Content -->
    <div class="flex-1 overflow-y-auto p-6 space-y-6">
      
      <!-- Resolution Report -->
      <div>
        <h3 class="text-lg font-semibold text-slate-800 mb-3 flex items-center gap-2">
          <i data-lucide="file-text" class="w-5 h-5 text-green-600"></i>
          Resolution Report
        </h3>
        <div class="bg-slate-50 rounded-lg p-4">
          <p class="text-slate-700 whitespace-pre-wrap">${escapeHtml(currentIssue.resolution || 'No resolution details available.')}</p>
        </div>
      </div>

      <!-- Technician Notes -->
      ${currentIssue.technician_notes ? `
        <div>
          <h3 class="text-lg font-semibold text-slate-800 mb-3 flex items-center gap-2">
            <i data-lucide="clipboard-list" class="w-5 h-5 text-blue-600"></i>
            Technician Notes
          </h3>
          <div class="bg-slate-50 rounded-lg p-4">
            <p class="text-slate-700 whitespace-pre-wrap">${escapeHtml(currentIssue.technician_notes)}</p>
          </div>
        </div>
      ` : ''}

      <!-- Before/After Images -->
      ${renderImages()}

      <!-- Rating Section -->
      <div class="border-t border-slate-200 pt-6">
        <h3 class="text-lg font-semibold text-slate-800 mb-4 flex items-center gap-2">
          <i data-lucide="star" class="w-5 h-5 text-yellow-500"></i>
          Service Rating
        </h3>
        
        ${hasRating ? renderExistingRating() : renderRatingForm()}
      </div>

      <!-- Recurring Issue -->
      <div class="border-t border-slate-200 pt-6">
        <button 
          id="reportRecurringBtn"
          class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-orange-50 text-orange-700 border border-orange-200 rounded-lg hover:bg-orange-100 transition-colors"
        >
          <i data-lucide="alert-triangle" class="w-5 h-5"></i>
          <span class="font-medium">Report Recurring Issue</span>
        </button>
      </div>
    </div>
  `;

  lucide.createIcons();
  setupDetailEventListeners();
}

/**
 * Setup event listeners for detail view
 */
function setupDetailEventListeners() {
  // Star rating
  const stars = document.querySelectorAll('.rating-star');
  stars.forEach((star, index) => {
    star.addEventListener('click', () => selectRating(index + 1));
    star.addEventListener('mouseenter', () => hoverRating(index + 1));
  });

  const ratingContainer = document.querySelector('.rating-stars');
  if (ratingContainer) {
    ratingContainer.addEventListener('mouseleave', resetRatingHover);
  }

  // Submit rating
  const submitBtn = document.getElementById('submitRatingBtn');
  if (submitBtn) {
    submitBtn.addEventListener('click', submitRating);
  }

  // Report recurring issue
  const recurringBtn = document.getElementById('reportRecurringBtn');
  if (recurringBtn) {
    recurringBtn.addEventListener('click', reportRecurringIssue);
  }
}

/**
 * Render rating stars form
 */
function renderRatingForm() {
  return `
    <div class="bg-slate-50 rounded-lg p-6">
      <p class="text-sm text-slate-600 mb-4">How would you rate this service?</p>
      
      <div class="rating-stars flex gap-2 mb-4">
        ${[1, 2, 3, 4, 5].map(i => `
          <i data-lucide="star" class="rating-star w-8 h-8 cursor-pointer text-slate-300 hover:text-yellow-500 transition-colors" data-rating="${i}"></i>
        `).join('')}
      </div>

      <div id="selectedRating" class="text-sm font-medium text-slate-700 mb-4 hidden">
        Rating: <span id="ratingValue">0</span> / 5
      </div>

      <div>
        <label for="feedbackText" class="block text-sm font-medium text-slate-700 mb-2">Additional Comments (Optional)</label>
        <textarea 
          id="feedbackText"
          rows="4"
          placeholder="Share your experience..."
          class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
        ></textarea>
      </div>

      <button 
        id="submitRatingBtn"
        class="mt-4 w-full px-4 py-2.5 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors font-medium disabled:opacity-50 disabled:cursor-not-allowed"
        disabled
      >
        Submit Rating
      </button>
    </div>
  `;
}

/**
 * Render existing rating
 */
function renderExistingRating() {
  return `
    <div class="bg-green-50 border border-green-200 rounded-lg p-6">
      <div class="flex items-center gap-2 mb-4">
        ${renderRating(currentIssue.rating, 'w-6 h-6')}
        <span class="text-lg font-semibold text-slate-800">${currentIssue.rating.toFixed(1)} / 5.0</span>
      </div>
      
      ${currentIssue.feedback ? `
        <div class="mt-3">
          <p class="text-sm font-medium text-slate-700 mb-1">Your Feedback:</p>
          <p class="text-sm text-slate-600 italic">"${escapeHtml(currentIssue.feedback)}"</p>
        </div>
      ` : ''}
      
      <div class="mt-4 flex items-center gap-2 text-xs text-green-700">
        <i data-lucide="check-circle-2" class="w-4 h-4"></i>
        <span>Thank you for your feedback!</span>
      </div>
    </div>
  `;
}

/**
 * Render images section
 */
function renderImages() {
  const beforeImages = currentIssue.before_images || [];
  const afterImages = currentIssue.after_images || [];
  
  if (beforeImages.length === 0 && afterImages.length === 0) {
    return '';
  }

  return `
    <div>
      <h3 class="text-lg font-semibold text-slate-800 mb-3 flex items-center gap-2">
        <i data-lucide="image" class="w-5 h-5 text-purple-600"></i>
        Before & After
      </h3>
      <div class="grid grid-cols-2 gap-4">
        ${beforeImages.length > 0 ? `
          <div>
            <p class="text-sm font-medium text-slate-600 mb-2">Before</p>
            <div class="grid gap-2">
              ${beforeImages.map(img => `
                <img src="${img}" alt="Before" class="w-full h-48 object-cover rounded-lg border border-slate-200" />
              `).join('')}
            </div>
          </div>
        ` : ''}
        
        ${afterImages.length > 0 ? `
          <div>
            <p class="text-sm font-medium text-slate-600 mb-2">After</p>
            <div class="grid gap-2">
              ${afterImages.map(img => `
                <img src="${img}" alt="After" class="w-full h-48 object-cover rounded-lg border border-slate-200" />
              `).join('')}
            </div>
          </div>
        ` : ''}
      </div>
    </div>
  `;
}

/**
 * Handle rating selection
 */
let selectedRatingValue = 0;

function selectRating(rating) {
  selectedRatingValue = rating;
  
  // Update visual feedback
  const stars = document.querySelectorAll('.rating-star');
  stars.forEach((star, index) => {
    if (index < rating) {
      star.classList.remove('text-slate-300');
      star.classList.add('text-yellow-500');
      star.setAttribute('fill', 'currentColor');
    } else {
      star.classList.add('text-slate-300');
      star.classList.remove('text-yellow-500');
      star.removeAttribute('fill');
    }
  });

  // Show selected rating text
  const ratingDisplay = document.getElementById('selectedRating');
  const ratingValue = document.getElementById('ratingValue');
  const submitBtn = document.getElementById('submitRatingBtn');
  
  if (ratingDisplay) ratingDisplay.classList.remove('hidden');
  if (ratingValue) ratingValue.textContent = rating;
  if (submitBtn) submitBtn.disabled = false;
}

function hoverRating(rating) {
  if (selectedRatingValue > 0) return; // Don't change if already selected
  
  const stars = document.querySelectorAll('.rating-star');
  stars.forEach((star, index) => {
    if (index < rating) {
      star.classList.remove('text-slate-300');
      star.classList.add('text-yellow-500');
    } else {
      star.classList.add('text-slate-300');
      star.classList.remove('text-yellow-500');
    }
  });
}

function resetRatingHover() {
  if (selectedRatingValue > 0) return;
  
  const stars = document.querySelectorAll('.rating-star');
  stars.forEach(star => {
    star.classList.add('text-slate-300');
    star.classList.remove('text-yellow-500');
    star.removeAttribute('fill');
  });
}

/**
 * Submit rating and feedback
 */
async function submitRating() {
  if (selectedRatingValue === 0) {
    showError('Please select a rating');
    return;
  }

  const feedbackText = document.getElementById('feedbackText');
  const feedback = feedbackText ? feedbackText.value.trim() : '';

  try {
    const response = await ApiClient.post(`/requests/${currentIssue.id}/rating`, {
      rating: selectedRatingValue,
      feedback: feedback
    });

    if (response.success) {
      showSuccess('Rating submitted successfully!');
      
      // Update current issue with new rating
      currentIssue.rating = selectedRatingValue;
      currentIssue.feedback = feedback;
      
      // Refresh display
      displayIssueDetail();
      
      // Reload list to show updated rating
      loadResolvedIssues();
    } else {
      showError(response.message || 'Failed to submit rating');
    }
  } catch (error) {
    console.error('Error submitting rating:', error);
    showError('Failed to submit rating');
  }
}

/**
 * Report recurring issue
 */
async function reportRecurringIssue() {
  if (!confirm('This will create a new service request based on this resolved issue. Continue?')) {
    return;
  }

  try {
    const response = await ApiClient.post(`/requests/${currentIssue.id}/recurring`, {
      original_request_id: currentIssue.id,
      title: `Recurring: ${currentIssue.title}`,
      description: `This is a recurring issue from ticket #${currentIssue.ticket_number}`,
      category: currentIssue.category,
      address: currentIssue.address
    });

    if (response.success) {
      showSuccess('Recurring issue reported! A new ticket has been created.');
      
      // Optionally navigate to active requests
      setTimeout(() => {
        window.location.href = 'user-dashboard.php';
      }, 2000);
    } else {
      showError(response.message || 'Failed to report recurring issue');
    }
  } catch (error) {
    console.error('Error reporting recurring issue:', error);
    showError('Failed to report recurring issue');
  }
}

/**
 * Handle search
 */
function handleSearch(e) {
  const query = e.target.value.toLowerCase().trim();
  
  if (!query) {
    filteredIssues = [...resolvedIssues];
  } else {
    filteredIssues = resolvedIssues.filter(issue => {
      return (
        issue.title.toLowerCase().includes(query) ||
        issue.ticket_number.toLowerCase().includes(query) ||
        issue.category.toLowerCase().includes(query) ||
        (issue.location && issue.location.toLowerCase().includes(query))
      );
    });
  }
  
  displayIssuesList();
}

/**
 * Toggle date filter dropdown
 */
function toggleDateFilterDropdown(e) {
  e.stopPropagation();
  const dropdown = document.getElementById('dateFilterDropdown');
  if (dropdown) {
    dropdown.classList.toggle('hidden');
  }
}

/**
 * Handle date filter change
 */
function handleDateFilterChange(e) {
  const filterValue = e.target.getAttribute('data-filter');
  
  // Update active state
  document.querySelectorAll('.date-filter-option').forEach(opt => {
    opt.classList.remove('bg-slate-50');
  });
  e.target.classList.add('bg-slate-50');
  
  // Update filter text
  const dateFilterText = document.getElementById('dateFilterText');
  if (dateFilterText) {
    dateFilterText.textContent = e.target.textContent.trim();
  }
  
  // Update current filter
  currentDateFilter = filterValue === 'all' ? 'all' : parseInt(filterValue);
  
  // Apply filter
  applyDateFilter();
  
  // Close dropdown
  const dropdown = document.getElementById('dateFilterDropdown');
  if (dropdown) {
    dropdown.classList.add('hidden');
  }
}

/**
 * Apply date filter to resolved issues
 */
function applyDateFilter() {
  if (currentDateFilter === 'all') {
    filteredIssues = [...resolvedIssues];
  } else {
    const cutoffDate = new Date();
    cutoffDate.setDate(cutoffDate.getDate() - currentDateFilter);
    
    filteredIssues = resolvedIssues.filter(issue => {
      if (!issue.resolved_at) return false;
      const resolvedDate = new Date(issue.resolved_at);
      return resolvedDate >= cutoffDate;
    });
  }
  
  displayIssuesList();
  
  // Select first issue if available
  if (filteredIssues.length > 0 && (!currentIssue || !filteredIssues.find(i => i.id === currentIssue.id))) {
    selectIssue(filteredIssues[0].id);
  } else if (filteredIssues.length === 0) {
    // Clear detail view if no issues match filter
    currentIssue = null;
    const detailView = document.getElementById('detailView');
    if (detailView) {
      detailView.innerHTML = `
        <div class="flex-1 flex items-center justify-center text-slate-400">
          <div class="text-center">
            <i data-lucide="calendar-x" class="w-16 h-16 mx-auto mb-4"></i>
            <p class="text-sm">No issues found in this time period</p>
          </div>
        </div>
      `;
      lucide.createIcons();
    }
  }
}

/**
 * Toggle filter menu (placeholder for future implementation)
 */
function toggleFilterMenu() {
  // TODO: Implement filter options (by category, rating)
  console.log('Filter menu clicked');
}

// ============ UTILITY FUNCTIONS ============

/**
 * Get category icon
 */
function getCategoryIcon(category) {
  const icons = {
    'Electricity': 'zap',
    'Water Supply': 'droplet',
    'Waste Management': 'trash-2',
    'Road Maintenance': 'construction',
    'Street Lighting': 'lightbulb',
    'Drainage': 'waves'
  };
  return icons[category] || 'wrench';
}

/**
 * Get category color
 */
function getCategoryColor(category) {
  const colors = {
    'Electricity': 'bg-yellow-100 text-yellow-700',
    'Water Supply': 'bg-blue-100 text-blue-700',
    'Waste Management': 'bg-green-100 text-green-700',
    'Road Maintenance': 'bg-orange-100 text-orange-700',
    'Street Lighting': 'bg-purple-100 text-purple-700',
    'Drainage': 'bg-cyan-100 text-cyan-700'
  };
  return colors[category] || 'bg-slate-100 text-slate-700';
}

/**
 * Render rating stars
 */
function renderRating(rating, sizeClass = 'w-4 h-4') {
  if (!rating || rating === 0) {
    return `<span class="text-xs text-slate-400">No rating</span>`;
  }
  
  const fullStars = Math.floor(rating);
  const hasHalfStar = rating % 1 >= 0.5;
  const emptyStars = 5 - fullStars - (hasHalfStar ? 1 : 0);
  
  let html = '<div class="flex items-center gap-0.5">';
  
  // Full stars
  for (let i = 0; i < fullStars; i++) {
    html += `<i data-lucide="star" class="${sizeClass} text-yellow-500" fill="currentColor"></i>`;
  }
  
  // Half star
  if (hasHalfStar) {
    html += `<i data-lucide="star-half" class="${sizeClass} text-yellow-500" fill="currentColor"></i>`;
  }
  
  // Empty stars
  for (let i = 0; i < emptyStars; i++) {
    html += `<i data-lucide="star" class="${sizeClass} text-slate-300"></i>`;
  }
  
  html += '</div>';
  return html;
}

/**
 * Format date
 */
function formatDate(dateString) {
  if (!dateString) return 'N/A';
  const date = new Date(dateString);
  return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
}

/**
 * Format date and time
 */
function formatDateTime(dateString) {
  if (!dateString) return 'N/A';
  const date = new Date(dateString);
  return date.toLocaleDateString('en-US', { 
    month: 'short', 
    day: 'numeric', 
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
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
  // You can implement a toast notification system here
  alert(message);
}

/**
 * Show error message
 */
function showError(message) {
  // You can implement a toast notification system here
  alert(message);
}
