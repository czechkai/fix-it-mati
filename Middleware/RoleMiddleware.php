<?php
/**
 * Role-Based Access Control Middleware
 * Extends Chain of Responsibility pattern
 * Checks if authenticated user has required role
 */

namespace FixItMati\Middleware;

use FixItMati\Core\Request;
use FixItMati\Core\Response;
use FixItMati\Services\AuthService;

class RoleMiddleware {
    private $authService;
    private $allowedRoles;
    
    public function __construct(array $allowedRoles = []) {
        $this->authService = AuthService::getInstance();
        $this->allowedRoles = $allowedRoles;
    }
    
    /**
     * Handle the request
     * Check if user has required role
     */
    public function handle(Request $request): ?Response {
        $user = $this->authService->user();
        
        if (!$user) {
            return Response::unauthorized('Authentication required');
        }
        
        // If no specific roles required, just being authenticated is enough
        if (empty($this->allowedRoles)) {
            return null;
        }
        
        // Check if user has any of the allowed roles
        foreach ($this->allowedRoles as $role) {
            if ($user->hasRole($role)) {
                return null; // User has required role
            }
        }
        
        return Response::forbidden('You do not have permission to access this resource');
    }
}
