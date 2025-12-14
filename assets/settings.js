/**
 * Account Settings Page
 * Handles user preferences, notifications, security with real-time updates
 */

let currentTab = 'notifications';
let userSettings = {};
let paymentMethods = [];
let householdMembers = [];

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
  loadUserSettings();
  setupEventListeners();
});

/**
 * Setup event listeners
 */
function setupEventListeners() {
  // Tab switching
  const tabButtons = document.querySelectorAll('.settings-tab');
  tabButtons.forEach(btn => {
    btn.addEventListener('click', function() {
      const tab = this.getAttribute('data-tab');
      switchTab(tab);
    });
  });
}

/**
 * Switch between tabs
 */
function switchTab(tab) {
  currentTab = tab;
  
  // Update active tab styling
  document.querySelectorAll('.settings-tab').forEach(btn => {
    const isActive = btn.getAttribute('data-tab') === tab;
    btn.classList.toggle('bg-blue-50', isActive);
    btn.classList.toggle('text-blue-700', isActive);
    btn.classList.toggle('border-blue-600', isActive);
    btn.classList.toggle('text-slate-600', !isActive);
    btn.classList.toggle('hover:bg-slate-50', !isActive);
    btn.classList.toggle('hover:text-slate-900', !isActive);
    btn.classList.toggle('border-transparent', !isActive);
  });
  
  // Render the appropriate content
  renderTabContent();
}

/**
 * Load user settings from API
 */
async function loadUserSettings() {
  showLoadingState();
  
  try {
    const response = await ApiClient.get('/settings');
    
    if (response.success) {
      userSettings = response.data.settings || getDefaultSettings();
      paymentMethods = response.data.payment_methods || [];
      householdMembers = response.data.household_members || [];
      
      renderTabContent();
      console.log('✅ Settings loaded successfully');
    } else {
      // Use default settings if none exist
      userSettings = getDefaultSettings();
      renderTabContent();
    }
  } catch (error) {
    console.error('Error loading settings:', error);
    userSettings = getDefaultSettings();
    renderTabContent();
  }
}

/**
 * Get default settings
 */
function getDefaultSettings() {
  return {
    bill_reminders: true,
    bill_reminder_days: 3,
    high_consumption_water: true,
    high_consumption_power: false,
    water_interrupt_alerts: true,
    power_interrupt_alerts: true,
    auto_pay: false,
    paperless: true,
    calendar_sync: false,
    language: 'English',
    font_size: 'Normal',
    dark_mode: false,
    two_factor: false,
    support_pin: generateSupportPin()
  };
}

/**
 * Generate random support PIN
 */
function generateSupportPin() {
  return Math.floor(1000 + Math.random() * 9000).toString();
}

/**
 * Show loading state
 */
function showLoadingState() {
  const loadingState = document.getElementById('loadingState');
  const content = document.getElementById('settingsContent');
  
  if (loadingState) loadingState.classList.remove('hidden');
  // Clear content except loading state
  Array.from(content.children).forEach(child => {
    if (child.id !== 'loadingState') {
      child.remove();
    }
  });
}

/**
 * Hide loading state
 */
function hideLoadingState() {
  const loadingState = document.getElementById('loadingState');
  if (loadingState) loadingState.classList.add('hidden');
}

/**
 * Render tab content based on current tab
 */
function renderTabContent() {
  hideLoadingState();
  
  const content = document.getElementById('settingsContent');
  
  switch(currentTab) {
    case 'notifications':
      content.innerHTML = renderNotificationsTab();
      break;
    case 'payments':
      content.innerHTML = renderPaymentsTab();
      break;
    case 'household':
      content.innerHTML = renderHouseholdTab();
      break;
    case 'preferences':
      content.innerHTML = renderPreferencesTab();
      break;
    case 'security':
      content.innerHTML = renderSecurityTab();
      break;
  }
  
  // Reinitialize icons and attach event listeners
  lucide.createIcons();
  attachContentEventListeners();
}

