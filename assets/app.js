// Basic interactions for FixItMati Dashboard
(function(){
  const mobileMenuBtn = document.getElementById('mobileMenuBtn');
  const mobileDrawer = document.getElementById('mobileDrawer');
  const tabsNav = document.getElementById('tabsNav');

  function openDrawer(){
    mobileDrawer.classList.remove('hidden');
    mobileDrawer.classList.add('show');
    // swap icon
    if (mobileMenuBtn) mobileMenuBtn.innerHTML = '<i data-lucide="x" class="w-6 h-6"></i>';
    lucide.createIcons();
  }
  function closeDrawer(){
    mobileDrawer.classList.add('hidden');
    mobileDrawer.classList.remove('show');
    if (mobileMenuBtn) mobileMenuBtn.innerHTML = '<i data-lucide="menu" class="w-6 h-6"></i>';
    lucide.createIcons();
  }

  if (mobileMenuBtn && mobileDrawer){
    mobileMenuBtn.addEventListener('click', () => {
      if (mobileDrawer.classList.contains('hidden')) openDrawer();
      else closeDrawer();
    });
    mobileDrawer.querySelectorAll('[data-close]').forEach(el => {
      el.addEventListener('click', closeDrawer);
    });
  }

  // Tabs: toggle active styles (no real content switching here)
  if (tabsNav){
    tabsNav.querySelectorAll('button[data-tab]').forEach(btn => {
      btn.addEventListener('click', () => {
        tabsNav.querySelectorAll('button[data-tab]').forEach(b => {
          b.classList.remove('border-blue-500','text-blue-600');
          b.classList.add('border-transparent','text-slate-500');
        });
        btn.classList.add('border-blue-500','text-blue-600');
        btn.classList.remove('border-transparent','text-slate-500');
      });
    });
  }
})();
