// Payments Page JavaScript

// State management
let bills = [];
let history = [];
let currentPaymentId = null;

// Initialize the page
async function init() {
    try {
        // Show loading state
        showLoading();

        // Load current bills from database
        const billsResponse = await fetch('/api/payments/current', {
            headers: {
                'Authorization': `Bearer ${localStorage.getItem('auth_token')}`
            }
        });
        
        if (billsResponse.ok) {
            const billsData = await billsResponse.json();
            if (billsData.success && billsData.data) {
                bills = billsData.data.bills || [];
                const totalDue = billsData.data.total_due || 0;
                updatePaymentInfo(bills, totalDue);
            }
        }
        
        // Load payment history from database
        const historyResponse = await fetch('/api/payments/history?limit=5', {
            headers: {
                'Authorization': `Bearer ${localStorage.getItem('auth_token')}`
            }
        });
        
        if (historyResponse.ok) {
            const historyData = await historyResponse.json();
            if (historyData.success && historyData.data) {
                history = historyData.data.payments || [];
            }
        }

        // Render UI
        renderBillsList();
        renderTransactionHistory();
        hideLoading();
    } catch (error) {
        console.error('Failed to load payment data:', error);
        hideLoading();
        showError('Failed to load payment data. Please refresh the page.');
    }
    
    attachEventListeners();
    
    // Initialize Lucide icons after content is rendered
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
}

// Show loading state
function showLoading() {
    const billsList = document.getElementById('billsList');
    const transactionList = document.getElementById('transactionList');
    
    if (billsList) {
        billsList.innerHTML = `
            <div class="p-8 text-center">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mb-2"></div>
                <p class="text-sm text-slate-600">Loading bills...</p>
            </div>
        `;
    }
    
    if (transactionList) {
        transactionList.innerHTML = `
            <div class="p-4 text-center text-slate-500 text-sm">
                <div class="inline-block animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600 mb-1"></div>
                <p>Loading transactions...</p>
            </div>
        `;
    }
    
    // Add loading animation to payment info card
    const amountElement = document.getElementById('totalAmount');
    const dueDateElement = document.getElementById('dueDate');
    const statusElement = document.getElementById('accountStatus');
    
    if (amountElement && !amountElement.classList.contains('loading-skeleton')) {
        amountElement.classList.add('loading-skeleton');
    }
    if (dueDateElement && !dueDateElement.classList.contains('loading-skeleton')) {
        dueDateElement.classList.add('loading-skeleton');
    }
    if (statusElement && !statusElement.classList.contains('loading-skeleton')) {
        statusElement.classList.add('loading-skeleton');
    }
}

// Hide loading state
function hideLoading() {
    // Loading will be replaced by actual content
}

// Show error message
function showError(message) {
    const billsList = document.getElementById('billsList');
    if (billsList) {
        billsList.innerHTML = `
            <div class="p-8 text-center">
                <i data-lucide="alert-circle" class="w-12 h-12 text-red-500 mx-auto mb-2"></i>
                <p class="text-sm text-red-600">${message}</p>
            </div>
        `;
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    }
}

// Update total amount display
function updateTotalDisplay(totalDue) {
    const amountElement = document.getElementById('totalAmount');
    if (amountElement) {
        amountElement.textContent = `₱${totalDue.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',')}`;
    }
}

// Update payment information card with bill data
function updatePaymentInfo(bills, totalDue) {
    const amountElement = document.getElementById('totalAmount');
    const dueDateElement = document.getElementById('dueDate');
    const statusElement = document.getElementById('accountStatus');
    
    if (amountElement) {
        amountElement.classList.remove('loading-skeleton');
        amountElement.textContent = `₱${totalDue.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',')}`;
    }
    
    // Find earliest due date
    if (dueDateElement) {
        dueDateElement.classList.remove('loading-skeleton');
        
        if (bills.length > 0) {
            const earliestBill = bills.reduce((earliest, bill) => {
                return new Date(bill.due_date) < new Date(earliest.due_date) ? bill : earliest;
            });
            const formattedDate = new Date(earliestBill.due_date).toLocaleDateString('en-US', { 
                month: 'short', 
                day: 'numeric', 
                year: 'numeric' 
            });
            dueDateElement.textContent = formattedDate;
        } else {
            dueDateElement.textContent = 'No pending bills';
        }
    }
    
    // Update account status
    if (statusElement) {
        statusElement.classList.remove('loading-skeleton');
        
        if (totalDue === 0) {
            statusElement.textContent = 'Paid';
            statusElement.className = 'bg-green-400 text-green-900 px-3 py-1 rounded-md text-xs font-bold';
        } else {
            const hasOverdue = bills.some(bill => bill.status === 'overdue');
            if (hasOverdue) {
                statusElement.textContent = 'Overdue';
                statusElement.className = 'bg-red-400 text-red-900 px-3 py-1 rounded-md text-xs font-bold';
            } else {
                statusElement.textContent = 'Payment Required';
                statusElement.className = 'bg-amber-400 text-amber-900 px-3 py-1 rounded-md text-xs font-bold';
            }
        }
    }
}

