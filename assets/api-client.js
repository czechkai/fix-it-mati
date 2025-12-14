/**
 * FixItMati API Client
 * Centralized API communication layer for frontend
 */

console.log('[API Client] Loading...');

const API_BASE_URL = '/api';

/**
 * API Client singleton with request/response handling
 */
const ApiClient = {
  /**
   * Make an API request
   * @param {string} endpoint - API endpoint (e.g., '/requests')
   * @param {object} options - Fetch options
   * @returns {Promise<object>} Response data
   */
  async request(endpoint, options = {}) {
    const url = `${API_BASE_URL}${endpoint}`;
    
    const config = {
      headers: {
        'Content-Type': 'application/json',
        ...options.headers,
      },
      ...options,
    };

    // Add auth token if available (stored in localStorage for cross-tab consistency)
    const token = localStorage.getItem('auth_token');
    if (token) {
      config.headers['Authorization'] = `Bearer ${token}`;
    }

    try {
      const response = await fetch(url, config);
      
      // Handle non-JSON responses
      const contentType = response.headers.get('content-type');
      if (!contentType || !contentType.includes('application/json')) {
        // Log more details for debugging
        console.error('Invalid content-type:', contentType);
        console.error('Response status:', response.status);
        console.error('Response headers:', [...response.headers.entries()]);
        const text = await response.text();
        console.error('Response body:', text.substring(0, 500));
        throw new Error('Invalid response format from server');
      }

      const data = await response.json();

      if (!response.ok) {
        const error = new Error(data.error || data.message || `HTTP ${response.status}`);
        error.response = data; // Attach full response data including validation errors
        throw error;
      }

      return data;
    } catch (error) {
      console.error(`API Error [${endpoint}]:`, error);
      throw error;
    }
  },

  // Convenience methods
  get(endpoint) {
    return this.request(endpoint, { method: 'GET' });
  },

  post(endpoint, data) {
    return this.request(endpoint, {
      method: 'POST',
      body: JSON.stringify(data),
    });
  },

  put(endpoint, data) {
    return this.request(endpoint, {
      method: 'PUT',
      body: JSON.stringify(data),
    });
  },

  delete(endpoint) {
    return this.request(endpoint, { method: 'DELETE' });
  },

  // Nested API endpoints
  auth: null, // Will be set after AuthAPI is defined
  requests: null, // Will be set after RequestsAPI is defined
  payments: null, // Will be set after PaymentsAPI is defined
  notifications: null, // Will be set after NotificationsAPI is defined
  commands: null, // Will be set after CommandsAPI is defined
  discussions: null, // Will be set after DiscussionsAPI is defined
};

// Export ApiClient to window immediately
if (typeof window !== 'undefined') {
  window.ApiClient = ApiClient;
  console.log('[API Client] Core client exported to window');
}

/**
 * Service Request API endpoints
 */
const RequestsAPI = {
  /**
   * Get all service requests for current user
   * @param {object} filters - Optional filters (status, category, etc.)
   * @returns {Promise<Array>} List of requests
   */
  async getAll(filters = {}) {
    const params = new URLSearchParams(filters);
    const query = params.toString() ? `?${params.toString()}` : '';
    return ApiClient.get(`/requests${query}`);
  },

  /**
   * Get a single service request by ID
   * @param {number} id - Request ID
   * @returns {Promise<object>} Request details
   */
  async getById(id) {
    return ApiClient.get(`/requests/${id}`);
  },

  /**
   * Create a new service request
   * @param {object} requestData - Request details
   * @returns {Promise<object>} Created request
   */
  async create(requestData) {
    return ApiClient.post('/requests', requestData);
  },

  /**
   * Update a service request
   * @param {number} id - Request ID
   * @param {object} updates - Fields to update
   * @returns {Promise<object>} Updated request
   */
  async update(id, updates) {
    return ApiClient.put(`/requests/${id}`, updates);
  },

  /**
   * Get request statistics for dashboard
   * @returns {Promise<object>} Statistics (active, pending, resolved, etc.)
   */
  async getStatistics() {
    return ApiClient.get('/requests/statistics');
  },
};
ApiClient.requests = RequestsAPI;
window.RequestsAPI = RequestsAPI;
console.log('[API Client] RequestsAPI defined and attached');

/**
 * Payment API endpoints
 */
