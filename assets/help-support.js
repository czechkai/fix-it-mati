/**
 * Help & Support Page - FixItMati
 * FAQ search and filtering functionality
 */

// State
let activeCategory = 'All';
let searchQuery = '';
let expandedFaq = null;

// Categories Data
const categories = [
  { id: 'All', label: 'All Topics', icon: 'file-text' },
  { id: 'Billing', label: 'Payments & Bills', icon: 'credit-card' },
  { id: 'Water', label: 'Water Supply', icon: 'droplets' },
  { id: 'Electricity', label: 'Electricity', icon: 'zap' },
  { id: 'Account', label: 'My Account', icon: 'user' },
];

// FAQs Data
const faqs = [
  {
    id: 1,
    category: 'Billing',
    question: "How long does it take for my payment to reflect?",
    answer: "Payments made via GCash or Maya are typically posted in real-time. However, please allow up to 24 hours for the system to fully update your balance. If it takes longer, please submit a ticket with your transaction reference number."
  },
  {
    id: 2,
    category: 'Water',
    question: "How do I report a water leak in my area?",
    answer: "You can submit a 'Service Request' directly from your Dashboard. Select 'Water Supply' as the category, take a photo of the leak, and pin the location. Our team usually responds within 2-4 hours for major leaks."
  },
  {
    id: 3,
    category: 'Account',
    question: "Can I link multiple meters to one account?",
    answer: "Yes! Go to Profile > Linked Meters. You can add as many water or electric meter accounts as you manage (e.g., for your home, business, or rental properties)."
  },
  {
    id: 4,
    category: 'Electricity',
    question: "My electricity bill seems unusually high.",
    answer: "First, check if your consumption (kWh) has increased compared to last month. If the reading matches your meter but still seems wrong, you can request a 'Meter Re-calibration' through the Service Request tab."
  },
  {
    id: 5,
    category: 'Billing',
    question: "Where can I pay my bill in person?",
    answer: "Aside from the app, you can pay at the City Treasurer's Office (City Hall) or at any authorized Bayad Center in Mati City."
  },
  {
    id: 6,
    category: 'Water',
    question: "Why is my water pressure low?",
    answer: "Low water pressure can be caused by several factors: maintenance work in your area, pipeline issues, or problems with your property's plumbing. Check our announcements for scheduled maintenance. If the issue persists, submit a service request with details about when the problem started."
  },
  {
    id: 7,
    category: 'Electricity',
    question: "What should I do during a power outage?",
    answer: "First, check if the outage affects your entire neighborhood or just your property. Look for announcements about scheduled maintenance or emergency repairs. If it's isolated to your property, check your circuit breakers. For wider outages, we typically restore power within 2-6 hours depending on the issue."
  },
  {
    id: 8,
    category: 'Account',
    question: "How do I reset my password?",
    answer: "Click 'Forgot Password' on the login page and enter your registered email address. You'll receive a password reset link within 5 minutes. If you don't receive it, check your spam folder or contact support at support@mati.gov.ph."
  },
  {
    id: 9,
    category: 'Billing',
    question: "Can I set up automatic payment for my bills?",
    answer: "Yes! You can enable auto-debit for your linked bank accounts or e-wallets. Go to Profile > Payment Settings > Auto-Pay. You'll receive a notification 3 days before each automatic payment is processed."
  },
  {
    id: 10,
    category: 'Water',
    question: "How is my water bill calculated?",
    answer: "Your water bill is based on your meter reading (cubic meters consumed) multiplied by the current rate per cubic meter, plus any applicable fees. The first 10 cubic meters are charged at a lower rate. You can view your consumption history and detailed breakdown in the Linked Meters section."
  }
];

// Render Categories
function renderCategories() {
  const container = document.getElementById('categoryTabs');
  container.innerHTML = categories.map(cat => `
    <button
      data-category="${cat.id}"
      class="category-btn flex items-center gap-2 px-4 py-2 rounded-full text-sm font-medium shadow-sm transition-all ${
        activeCategory === cat.id
          ? 'bg-white text-blue-600 ring-2 ring-blue-600'
          : 'bg-white text-slate-600 hover:bg-slate-50 border border-slate-200'
      }"
    >
      <i data-lucide="${cat.icon}" class="w-4 h-4"></i>
      ${cat.label}
    </button>
  `).join('');
  
  lucide.createIcons();
  
  // Attach event listeners
  document.querySelectorAll('.category-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      activeCategory = btn.dataset.category;
      renderCategories();
      renderFAQs();
    });
  });
}

