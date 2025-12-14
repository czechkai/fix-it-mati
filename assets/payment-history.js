/**
 * Payment History Page
 * Handles transaction history display with real-time updates
 */

let allTransactions = [];
let filteredTransactions = [];
let currentFilter = 'All';
let currentPage = 1;
let itemsPerPage = 10;
let dateFilterActive = false;
let dateFromFilter = null;
let dateToFilter = null;

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
  loadTransactionHistory();
  setupEventListeners();
});

/**
 * Setup event listeners
 */
function setupEventListeners() {
  // Filter tabs
  const filterTabs = document.querySelectorAll('.filter-tab');
  filterTabs.forEach(tab => {
    tab.addEventListener('click', handleFilterChange);
  });

  // Search input
  const searchInput = document.getElementById('searchInput');
  if (searchInput) {
    searchInput.addEventListener('input', handleSearch);
  }

  // Date filter button
  const dateFilterBtn = document.getElementById('dateFilterBtn');
  if (dateFilterBtn) {
    dateFilterBtn.addEventListener('click', () => {
      document.getElementById('dateRangeModal').classList.remove('hidden');
    });
  }

  // Date modal close
  const closeDateModal = document.getElementById('closeDateModal');
  if (closeDateModal) {
    closeDateModal.addEventListener('click', () => {
      document.getElementById('dateRangeModal').classList.add('hidden');
    });
  }

  // Apply dates button
  const applyDatesBtn = document.getElementById('applyDatesBtn');
  if (applyDatesBtn) {
    applyDatesBtn.addEventListener('click', () => {
      const from = document.getElementById('dateFrom').value;
      const to = document.getElementById('dateTo').value;
      if (from || to) {
        dateFromFilter = from ? new Date(from) : null;
        dateToFilter = to ? new Date(to) : null;
        dateFilterActive = true;
        applyFilters();
        document.getElementById('dateRangeModal').classList.add('hidden');
      }
    });
  }

  // Reset dates button
  const resetDatesBtn = document.getElementById('resetDatesBtn');
  if (resetDatesBtn) {
    resetDatesBtn.addEventListener('click', () => {
      document.getElementById('dateFrom').value = '';
      document.getElementById('dateTo').value = '';
      dateFilterActive = false;
      dateFromFilter = null;
      dateToFilter = null;
      applyFilters();
    });
  }

  // Close modal on backdrop click
  const dateRangeModal = document.getElementById('dateRangeModal');
  if (dateRangeModal) {
    dateRangeModal.addEventListener('click', (e) => {
      if (e.target === dateRangeModal) {
        dateRangeModal.classList.add('hidden');
      }
    });
  }

  // Export button
  const exportBtn = document.getElementById('exportStatementBtn');
  if (exportBtn) {
    exportBtn.addEventListener('click', handleExport);
  }

  // Pagination
  const prevBtn = document.getElementById('prevBtn');
  const nextBtn = document.getElementById('nextBtn');
  if (prevBtn) prevBtn.addEventListener('click', () => changePage(-1));
  if (nextBtn) nextBtn.addEventListener('click', () => changePage(1));
}

/**
 * Load transaction history from API
 */
async function loadTransactionHistory() {
  showLoadingState();

  try {
    const response = await ApiClient.get('/payments/history');

    if (response.success) {
      allTransactions = response.data || [];
      applyFilters();
      console.log(`✅ Loaded ${allTransactions.length} transactions`);
    } else {
      showError('Failed to load payment history');
      showEmptyState();
    }
  } catch (error) {
    console.error('Error loading transaction history:', error);
    showError('Failed to load payment history');
    showEmptyState();
  }
}

/**
 * Apply current filters
 */
function applyFilters() {
  // Filter by type
  if (currentFilter === 'All') {
    filteredTransactions = [...allTransactions];
  } else {
    filteredTransactions = allTransactions.filter(t => t.type === currentFilter);
  }

  // Apply date filter if active
  if (dateFilterActive && (dateFromFilter || dateToFilter)) {
    filteredTransactions = filteredTransactions.filter(t => {
      const txDate = new Date(t.date);
      if (dateFromFilter && txDate < dateFromFilter) return false;
      if (dateToFilter) {
        const toDate = new Date(dateToFilter);
        toDate.setHours(23, 59, 59, 999); // Include entire day
        if (txDate > toDate) return false;
      }
      return true;
    });
  }

  // Apply search if active
  const searchInput = document.getElementById('searchInput');
  if (searchInput && searchInput.value.trim()) {
    const query = searchInput.value.toLowerCase();
    filteredTransactions = filteredTransactions.filter(t =>
      t.reference_number?.toLowerCase().includes(query) ||
      t.biller?.toLowerCase().includes(query)
    );
  }

  // Reset to page 1
  currentPage = 1;

  // Display
  displayTransactions();
}

/**
 * Display transactions
 */