/**
 * Render Notifications Tab
 */
function renderNotificationsTab() {
  return `
    <div class="space-y-6">
      
      <!-- Consumption Alerts -->
      <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
        <h2 class="text-lg font-bold text-slate-800 mb-1">Consumption Watch</h2>
        <p class="text-sm text-slate-500 mb-6">Get notified if your usage spikes unexpectedly (potential leaks).</p>
        
        <div class="space-y-4">
          <label class="flex items-center justify-between p-3 border border-slate-100 rounded-lg hover:bg-slate-50 cursor-pointer">
            <div class="flex items-center gap-3">
              <div class="bg-blue-100 text-blue-600 p-2 rounded-lg">
                <i data-lucide="droplets" class="w-4.5 h-4.5"></i>
              </div>
              <div>
                <span class="block text-sm font-bold text-slate-700">High Water Usage Alert</span>
                <span class="block text-xs text-slate-500">Alert if usage exceeds 30m³</span>
              </div>
            </div>
            <input type="checkbox" ${userSettings.high_consumption_water ? 'checked' : ''} onchange="toggleSetting('high_consumption_water')" class="w-5 h-5 text-blue-600 rounded focus:ring-blue-500 border-gray-300" />
          </label>

          <label class="flex items-center justify-between p-3 border border-slate-100 rounded-lg hover:bg-slate-50 cursor-pointer">
            <div class="flex items-center gap-3">
              <div class="bg-amber-100 text-amber-600 p-2 rounded-lg">
                <i data-lucide="zap" class="w-4.5 h-4.5"></i>
              </div>
              <div>
                <span class="block text-sm font-bold text-slate-700">High Electricity Usage Alert</span>
                <span class="block text-xs text-slate-500">Alert if usage exceeds 200kWh</span>
              </div>
            </div>
            <input type="checkbox" ${userSettings.high_consumption_power ? 'checked' : ''} onchange="toggleSetting('high_consumption_power')" class="w-5 h-5 text-blue-600 rounded focus:ring-blue-500 border-gray-300" />
          </label>
        </div>
      </div>

      <!-- Bill Reminders -->
      <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
        <h2 class="text-lg font-bold text-slate-800 mb-1">Payment Reminders</h2>
        <p class="text-sm text-slate-500 mb-6">Avoid late fees by setting up due date alerts.</p>

        <div class="flex items-center justify-between mb-4">
          <div class="flex gap-3">
            <div class="p-2 bg-red-100 text-red-600 rounded-lg h-fit">
              <i data-lucide="calendar" class="w-5 h-5"></i>
            </div>
            <div>
              <p class="font-bold text-slate-800 text-sm">Due Date Notification</p>
              <p class="text-xs text-slate-500">Sent via SMS and Push Notification.</p>
            </div>
          </div>
          <button onclick="toggleSetting('bill_reminders')" class="w-11 h-6 flex items-center rounded-full p-1 transition-colors ${userSettings.bill_reminders ? 'bg-blue-600' : 'bg-slate-300'}">
            <div class="bg-white w-4 h-4 rounded-full shadow-md transform transition-transform ${userSettings.bill_reminders ? 'translate-x-5' : ''}"></div>
          </button>
        </div>

        ${userSettings.bill_reminders ? `
          <div class="ml-12 p-4 bg-slate-50 rounded-lg border border-slate-100">
            <label class="text-xs font-bold text-slate-500 uppercase block mb-2">Remind me</label>
            <select value="${userSettings.bill_reminder_days}" onchange="updateSetting('bill_reminder_days', this.value)" class="w-full border border-slate-200 rounded-lg p-2 text-sm bg-white">
              <option value="1">1 Day before due date</option>
              <option value="3">3 Days before due date</option>
              <option value="7">1 Week before due date</option>
            </select>
          </div>
        ` : ''}
      </div>
    </div>
  `;
}

/**
 * Render Payments Tab
 */
