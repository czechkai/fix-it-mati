<?php
/**
 * HTTP Response Handler
 * Handles HTTP responses with proper status codes and content types
 */

namespace FixItMati\Core;

class Response {
    private $statusCode = 200;
    private $headers = [];
    private $body;
    
    /**
     * Set HTTP status code
     */
    public function setStatusCode(int $code): self {
        $this->statusCode = $code;
        return $this;
    }
    
    /**
     * Set response header
     */
    public function setHeader(string $name, string $value): self {
        $this->headers[$name] = $value;
        return $this;
    }
    
    /**
     * Set response body
     */
    public function setBody($body): self {
        $this->body = $body;
        return $this;
    }
    
    /**
     * Send the response
     */
    public function send(): void {
        // Set status code
        http_response_code($this->statusCode);
        
        // Set headers
        foreach ($this->headers as $name => $value) {
            header("$name: $value");
        }
        
        // Output body
        echo $this->body;
        exit;
    }
    
    /**
     * Create JSON response
     */
    public static function json($data, int $statusCode = 200): self {
        $response = new self();
        return $response
            ->setStatusCode($statusCode)
            ->setHeader('Content-Type', 'application/json')
            ->setBody(json_encode($data, JSON_PRETTY_PRINT));
    }
    
    /**
     * Create success response
     */
    public static function success($data = null, string $message = 'Success', int $statusCode = 200): self {
        return self::json([
            'success' => true,
            'message' => $message,
            'data' => $data
        ], $statusCode);
    }
    
    /**
     * Create error response
     */
    public static function error(string $message, int $statusCode = 400, $errors = null): self {
        $response = [
            'success' => false,
            'message' => $message
        ];
        
        if ($errors !== null) {
            $response['errors'] = $errors;
        }
        
        return self::json($response, $statusCode);
    }
    
    /**
     * Create 400 Bad Request response
     */
    public static function badRequest(string $message = 'Bad Request', $errors = null): self {
        return self::error($message, 400, $errors);
    }
    
    /**
     * Create 401 Unauthorized response
     */
    public static function unauthorized(string $message = 'Unauthorized'): self {
        return self::error($message, 401);
    }
    
    /**
     * Create 403 Forbidden response
     */
    public static function forbidden(string $message = 'Forbidden'): self {
        return self::error($message, 403);
    }
    
    /**
     * Create 404 Not Found response
     */
    public static function notFound(string $message = 'Resource not found'): self {
        return self::error($message, 404);
    }
    
    /**
     * Create 422 Unprocessable Entity response (validation errors)
     */
    public static function validationError(string $message = 'Validation failed', $errors = null): self {
        return self::error($message, 422, $errors);
    }
    
    /**
     * Create 500 Internal Server Error response
     */
    public static function serverError(string $message = 'Internal server error'): self {
        return self::error($message, 500);
    }
    
    /**
     * Create 201 Created response
     */
    public static function created($data = null, string $message = 'Resource created successfully'): self {
        return self::success($data, $message, 201);
    }
    
    /**
     * Create 204 No Content response
     */
    public static function noContent(): self {
        $response = new self();
        return $response->setStatusCode(204);
    }
    
    /**
     * Create HTML response
     */
    public static function html(string $html, int $statusCode = 200): self {
        $response = new self();
        return $response
            ->setStatusCode($statusCode)
            ->setHeader('Content-Type', 'text/html; charset=utf-8')
            ->setBody($html);
    }
    
    /**
     * Create redirect response
     */
    public static function redirect(string $url, int $statusCode = 302): self {
        $response = new self();
        return $response
            ->setStatusCode($statusCode)
            ->setHeader('Location', $url)
            ->setBody('');
    }
}
