<?php

namespace App\Controllers;

use App\Interfaces\LanguageServiceInterface;

/**
 * Controller for languages
 */
class LanguageController extends BaseController
{
    /**
     * @var LanguageServiceInterface
     */
    private $service;
    
    /**
     * Constructor
     * 
     * @param LanguageServiceInterface $service
     */
    public function __construct(LanguageServiceInterface $service)
    {
        $this->service = $service;
    }
    
    /**
     * Handle the request
     * 
     * @param string $method HTTP method
     * @param string|null $code Language code
     * @param string|null $action Additional action
     * @return void
     */
    public function handleRequest(string $method, ?string $code, ?string $action): void
    {
        try {
            switch ($method) {
                case 'GET':
                    $this->handleGet($code);
                    break;
                
                case 'POST':
                    $this->handlePost();
                    break;
                
                case 'PUT':
                    $this->handlePut($code);
                    break;
                
                case 'DELETE':
                    $this->handleDelete($code);
                    break;
                
                default:
                    $this->sendResponse(['error' => 'Method not allowed'], 405);
            }
        } catch (\Exception $e) {
            $this->handleException($e);
        }
    }
    
    /**
     * Handle GET requests
     * 
     * @param string|null $code
     * @return void
     */
    private function handleGet(?string $code): void
    {
        if ($code === null) {
            // GET /api/languages - List all languages
            $languages = $this->service->getAll();
            $this->sendResponse($languages);
        } else {
            // GET /api/languages/{code} - Get a specific language
            $language = $this->service->getByCode($code);
            if ($language) {
                $this->sendResponse($language);
            } else {
                $this->sendResponse(['error' => 'Language not found'], 404);
            }
        }
    }
    
    /**
     * Handle POST requests
     * 
     * @return void
     */
    private function handlePost(): void
    {
        // POST /api/languages - Create a new language
        $requestData = $this->getRequestBody();
        
        if (!$this->validateRequiredFields($requestData, ['code', 'name'])) {
            return;
        }
        
        $newLanguage = $this->service->create($requestData);
        $this->sendResponse($newLanguage, 201);
    }
    
    /**
     * Handle PUT requests
     * 
     * @param string|null $code
     * @return void
     */
    private function handlePut(?string $code): void
    {
        if ($code === null) {
            $this->sendResponse(['error' => 'Language code is required'], 400);
            return;
        }
        
        // PUT /api/languages/{code} - Update a language
        $requestData = $this->getRequestBody();
        
        if (!$this->validateRequiredFields($requestData, ['name'])) {
            return;
        }
        
        // Get the language first to get its ID
        $language = $this->service->getByCode($code);
        if (!$language) {
            $this->sendResponse(['error' => 'Language not found'], 404);
            return;
        }
        
        $updated = $this->service->update($language['id'], $requestData);
        
        if ($updated) {
            $updatedLanguage = $this->service->getByCode($code);
            $this->sendResponse($updatedLanguage);
        } else {
            $this->sendResponse(['error' => 'Failed to update language'], 500);
        }
    }
    
    /**
     * Handle DELETE requests
     * 
     * @param string|null $code
     * @return void
     */
    private function handleDelete(?string $code): void
    {
        if ($code === null) {
            $this->sendResponse(['error' => 'Language code is required'], 400);
            return;
        }
        
        // Get the language first to get its ID
        $language = $this->service->getByCode($code);
        if (!$language) {
            $this->sendResponse(['error' => 'Language not found'], 404);
            return;
        }
        
        // DELETE /api/languages/{code} - Delete a language
        $deleted = $this->service->delete($language['id']);
        
        if ($deleted) {
            $this->sendResponse(['success' => true, 'message' => 'Language deleted']);
        } else {
            $this->sendResponse(['error' => 'Failed to delete language'], 500);
        }
    }
} 