function renderPaymentsTab() {
  return `
    <div class="space-y-6">
      <!-- Auto Pay Feature -->
      <div class="bg-gradient-to-r from-blue-600 to-blue-700 rounded-xl p-6 text-white shadow-md relative overflow-hidden">
        <div class="absolute right-0 top-0 p-6 opacity-10">
          <i data-lucide="credit-card" class="w-24 h-24"></i>
        </div>
        <div class="relative z-10 flex items-start justify-between">
          <div>
            <h2 class="text-lg font-bold mb-1">Auto-Debit Arrangement</h2>
            <p class="text-sm text-blue-100 mb-4 max-w-sm">
              Never worry about late fees again. Automatically pay your bills on the due date using your preferred method.
            </p>
            <button onclick="toggleSetting('auto_pay')" class="flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-bold transition-colors ${
              userSettings.auto_pay 
              ? 'bg-white text-blue-700' 
              : 'bg-blue-800 text-blue-200 hover:bg-blue-900'
            }">
              <i data-lucide="${userSettings.auto_pay ? 'check' : 'plus'}" class="w-4 h-4"></i>
              ${userSettings.auto_pay ? 'Auto-Pay Active' : 'Enable Auto-Pay'}
            </button>
          </div>
        </div>
      </div>

      <!-- Saved Methods -->
      <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
        <div class="flex items-center justify-between mb-4">
          <h2 class="text-lg font-bold text-slate-800">Saved Payment Methods</h2>
          <button onclick="addPaymentMethod()" class="text-sm font-bold text-blue-600 hover:underline flex items-center gap-1">
            <i data-lucide="plus" class="w-4 h-4"></i> Add New
          </button>
        </div>
        
        <div class="space-y-3">
          ${paymentMethods.length > 0 ? paymentMethods.map(method => `
            <div class="flex items-center justify-between p-3 border border-slate-200 rounded-lg">
              <div class="flex items-center gap-3">
                <div class="w-12 h-8 bg-blue-100 rounded flex items-center justify-center text-xs font-bold text-blue-700">${method.type}</div>
                <div>
                  <p class="text-sm font-bold text-slate-700">${method.display_name}</p>
                  <p class="text-xs text-slate-500">${method.details}</p>
                </div>
              </div>
              <button onclick="deletePaymentMethod('${method.id}')" class="text-slate-400 hover:text-red-500">
                <i data-lucide="trash-2" class="w-4 h-4"></i>
              </button>
            </div>
          `).join('') : `
            <div class="text-center py-8 text-slate-400">
              <i data-lucide="credit-card" class="w-12 h-12 mx-auto mb-2"></i>
              <p class="text-sm">No payment methods saved yet</p>
            </div>
          `}
        </div>
      </div>
    </div>
  `;
}

/**
 * Render Household Tab
 */
function renderHouseholdTab() {
  return `
    <div class="space-y-6">
      <div class="bg-indigo-50 border border-indigo-100 rounded-xl p-6">
        <div class="flex gap-4">
          <div class="bg-indigo-100 text-indigo-600 p-3 rounded-lg h-fit">
            <i data-lucide="users" class="w-6 h-6"></i>
          </div>
          <div>
            <h2 class="text-lg font-bold text-indigo-900">Share Account Access</h2>
            <p class="text-sm text-indigo-800 mt-1 mb-4 max-w-lg">
              Give family members or tenants "View Only" access to this account so they can check bills and report issues without needing your password.
            </p>
            <button onclick="inviteMember()" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-bold transition-colors flex items-center gap-2">
              <i data-lucide="plus" class="w-4 h-4"></i> Invite Member
            </button>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
        <h2 class="text-lg font-bold text-slate-800 mb-4">Authorized Users</h2>
        <div class="space-y-4">
          ${householdMembers.length > 0 ? householdMembers.map(member => `
            <div class="flex items-center justify-between p-3 border border-slate-100 rounded-lg">
              <div class="flex items-center gap-3">
                <div class="h-10 w-10 rounded-full bg-slate-200 flex items-center justify-center font-bold text-slate-500">${getInitials(member.name)}</div>
                <div>
                  <p class="text-sm font-bold text-slate-700">${escapeHtml(member.name)} ${member.relationship ? `(${member.relationship})` : ''}</p>
                  <p class="text-xs text-slate-500">${escapeHtml(member.email)}</p>
                </div>
              </div>
              <div class="flex items-center gap-3">
                <span class="text-xs ${member.role === 'Admin' ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-600'} px-2 py-1 rounded font-bold">${member.role}</span>
                ${member.role !== 'Admin' ? `
                  <button onclick="removeMember('${member.id}')" class="text-slate-400 hover:text-red-500">
                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                  </button>
                ` : ''}
              </div>
            </div>
          `).join('') : `
            <div class="text-center py-8 text-slate-400">
              <i data-lucide="users" class="w-12 h-12 mx-auto mb-2"></i>
              <p class="text-sm">No household members added yet</p>
            </div>
          `}
        </div>
      </div>
    </div>
  `;
}

