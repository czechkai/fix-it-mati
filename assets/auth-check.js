/**
 * Authentication Check and Cross-Tab Sync
 * Include this script on all protected pages to:
 * 1. Check if user is authenticated
 * 2. Redirect to login if not authenticated
 * 3. Sync logout across all tabs
 */

(function() {
  'use strict';
  
  // Check authentication
  const token = localStorage.getItem('auth_token');
  if (!token) {
    console.log('[Auth Check] No auth token found, redirecting to login');
    window.location.replace('/login.php');
    throw new Error('Not authenticated');
  }
  
  console.log('[Auth Check] User authenticated');
  
  // Listen for storage events from other tabs (logout sync)
  window.addEventListener('storage', function(e) {
    // Detect logout in another tab
    if (e.key === 'logout_event') {
      console.log('[Auth Check] Logout detected from another tab');
      window.location.replace('/login.php');
    }
    
    // Detect token removal in another tab
    if (e.key === 'auth_token' && !e.newValue) {
      console.log('[Auth Check] Auth token removed in another tab');
      window.location.replace('/login.php');
    }
  });
  
  console.log('[Auth Check] Cross-tab logout sync enabled');
})();
