<?php
/**
 * Authentication Middleware
 * Implements CHAIN OF RESPONSIBILITY DESIGN PATTERN
 * 
 * Design Pattern: Chain of Responsibility
 * Purpose: Passes request through a chain of handlers (middleware)
 * Each handler decides whether to process the request or pass it to the next handler
 */

namespace FixItMati\Middleware;

use FixItMati\Core\Request;
use FixItMati\Core\Response;
use FixItMati\Services\AuthService;

class AuthMiddleware {
    private $authService;
    
    public function __construct() {
        $this->authService = AuthService::getInstance();
    }
    
    /**
     * Handle the request
     * Chain of Responsibility pattern - checks auth, then passes to next handler
     */
    public function handle(Request $request): ?Response {
        // Try token authentication first (for API)
        $token = $request->bearerToken();
        
        if ($token) {
            $payload = $this->authService->verifyToken($token);
            
            if ($payload) {
                // Token is valid - attach user data to request
                $request->setUser($payload);
                return null;
            }
            
            return Response::unauthorized('Invalid or expired token');
        }
        
        // Try session authentication (for web)
        if ($this->authService->check()) {
            // User is authenticated, allow request to proceed
            $request->setUser($this->authService->user());
            return null;
        }
        
        // Not authenticated
        return Response::unauthorized('Not authenticated');
    }
}