/**
 * Render Preferences Tab
 */
function renderPreferencesTab() {
  return `
    <div class="space-y-6">
      <!-- Paperless Billing -->
      <div class="bg-green-50 border border-green-100 rounded-xl p-6">
        <div class="flex justify-between items-start">
          <div class="flex gap-3">
            <div class="bg-green-100 text-green-600 p-2 rounded-lg">
              <i data-lucide="leaf" class="w-5 h-5"></i>
            </div>
            <div>
              <h2 class="text-lg font-bold text-green-900">Go Paperless</h2>
              <p class="text-sm text-green-800 mt-1 max-w-sm">
                Help Mati City stay green. Stop receiving physical mail and get your bills via email and app only.
              </p>
            </div>
          </div>
          <button onclick="toggleSetting('paperless')" class="w-11 h-6 flex items-center rounded-full p-1 transition-colors ${userSettings.paperless ? 'bg-green-600' : 'bg-slate-300'}">
            <div class="bg-white w-4 h-4 rounded-full shadow-md transform transition-transform ${userSettings.paperless ? 'translate-x-5' : ''}"></div>
          </button>
        </div>
      </div>

      <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
        <h2 class="text-lg font-bold text-slate-800 mb-6">App Customization</h2>
        
        <div class="space-y-6">
          <!-- Calendar Sync -->
          <div class="flex items-center justify-between">
            <div class="flex gap-3 items-center">
              <i data-lucide="calendar" class="w-4.5 h-4.5 text-slate-400"></i>
              <div>
                <label class="text-sm font-bold text-slate-700">Calendar Sync</label>
                <p class="text-xs text-slate-500">Automatically add bill due dates to your phone calendar.</p>
              </div>
            </div>
            <button onclick="toggleSetting('calendar_sync')" class="w-11 h-6 flex items-center rounded-full p-1 transition-colors ${userSettings.calendar_sync ? 'bg-blue-600' : 'bg-slate-300'}">
              <div class="bg-white w-4 h-4 rounded-full shadow-md transform transition-transform ${userSettings.calendar_sync ? 'translate-x-5' : ''}"></div>
            </button>
          </div>

          <div class="border-t border-slate-50"></div>

          <!-- Language -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4 items-center">
            <div>
              <label class="text-sm font-bold text-slate-700">Language / Pinulongan</label>
              <p class="text-xs text-slate-500">Select your preferred language.</p>
            </div>
            <select value="${userSettings.language}" onchange="updateSetting('language', this.value)" class="w-full border border-slate-200 rounded-lg p-2.5 text-sm font-medium focus:ring-2 focus:ring-blue-500 outline-none bg-white">
              <option>English</option>
              <option>Cebuano / Bisaya</option>
              <option>Tagalog</option>
            </select>
          </div>

          <div class="border-t border-slate-50"></div>

          <!-- Font Size -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4 items-center">
            <div class="flex gap-3 items-center">
              <i data-lucide="type" class="w-4.5 h-4.5 text-slate-400"></i>
              <div>
                <label class="text-sm font-bold text-slate-700">Text Size</label>
                <p class="text-xs text-slate-500">Adjust content size for better readability.</p>
              </div>
            </div>
            <div class="flex bg-slate-100 rounded-lg p-1">
              ${['Small', 'Normal', 'Large'].map(size => `
                <button onclick="updateSetting('font_size', '${size}')" class="flex-1 text-xs font-medium py-1.5 rounded-md transition-all ${
                  userSettings.font_size === size ? 'bg-white text-blue-600 shadow-sm' : 'text-slate-500 hover:text-slate-700'
                }">
                  ${size}
                </button>
              `).join('')}
            </div>
          </div>
        </div>
      </div>
    </div>
  `;
}

