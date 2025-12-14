<!-- VIEW: BILLING -->
<div id="viewBilling" class="view-content hidden space-y-6">
  <h2 class="text-2xl font-bold text-slate-800">Billing Overview</h2>

  <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <div class="bg-gradient-to-r from-blue-600 to-blue-800 rounded-xl p-6 text-white shadow-lg">
      <p class="text-blue-100 text-sm font-medium">Total Revenue (Current Month)</p>
      <h3 class="text-3xl font-bold mt-1" id="totalRevenue">₱0.00</h3>
      <p class="text-xs text-blue-200 mt-2" id="revenueChange">Loading...</p>
    </div>
    <div class="bg-white rounded-xl p-6 border border-slate-200 shadow-sm">
      <p class="text-slate-500 text-sm font-medium">Pending Payments</p>
      <h3 class="text-3xl font-bold mt-1 text-amber-600" id="pendingPayments">0</h3>
      <p class="text-xs text-slate-400 mt-2">Requires verification</p>
    </div>
    <div class="bg-white rounded-xl p-6 border border-slate-200 shadow-sm">
      <p class="text-slate-500 text-sm font-medium">Successful Transactions</p>
      <h3 class="text-3xl font-bold mt-1 text-green-600" id="successfulTransactions">0</h3>
      <p class="text-xs text-slate-400 mt-2" id="successRate">0% success rate</p>
    </div>
  </div>

  <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="p-5 border-b border-slate-100">
      <h3 class="font-bold text-slate-800">Recent Transactions</h3>
    </div>
    <table class="w-full text-left text-sm">
      <thead class="bg-slate-50 text-slate-500 border-b border-slate-200">
        <tr>
          <th class="px-5 py-3">Transaction ID</th>
          <th class="px-5 py-3">Citizen</th>
          <th class="px-5 py-3">Type</th>
          <th class="px-5 py-3">Amount</th>
          <th class="px-5 py-3">Date</th>
          <th class="px-5 py-3">Status</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100" id="transactionsBody">
        <!-- Transactions will be loaded here -->
      </tbody>
    </table>
  </div>
</div>
