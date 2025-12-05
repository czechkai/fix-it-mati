// Payments Page JavaScript

// State management
let bills = [];
let history = [];
let availableGateways = [];

// Initialize the page
async function init() {
    try {
        // Load available payment gateways
        const gatewaysResponse = await PaymentsAPI.getGateways();
        availableGateways = (gatewaysResponse.data && gatewaysResponse.data.gateways) || [];
        
        // Load payment history
        const historyResponse = await PaymentsAPI.getHistory({ limit: 5 });
        history = (historyResponse.data && historyResponse.data.payments) || [];
        
        // For now, use mock bills data (would come from billing API in production)
        loadMockBills();
        
        renderTotalCard();
        renderBillsList();
        renderTransactionHistory();
    } catch (error) {
        console.error('Failed to load payment data:', error);
        UIHelpers.showError('Failed to load payment data. Using demo data.');
        
        // Fallback to mock data
        loadMockBills();
        loadMockHistory();
        renderTotalCard();
        renderBillsList();
        renderTransactionHistory();
    }
    
    attachEventListeners();
    
    // Initialize Lucide icons after content is rendered
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
}

// Load mock bills data (placeholder until billing API is implemented)
function loadMockBills() {
    bills = [
        {
            id: 1,
            type: "Water",
            label: "Mati Water District",
            amount: 450.00,
            period: "Sept 15 - Oct 15",
            consumption: "24 m³",
            due: "Oct 25, 2023",
            iconName: "droplets",
            iconColor: "text-blue-500",
            status: "Unpaid"
        },
        {
            id: 3,
            type: "Electricity",
            label: "Davao Light (Linked)",
            amount: 650.00,
            period: "Sept 01 - Oct 01",
            consumption: "128 kWh",
            due: "Oct 20, 2023",
            iconName: "zap",
            iconColor: "text-amber-500",
            status: "Overdue"
        }
    ];
}

// Load mock history (fallback)
function loadMockHistory() {
    history = [
        { 
            id: 101, 
            created_at: "2023-09-25T10:00:00Z", 
            amount: 1100.00, 
            gateway: "gcash", 
            status: "completed", 
            transaction_id: "TRX-998822" 
        },
        { 
            id: 102, 
            created_at: "2023-08-25T10:00:00Z", 
            amount: 1050.00, 
            gateway: "stripe", 
            status: "completed", 
            transaction_id: "TRX-776611" 
        }
    ];
}

// Calculate total due
function calculateTotal() {
    return bills.reduce((acc, bill) => acc + bill.amount, 0);
}

