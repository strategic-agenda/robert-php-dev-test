<?php

namespace App\Controllers;

use App\Interfaces\TranslationUnitServiceInterface;

/**
 * Controller for translation units
 */
class TranslationUnitController extends BaseController
{
    /**
     * @var TranslationUnitServiceInterface
     */
    private $service;
    
    /**
     * Constructor
     * 
     * @param TranslationUnitServiceInterface $service
     */
    public function __construct(TranslationUnitServiceInterface $service)
    {
        $this->service = $service;
    }
    
    /**
     * Handle the request
     * 
     * @param string $method HTTP method
     * @param string|null $id Resource ID
     * @param string|null $action Additional action
     * @return void
     */
    public function handleRequest(string $method, ?string $id, ?string $action): void
    {
        try {
            switch ($method) {
                case 'GET':
                    $this->handleGet($id, $action);
                    break;
                
                case 'POST':
                    $this->handlePost($id, $action);
                    break;
                
                case 'PUT':
                    $this->handlePut($id, $action);
                    break;
                
                case 'DELETE':
                    $this->handleDelete($id);
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
     * @param string|null $id
     * @param string|null $action
     * @return void
     */
    private function handleGet(?string $id, ?string $action): void
    {
        if ($id === null) {
            // GET /api/units - List all units
            $units = $this->service->getAll();
            $this->sendResponse($units);
        } else if ($action === 'history') {
            // GET /api/units/{id}/history - Get unit history
            $history = $this->service->getHistory((int)$id);
            $this->sendResponse($history);
        } else {
            // GET /api/units/{id} - Get a specific unit
            $unit = $this->service->get($id);
            if ($unit) {
                $this->sendResponse($unit);
            } else {
                $this->sendResponse(['error' => 'Translation unit not found'], 404);
            }
        }
    }
    
    /**
     * Handle POST requests
     * 
     * @param string|null $id
     * @param string|null $action
     * @return void
     */
    private function handlePost(?string $id, ?string $action): void
    {
        if ($id !== null) {
            $this->sendResponse(['error' => 'POST method not allowed for individual resources'], 405);
            return;
        }
        
        // POST /api/units - Create a new unit
        $requestData = $this->getRequestBody();
        $newUnit = $this->service->create($requestData);
        $this->sendResponse($newUnit, 201);
    }
    
    /**
     * Handle PUT requests
     * 
     * @param string|null $id
     * @param string|null $action
     * @return void
     */
    private function handlePut(?string $id, ?string $action): void
    {
        if ($id === null) {
            $this->sendResponse(['error' => 'Unit ID is required'], 400);
            return;
        }
        
        // PUT /api/units/{id} - Update a unit
        $requestData = $this->getRequestBody();
        
        // Extract target text from request data - supports both camelCase and snake_case
        $targetText = $requestData['targetText'] ?? $requestData['target_text'] ?? null;
        
        if (empty($targetText)) {
            $this->sendResponse(['error' => 'Target text is required'], 400);
            return;
        }
        
        $updatedUnit = $this->service->updateTranslation((int)$id, $targetText);
        
        if ($updatedUnit) {
            $this->sendResponse($updatedUnit);
        } else {
            $this->sendResponse(['error' => 'Failed to update translation unit'], 500);
        }
    }
    
    /**
     * Handle DELETE requests
     * 
     * @param string|null $id
     * @return void
     */
    private function handleDelete(?string $id): void
    {
        if ($id === null) {
            $this->sendResponse(['error' => 'Unit ID is required'], 400);
            return;
        }
        
        // DELETE /api/units/{id} - Delete a unit
        $deleted = $this->service->delete($id);
        
        if ($deleted) {
            $this->sendResponse(['success' => true, 'message' => 'Translation unit deleted']);
        } else {
            $this->sendResponse(['error' => 'Failed to delete translation unit'], 500);
        }
    }
} 