// Render transaction history
function renderTransactionHistory() {
    const listContainer = document.getElementById('transactionList');
    if (!listContainer) return;
    
    listContainer.innerHTML = history.map(tx => {
        const gatewayNames = {
            'gcash': 'GCash',
            'stripe': 'Visa **** 4242',
            'paypal': 'PayPal',
            'paymongo': 'PayMongo'
        };
        const methodDisplay = gatewayNames[tx.gateway] || tx.gateway;
        
        return `
            <div class="p-4 flex items-center justify-between hover:bg-slate-50 transition-colors">
                <div class="flex items-center gap-3">
                    <div class="bg-green-100 p-1.5 rounded-full text-green-600">
                        <i data-lucide="check-circle-2" class="w-3.5 h-3.5"></i>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-slate-800">Payment via ${methodDisplay}</p>
                        <p class="text-xs text-slate-500">${UIHelpers.formatDate(tx.created_at)} • ${tx.transaction_id}</p>
                    </div>
                </div>
                <div class="text-right">
                    <div class="font-bold text-slate-900">${UIHelpers.formatCurrency(tx.amount)}</div>
                    <div class="text-xs text-green-600 font-medium capitalize">${tx.status}</div>
                </div>
            </div>
        `;
    }).join('');
    
    // Reinitialize icons after rendering
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
}

// Render bills list
function renderBillsList() {
    const listContainer = document.getElementById('billsList');
    
    if (!listContainer) return;
    
    if (bills.length === 0) {
        listContainer.innerHTML = `
            <div class="p-8 text-center">
                <i data-lucide="check-circle" class="w-12 h-12 text-green-500 mx-auto mb-2"></i>
                <p class="text-sm text-slate-600">No pending bills</p>
            </div>
        `;
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
        return;
    }
    
    listContainer.innerHTML = bills.map(bill => {
        const items = bill.items || [];
        const statusClass = bill.status === 'overdue' ? 'text-red-500' : 'text-slate-500';
        const statusBadge = bill.status === 'overdue' ? 
            '<span class="text-[10px] bg-red-100 text-red-600 px-2 py-0.5 rounded-full font-bold mt-1 inline-block">OVERDUE</span>' : '';
        
        // Build items display
        const itemsHtml = items.map(item => {
            const icon = item.category === 'water' ? 'droplets' : 'zap';
            const iconColor = item.category === 'water' ? 'text-blue-500' : 'text-amber-500';
            
            return `
                <div class="flex items-start gap-3 mt-2">
                    <div class="p-1.5 bg-slate-50 rounded">
                        <i data-lucide="${icon}" class="w-4 h-4 ${iconColor}"></i>
                    </div>
                    <div class="flex-1">
                        <h4 class="font-medium text-slate-800 text-sm">${item.description}</h4>
                        <p class="text-xs text-slate-500 capitalize">${item.category}</p>
                    </div>
                    <div class="font-bold text-slate-900">₱${parseFloat(item.amount).toFixed(2)}</div>
                </div>
            `;
        }).join('');
        
        return `
            <div class="p-4 hover:bg-slate-50 transition-colors">
                <div class="flex items-start justify-between gap-4 mb-3">
                    <div>
                        <h4 class="font-semibold text-slate-800">${bill.bill_month}</h4>
                        <p class="text-xs ${statusClass} capitalize mt-1">Status: ${bill.status}</p>
                        ${statusBadge}
                    </div>
                    <div class="text-right">
                        <div class="font-bold text-slate-900 text-lg">₱${parseFloat(bill.amount).toFixed(2)}</div>
                        <div class="text-xs text-slate-500">Due: ${formatDate(bill.due_date)}</div>
                    </div>
                </div>
                ${items.length > 0 ? `<div class="border-t border-slate-100 pt-3 space-y-2">${itemsHtml}</div>` : ''}
            </div>
        `;
    }).join('');
    
    // Reinitialize icons after rendering
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
}

// Render transaction history
function renderTransactionHistory() {
    const listContainer = document.getElementById('transactionList');
    
    if (!listContainer) return;
    
    if (history.length === 0) {
        listContainer.innerHTML = `
            <div class="p-4 text-center text-slate-500 text-sm">No transaction history</div>
        `;
        return;
    }
    
    listContainer.innerHTML = history.map(tx => {
        const methodNames = {
            'gcash': 'GCash',
            'maya': 'Maya',
            'card': 'Card'
        };
        const methodDisplay = methodNames[tx.payment_method] || tx.payment_method || 'Payment';
        
        return `
            <div class="p-4 flex items-center justify-between hover:bg-slate-50 transition-colors">
                <div class="flex items-center gap-3">
                    <div class="bg-green-100 p-1.5 rounded-full text-green-600">
                        <i data-lucide="check-circle-2" class="w-3.5 h-3.5"></i>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-slate-800">${methodDisplay}</p>
                        <p class="text-xs text-slate-500">${formatDate(tx.created_at)} • ${tx.reference_number || 'N/A'}</p>
                    </div>
                </div>
                <div class="text-right">
                    <div class="font-bold text-slate-900">₱${parseFloat(tx.amount).toFixed(2)}</div>
                    <div class="text-xs text-green-600 font-medium capitalize">${tx.status}</div>
                </div>
            </div>
        `;
    }).join('');
    
    // Reinitialize icons after rendering
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
}

