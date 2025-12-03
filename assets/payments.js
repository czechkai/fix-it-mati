// Payments Page JavaScript

// Mock Data for Bills
const bills = [
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
        id: 2,
        type: "Garbage",
        label: "City Environment Office",
        amount: 150.00,
        period: "October 2023",
        consumption: "Fixed Rate",
        due: "Oct 30, 2023",
        iconName: "hammer",
        iconColor: "text-green-600",
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

// Mock Data for Transaction History
const history = [
    { id: 101, date: "Sep 25, 2023", amount: 1100.00, method: "GCash", status: "Paid", ref: "TRX-998822" },
    { id: 102, date: "Aug 25, 2023", amount: 1050.00, method: "Visa **** 4242", status: "Paid", ref: "TRX-776611" }
];

// Calculate total due
function calculateTotal() {
    return bills.reduce((acc, bill) => acc + bill.amount, 0);
}

// Initialize the page
function init() {
    renderTotalCard();
    renderBillsList();
    renderTransactionHistory();
    attachEventListeners();
    // Initialize Lucide icons after content is rendered
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
}

// Render total card
function renderTotalCard() {
    const totalDue = calculateTotal();
    const amountElement = document.getElementById('totalAmount');
    if (amountElement) {
        amountElement.textContent = `₱${totalDue.toFixed(2)}`;
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
    
    // Pay All button
    const payAllBtn = document.getElementById('payAllBtn');
    if (payAllBtn) {
        payAllBtn.addEventListener('click', function() {
            const total = calculateTotal();
            alert(`Proceed to payment: ₱${total.toFixed(2)}`);
        });
    }
    
    // View Statement button
    const statementBtn = document.getElementById('statementBtn');
    if (statementBtn) {
        statementBtn.addEventListener('click', function() {
            alert('View billing statement');
        });
    }
    
    // Payment method buttons
    const gcashBtn = document.getElementById('gcashBtn');
    if (gcashBtn) {
        gcashBtn.addEventListener('click', function() {
            alert('Pay with GCash');
        });
    }
    
    const mayaBtn = document.getElementById('mayaBtn');
    if (mayaBtn) {
        mayaBtn.addEventListener('click', function() {
            alert('Pay with Maya');
        });
    }
    
    const cardBtn = document.getElementById('cardBtn');
    if (cardBtn) {
        cardBtn.addEventListener('click', function() {
            alert('Pay with Card');
        });
    }
    
    // View All transactions button
    const viewAllBtn = document.getElementById('viewAllBtn');
    if (viewAllBtn) {
        viewAllBtn.addEventListener('click', function() {
            alert('View all transactions');
        });
    }
    
    // Report issue button
    const reportBtn = document.getElementById('reportBtn');
    if (reportBtn) {
        reportBtn.addEventListener('click', function() {
            alert('Report billing issue');
        });
    }
}

// Download receipt function
function downloadReceipt(refNumber) {
    alert(`Download receipt for ${refNumber}`);
    // In a real app: window.location.href = `download-receipt.php?ref=${refNumber}`;
}

// Initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
} else {
    init();
}