const PaymentsAPI = {
  /**
   * Get available payment gateways
   * @returns {Promise<Array>} List of payment gateways
   */
  async getGateways() {
    return ApiClient.get('/payments/gateways');
  },

  /**
   * Process a payment
   * @param {object} paymentData - Payment details (gateway, amount, etc.)
   * @returns {Promise<object>} Payment result
   */
  async process(paymentData) {
    return ApiClient.post('/payments/process', paymentData);
  },

  /**
   * Refund a payment
   * @param {string} transactionId - Original transaction ID
   * @param {number} amount - Amount to refund
   * @param {string} reason - Refund reason
   * @returns {Promise<object>} Refund result
   */
  async refund(transactionId, amount, reason) {
    return ApiClient.post('/payments/refund', {
      transaction_id: transactionId,
      amount,
      reason,
    });
  },

  /**
   * Get payment transaction status
   * @param {string} transactionId - Transaction ID
   * @returns {Promise<object>} Transaction status
   */
  async getStatus(transactionId) {
    return ApiClient.get(`/payments/status/${transactionId}`);
  },

  /**
   * Get payment history for current user
   * @param {object} filters - Optional filters (date range, status, etc.)
   * @returns {Promise<Array>} Payment history
   */
  async getHistory(filters = {}) {
    const params = new URLSearchParams(filters);
    const query = params.toString() ? `?${params.toString()}` : '';
    return ApiClient.get(`/payments/history${query}`);
  },
};
ApiClient.payments = PaymentsAPI;
window.PaymentsAPI = PaymentsAPI;
console.log('[API Client] PaymentsAPI defined and attached');

/**
 * Notifications API endpoints
 */
const NotificationsAPI = {
  /**
   * Get all notifications for current user
   * @param {object} filters - Optional filters (read/unread, limit, etc.)
   * @returns {Promise<Array>} List of notifications
   */
  async getAll(filters = {}) {
    const params = new URLSearchParams(filters);
    const query = params.toString() ? `?${params.toString()}` : '';
    return ApiClient.get(`/notifications${query}`);
  },

  /**
   * Mark notification as read
   * @param {number} id - Notification ID
   * @returns {Promise<object>} Updated notification
   */
  async markAsRead(id) {
    return ApiClient.put(`/notifications/${id}/read`);
  },

  /**
   * Mark all notifications as read
   * @returns {Promise<object>} Result
   */
  async markAllAsRead() {
    return ApiClient.post('/notifications/read-all');
  },

  /**
   * Get unread notification count
   * @returns {Promise<object>} Count
   */
  async getUnreadCount() {
    return ApiClient.get('/notifications/unread-count');
  },
};
ApiClient.notifications = NotificationsAPI;
window.NotificationsAPI = NotificationsAPI;
console.log('[API Client] NotificationsAPI defined and attached');

/**
 * Command API endpoints (for undo/redo functionality)
 */
const CommandsAPI = {
  /**
   * Execute a command
   * @param {string} commandType - Command type (e.g., 'CreateRequest', 'UpdateRequest')
   * @param {object} data - Command data
   * @returns {Promise<object>} Command result
   */
  async execute(commandType, data) {
    return ApiClient.post('/commands/execute', {
      command_type: commandType,
      data,
    });
  },

  /**
   * Undo the last command
   * @returns {Promise<object>} Undo result
   */
  async undo() {
    return ApiClient.post('/commands/undo');
  },

  /**
   * Redo the last undone command
   * @returns {Promise<object>} Redo result
   */
  async redo() {
    return ApiClient.post('/commands/redo');
  },

  /**
   * Get command history
   * @returns {Promise<Array>} Command history
   */
  async getHistory() {
    return ApiClient.get('/commands/history');
  },
};
ApiClient.commands = CommandsAPI;
window.CommandsAPI = CommandsAPI;
console.log('[API Client] CommandsAPI defined and attached');

/**
 * Discussions API endpoints
 */
const DiscussionsAPI = {
  /**
   * Get user's recent activity (discussions and comments)
   * @param {number} limit - Maximum number of items to return
   * @returns {Promise<object>} User activity data
   */
  async getMyActivity(limit = 10) {
    return ApiClient.get(`/discussions/my-activity?limit=${limit}`);
  },

  /**
   * Get all discussions
   * @param {object} params - Optional query parameters (category, sort)
   * @returns {Promise<object>} Discussions list
   */
  async getAll(params = {}) {
    const queryString = new URLSearchParams(params).toString();
    return ApiClient.get(`/discussions${queryString ? '?' + queryString : ''}`);
  },

  /**
   * Get single discussion with comments
   * @param {string} id - Discussion ID
   * @returns {Promise<object>} Discussion details
   */
  async getById(id) {
    return ApiClient.get(`/discussions/${id}`);
  },

  /**
   * Create new discussion
   * @param {object} data - Discussion data (title, content, category)
   * @returns {Promise<object>} Created discussion
   */
  async create(data) {
    return ApiClient.post('/discussions', data);
  },

  /**
   * Add comment to discussion
   * @param {string} discussionId - Discussion ID
   * @param {string} content - Comment content
   * @returns {Promise<object>} Created comment
   */
  async addComment(discussionId, content) {
    return ApiClient.post(`/discussions/${discussionId}/comments`, { content });
  },

  /**
   * Upvote a discussion
   * @param {string} discussionId - Discussion ID
   * @returns {Promise<object>} Updated upvote count
   */
  async upvote(discussionId) {
    return ApiClient.post(`/discussions/${discussionId}/upvote`);
  },

  /**
   * Delete discussion
   * @param {string} discussionId - Discussion ID
   * @returns {Promise<object>} Deletion result
   */
  async delete(discussionId) {
    return ApiClient.delete(`/discussions/${discussionId}`);
  },
};
ApiClient.discussions = DiscussionsAPI;
window.DiscussionsAPI = DiscussionsAPI;
console.log('[API Client] DiscussionsAPI defined and attached');

