/**
 * Admin Billing & Payments Management
 * Real-time transaction monitoring and invoice generation
 */

(function() {
  'use strict';

  // State Management
  const state = {
    transactions: [],
    filteredTransactions: [],
    currentFilter: 'All',
    selectedTransaction: null,
    stats: {
      totalRevenue: 0,
      pendingCount: 0,
      collectionRate: 0
    },
    users: []
  };

  // DOM Elements
  const elements = {
    transactionsTableBody: document.getElementById('transactionsTableBody'),
    transactionCount: document.getElementById('transactionCount'),
    filterBtns: document.querySelectorAll('.filter-btn'),
    searchInput: document.getElementById('searchInput'),
    createInvoiceBtn: document.getElementById('createInvoiceBtn'),
    exportBtn: document.getElementById('exportBtn'),
    invoiceModal: document.getElementById('invoiceModal'),
    invoiceForm: document.getElementById('invoiceForm'),
    closeInvoiceModal: document.getElementById('closeInvoiceModal'),
    cancelInvoiceBtn: document.getElementById('cancelInvoiceBtn'),
    invoiceModalBackdrop: document.getElementById('invoiceModalBackdrop'),
    transactionDrawer: document.getElementById('transactionDrawer'),
    drawerBackdrop: document.getElementById('drawerBackdrop'),
    closeDrawer: document.getElementById('closeDrawer'),
    drawerContent: document.getElementById('drawerContent'),
    citizenSelect: document.getElementById('citizenSelect'),
    logoutBtn: document.getElementById('logoutBtn'),
    totalRevenue: document.getElementById('totalRevenue'),
    pendingCount: document.getElementById('pendingCount'),
    collectionRate: document.getElementById('collectionRate'),
    pendingProgress: document.getElementById('pendingProgress')
  };

  // Initialize
  function init() {
    setupEventListeners();
    loadTransactions();
    loadUsers();
    loadStats();
    startRealtimeUpdates();
    lucide.createIcons();
  }

  // Event Listeners
  function setupEventListeners() {
    // Filter buttons
    elements.filterBtns.forEach(btn => {
      btn.addEventListener('click', handleFilterChange);
    });

    // Search
    if (elements.searchInput) {
      elements.searchInput.addEventListener('input', handleSearch);
    }

    // Create Invoice
    if (elements.createInvoiceBtn) {
      elements.createInvoiceBtn.addEventListener('click', openInvoiceModal);
    }

    // Export
    if (elements.exportBtn) {
      elements.exportBtn.addEventListener('click', exportTransactions);
    }

    // Invoice Modal
    if (elements.closeInvoiceModal) {
      elements.closeInvoiceModal.addEventListener('click', closeInvoiceModal);
    }
    if (elements.cancelInvoiceBtn) {
      elements.cancelInvoiceBtn.addEventListener('click', closeInvoiceModal);
    }
    if (elements.invoiceModalBackdrop) {
      elements.invoiceModalBackdrop.addEventListener('click', closeInvoiceModal);
    }
    if (elements.invoiceForm) {
      elements.invoiceForm.addEventListener('submit', handleInvoiceSubmit);
    }

    // Transaction Drawer
    if (elements.closeDrawer) {
      elements.closeDrawer.addEventListener('click', closeTransactionDrawer);
    }
    if (elements.drawerBackdrop) {
      elements.drawerBackdrop.addEventListener('click', closeTransactionDrawer);
    }

    // Logout
    if (elements.logoutBtn) {
      elements.logoutBtn.addEventListener('click', handleLogout);
    }
  }

  // Load Transactions
  async function loadTransactions() {
    try {
      const token = localStorage.getItem('auth_token');
      const response = await fetch('/api/admin/transactions', {
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json'
        }
      });

      const data = await response.json();

      if (data.success) {
        state.transactions = data.data || [];
        state.filteredTransactions = state.transactions;
        renderTransactions();
      } else {
        showError('Failed to load transactions');
      }
    } catch (error) {
      console.error('Error loading transactions:', error);
      showError('Error loading transactions');
    }
  }

  // Load Users for Invoice Creation
  async function loadUsers() {
    try {
      const token = localStorage.getItem('auth_token');
      const response = await fetch('/api/admin/users', {
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json'
        }
      });

      const data = await response.json();

      if (data.success) {
        state.users = data.data || [];
        populateUserSelect();
      }
    } catch (error) {
      console.error('Error loading users:', error);
    }
  }

  // Load Stats
  async function loadStats() {
    try {
      const token = localStorage.getItem('auth_token');
      const response = await fetch('/api/admin/billing/stats', {
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json'
        }
      });

      const data = await response.json();

      if (data.success) {
        state.stats = data.data;
        updateStatsDisplay();
      }
    } catch (error) {
      console.error('Error loading stats:', error);
    }
  }

  // Update Stats Display
  function updateStatsDisplay() {
    if (elements.totalRevenue) {
      elements.totalRevenue.textContent = `₱${parseFloat(state.stats.totalRevenue || 0).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
    }
    if (elements.pendingCount) {
      elements.pendingCount.textContent = state.stats.pendingCount || 0;
    }
    if (elements.collectionRate) {
      elements.collectionRate.textContent = `${state.stats.collectionRate || 0}%`;
    }
    if (elements.pendingProgress) {
      const progress = Math.min((state.stats.pendingCount || 0) / 100 * 100, 100);
      elements.pendingProgress.style.width = `${progress}%`;
    }
  }

  // Populate User Select
  function populateUserSelect() {
    if (!elements.citizenSelect) return;

    elements.citizenSelect.innerHTML = '<option value="">Select User...</option>';
    
    state.users.forEach(user => {
      const option = document.createElement('option');
      option.value = user.id;
      option.textContent = `${user.full_name || user.username} (${user.email})`;
      elements.citizenSelect.appendChild(option);
    });
  }

  // Render Transactions
  function renderTransactions() {
    if (!elements.transactionsTableBody) return;

    if (state.filteredTransactions.length === 0) {
      elements.transactionsTableBody.innerHTML = `
        <tr>
          <td colspan="7" class="px-6 py-12 text-center text-slate-500">
            <div class="flex flex-col items-center gap-3">
              <i data-lucide="inbox" class="w-12 h-12 text-slate-300"></i>
              <p>No transactions found</p>
            </div>
          </td>
        </tr>
      `;
      lucide.createIcons();
      return;
    }

    elements.transactionsTableBody.innerHTML = state.filteredTransactions.map(transaction => `
      <tr class="hover:bg-slate-50 transition-colors group cursor-pointer" data-transaction-id="${transaction.id}">
        <td class="px-6 py-4 font-mono text-slate-500">${transaction.reference_number || transaction.id}</td>
        <td class="px-6 py-4">
          <div class="font-medium text-slate-800">${transaction.user_name || 'Unknown User'}</div>
          <div class="text-xs text-slate-500">${formatDate(transaction.created_at)}</div>
        </td>
        <td class="px-6 py-4 text-slate-600">${transaction.payment_type || 'General Payment'}</td>
        <td class="px-6 py-4 font-bold text-slate-800">₱${parseFloat(transaction.amount || 0).toFixed(2)}</td>
        <td class="px-6 py-4 text-slate-600 flex items-center gap-2">
          <i data-lucide="credit-card" class="w-3.5 h-3.5 text-slate-400"></i> ${transaction.payment_method || 'N/A'}
        </td>
        <td class="px-6 py-4">
          <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-bold border ${getStatusBadge(transaction.status)}">
            ${transaction.status || 'Unknown'}
          </span>
        </td>
        <td class="px-6 py-4 text-right">
          <button class="view-transaction text-slate-400 hover:text-blue-600 p-1 rounded hover:bg-blue-50 transition-colors">
            <i data-lucide="eye" class="w-4 h-4"></i>
          </button>
        </td>
      </tr>
    `).join('');

    // Add click listeners
    document.querySelectorAll('[data-transaction-id]').forEach(row => {
      row.addEventListener('click', (e) => {
        const transactionId = row.dataset.transactionId;
        openTransactionDrawer(transactionId);
      });
    });

    // Update count
    if (elements.transactionCount) {
      elements.transactionCount.textContent = `Showing ${state.filteredTransactions.length} transaction${state.filteredTransactions.length !== 1 ? 's' : ''}`;
    }

    lucide.createIcons();
  }

  // Get Status Badge Classes
  function getStatusBadge(status) {
    const statusMap = {
      'success': 'bg-green-100 text-green-700 border-green-200',
      'completed': 'bg-green-100 text-green-700 border-green-200',
      'paid': 'bg-green-100 text-green-700 border-green-200',
      'pending': 'bg-amber-100 text-amber-700 border-amber-200',
      'processing': 'bg-amber-100 text-amber-700 border-amber-200',
      'failed': 'bg-red-100 text-red-700 border-red-200',
      'cancelled': 'bg-red-100 text-red-700 border-red-200'
    };
    return statusMap[status?.toLowerCase()] || 'bg-slate-100 text-slate-700 border-slate-200';
  }

  // Format Date
  function formatDate(dateString) {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleString('en-US', {
      month: 'short',
      day: 'numeric',
      hour: 'numeric',
      minute: '2-digit',
      hour12: true
    });
  }

  // Handle Filter Change
  function handleFilterChange(e) {
    const status = e.target.dataset.status;
    state.currentFilter = status;

    // Update active button
    elements.filterBtns.forEach(btn => {
      btn.classList.remove('active', 'bg-slate-800', 'text-white', 'border-slate-800');
      btn.classList.add('bg-white', 'text-slate-600', 'border-slate-200');
    });
    e.target.classList.add('active', 'bg-slate-800', 'text-white', 'border-slate-800');
    e.target.classList.remove('bg-white', 'text-slate-600', 'border-slate-200');

    // Filter transactions
    if (status === 'All') {
      state.filteredTransactions = state.transactions;
    } else {
      state.filteredTransactions = state.transactions.filter(t => 
        t.status?.toLowerCase() === status.toLowerCase()
      );
    }

    renderTransactions();
  }

  // Handle Search
  function handleSearch(e) {
    const searchTerm = e.target.value.toLowerCase();

    if (!searchTerm) {
      state.filteredTransactions = state.transactions;
    } else {
      state.filteredTransactions = state.transactions.filter(t => 
        (t.reference_number || '').toLowerCase().includes(searchTerm) ||
        (t.user_name || '').toLowerCase().includes(searchTerm) ||
        (t.amount || '').toString().includes(searchTerm)
      );
    }

    renderTransactions();
  }

  // Open Invoice Modal
  function openInvoiceModal() {
    if (elements.invoiceModal) {
      elements.invoiceModal.classList.remove('hidden');
      lucide.createIcons();
    }
  }

  // Close Invoice Modal
  function closeInvoiceModal() {
    if (elements.invoiceModal) {
      elements.invoiceModal.classList.add('hidden');
    }
    if (elements.invoiceForm) {
      elements.invoiceForm.reset();
    }
  }

  // Handle Invoice Submit
  async function handleInvoiceSubmit(e) {
    e.preventDefault();

    const formData = new FormData(e.target);
    const invoiceData = {
      user_id: formData.get('user_id'),
      bill_type: formData.get('bill_type'),
      amount: parseFloat(formData.get('amount')),
      due_date: formData.get('due_date'),
      description: formData.get('description'),
      status: 'unpaid'
    };

    // Validation
    if (!invoiceData.user_id) {
      showError('Please select a user');
      return;
    }

    if (!invoiceData.bill_type) {
      showError('Please select a bill type');
      return;
    }

    if (!invoiceData.amount || invoiceData.amount <= 0) {
      showError('Please enter a valid amount');
      return;
    }

    if (!invoiceData.due_date) {
      showError('Please select a due date');
      return;
    }

    // Check if due date is in the past
    const selectedDate = new Date(invoiceData.due_date);
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    
    if (selectedDate < today) {
      showError('Due date cannot be in the past');
      return;
    }

    try {
      const token = localStorage.getItem('auth_token');
      const response = await fetch('/api/admin/billing/create-invoice', {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json'
        },
        body: JSON.stringify(invoiceData)
      });

      const data = await response.json();

      if (data.success) {
        showSuccess('Invoice generated and user has been notified!');
        closeInvoiceModal();
        
        // Reload data to show new invoice
        await loadTransactions();
        await loadStats();
      } else {
        showError(data.message || 'Failed to create invoice');
      }
    } catch (error) {
      console.error('Error creating invoice:', error);
      showError('Error creating invoice. Please try again.');
    }
  }

  // Open Transaction Drawer
  async function openTransactionDrawer(transactionId) {
    const transaction = state.transactions.find(t => t.id == transactionId);
    if (!transaction) return;

    state.selectedTransaction = transaction;

    // Render drawer content
    if (elements.drawerContent) {
      elements.drawerContent.innerHTML = `
        <!-- Status Banner -->
        <div class="p-4 rounded-xl flex items-center gap-3 ${getStatusBadge(transaction.status)}">
          <i data-lucide="${transaction.status === 'success' || transaction.status === 'paid' ? 'check-circle-2' : 'alert-circle'}" class="w-6 h-6"></i>
          <div>
            <p class="font-bold text-sm">Payment ${transaction.status}</p>
            <p class="text-xs opacity-80">Reference: ${transaction.reference_number || transaction.id}</p>
          </div>
        </div>

        <!-- Amount Display -->
        <div class="text-center py-4 border-b border-slate-100">
          <p class="text-slate-500 text-xs uppercase font-bold tracking-wider mb-1">Total Amount</p>
          <h2 class="text-3xl font-bold text-slate-900">₱${parseFloat(transaction.amount || 0).toFixed(2)}</h2>
        </div>

        <!-- Details List -->
        <div class="space-y-4">
          <div class="flex justify-between text-sm">
            <span class="text-slate-500">Payer Name</span>
            <span class="font-medium text-slate-800">${transaction.user_name || 'Unknown'}</span>
          </div>
          <div class="flex justify-between text-sm">
            <span class="text-slate-500">Payment For</span>
            <span class="font-medium text-slate-800">${transaction.payment_type || 'General Payment'}</span>
          </div>
          <div class="flex justify-between text-sm">
            <span class="text-slate-500">Payment Method</span>
            <span class="font-medium text-slate-800">${transaction.payment_method || 'N/A'}</span>
          </div>
          <div class="flex justify-between text-sm">
            <span class="text-slate-500">Date & Time</span>
            <span class="font-medium text-slate-800">${formatDate(transaction.created_at)}</span>
          </div>
        </div>

        ${transaction.status === 'pending' ? `
          <!-- Proof of Payment -->
          <div class="bg-slate-50 p-4 rounded-lg border border-slate-200 text-center">
            <p class="text-xs font-bold text-slate-500 uppercase mb-2">Proof of Payment</p>
            <div class="h-32 bg-slate-200 rounded flex items-center justify-center text-slate-400 mb-2">
              <p class="text-sm">Awaiting manual verification</p>
            </div>
            ${transaction.notes ? `<p class="text-xs text-slate-500 mt-2">${transaction.notes}</p>` : ''}
          </div>

          <!-- Approval Actions -->
          <div class="pt-4 flex gap-3">
            <button class="approve-btn flex-1 bg-green-600 hover:bg-green-700 text-white font-bold py-2.5 rounded-lg text-sm transition-colors shadow-sm">
              Approve
            </button>
            <button class="reject-btn flex-1 bg-white border border-red-200 text-red-600 hover:bg-red-50 font-bold py-2.5 rounded-lg text-sm transition-colors">
              Reject
            </button>
          </div>
        ` : `
          <!-- Download Receipt -->
          <div class="pt-4">
            <button class="download-receipt-btn w-full bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 font-bold py-2.5 rounded-lg text-sm transition-colors flex items-center justify-center gap-2">
              <i data-lucide="download" class="w-4 h-4"></i> Download Receipt
            </button>
          </div>
        `}
      `;

      // Add action listeners
      const approveBtn = elements.drawerContent.querySelector('.approve-btn');
      const rejectBtn = elements.drawerContent.querySelector('.reject-btn');
      const downloadBtn = elements.drawerContent.querySelector('.download-receipt-btn');

      if (approveBtn) {
        approveBtn.addEventListener('click', () => handleTransactionAction(transaction.id, 'approve'));
      }
      if (rejectBtn) {
        rejectBtn.addEventListener('click', () => handleTransactionAction(transaction.id, 'reject'));
      }
      if (downloadBtn) {
        downloadBtn.addEventListener('click', () => downloadReceipt(transaction.id));
      }

      lucide.createIcons();
    }

    if (elements.transactionDrawer) {
      elements.transactionDrawer.classList.remove('hidden');
    }
  }

  // Close Transaction Drawer
  function closeTransactionDrawer() {
    if (elements.transactionDrawer) {
      elements.transactionDrawer.classList.add('hidden');
    }
    state.selectedTransaction = null;
  }

  // Handle Transaction Action (Approve/Reject)
  async function handleTransactionAction(transactionId, action) {
    // Confirm action
    const confirmMessage = action === 'approve' 
      ? 'Are you sure you want to approve this transaction? The user will be notified.'
      : 'Are you sure you want to reject this transaction? The user will be notified.';
    
    if (!confirm(confirmMessage)) {
      return;
    }

    let reason = null;
    if (action === 'reject') {
      reason = prompt('Please provide a reason for rejection:', 'Invalid payment proof');
      if (!reason || reason.trim() === '') {
        showError('Rejection reason is required');
        return;
      }
    }

    try {
      const token = localStorage.getItem('auth_token');
      const body = action === 'reject' ? JSON.stringify({ reason }) : null;
      
      const response = await fetch(`/api/admin/transactions/${transactionId}/${action}`, {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json'
        },
        body: body
      });

      const data = await response.json();

      if (data.success) {
        showSuccess(`Transaction ${action}d successfully. User has been notified.`);
        closeTransactionDrawer();
        
        // Reload data to reflect changes
        await loadTransactions();
        await loadStats();
      } else {
        showError(data.message || `Failed to ${action} transaction`);
      }
    } catch (error) {
      console.error(`Error ${action}ing transaction:`, error);
      showError(`Error ${action}ing transaction. Please try again.`);
    }
  }

  // Download Receipt
  async function downloadReceipt(transactionId) {
    try {
      const token = localStorage.getItem('auth_token');
      const response = await fetch(`/api/admin/transactions/${transactionId}/receipt`, {
        headers: {
          'Authorization': `Bearer ${token}`
        }
      });

      if (response.ok) {
        const blob = await response.blob();
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `receipt-${transactionId}.pdf`;
        document.body.appendChild(a);
        a.click();
        window.URL.revokeObjectURL(url);
        document.body.removeChild(a);
        showSuccess('Receipt downloaded successfully');
      } else {
        showError('Failed to download receipt');
      }
    } catch (error) {
      console.error('Error downloading receipt:', error);
      showError('Error downloading receipt');
    }
  }

  // Export Transactions
  async function exportTransactions() {
    try {
      const token = localStorage.getItem('auth_token');
      const response = await fetch('/api/admin/transactions/export', {
        headers: {
          'Authorization': `Bearer ${token}`
        }
      });

      if (response.ok) {
        const blob = await response.blob();
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `transactions-${new Date().toISOString().split('T')[0]}.csv`;
        document.body.appendChild(a);
        a.click();
        window.URL.revokeObjectURL(url);
        document.body.removeChild(a);
        showSuccess('Transactions exported successfully');
      } else {
        showError('Failed to export transactions');
      }
    } catch (error) {
      console.error('Error exporting transactions:', error);
      showError('Error exporting transactions');
    }
  }

  // Start Realtime Updates
  function startRealtimeUpdates() {
    // Refresh data every 30 seconds
    setInterval(() => {
      loadTransactions();
      loadStats();
    }, 30000);
  }

  // Handle Logout
  function handleLogout() {
    if (confirm('Are you sure you want to logout?')) {
      localStorage.removeItem('auth_token');
      localStorage.removeItem('user');
      window.location.href = '/login.php';
    }
  }

  // Toast Notifications
  function showSuccess(message) {
    showToast(message, 'success');
  }

  function showError(message) {
    showToast(message, 'error');
  }

  function showToast(message, type) {
    const toast = document.createElement('div');
    toast.className = `fixed top-4 right-4 px-6 py-3 rounded-lg shadow-lg text-white font-medium z-[100] ${
      type === 'success' ? 'bg-green-600' : 'bg-red-600'
    }`;
    toast.textContent = message;
    toast.style.animation = 'slide-in-from-top-5 0.3s ease-out';

    document.body.appendChild(toast);

    setTimeout(() => {
      toast.style.animation = 'fade-out 0.3s ease-out';
      setTimeout(() => {
        document.body.removeChild(toast);
      }, 300);
    }, 3000);
  }

  // Start the application
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }

})();