// Filter FAQs
function getFilteredFAQs() {
  return faqs.filter(faq => {
    const matchesCategory = activeCategory === 'All' || faq.category === activeCategory;
    const matchesSearch = faq.question.toLowerCase().includes(searchQuery.toLowerCase()) ||
                          faq.answer.toLowerCase().includes(searchQuery.toLowerCase());
    return matchesCategory && matchesSearch;
  });
}

// Render FAQs
function renderFAQs() {
  const filteredFaqs = getFilteredFAQs();
  const container = document.getElementById('faqList');
  const noResults = document.getElementById('noResults');
  const heading = document.getElementById('faqHeading');
  
  // Update heading
  heading.textContent = activeCategory === 'All' 
    ? 'Frequently Asked Questions' 
    : `${activeCategory} Questions`;
  
  if (filteredFaqs.length === 0) {
    container.classList.add('hidden');
    noResults.classList.remove('hidden');
    document.getElementById('searchTerm').textContent = searchQuery;
  } else {
    container.classList.remove('hidden');
    noResults.classList.add('hidden');
    
    container.innerHTML = filteredFaqs.map(faq => `
      <div 
        class="faq-item bg-white rounded-xl border transition-all duration-200 overflow-hidden ${
          expandedFaq === faq.id 
            ? 'border-blue-200 shadow-md ring-1 ring-blue-50' 
            : 'border-slate-200 shadow-sm hover:border-blue-300'
        }"
        data-faq-id="${faq.id}"
      >
        <button 
          class="faq-toggle w-full text-left px-5 py-4 flex justify-between items-center gap-4 bg-white"
        >
          <span class="font-semibold text-sm ${expandedFaq === faq.id ? 'text-blue-700' : 'text-slate-800'}">
            ${highlightSearchTerm(faq.question)}
          </span>
          <span class="text-slate-400 flex-shrink-0">
            <i data-lucide="${expandedFaq === faq.id ? 'chevron-up' : 'chevron-down'}" class="w-5 h-5"></i>
          </span>
        </button>
        
        ${expandedFaq === faq.id ? `
          <div class="faq-answer px-5 pb-5 pt-0">
            <div class="border-t border-slate-100 pt-3 text-sm text-slate-600 leading-relaxed bg-slate-50/50 p-4 rounded-lg">
              ${highlightSearchTerm(faq.answer)}
            </div>
          </div>
        ` : ''}
      </div>
    `).join('');
  }
  
  lucide.createIcons();
  
  // Attach event listeners
  document.querySelectorAll('.faq-toggle').forEach(btn => {
    btn.addEventListener('click', () => {
      const faqItem = btn.closest('.faq-item');
      const faqId = parseInt(faqItem.dataset.faqId);
      expandedFaq = expandedFaq === faqId ? null : faqId;
      renderFAQs();
    });
  });
}

// Highlight search term in text
function highlightSearchTerm(text) {
  if (!searchQuery || searchQuery.length < 2) return text;
  
  const regex = new RegExp(`(${searchQuery})`, 'gi');
  return text.replace(regex, '<mark class="bg-yellow-200 text-slate-900">$1</mark>');
}

// Get search suggestions
function getSearchSuggestions(query) {
  if (!query || query.length < 2) return [];
  
  const suggestions = [];
  const lowerQuery = query.toLowerCase();
  
  // Search in questions and answers
  faqs.forEach(faq => {
    const questionMatch = faq.question.toLowerCase().includes(lowerQuery);
    const answerMatch = faq.answer.toLowerCase().includes(lowerQuery);
    
    if (questionMatch || answerMatch) {
      suggestions.push({
        text: faq.question,
        category: faq.category,
        id: faq.id
      });
    }
  });
  
  // Remove duplicates and limit to 5 suggestions
  return suggestions.slice(0, 5);
}

