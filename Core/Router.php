<?php
/**
 * API Router
 * Handles routing for REST API endpoints
 * Supports RESTful HTTP methods: GET, POST, PUT, PATCH, DELETE
 */

namespace FixItMati\Core;

class Router {
    private $routes = [];
    private $middlewares = [];
    private $currentMiddlewares = []; // Track middlewares for current route group
    
    /**
     * Register GET route
     */
    public function get(string $path, $handler): void {
        $this->addRoute('GET', $path, $handler);
    }
    
    /**
     * Register POST route
     */
    public function post(string $path, $handler): void {
        $this->addRoute('POST', $path, $handler);
    }
    
    /**
     * Register PUT route
     */
    public function put(string $path, $handler): void {
        $this->addRoute('PUT', $path, $handler);
    }
    
    /**
     * Register PATCH route
     */
    public function patch(string $path, $handler): void {
        $this->addRoute('PATCH', $path, $handler);
    }
    
    /**
     * Register DELETE route
     */
    public function delete(string $path, $handler): void {
        $this->addRoute('DELETE', $path, $handler);
    }
    
    /**
     * Add route to routes array
     */
    private function addRoute(string $method, string $path, $handler): void {
        // Normalize path
        $path = '/' . trim($path, '/');
        
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'handler' => $handler,
            'pattern' => $this->convertToPattern($path),
            'middlewares' => $this->currentMiddlewares // Store current middlewares with route
        ];
    }
    
    /**
     * Convert route path to regex pattern (supports dynamic parameters)
     * Example: /api/requests/{id} becomes /^\/api\/requests\/([^\/]+)$/
     */
    private function convertToPattern(string $path): string {
        // Escape forward slashes
        $pattern = str_replace('/', '\/', $path);
        
        // Convert {param} to regex capture group
        $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '([^\/]+)', $pattern);
        
        return '/^' . $pattern . '$/';
    }
    
    /**
     * Add global middleware
     */
    public function addMiddleware($middleware): void {
        $this->middlewares[] = $middleware;
        $this->currentMiddlewares[] = $middleware; // Add to current middlewares for future routes
    }
    
    /**
     * Dispatch request to appropriate handler
     */
    public function dispatch(Request $request): Response {
        $method = $request->getMethod();
        $uri = $request->getUri();
        
        // Find matching route
        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }
            
            // Check if URI matches route pattern
            if (preg_match($route['pattern'], $uri, $matches)) {
                // Remove full match, keep only captured groups
                array_shift($matches);
                
                // Extract parameter names from path
                preg_match_all('/\{([a-zA-Z0-9_]+)\}/', $route['path'], $paramNames);
                $params = [];
                
                if (!empty($paramNames[1])) {
                    foreach ($paramNames[1] as $index => $name) {
                        $params[$name] = $matches[$index] ?? null;
                    }
                }
                
                // Inject route params into Request object
                $request->setParams($params);
                
                // Run route-specific middlewares (if any)
                $routeMiddlewares = $route['middlewares'] ?? [];
                foreach ($routeMiddlewares as $middleware) {
                    $middlewareResult = $this->callMiddleware($middleware, $request);
                    
                    // If middleware returns a Response, return it immediately
                    if ($middlewareResult instanceof Response) {
                        return $middlewareResult;
                    }
                }
                
                // Call the route handler
                return $this->callHandler($route['handler'], $request, $params);
            }
        }
        
        // No route found
        return Response::notFound('Endpoint not found');
    }
    
    /**
     * Call middleware
     */
    private function callMiddleware($middleware, Request $request) {
        if (is_callable($middleware)) {
            return $middleware($request);
        }
        
        if (is_string($middleware) && class_exists($middleware)) {
            $instance = new $middleware();
            if (method_exists($instance, 'handle')) {
                return $instance->handle($request);
            }
        }
        
        if (is_object($middleware) && method_exists($middleware, 'handle')) {
            return $middleware->handle($request);
        }
        
        return null;
    }
    
    /**
     * Call route handler
     */
    private function callHandler($handler, Request $request, array $params): Response {
        try {
            // If handler is a closure/function
            if (is_callable($handler)) {
                $result = $handler($request, $params);
                return $this->normalizeResponse($result);
            }
            
            // If handler is a string like "ControllerName@method"
            if (is_string($handler) && strpos($handler, '@') !== false) {
                [$controller, $method] = explode('@', $handler);
                
                // Add namespace if not present
                if (strpos($controller, '\\') === false) {
                    $controller = "FixItMati\\Controllers\\$controller";
                }
                
                if (class_exists($controller)) {
                    $instance = new $controller();
                    if (method_exists($instance, $method)) {
                        $result = $instance->$method($request, $params);
                        return $this->normalizeResponse($result);
                    }
                }
            }
            
            // If handler is array [ControllerInstance, 'method']
            if (is_array($handler) && count($handler) === 2) {
                [$instance, $method] = $handler;
                if (method_exists($instance, $method)) {
                    $result = $instance->$method($request, $params);
                    return $this->normalizeResponse($result);
                }
            }
            
            return Response::serverError('Invalid route handler');
            
        } catch (\Exception $e) {
            // Log error in production
            error_log($e->getMessage());
            
            return Response::serverError(
                $_ENV['APP_ENV'] === 'development' ? $e->getMessage() : 'An error occurred'
            );
        }
    }
    
    /**
     * Normalize handler result to Response object
     */
    private function normalizeResponse($result): Response {
        // If already a Response, return as is
        if ($result instanceof Response) {
            return $result;
        }
        
        // If array or object, convert to JSON response
        if (is_array($result) || is_object($result)) {
            return Response::json($result);
        }
        
        // If string, return as JSON with success wrapper
        if (is_string($result)) {
            return Response::success(null, $result);
        }
        
        // Default success response
        return Response::success($result);
    }
}
