<!-- VIEW: TECHNICIANS -->
<div id="viewTechnicians" class="view-content hidden space-y-6">
  <div class="flex justify-between items-center">
    <h2 class="text-2xl font-bold text-slate-800">Technician Teams</h2>
    <button class="px-3 py-2 bg-slate-800 text-white rounded-lg text-sm font-bold flex items-center gap-2 hover:bg-slate-700">
      <i data-lucide="user-plus" class="w-4 h-4"></i>
      Add Team
    </button>
  </div>
  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="techniciansGrid">
    <!-- Technicians will be loaded here -->
  </div>
</div>
