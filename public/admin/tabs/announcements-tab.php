<!-- VIEW: ANNOUNCEMENTS -->
<div id="viewAnnouncements" class="view-content hidden space-y-6">
  <div class="flex justify-between items-center">
    <h2 class="text-2xl font-bold text-slate-800">Announcements</h2>
    <button id="createAnnouncementBtn" class="px-3 py-2 bg-blue-600 text-white rounded-lg text-sm font-bold hover:bg-blue-700 flex items-center gap-2 shadow-sm">
      <i data-lucide="plus" class="w-4 h-4"></i>
      Create Announcement
    </button>
  </div>

  <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
    <table class="w-full text-left text-sm">
      <thead class="bg-slate-50 text-slate-500 border-b border-slate-200">
        <tr>
          <th class="px-5 py-3">Title</th>
          <th class="px-5 py-3">Category</th>
          <th class="px-5 py-3">Type</th>
          <th class="px-5 py-3">Date</th>
          <th class="px-5 py-3">Status</th>
          <th class="px-5 py-3 text-right">Actions</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100" id="announcementsBody">
        <!-- Announcements will be loaded here -->
      </tbody>
    </table>
  </div>
</div>