/**
 * Render Security Tab
 */
function renderSecurityTab() {
  return `
    <div class="space-y-6">
      <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
        <h2 class="text-lg font-bold text-slate-800 mb-4">Login Security</h2>
        
        <!-- Support PIN -->
        <div class="flex items-center justify-between py-4 border-b border-slate-100">
          <div>
            <div class="flex items-center gap-2">
              <i data-lucide="key-round" class="w-4 h-4 text-blue-600"></i>
              <p class="font-bold text-slate-800 text-sm">Customer Support PIN</p>
            </div>
            <p class="text-xs text-slate-500 max-w-sm mt-1">Provide this code when calling the hotline to verify your identity.</p>
          </div>
          <div class="flex items-center gap-2">
            <span class="bg-slate-100 text-slate-800 font-mono font-bold text-lg px-3 py-1 rounded tracking-widest">${userSettings.support_pin}</span>
            <button onclick="copySupportPin()" class="text-slate-400 hover:text-blue-600" title="Copy">
              <i data-lucide="copy" class="w-4 h-4"></i>
            </button>
          </div>
        </div>

        <!-- 2FA -->
        <div class="flex items-center justify-between py-4 border-b border-slate-100">
          <div>
            <p class="font-bold text-slate-800 text-sm">Two-Factor Authentication (2FA)</p>
            <p class="text-xs text-slate-500 max-w-sm mt-1">Add an extra layer of security. We'll send a code to your phone when you log in.</p>
          </div>
          <button onclick="toggleSetting('two_factor')" class="w-11 h-6 flex items-center rounded-full p-1 transition-colors ${userSettings.two_factor ? 'bg-blue-600' : 'bg-slate-300'}">
            <div class="bg-white w-4 h-4 rounded-full shadow-md transform transition-transform ${userSettings.two_factor ? 'translate-x-5' : ''}"></div>
          </button>
        </div>

        <!-- Change Password -->
        <div class="flex items-center justify-between py-4">
          <div>
            <p class="font-bold text-slate-800 text-sm">Change Password</p>
            <p class="text-xs text-slate-500">Last changed 3 months ago</p>
          </div>
          <button onclick="changePassword()" class="text-xs font-bold text-blue-600 border border-blue-200 px-3 py-2 rounded-lg hover:bg-blue-50">
            Update
          </button>
        </div>
      </div>
      
      <!-- Data Privacy -->
      <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
        <h2 class="text-lg font-bold text-slate-800 mb-4">Data Privacy</h2>
        <div class="flex items-center justify-between">
          <div>
            <p class="font-bold text-slate-800 text-sm">Download My Data</p>
            <p class="text-xs text-slate-500">Get a copy of your transaction history and account logs.</p>
          </div>
          <button onclick="downloadData()" class="flex items-center gap-2 text-xs font-bold text-slate-600 border border-slate-200 px-3 py-2 rounded-lg hover:bg-slate-50">
            <i data-lucide="download" class="w-3.5 h-3.5"></i> Download Archive
          </button>
        </div>
      </div>

      <!-- Danger Zone -->
      <div class="bg-red-50 border border-red-100 rounded-xl p-6">
        <h2 class="text-lg font-bold text-red-900 mb-2">Danger Zone</h2>
        <p class="text-sm text-red-800 mb-4">
          Deleting your account will remove all your transaction history and linked meters. This action cannot be undone.
        </p>
        <button onclick="deleteAccount()" class="bg-white border border-red-200 text-red-600 hover:bg-red-600 hover:text-white px-4 py-2 rounded-lg text-sm font-bold transition-colors flex items-center gap-2">
          <i data-lucide="trash-2" class="w-4 h-4"></i> Delete My Account
        </button>
      </div>
    </div>
  `;
}