// Render search suggestions
function renderSearchSuggestions(suggestions) {
  const container = document.getElementById('searchSuggestions');
  
  if (suggestions.length === 0) {
    container.classList.add('hidden');
    return;
  }
  
  container.innerHTML = suggestions.map(suggestion => `
    <button
      class="search-suggestion w-full text-left px-4 py-3 hover:bg-blue-50 transition-colors border-b border-slate-100 last:border-b-0 flex items-start gap-3"
      data-faq-id="${suggestion.id}"
    >
      <i data-lucide="search" class="w-4 h-4 text-slate-400 mt-0.5 flex-shrink-0"></i>
      <div class="flex-1">
        <div class="text-sm text-slate-800 font-medium">${suggestion.text}</div>
        <div class="text-xs text-slate-500 mt-0.5">${suggestion.category}</div>
      </div>
    </button>
  `).join('');
  
  container.classList.remove('hidden');
  lucide.createIcons();
  
  // Attach click handlers
  document.querySelectorAll('.search-suggestion').forEach(btn => {
    btn.addEventListener('click', () => {
      const faqId = parseInt(btn.dataset.faqId);
      expandedFaq = faqId;
      container.classList.add('hidden');
      
      // Set search and filter
      const faq = faqs.find(f => f.id === faqId);
      if (faq) {
        activeCategory = faq.category;
        renderCategories();
      }
      renderFAQs();
      
      // Scroll to the FAQ
      setTimeout(() => {
        const faqElement = document.querySelector(`[data-faq-id="${faqId}"]`);
        if (faqElement) {
          faqElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
      }, 100);
    });
  });
}

// Search functionality
function handleSearch(event) {
  searchQuery = event.target.value.toLowerCase();
  
  // Show suggestions
  const suggestions = getSearchSuggestions(searchQuery);
  renderSearchSuggestions(suggestions);
  
  // Update FAQ list
  renderFAQs();
}

// Clear search
function clearSearch() {
  searchQuery = '';
  document.getElementById('searchInput').value = '';
  document.getElementById('searchSuggestions').classList.add('hidden');
  renderFAQs();
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
  // Render all lucide icons
  lucide.createIcons();
  
  // Initial render
  renderCategories();
  renderFAQs();
  
  // Search input
  const searchInput = document.getElementById('searchInput');
  const searchSuggestions = document.getElementById('searchSuggestions');
  
  searchInput.addEventListener('input', handleSearch);
  
  // Focus event - show suggestions if there's a query
  searchInput.addEventListener('focus', () => {
    if (searchQuery.length >= 2) {
      const suggestions = getSearchSuggestions(searchQuery);
      renderSearchSuggestions(suggestions);
    }
  });
  
  // Click outside to close suggestions
  document.addEventListener('click', (e) => {
    if (!searchInput.contains(e.target) && !searchSuggestions.contains(e.target)) {
      searchSuggestions.classList.add('hidden');
    }
  });
  
  // Clear search button
  const clearSearchBtn = document.getElementById('clearSearchBtn');
  clearSearchBtn.addEventListener('click', clearSearch);
  
  // Mobile drawer
  const mobileMenuBtn = document.getElementById('mobileMenuBtn');
  const mobileDrawer = document.getElementById('mobileDrawer');
  const drawerCloseButtons = mobileDrawer.querySelectorAll('[data-close]');
  
  mobileMenuBtn.addEventListener('click', () => {
    mobileDrawer.classList.remove('hidden');
  });
  
  drawerCloseButtons.forEach(btn => {
    btn.addEventListener('click', () => {
      mobileDrawer.classList.add('hidden');
    });
  });
  
  // Profile dropdown
  const profileBtn = document.getElementById('profileBtn');
  const profileDropdown = document.getElementById('profileDropdown');
  
  profileBtn.addEventListener('click', (e) => {
    e.stopPropagation();
    profileDropdown.classList.toggle('hidden');
  });
  
  document.addEventListener('click', (e) => {
    if (!profileBtn.contains(e.target) && !profileDropdown.contains(e.target)) {
      profileDropdown.classList.add('hidden');
    }
  });
  
  // Navigation buttons
  document.getElementById('serviceAddressesBtn')?.addEventListener('click', () => {
    window.location.href = 'service-addresses.php';
  });
  
  document.getElementById('linkedMetersBtn')?.addEventListener('click', () => {
    window.location.href = 'linked-meters.php';
  });
  
  document.getElementById('helpSupportBtn')?.addEventListener('click', () => {
    window.location.href = 'help-support.php';
  });
});
