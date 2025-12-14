<!-- VIEW: USERS -->
<div id="viewUsers" class="view-content hidden space-y-6">
  <div class="flex justify-between items-center">
    <h2 class="text-2xl font-bold text-slate-800">Citizen Database</h2>
    <div class="relative">
      <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 w-4 h-4"></i>
      <input type="text" id="userSearch" placeholder="Search citizens..." class="pl-9 pr-4 py-2 border border-slate-200 rounded-lg text-sm" />
    </div>
  </div>
  <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
    <table class="w-full text-left text-sm">
      <thead class="bg-slate-50 text-slate-500 border-b border-slate-200">
        <tr>
          <th class="px-5 py-3">Name</th>
          <th class="px-5 py-3">Email</th>
          <th class="px-5 py-3">Phone</th>
          <th class="px-5 py-3">Status</th>
          <th class="px-5 py-3 text-right">Actions</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100" id="usersBody">
        <!-- Users will be loaded here -->
      </tbody>
    </table>
  </div>
</div>