/**
 * Attach event listeners to dynamic content
 */
function attachContentEventListeners() {
  // Event listeners are handled via inline onclick attributes for simplicity
  // This function can be expanded if needed for more complex event handling
}

/**
 * Toggle a boolean setting
 */
async function toggleSetting(key) {
  userSettings[key] = !userSettings[key];
  
  // Update UI immediately
  renderTabContent();
  
  // Save to database
  await saveSetting(key, userSettings[key]);
}

/**
 * Update a setting value
 */
async function updateSetting(key, value) {
  userSettings[key] = value;
  
  // Update UI immediately
  renderTabContent();
  
  // Save to database
  await saveSetting(key, value);
}

/**
 * Save setting to database
 */
async function saveSetting(key, value) {
  try {
    const response = await ApiClient.put('/settings', {
      [key]: value
    });
    
    if (response.success) {
      console.log(`✅ Setting ${key} saved successfully`);
    } else {
      showError('Failed to save setting');
    }
  } catch (error) {
    console.error('Error saving setting:', error);
    showError('Failed to save setting');
  }
}

/**
 * Copy support PIN to clipboard
 */
function copySupportPin() {
  navigator.clipboard.writeText(userSettings.support_pin).then(() => {
    showSuccess('Support PIN copied to clipboard!');
  });
}

/**
 * Placeholder functions for future implementation
 */
function addPaymentMethod() {
  showInfo('Payment method addition coming soon!');
}

async function deletePaymentMethod(id) {
  const ok = await UIHelpers.confirm({
    title: 'Remove Payment Method',
    message: 'Remove this payment method?',
    confirmText: 'Remove',
    cancelText: 'Cancel',
    variant: 'danger'
  });
  if (ok) {
    showInfo('Payment method removal coming soon!');
  }
}

function inviteMember() {
  showInfo('Member invitation coming soon!');
}

async function removeMember(id) {
  const ok = await UIHelpers.confirm({
    title: 'Remove Member',
    message: 'Remove this member from your household?',
    confirmText: 'Remove',
    cancelText: 'Cancel',
    variant: 'danger'
  });
  if (ok) {
    showInfo('Member removal coming soon!');
  }
}

function changePassword() {
  showInfo('Password change coming soon!');
}

function downloadData() {
  showInfo('Data export coming soon!');
}

async function deleteAccount() {
  const ok = await UIHelpers.confirm({
    title: 'Delete Account',
    message: 'Are you sure you want to delete your account? This action cannot be undone!',
    confirmText: 'Delete Account',
    cancelText: 'Cancel',
    variant: 'danger'
  });
  if (ok) {
    showInfo('Account deletion coming soon!');
  }
}

/**
 * Utility functions
 */
function getInitials(name) {
  if (!name) return '??';
  const parts = name.split(' ');
  return (parts[0]?.[0] || '') + (parts[1]?.[0] || '');
}

function escapeHtml(text) {
  if (!text) return '';
  const div = document.createElement('div');
  div.textContent = text;
  return div.innerHTML;
}

function showSuccess(message) {
  UIHelpers.showSuccess(message);
}

function showError(message) {
  UIHelpers.showError(message);
}

function showInfo(message) {
  UIHelpers.showInfo(message);
}