// Render total card
function renderTotalCard() {
    const totalDue = calculateTotal();
    const amountElement = document.getElementById('totalAmount');
    if (amountElement) {
        amountElement.textContent = UIHelpers.formatCurrency(totalDue);
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
    
    listContainer.innerHTML = bills.map(bill => {
        const overdueClass = bill.status === 'Overdue' ? 'text-red-500' : 'text-slate-500';
        const overdueBadge = bill.status === 'Overdue' ? 
            '<span class="text-[10px] bg-red-100 text-red-600 px-2 py-0.5 rounded-full font-bold mt-1 inline-block">OVERDUE</span>' : '';
        
        return `
            <div class="p-4 hover:bg-slate-50 transition-colors flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-start gap-3">
                    <div class="p-2 bg-slate-100 rounded-lg">
                        <i data-lucide="${bill.iconName}" class="w-5 h-5 ${bill.iconColor}"></i>
                    </div>
                    <div>
                        <h4 class="font-semibold text-slate-800">${bill.type} Bill</h4>
                        <p class="text-xs text-slate-500">${bill.label}</p>
                        <div class="flex items-center gap-2 mt-1 text-xs text-slate-600">
                            <span class="bg-slate-100 px-1.5 py-0.5 rounded">Used: ${bill.consumption}</span>
                            <span>•</span>
                            <span>Period: ${bill.period}</span>
                        </div>
                    </div>
                </div>
                <div class="text-right">
                    <div class="font-bold text-slate-900 text-lg">₱${bill.amount.toFixed(2)}</div>
                    <div class="text-xs font-medium ${overdueClass}">Due: ${bill.due}</div>
                    ${overdueBadge}
                </div>
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
    
    listContainer.innerHTML = history.map(tx => {
        return `
            <div class="p-4 flex items-center justify-between hover:bg-slate-50 transition-colors">
                <div class="flex items-center gap-3">
                    <div class="bg-green-100 p-1.5 rounded-full text-green-600">
                        <i data-lucide="check-circle-2" class="w-3.5 h-3.5"></i>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-slate-800">Payment via ${tx.method}</p>
                        <p class="text-xs text-slate-400">${tx.date}</p>
                    </div>
                </div>
                <div class="text-right">
                    <p class="text-sm font-bold text-slate-800">- ₱${tx.amount.toFixed(2)}</p>
                    <button class="text-[10px] flex items-center gap-1 text-slate-400 hover:text-blue-600 ml-auto mt-1 transition-colors" onclick="downloadReceipt('${tx.ref}')">
                        <i data-lucide="download" class="w-2.5 h-2.5"></i> Receipt
                    </button>
                </div>
            </div>
        `;
    }).join('');
    
    // Reinitialize icons after rendering
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
}

// Attach event listeners
function attachEventListeners() {
    // Notification button
    const notificationBtn = document.getElementById('notificationBtn');
    if (notificationBtn) {
        notificationBtn.addEventListener('click', function() {
            alert('Open notifications');
        });
    }
    
    // Pay All button - Process payment via API
    const payAllBtn = document.getElementById('payAllBtn');
    if (payAllBtn) {
        payAllBtn.addEventListener('click', async function() {
            const total = calculateTotal();
            
            // Show payment gateway selection
            const gateway = prompt('Select payment gateway:\n1. GCash\n2. PayMongo\n3. Stripe\n\nEnter 1, 2, or 3:');
            const gatewayMap = { '1': 'gcash', '2': 'paymongo', '3': 'stripe' };
            const selectedGateway = gatewayMap[gateway];
            
            if (!selectedGateway) {
                alert('Invalid selection');
                return;
            }
            
            UIHelpers.showLoading(payAllBtn, 'Processing...');
            
            try {
                const result = await PaymentsAPI.process({
                    gateway: selectedGateway,
                    amount: total,
                    currency: 'PHP',
                    description: 'Utility bills payment',
                    metadata: {
                        bills: bills.map(b => b.id).join(',')
                    }
                });
                
                UIHelpers.hideLoading(payAllBtn);
                
                if (result.status === 'success' || result.status === 'completed') {
                    UIHelpers.showSuccess(`Payment successful! Transaction ID: ${result.transaction_id}`);
                    
                    // Reload payment history
                    const historyResponse = await PaymentsAPI.getHistory({ limit: 5 });
                    history = historyResponse.data || [];
                    renderTransactionHistory();
                } else {
                    UIHelpers.showError('Payment failed. Please try again.');
                }
            } catch (error) {
                UIHelpers.hideLoading(payAllBtn);
                UIHelpers.showError(`Payment error: ${error.message}`);
            }
        });
    }
    
    // View Statement button
    const statementBtn = document.getElementById('statementBtn');
    if (statementBtn) {
        statementBtn.addEventListener('click', function() {
            alert('View billing statement');
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
            window.location.href = 'user-dashboard.php'; // Redirect to create request
        });
    }
}

// Helper function to process payment
async function processPayment(gateway) {
    const total = calculateTotal();
    
    const confirmed = confirm(`Proceed with payment of ${UIHelpers.formatCurrency(total)} via ${gateway.toUpperCase()}?`);
    if (!confirmed) return;
    
    try {
        const result = await PaymentsAPI.process({
            gateway: gateway,
            amount: total,
            currency: 'PHP',
            description: 'Utility bills payment',
            metadata: {
                bills: bills.map(b => b.id).join(',')
            }
        });
        
        if (result.status === 'success' || result.status === 'completed') {
            UIHelpers.showSuccess(`Payment successful! Transaction ID: ${result.transaction_id}`);
            
            // Reload payment history
            const historyResponse = await PaymentsAPI.getHistory({ limit: 5 });
            history = historyResponse.data || [];
            renderTransactionHistory();
        } else {
            UIHelpers.showError('Payment failed. Please try again.');
        }
    } catch (error) {
        UIHelpers.showError(`Payment error: ${error.message}`);
    }
}

// Download receipt function
function downloadReceipt(transactionId) {
    alert(`Download receipt for transaction: ${transactionId}`);
    // In production, this would trigger a PDF download
}

// Initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
} else {
    init();
}
