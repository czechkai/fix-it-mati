<?php
/**
 * Authentication Controller
 * Handles API endpoints for authentication
 */

namespace FixItMati\Controllers;

use FixItMati\Core\Request;
use FixItMati\Core\Response;
use FixItMati\Services\AuthService;

class AuthController {
    private $authService;
    
    public function __construct() {
        $this->authService = AuthService::getInstance();
    }
    
    /**
     * POST /api/auth/register
     * Register a new user
     */
    public function register(Request $request): Response {
        $data = $request->all();
        
        $result = $this->authService->register($data);
        
        if ($result['success']) {
            // Generate JWT token for the new user (use user_object, not array)
            $token = $this->authService->generateToken($result['user_object']);
            
            return Response::created([
                'user' => $result['user'],
                'token' => $token
            ], $result['message']);
        }
        
        return Response::validationError(
            'Registration failed',
            $result['errors'] ?? null
        );
    }
    
    /**
     * POST /api/auth/login
     * Login user
     */
    public function login(Request $request): Response {
        $email = $request->input('email');
        $password = $request->input('password');
        $remember = $request->input('remember', false);
        
        if (empty($email) || empty($password)) {
            return Response::badRequest('Email and password are required');
        }
        
        $result = $this->authService->login($email, $password, $remember);
        
        if ($result['success']) {
            // Generate JWT token for API authentication
            $user = $this->authService->user();
            $token = $this->authService->generateToken($user);
            
            return Response::success([
                'user' => $result['user'],
                'token' => $token
            ], $result['message']);
        }
        
        return Response::unauthorized($result['message']);
    }
    
    /**
     * POST /api/auth/logout
     * Logout user
     */
    public function logout(Request $request): Response {
        $this->authService->logout();
        
        return Response::success(null, 'Logout successful');
    }
    
    /**
     * GET /api/auth/me
     * Get currently authenticated user
     */
    public function me(Request $request): Response {
        // Get user from request (set by AuthMiddleware)
        $user = $request->user();
        
        if (!$user) {
            return Response::unauthorized('Not authenticated');
        }
        
        // If user is already an array (from JWT), return it
        if (is_array($user)) {
            return Response::success($user);
        }
        
        // If user is a User object (from session), convert to array
        if (is_object($user) && method_exists($user, 'toArray')) {
            return Response::success($user->toArray());
        }
        
        return Response::success($user);
    }
    
    /**
     * POST /api/auth/refresh
     * Refresh JWT token
     */
    public function refresh(Request $request): Response {
        $token = $request->bearerToken();
        
        if (!$token) {
            return Response::unauthorized('Token not provided');
        }
        
        $payload = $this->authService->verifyToken($token);
        
        if (!$payload) {
            return Response::unauthorized('Invalid or expired token');
        }
        
        // Get user and generate new token
        $user = $this->authService->user();
        
        if (!$user) {
            return Response::unauthorized('User not found');
        }
        
        $newToken = $this->authService->generateToken($user);
        
        return Response::success([
            'token' => $newToken
        ], 'Token refreshed successfully');
    }
}
