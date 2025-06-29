<?php
namespace CAT\Controller;

use CAT\Service\TranslationService;

class TranslationController
{
    private $service;

    public function __construct(TranslationService $service)
    {
        $this->service = $service;
    }

    public function listForUnit(int $unitId): void
    {
        $translations = $this->service->listTranslationsForUnit($unitId);
        sendJson($translations);
    }

    public function get(int $id): void
    {
        $translation = $this->service->getTranslation($id);
        if (!$translation) {
            sendJson(['error' => 'Not Found'], 404);
        }
        sendJson($translation);
    }

    public function create(): void
    {
        $input = json_decode(file_get_contents('php://input'), true);
        if (empty($input['unit_id']) || empty($input['language_id']) || empty($input['translated_text'])) {
            sendJson(['error' => 'Missing required fields'], 400);
        }
        $data = $this->service->createTranslation($input['unit_id'], $input['language_id'], $input['translated_text']);
        sendJson($data, 201);
    }

    public function update(int $id): void
    {
        $input = json_decode(file_get_contents('php://input'), true);
        if (empty($input['translated_text'])) {
            sendJson(['error' => 'Missing translated_text'], 400);
        }
        $data = $this->service->updateTranslation($id, $input['translated_text']);
        if (!$data) {
            sendJson(['error' => 'Update failed'], 500);
        }
        sendJson($data);
    }

    public function delete(int $id): void
    {
        $success = $this->service->deleteTranslation($id);
        if (!$success) {
            sendJson(['error' => 'Delete failed'], 500);
        }
        sendJson(['status' => 'deleted']);
    }
} 