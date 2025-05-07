<?php

namespace App\Controllers;

/**
 * Base controller with common functionality
 */
abstract class BaseController
{
    /**
     * Send a JSON response
     * 
     * @param mixed $data
     * @param int $statusCode
     * @return void
     */
    protected function sendResponse($data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        
        // Ensure data is valid for JSON encoding
        if (is_null($data)) {
            $data = [];
        }
        
        // Add debug info for error responses
        if (empty($data) && $statusCode >= 400) {
            $debug = [
                'error' => 'An error occurred',
                'timestamp' => date('Y-m-d H:i:s'),
                'uri' => $_SERVER['REQUEST_URI'] ?? 'unknown',
                'method' => $_SERVER['REQUEST_METHOD'] ?? 'unknown'
            ];
            $data = is_array($data) ? array_merge($data, $debug) : $debug;
        }
        
        // For empty array results, ensure we return an empty array, not an empty object
        if ($data === []) {
            echo '[]';
        } else {
            echo json_encode($data, JSON_PRETTY_PRINT);
        }
        exit;
    }
    
    /**
     * Parse the request body as JSON
     * 
     * @return array
     */
    protected function getRequestBody(): array
    {
        $requestBody = file_get_contents('php://input');
        if (empty($requestBody)) {
            return [];
        }
        
        $data = json_decode($requestBody, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->sendResponse(['error' => 'Invalid JSON in request body'], 400);
        }
        
        return $data;
    }
    
    /**
     * Validate required fields in the request data
     * 
     * @param array $data
     * @param array $requiredFields
     * @return bool
     */
    protected function validateRequiredFields(array $data, array $requiredFields): bool
    {
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                $this->sendResponse(['error' => "Field '$field' is required"], 400);
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Handle an exception and send an appropriate response
     * 
     * @param \Exception $e
     * @return void
     */
    protected function handleException(\Exception $e): void
    {
        $statusCode = 500;
        
        // Determine appropriate status code based on exception type
        if ($e instanceof \InvalidArgumentException) {
            $statusCode = 400;
        }
        
        $this->sendResponse(['error' => $e->getMessage()], $statusCode);
    }
} 