/**
 * Authentication API endpoints
 */
const AuthAPI = {
  /**
   * Login user
   * @param {string} email - User email
   * @param {string} password - User password
   * @returns {Promise<object>} Auth token and user info
   */
  async login(email, password) {
    const result = await ApiClient.post('/auth/login', { email, password });
    // API returns {success: true, data: {user, token}, message}
    if (result.success && result.data && result.data.token) {
      localStorage.setItem('auth_token', result.data.token);
      localStorage.setItem('user', JSON.stringify(result.data.user));
    }
    return result;
  },

  /**
   * Register new user
   * @param {object} userData - User registration data
   * @returns {Promise<object>} Created user and token
   */
  async register(userData) {
    const result = await ApiClient.post('/auth/register', userData);
    // API returns {success: true, data: {user, token}, message}
    if (result.success && result.data && result.data.token) {
      localStorage.setItem('auth_token', result.data.token);
      localStorage.setItem('user', JSON.stringify(result.data.user));
    }
    return result;
  },

  /**
   * Logout current user
   */
  async logout() {
    try {
      // Call backend logout API to destroy session
      await ApiClient.post('/auth/logout', {});
    } catch (error) {
      console.error('[Auth] Logout API error:', error);
      // Continue with client-side cleanup even if API fails
    }
    
    // Clear client-side storage
    localStorage.removeItem('auth_token');
    localStorage.removeItem('user');
    
    // Notify other tabs about logout by setting a flag
    localStorage.setItem('logout_event', Date.now().toString());
    
    // Redirect to login
    window.location.href = '/login.php';
  },

  /**
   * Get current user info
   * @returns {Promise<object>} Current user
   */
  async getCurrentUser() {
    return ApiClient.get('/auth/me');
  },

  /**
   * Check if user is authenticated
   * @returns {boolean} Is authenticated
   */
  isAuthenticated() {
    return !!sessionStorage.getItem('auth_token');
  },

  /**
   * Get stored user from session
   * @returns {object|null} User object or null
   */
  getStoredUser() {
    const user = sessionStorage.getItem('user');
    return user ? JSON.parse(user) : null;
  },
};
ApiClient.auth = AuthAPI;
window.AuthAPI = AuthAPI;
console.log('[API Client] AuthAPI defined and attached');

/**
 * Utility functions for UI feedback
 */
const UIHelpers = {
  /**
   * Show loading state on element
   * @param {HTMLElement} element - Element to show loading on
   * @param {string} message - Loading message
   */
  showLoading(element, message = 'Loading...') {
    if (!element) return;
    element.setAttribute('data-original-html', element.innerHTML);
    element.disabled = true;
    element.innerHTML = `
      <svg class="animate-spin inline-block w-4 h-4 mr-2" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
      </svg>
      ${message}
    `;
  },

  /**
   * Hide loading state on element
   * @param {HTMLElement} element - Element to hide loading from
   */
  hideLoading(element) {
    if (!element) return;
    const originalHtml = element.getAttribute('data-original-html');
    if (originalHtml) {
      element.innerHTML = originalHtml;
      element.removeAttribute('data-original-html');
    }
    element.disabled = false;
  },

  /**
   * Show error toast notification
   * @param {string} message - Error message
   */
  showError(message) {
    console.error('Error:', message);
    // Simple alert for now - can be replaced with toast notification library
    alert(`Error: ${message}`);
  },

  /**
   * Show success toast notification
   * @param {string} message - Success message
   */
  showSuccess(message) {
    console.log('Success:', message);
    // Simple alert for now - can be replaced with toast notification library
    alert(`Success: ${message}`);
  },

  /**
   * Format currency amount (Philippine Peso)
   * @param {number} amount - Amount to format
   * @returns {string} Formatted amount
   */
  formatCurrency(amount) {
    return `₱${amount.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,')}`;
  },

  /**
   * Format date to readable string
   * @param {string|Date} date - Date to format
   * @returns {string} Formatted date
   */
  formatDate(date) {
    return new Date(date).toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'short',
      day: 'numeric',
    });
  },

  /**
   * Format date with time
   * @param {string|Date} date - Date to format
   * @returns {string} Formatted date and time
   */
  formatDateTime(date) {
    return new Date(date).toLocaleString('en-US', {
      year: 'numeric',
      month: 'short',
      day: 'numeric',
      hour: 'numeric',
      minute: '2-digit',
      hour12: true,
    });
  },
};
window.UIHelpers = UIHelpers;
console.log('[API Client] UIHelpers defined and attached');

// Export for use in other scripts
if (typeof window !== 'undefined') {
  console.log('[API Client] All modules loaded successfully');
  console.log('[API Client] Available APIs:', {
    auth: typeof ApiClient.auth,
    requests: typeof ApiClient.requests,
    payments: typeof ApiClient.payments,
    notifications: typeof ApiClient.notifications,
    commands: typeof ApiClient.commands,
    discussions: typeof ApiClient.discussions
  });
}
