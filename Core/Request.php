<?php
/**
 * HTTP Request Handler
 * Handles incoming HTTP requests and provides easy access to request data
 */

namespace FixItMati\Core;

class Request {
    private $method;
    private $uri;
    private $headers;
    private $queryParams;
    private $bodyParams;
    private $files;
    private $routeParams = []; // Route parameters (e.g., {id})
    private $user = null; // Authenticated user data
    
    public function __construct() {
        $this->method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $this->uri = $this->parseUri();
        $this->headers = $this->parseHeaders();
        $this->queryParams = $_GET ?? [];
        $this->bodyParams = $this->parseBody();
        $this->files = $_FILES ?? [];
        
        // Handle method override for file uploads (POST with _method=PUT)
        if ($this->method === 'POST' && isset($this->bodyParams['_method'])) {
            $this->method = strtoupper($this->bodyParams['_method']);
            unset($this->bodyParams['_method']); // Remove the override param
        }
    }
    
    /**
     * Parse the request URI
     */
    private function parseUri(): string {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        
        // Remove query string
        if (($pos = strpos($uri, '?')) !== false) {
            $uri = substr($uri, 0, $pos);
        }
        
        return $uri;
    }
    
    /**
     * Parse request headers
     */
    private function parseHeaders(): array {
        $headers = [];
        
        foreach ($_SERVER as $key => $value) {
            if (strpos($key, 'HTTP_') === 0) {
                $header = str_replace(' ', '-', ucwords(str_replace('_', ' ', strtolower(substr($key, 5)))));
                $headers[$header] = $value;
            }
        }
        
        return $headers;
    }
    
    /**
     * Parse request body (handles JSON and form data)
     */
    private function parseBody(): array {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        
        // Handle JSON requests
        if (strpos($contentType, 'application/json') !== false) {
            $rawBody = file_get_contents('php://input');
            $data = json_decode($rawBody, true);
            return $data ?? [];
        }
        
        // Handle form data (POST or POST with method override)
        return $_POST;
    }
    
    /**
     * Get HTTP method
     */
    public function getMethod(): string {
        return $this->method;
    }
    
    /**
     * Get request URI
     */
    public function getUri(): string {
        return $this->uri;
    }
    
    /**
     * Get specific header
     */
    public function getHeader(string $name): ?string {
        return $this->headers[$name] ?? null;
    }
    
    /**
     * Get all headers
     */
    public function getHeaders(): array {
        return $this->headers;
    }
    
    /**
     * Get query parameter
     */
    public function query(string $key, $default = null) {
        return $this->queryParams[$key] ?? $default;
    }
    
    /**
     * Get all query parameters
     */
    public function allQuery(): array {
        return $this->queryParams;
    }
    
    /**
     * Get body parameter
     */
    public function input(string $key, $default = null) {
        return $this->bodyParams[$key] ?? $default;
    }
    
    /**
     * Get all body parameters
     */
    public function all(): array {
        return $this->bodyParams;
    }
    
    /**
     * Get uploaded file
     */
    public function file(string $key) {
        return $this->files[$key] ?? null;
    }
    
    /**
     * Check if request is JSON
     */
    public function isJson(): bool {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        return strpos($contentType, 'application/json') !== false;
    }
    
    /**
     * Check if request method is GET
     */
    public function isGet(): bool {
        return $this->method === 'GET';
    }
    
    /**
     * Check if request method is POST
     */
    public function isPost(): bool {
        return $this->method === 'POST';
    }
    
    /**
     * Check if request method is PUT
     */
    public function isPut(): bool {
        return $this->method === 'PUT';
    }
    
    /**
     * Check if request method is PATCH
     */
    public function isPatch(): bool {
        return $this->method === 'PATCH';
    }
    
    /**
     * Check if request method is DELETE
     */
    public function isDelete(): bool {
        return $this->method === 'DELETE';
    }
    
    /**
     * Get raw request body (for webhooks)
     */
    public function getBody(): string {
        return file_get_contents('php://input');
    }
    
    /**
     * Get bearer token from Authorization header
     */
    public function bearerToken(): ?string {
        $header = $this->getHeader('Authorization');
        
        if ($header && strpos($header, 'Bearer ') === 0) {
            return substr($header, 7);
        }
        
        return null;
    }
    
    /**
     * Set route parameters
     */
    public function setParams(array $params): void {
        $this->routeParams = $params;
    }
    
    /**
     * Get route parameter
     */
    public function param(string $key, $default = null) {
        return $this->routeParams[$key] ?? $default;
    }
    
    /**
     * Get all route parameters
     */
    public function allParams(): array {
        return $this->routeParams;
    }
    
    /**
     * Set authenticated user data
     */
    public function setUser(array $user): void {
        $this->user = $user;
    }
    
    /**
     * Get authenticated user data
     */
    public function user(): ?array {
        return $this->user;
    }
}
