/**
 * FixItMati API Client
 * Centralized API communication layer for frontend
 */

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

    // Add auth token if available (stored in sessionStorage)
    const token = sessionStorage.getItem('auth_token');
    if (token) {
      config.headers['Authorization'] = `Bearer ${token}`;
    }

    try {
      const response = await fetch(url, config);
      
      // Handle non-JSON responses
      const contentType = response.headers.get('content-type');
      if (!contentType || !contentType.includes('application/json')) {
        throw new Error('Invalid response format from server');
      }

      const data = await response.json();

      if (!response.ok) {
        throw new Error(data.error || data.message || `HTTP ${response.status}`);
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
};

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

/**
 * Notification API endpoints
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
    if (result.token) {
      sessionStorage.setItem('auth_token', result.token);
      sessionStorage.setItem('user', JSON.stringify(result.user));
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
    if (result.token) {
      sessionStorage.setItem('auth_token', result.token);
      sessionStorage.setItem('user', JSON.stringify(result.user));
    }
    return result;
  },

  /**
   * Logout current user
   */
  logout() {
    sessionStorage.removeItem('auth_token');
    sessionStorage.removeItem('user');
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

// Export for use in other scripts
if (typeof window !== 'undefined') {
  window.ApiClient = ApiClient;
  window.RequestsAPI = RequestsAPI;
  window.PaymentsAPI = PaymentsAPI;
  window.NotificationsAPI = NotificationsAPI;
  window.CommandsAPI = CommandsAPI;
  window.AuthAPI = AuthAPI;
  window.UIHelpers = UIHelpers;
}