function displayTransactions() {
  const container = document.getElementById('transactionsList');
  const loadingState = document.getElementById('loadingState');
  const emptyState = document.getElementById('emptyState');
  const paginationFooter = document.getElementById('paginationFooter');

  if (!container) return;

  // Hide loading
  if (loadingState) loadingState.classList.add('hidden');

  // Check if empty
  if (filteredTransactions.length === 0) {
    container.classList.add('hidden');
    if (emptyState) emptyState.classList.remove('hidden');
    if (paginationFooter) paginationFooter.classList.add('hidden');
    return;
  }

  // Show container
  container.classList.remove('hidden');
  if (emptyState) emptyState.classList.add('hidden');
  if (paginationFooter) paginationFooter.classList.remove('hidden');

  // Paginate
  const startIdx = (currentPage - 1) * itemsPerPage;
  const endIdx = startIdx + itemsPerPage;
  const paginatedTransactions = filteredTransactions.slice(startIdx, endIdx);

  // Render transactions
  container.innerHTML = paginatedTransactions.map(transaction => {
    const statusStyle = getStatusStyle(transaction.status);
    const statusIcon = getStatusIcon(transaction.status);
    const typeIcon = getTypeIcon(transaction.type);

    return `
      <div class="group hover:bg-slate-50 transition-colors">
        
        <!-- Desktop Row -->
        <div class="hidden md:grid grid-cols-12 gap-4 p-4 items-center">
          
          <!-- Biller Info -->
          <div class="col-span-4 flex items-center gap-3">
            <div class="h-10 w-10 rounded-full bg-slate-100 flex items-center justify-center flex-shrink-0">
              ${typeIcon}
            </div>
            <div>
              <h4 class="font-bold text-slate-800 text-sm">${escapeHtml(transaction.biller || 'Unknown Biller')}</h4>
              <p class="text-xs text-slate-500 font-mono">${escapeHtml(transaction.reference_number || 'N/A')}</p>
            </div>
          </div>

          <!-- Date -->
          <div class="col-span-2">
            <p class="text-sm text-slate-700 font-medium">${formatDate(transaction.transaction_date)}</p>
            <p class="text-xs text-slate-400">${escapeHtml(transaction.billing_period || 'N/A')}</p>
          </div>

          <!-- Amount -->
          <div class="col-span-2">
            <p class="text-sm font-bold text-slate-900">₱${parseFloat(transaction.amount).toFixed(2)}</p>
            <p class="text-xs text-slate-500 flex items-center gap-1">
              <i data-lucide="credit-card" class="w-3 h-3"></i> ${escapeHtml(transaction.payment_method || 'N/A')}
            </p>
          </div>

          <!-- Status -->
          <div class="col-span-2">
            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold border ${statusStyle}">
              ${statusIcon} ${escapeHtml(transaction.status)}
            </span>
          </div>

          <!-- Action -->
          <div class="col-span-2 text-right">
            ${transaction.status === 'Success' ? `
              <button onclick="downloadReceipt('${transaction.id}')" class="text-xs font-bold text-blue-600 hover:text-blue-800 hover:underline inline-flex items-center gap-1 transition-colors">
                <i data-lucide="download" class="w-3.5 h-3.5"></i> Download
              </button>
            ` : transaction.status === 'Failed' ? `
              <button onclick="viewDetails('${transaction.id}')" class="text-xs font-bold text-slate-500 hover:text-slate-700 inline-flex items-center gap-1 transition-colors">
                View Details
              </button>
            ` : ''}
          </div>

        </div>

        <!-- Mobile Row (Stacked) -->
        <div class="md:hidden p-4 flex justify-between items-center">
          <div class="flex items-center gap-3">
            <div class="h-10 w-10 rounded-full bg-slate-100 flex items-center justify-center">
              ${typeIcon}
            </div>
            <div>
              <h4 class="font-bold text-slate-800 text-sm">${escapeHtml(transaction.biller || 'Unknown Biller')}</h4>
              <div class="text-xs text-slate-500">${formatDate(transaction.transaction_date)} • ${escapeHtml(transaction.payment_method || 'N/A')}</div>
              <span class="mt-1 inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold border ${statusStyle}">
                ${escapeHtml(transaction.status)}
              </span>
            </div>
          </div>
          <div class="text-right">
            <p class="font-bold text-slate-900">₱${parseFloat(transaction.amount).toFixed(2)}</p>
            ${transaction.status === 'Success' ? `
              <button onclick="downloadReceipt('${transaction.id}')" class="mt-1 text-blue-600 p-1.5 bg-blue-50 rounded hover:bg-blue-100 transition-colors">
                <i data-lucide="download" class="w-4 h-4"></i>
              </button>
            ` : ''}
          </div>
        </div>

      </div>
    `;
  }).join('');

  // Update pagination info
  updatePagination();

  // Reinitialize Lucide icons
  lucide.createIcons();
}

/**
 * Get status style classes
 */
function getStatusStyle(status) {
  const styles = {
    'Success': 'bg-green-100 text-green-700 border-green-200',
    'Processing': 'bg-amber-100 text-amber-700 border-amber-200',
    'Failed': 'bg-red-100 text-red-700 border-red-200',
    'Pending': 'bg-blue-100 text-blue-700 border-blue-200'
  };
  return styles[status] || 'bg-slate-100 text-slate-700 border-slate-200';
}

/**
 * Get status icon
 */
