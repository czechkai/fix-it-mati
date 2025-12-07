<?php
/**
 * API Entry Point
 * All API requests go through this file
 * Implements RESTful API routing
 */

// Enable error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Allow CORS (adjust in production)
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
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
    $router->get('/api/test', function(Request $req) {
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
    $router->post('/api/webhooks/paypal', 'WebhookController@paypalWebhook');
    $router->post('/api/webhooks/gcash', 'WebhookController@gcashWebhook');
    
    // Authentication endpoints
    $router->post('/api/auth/register', 'AuthController@register');
    $router->post('/api/auth/login', 'AuthController@login');
    $router->post('/api/auth/logout', 'AuthController@logout');
    $router->post('/api/auth/refresh', 'AuthController@refresh');
    
    // Health check
    $router->get('/api/health', function() {
        return Response::success([
            'status' => 'healthy',
            'database' => 'connected',
            'timestamp' => date('Y-m-d H:i:s')
        ]);
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
    
    // Get current authenticated user
    $router->get('/api/auth/me', 'AuthController@me');
    
    // ============================================
    // SERVICE REQUEST ROUTES (Protected)
    // ============================================
    
    // Statistics (must come before {id} route)
    $router->get('/api/requests/statistics', 'RequestController@statistics');
    
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
    
    // ============================================
    // NOTIFICATION ROUTES (Protected)
    // ============================================
    
    // List and count notifications
    $router->get('/api/notifications', 'NotificationController@index');
    $router->get('/api/notifications/unread-count', 'NotificationController@unreadCount');
    
    // Mark as read
    $router->patch('/api/notifications/{id}/read', 'NotificationController@markAsRead');
    $router->post('/api/notifications/mark-all-read', 'NotificationController@markAllAsRead');
    
    // Delete notification
    $router->delete('/api/notifications/{id}', 'NotificationController@delete');
    
    // Test notification (development only)
    $router->post('/api/notifications/test', 'NotificationController@sendTest');
    
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
    // Will be implemented in next phase
    // $router->post('/api/admin/requests/{id}/assign', 'AdminController@assignTechnician');
    // $router->get('/api/admin/technicians', 'AdminController@getTechnicians');
    // $router->get('/api/admin/dashboard/stats', 'AdminController@getDashboardStats');
    
    // Dispatch request to appropriate handler
    $response = $router->dispatch($request);
    $response->send();
    
} catch (Exception $e) {
    // Handle any uncaught exceptions
    error_log($e->getMessage());
    
    $response = Response::serverError(
        $_ENV['APP_ENV'] === 'development' ? $e->getMessage() : 'An error occurred'
    );
    $response->send();
}