// Format date helper
function formatDate(dateString) {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
}

// Attach event listeners
function attachEventListeners() {
    // Pay All button - Open modal
    const payAllBtn = document.getElementById('payAllBtn');
    if (payAllBtn) {
        payAllBtn.addEventListener('click', openPaymentModal);
    }
    
    // Close modal button
    const closeModal = document.getElementById('closeModal');
    if (closeModal) {
        closeModal.addEventListener('click', closePaymentModal);
    }
    
    // Payment option buttons
    const paymentOptions = document.querySelectorAll('.payment-option-btn');
    paymentOptions.forEach(btn => {
        btn.addEventListener('click', function() {
            const gateway = this.getAttribute('data-gateway');
            processPayment(gateway);
        });
    });
    
    // Close modal when clicking outside
    const modal = document.getElementById('paymentModal');
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closePaymentModal();
            }
        });
    }
    
    // View Statement button
    const statementBtn = document.getElementById('statementBtn');
    if (statementBtn) {
        statementBtn.addEventListener('click', function() {
            UIHelpers.showInfo('View billing statement');
        });
    }
    
    // Payment method buttons - Quick pay shortcuts
    const gcashBtn = document.getElementById('gcashBtn');
    if (gcashBtn) {
        gcashBtn.addEventListener('click', async function() {
            await processPayment('gcash');
        });
    }
    
    const mayaBtn = document.getElementById('mayaBtn');
    if (mayaBtn) {
        mayaBtn.addEventListener('click', async function() {
            await processPayment('paymongo'); // Maya through PayMongo
        });
    }
    
    const cardBtn = document.getElementById('cardBtn');
    if (cardBtn) {
        cardBtn.addEventListener('click', async function() {
            await processPayment('stripe');
        });
    }

    // View All transactions button
    const viewAllBtn = document.getElementById('viewAllBtn');
    if (viewAllBtn) {
        viewAllBtn.addEventListener('click', function() {
            window.location.href = 'payment-history.php';
        });
    }
    
    // Report issue button
    const reportBtn = document.getElementById('reportBtn');
    if (reportBtn) {
        reportBtn.addEventListener('click', function() {
            window.location.href = 'pages/user/create-request.php'; // Redirect to create request
        });
    }
}

// Open payment modal
function openPaymentModal() {
    if (bills.length === 0) {
        UIHelpers.showInfo('No bills to pay');
        return;
    }
    
    const modal = document.getElementById('paymentModal');
    const modalAmount = document.getElementById('modalTotalAmount');
    
    if (modal && modalAmount) {
        const totalDue = bills.reduce((sum, bill) => sum + parseFloat(bill.amount), 0);
        modalAmount.textContent = `₱${totalDue.toFixed(2)}`;
        modal.style.display = 'flex';
        
        // Store current payment ID (use first bill for now)
        currentPaymentId = bills[0].id;
        
        // Reinitialize icons
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    }
}

// Close payment modal
function closePaymentModal() {
    const modal = document.getElementById('paymentModal');
    const processing = document.getElementById('paymentProcessing');
    
    if (modal) {
        modal.style.display = 'none';
    }
    
    if (processing) {
        processing.classList.add('hidden');
    }
    
    currentPaymentId = null;
}

// Process payment through selected gateway
async function processPayment(gateway) {
    if (!currentPaymentId) {
        UIHelpers.showError('No payment selected');
        return;
    }
    
    const processing = document.getElementById('paymentProcessing');
    const paymentOptions = document.querySelectorAll('.payment-option-btn');
    
    // Show processing state
    if (processing) {
        processing.classList.remove('hidden');
    }
    
    // Disable all payment options
    paymentOptions.forEach(btn => btn.disabled = true);
    
    try {
        const totalDue = bills.reduce((sum, bill) => sum + parseFloat(bill.amount), 0);
        
        const response = await fetch('/api/payments/process', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${localStorage.getItem('auth_token')}`
            },
            body: JSON.stringify({
                payment_id: currentPaymentId,
                gateway: gateway,
                amount: totalDue
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            // Hide modal
            closePaymentModal();
            
            // Show success message
            UIHelpers.showSuccess(`Payment successful! Reference: ${result.data.reference_number}`);
            
            // Reload page data
            init();
        } else {
            throw new Error(result.message || 'Payment failed');
        }
    } catch (error) {
        console.error('Payment error:', error);
        UIHelpers.showError(`Payment failed: ${error.message}`);
    } finally {
        // Hide processing state
        if (processing) {
            processing.classList.add('hidden');
        }
        
        // Re-enable payment options
        paymentOptions.forEach(btn => btn.disabled = false);
    }
}

// Initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
} else {
    init();
}
