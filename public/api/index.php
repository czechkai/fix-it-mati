<?php

/**
 * API Entry Point
 * All API requests go through this file
 * Implements RESTful API routing
 */

// Start output buffering to prevent any accidental output
ob_start();

// Enable error reporting for development but don't display
error_reporting(E_ALL);
ini_set('display_errors', '0'); // Don't display errors as HTML
ini_set('log_errors', '1'); // Log errors instead

// Start session before any output
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Allow CORS with credentials (development - localhost only)
$origin = $_SERVER['HTTP_ORIGIN'] ?? 'http://localhost:8000';
header('Access-Control-Allow-Origin: ' . $origin);
header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Credentials: true');

// Handle preflight requests
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    ob_end_clean();
    exit;
}

// Load autoloader
require_once __DIR__ . '/../../autoload.php';

use FixItMati\Core\Router;
use FixItMati\Core\Request;
use FixItMati\Core\Response;

try {
    // Create router instance
    $router = new Router();

    // Create request instance
    $request = new Request();

    // ============================================
    // PUBLIC ROUTES (No Authentication Required)
    // ============================================

    // Test endpoint
    $router->get('/api/test', function (Request $req) {
        return Response::success([
            'message' => 'FixItMati API is working!',
            'timestamp' => date('Y-m-d H:i:s'),
            'method' => $req->getMethod(),
            'uri' => $req->getUri(),
            'version' => '1.0.0'
        ]);
    });

    // Webhook endpoints (public but secured with signatures)
    $router->post('/api/webhooks/stripe', 'WebhookController@stripeWebhook');
    $router->post('/api/webhooks/paypal', 'PaymentController@handlePayPalWebhook');
    $router->post('/api/webhooks/gcash', 'PaymentController@handleGCashWebhook');

    // Authentication endpoints
    $router->post('/api/auth/register', 'AuthController@register');
    $router->post('/api/auth/login', 'AuthController@login');
    $router->post('/api/auth/logout', 'AuthController@logout');
    $router->post('/api/auth/refresh', 'AuthController@refresh');

    // Email verification endpoints
    $router->post('/api/auth/send-verification-code', 'AuthController@sendVerificationCode');
    $router->post('/api/auth/verify-code', 'AuthController@verifyCode');
    $router->post('/api/auth/verify-and-register', 'AuthController@verifyAndRegister');
    // Health check
    $router->get('/api/health', function () {
        return Response::success([
            'status' => 'healthy',
            'database' => 'connected',
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    });

    // Serve uploaded profile images (public access)
    $router->get('/api/uploads/profiles/{filename}', function (Request $req, $params) {
        $filename = $params['filename'] ?? '';

        // Sanitize filename to prevent directory traversal
        $filename = basename($filename);

        // Construct the file path
        $filePath = __DIR__ . '/../../uploads/profiles/' . $filename;

        // Check if file exists
        if (!file_exists($filePath) || !is_file($filePath)) {
            http_response_code(404);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Image not found']);
            exit;
        }

        // Get MIME type using getimagesize (doesn't require finfo extension)
        $imageInfo = @getimagesize($filePath);
        $mimeType = $imageInfo ? $imageInfo['mime'] : 'application/octet-stream';

        // Set appropriate headers
        header('Content-Type: ' . $mimeType);
        header('Content-Length: ' . filesize($filePath));
        header('Cache-Control: public, max-age=31536000'); // Cache for 1 year
        header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 31536000) . ' GMT');

        // Output the file
        readfile($filePath);
        exit;
    });

    // Public announcements (no auth required)
    $router->get('/api/announcements', 'AnnouncementController@getPublished');
    $router->get('/api/announcements/active', 'AnnouncementController@getActive');
    $router->get('/api/announcements/:id', 'AnnouncementController@show');
    $router->get('/api/announcements/category/:category', 'AnnouncementController@getByCategory');

    // ============================================
    // PROTECTED ROUTES (Authentication Required)
    // ============================================
    // Note: Middleware applies to routes defined AFTER addMiddleware call
    $router->addMiddleware(new \FixItMati\Middleware\AuthMiddleware());

    // Admin announcements endpoint (requires auth)
    $router->get('/api/admin/announcements/all', 'AnnouncementController@getAll');

    // Get current authenticated user
    $router->get('/api/auth/me', 'AuthController@me');

    // Update user profile
    $router->put('/api/auth/profile', 'AuthController@updateProfile');

    // ============================================
    // SERVICE REQUEST ROUTES (Protected)
    // ============================================

    // Statistics (must come before {id} route)
    $router->get('/api/requests/statistics', 'RequestController@statistics');

    // Resolved/completed requests (must come before {id} route)
    $router->get('/api/requests/resolved', 'RequestController@resolved');

    // List and create requests
    $router->get('/api/requests', 'RequestController@index');
    $router->post('/api/requests', 'RequestController@create');

    // Get, update, cancel specific request
    $router->get('/api/requests/{id}', 'RequestController@show');
    $router->patch('/api/requests/{id}', 'RequestController@update');
    $router->delete('/api/requests/{id}', 'RequestController@cancel');

    // Request actions
    $router->post('/api/requests/{id}/review', 'RequestController@review');
    $router->post('/api/requests/{id}/assign', 'RequestController@assign');
    $router->post('/api/requests/{id}/start', 'RequestController@start');
    $router->post('/api/requests/{id}/complete', 'RequestController@complete');

    // Rating and recurring issues
    $router->post('/api/requests/{id}/rating', 'RequestController@submitRating');
    $router->post('/api/requests/{id}/recurring', 'RequestController@reportRecurring');

    // ============================================
    // DISCUSSION ROUTES (Protected)
    // ============================================

    // List and create discussions
    $router->get('/api/discussions', 'DiscussionController@index');
    $router->post('/api/discussions', 'DiscussionController@create');

    // Get user's activity (discussions and comments)
    $router->get('/api/discussions/my-activity', 'DiscussionController@myActivity');

    // Get, update, delete specific discussion
    $router->get('/api/discussions/{id}', 'DiscussionController@show');
    $router->delete('/api/discussions/{id}', 'DiscussionController@delete');

    // Discussion actions
    $router->post('/api/discussions/{id}/upvote', 'DiscussionController@upvote');
    $router->post('/api/discussions/{id}/comments', 'DiscussionController@addComment');
    $router->post('/api/discussions/{id}/comments/{commentId}/mark-solution', 'DiscussionController@markSolution');

    // ============================================
    // NOTIFICATION ROUTES (Protected)
    // ============================================

    // List and count notifications
    $router->get('/api/notifications', 'NotificationController@index');
    $router->get('/api/notifications/unread-count', 'NotificationController@unreadCount');

    // Mark as read
    $router->patch('/api/notifications/{id}/read', 'NotificationController@markAsRead');
    $router->put('/api/notifications/{id}/read', 'NotificationController@markAsRead'); // Also support PUT
    $router->post('/api/notifications/mark-all-read', 'NotificationController@markAllAsRead');

    // Delete notification
    $router->delete('/api/notifications/{id}', 'NotificationController@delete');

    // Test notification (development only)
    $router->post('/api/notifications/test', 'NotificationController@sendTest');

    // ============================================
    // SERVICE ADDRESSES ROUTES (Protected)
    // ============================================

    // Get default address (must come before {id} route)
    $router->get('/api/service-addresses/default', 'ServiceAddressController@getDefault');

    // List and create addresses
    $router->get('/api/service-addresses', 'ServiceAddressController@index');
    $router->post('/api/service-addresses', 'ServiceAddressController@create');

    // Get, update, delete specific address
    $router->get('/api/service-addresses/{id}', 'ServiceAddressController@show');
    $router->put('/api/service-addresses/{id}', 'ServiceAddressController@update');
    $router->delete('/api/service-addresses/{id}', 'ServiceAddressController@delete');

    // Set default address
    $router->patch('/api/service-addresses/{id}/set-default', 'ServiceAddressController@setDefault');

    // ============================================
    // LINKED METERS ROUTES (Protected)
    // ============================================

    // Get meters by type (must come before {id} route)
    $router->get('/api/linked-meters/type/{type}', 'LinkedMeterController@getByType');

    // List and create meters
    $router->get('/api/linked-meters', 'LinkedMeterController@index');
    $router->post('/api/linked-meters', 'LinkedMeterController@create');

    // Get, update, delete specific meter
    $router->get('/api/linked-meters/{id}', 'LinkedMeterController@show');
    $router->put('/api/linked-meters/{id}', 'LinkedMeterController@update');
    $router->delete('/api/linked-meters/{id}', 'LinkedMeterController@delete');

    // ============================================
    // ANNOUNCEMENTS ROUTES (Protected Admin only)
    // ============================================

    // Admin routes (protected)
    $router->post('/api/announcements', 'AnnouncementController@create');
    $router->put('/api/announcements/:id', 'AnnouncementController@update');
    $router->delete('/api/announcements/:id', 'AnnouncementController@delete');

    // Comments (requires auth)
    $router->post('/api/announcements/comments', 'AnnouncementController@addComment');

    // ============================================
    // COMMAND PATTERN ROUTES (Protected)
    // ============================================

    // Execute commands with undo/redo support
    $router->post('/api/commands/execute', 'CommandController@execute');
    $router->post('/api/commands/undo', 'CommandController@undo');
    $router->post('/api/commands/redo', 'CommandController@redo');
    $router->get('/api/commands/history', 'CommandController@history');

    // ============================================
    // MEMENTO PATTERN ROUTES (Protected)
    // ============================================

    // Request snapshots for state history
    $router->post('/api/snapshots', 'MementoController@createSnapshot');
    $router->get('/api/snapshots', 'MementoController@listSnapshots');
    $router->post('/api/snapshots/restore', 'MementoController@restoreSnapshot');
    $router->delete('/api/snapshots', 'MementoController@deleteSnapshot');

    // ============================================
    // COMPOSITE PATTERN ROUTES (Protected)
    // ============================================

    // Batch operations on grouped requests
    $router->post('/api/request-groups', 'CompositeController@createGroup');
    $router->patch('/api/request-groups/status', 'CompositeController@updateGroupStatus');
    $router->post('/api/request-groups/info', 'CompositeController@getGroupInfo');
    $router->post('/api/request-groups/nested', 'CompositeController@createNestedGroup');

    // ============================================
    // DECORATOR PATTERN ROUTES (Protected)
    // ============================================

    // Dynamic feature enhancement for requests
    $router->post('/api/requests/enhance', 'DecoratorController@enhanceRequest');
    $router->post('/api/requests/cost-estimate', 'DecoratorController@getCostEstimate');
    $router->get('/api/requests/available-features', 'DecoratorController@getAvailableFeatures');

    // ============================================
    // ADAPTER PATTERN ROUTES (Protected)
    // ============================================

    // Payment gateway operations
    $router->get('/api/payments/current', 'PaymentController@getCurrentBills');
    $router->post('/api/payments/process', 'PaymentController@processPayment');
    $router->post('/api/payments/refund', 'PaymentController@refundPayment');
    $router->get('/api/payments/status', 'PaymentController@getTransactionStatus');
    $router->get('/api/payments/gateways', 'PaymentController@getSupportedGateways');
    $router->get('/api/payments/history', 'PaymentController@getHistory');

    // Receipt endpoints
    $router->get('/api/payments/receipt/{transactionId}', 'PaymentController@downloadReceipt');
    $router->post('/api/payments/receipt/send', 'PaymentController@sendReceipt');

    // Payment return/callback URLs (no auth required - user redirected from gateway)
    $router->get('/api/payments/paypal/return', 'PaymentController@handlePayPalReturn');
    $router->get('/api/payments/paypal/cancel', 'PaymentController@handlePayPalCancel');
    $router->get('/api/payments/gcash/return', 'PaymentController@handleGCashReturn');

    // ============================================
    // SETTINGS ROUTES (Protected)
    // ============================================

    // Get user settings with payment methods and household members
    $router->get('/api/settings', 'SettingsController@index');

    // Update user settings
    $router->put('/api/settings', 'SettingsController@update');

    // Reset settings to defaults
    $router->post('/api/settings/reset', 'SettingsController@reset');

    // ============================================
    // TEMPLATE METHOD PATTERN ROUTES (Protected)
    // ============================================

    // Request processing workflows
    $router->post('/api/processors/new-request', 'ProcessorController@processNewRequest');
    $router->post('/api/processors/assign', 'ProcessorController@processAssignment');
    $router->post('/api/processors/complete', 'ProcessorController@processCompletion');
    $router->get('/api/processors', 'ProcessorController@getProcessors');

    // ============================================
    // ADMIN ROUTES (Protected + Role Check)
    // ============================================

    // Admin Billing & Payments
    $router->get('/api/admin/transactions', 'PaymentController@getAllTransactions');
    $router->get('/api/admin/billing/stats', 'PaymentController@getStats');
    $router->get('/api/admin/billing/all-payments', 'PaymentController@getAllPayments');
    $router->post('/api/admin/billing/create-invoice', 'PaymentController@createInvoice');
    $router->post('/api/admin/transactions/{id}/approve', 'PaymentController@approveTransaction');
    $router->post('/api/admin/transactions/{id}/reject', 'PaymentController@rejectTransaction');
    $router->get('/api/admin/transactions/export', 'PaymentController@exportTransactions');

    // Admin User Management
    $router->get('/api/users/all', 'UserController@getAllUsers');
    $router->post('/api/users/create', 'UserController@createUser');
    $router->post('/api/users/{id}/verify', 'UserController@verifyUser');
    $router->post('/api/users/{id}/suspend', 'UserController@suspendUser');
    $router->post('/api/users/{id}/reset-password', 'UserController@resetPassword');
    $router->delete('/api/users/{id}', 'UserController@deleteUser');
    $router->get('/api/users/stats', 'UserController@getUserStats');

    // Admin Technician Team Management
    $router->get('/api/technicians/all', 'TechnicianTeamController@getAllTeams');
    $router->get('/api/technicians/{id}', 'TechnicianTeamController@getTeam');
    $router->post('/api/technicians/create', 'TechnicianTeamController@createTeam');
    $router->post('/api/technicians/{id}/status', 'TechnicianTeamController@updateStatus');
    $router->post('/api/technicians/{id}/assign', 'TechnicianTeamController@assignTask');
    $router->post('/api/technicians/{id}/complete', 'TechnicianTeamController@completeTask');
    $router->put('/api/technicians/{id}', 'TechnicianTeamController@updateTeam');
    $router->delete('/api/technicians/{id}', 'TechnicianTeamController@deleteTeam');
    $router->get('/api/technicians/stats', 'TechnicianTeamController@getStats');

    // Other admin routes (to be implemented)
    // $router->post('/api/admin/requests/{id}/assign', 'AdminController@assignTechnician');
    // $router->get('/api/admin/dashboard/stats', 'AdminController@getDashboardStats');

    // Dispatch request to appropriate handler
    $response = $router->dispatch($request);

    // Clear any accidental output before sending JSON
    ob_end_clean();

    $response->send();
} catch (Exception $e) {
    // Handle any uncaught exceptions
    error_log($e->getMessage());

    // Clear any output
    ob_end_clean();

    $response = Response::serverError(
        $_ENV['APP_ENV'] === 'development' ? $e->getMessage() : 'An error occurred'
    );
    $response->send();
}
