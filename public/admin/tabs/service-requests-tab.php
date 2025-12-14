<!-- VIEW: TICKETS -->
<div id="viewTickets" class="view-content hidden space-y-6">
  <div class="flex justify-between items-center">
    <h2 class="text-2xl font-bold text-slate-800">Service Requests</h2>
    <div class="flex gap-2">
      <button class="px-3 py-2 bg-white border border-slate-200 rounded-lg text-sm font-medium text-slate-600 hover:bg-slate-50 flex items-center gap-2">
        <i data-lucide="filter" class="w-4 h-4"></i>
        Filter
      </button>
    </div>
  </div>

  <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
      <table class="w-full text-left text-sm">
        <thead class="bg-slate-50 text-slate-500 border-b border-slate-200">
          <tr>
            <th class="px-5 py-3 font-medium">Ticket ID</th>
            <th class="px-5 py-3 font-medium">Citizen</th>
            <th class="px-5 py-3 font-medium">Type</th>
            <th class="px-5 py-3 font-medium">Issue</th>
            <th class="px-5 py-3 font-medium">Location</th>
            <th class="px-5 py-3 font-medium">Priority</th>
            <th class="px-5 py-3 font-medium">Status</th>
            <th class="px-5 py-3 font-medium">Date</th>
            <th class="px-5 py-3 font-medium text-right">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100" id="allTicketsBody">
          <!-- All tickets will be loaded here -->
        </tbody>
      </table>
    </div>
  </div>
</div>