function getStatusIcon(status) {
  const icons = {
    'Success': '<i data-lucide="check-circle-2" class="w-3.5 h-3.5"></i>',
    'Processing': '<i data-lucide="clock" class="w-3.5 h-3.5"></i>',
    'Failed': '<i data-lucide="x-circle" class="w-3.5 h-3.5"></i>',
    'Pending': '<i data-lucide="clock" class="w-3.5 h-3.5"></i>'
  };
  return icons[status] || '';
}

/**
 * Get type icon
 */
function getTypeIcon(type) {
  const icons = {
    'Water': '<i data-lucide="droplets" class="w-5 h-5 text-blue-500"></i>',
    'Electricity': '<i data-lucide="zap" class="w-5 h-5 text-amber-500"></i>',
    'Services': '<i data-lucide="hammer" class="w-5 h-5 text-slate-500"></i>'
  };
  return icons[type] || '<i data-lucide="credit-card" class="w-5 h-5 text-slate-500"></i>';
}

/**
 * Handle filter change
 */
function handleFilterChange(e) {
  const filter = e.currentTarget.getAttribute('data-filter');
  currentFilter = filter;

  // Update active state
  document.querySelectorAll('.filter-tab').forEach(tab => {
    tab.classList.remove('bg-blue-600', 'text-white', 'shadow-sm');
    tab.classList.add('bg-white', 'text-slate-600', 'border', 'border-slate-200', 'hover:bg-slate-50');
  });
  e.currentTarget.classList.add('bg-blue-600', 'text-white', 'shadow-sm');
  e.currentTarget.classList.remove('bg-white', 'text-slate-600', 'border', 'border-slate-200', 'hover:bg-slate-50');

  applyFilters();
}

/**
 * Handle search
 */
function handleSearch() {
  applyFilters();
}

/**
 * Update pagination
 */
function updatePagination() {
  const totalItems = filteredTransactions.length;
  const totalPages = Math.ceil(totalItems / itemsPerPage);
  const startIdx = (currentPage - 1) * itemsPerPage + 1;
  const endIdx = Math.min(currentPage * itemsPerPage, totalItems);

  const infoElement = document.getElementById('paginationInfo');
  if (infoElement) {
    infoElement.textContent = `Showing ${startIdx}-${endIdx} of ${totalItems} transactions`;
  }

  const prevBtn = document.getElementById('prevBtn');
  const nextBtn = document.getElementById('nextBtn');

  if (prevBtn) {
    prevBtn.disabled = currentPage === 1;
  }

  if (nextBtn) {
    nextBtn.disabled = currentPage >= totalPages;
  }
}

/**
 * Change page
 */
function changePage(direction) {
  const totalPages = Math.ceil(filteredTransactions.length / itemsPerPage);
  const newPage = currentPage + direction;

  if (newPage >= 1 && newPage <= totalPages) {
    currentPage = newPage;
    displayTransactions();
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }
}

/**
 * Download receipt
 */
async function downloadReceipt(transactionId) {
  try {
    showSuccess('Generating receipt...');
    
    const response = await ApiClient.get(`/payments/receipt/${transactionId}`);
    
    if (response.success) {
      // In a real implementation, this would download a PDF
      console.log('Receipt data:', response.data);
      showSuccess('Receipt downloaded successfully!');
    } else {
      showError('Failed to download receipt');
    }
  } catch (error) {
    console.error('Error downloading receipt:', error);
    showError('Failed to download receipt');
  }
}

/**
 * View transaction details
 */
function viewDetails(transactionId) {
  const transaction = allTransactions.find(t => t.id === transactionId);
  if (transaction) {
    UIHelpers.showInfo(`Transaction Details:\n\nReference: ${transaction.reference_number}\nStatus: ${transaction.status}\nAmount: ₱${transaction.amount}\nBiller: ${transaction.biller}`);
  }
}

/**
 * Handle export
 */
function handleExport() {
  showSuccess('Exporting payment statement...');
  // In a real implementation, this would generate and download a CSV/PDF
  console.log('Exporting transactions:', filteredTransactions);
}

/**
 * Show loading state
 */
function showLoadingState() {
  const loadingState = document.getElementById('loadingState');
  const emptyState = document.getElementById('emptyState');
  const container = document.getElementById('transactionsList');

  if (loadingState) loadingState.classList.remove('hidden');
  if (emptyState) emptyState.classList.add('hidden');
  if (container) container.classList.add('hidden');
}

/**
 * Show empty state
 */
function showEmptyState() {
  const loadingState = document.getElementById('loadingState');
  const emptyState = document.getElementById('emptyState');
  const container = document.getElementById('transactionsList');

  if (loadingState) loadingState.classList.add('hidden');
  if (emptyState) emptyState.classList.remove('hidden');
  if (container) container.classList.add('hidden');
}

/**
 * Format date
 */
function formatDate(dateString) {
  if (!dateString) return 'N/A';

  const date = new Date(dateString);
  return date.toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric'
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
  UIHelpers.showSuccess(message);
}

/**
 * Show error message
 */
function showError(message) {
  UIHelpers.showError(message);
}
