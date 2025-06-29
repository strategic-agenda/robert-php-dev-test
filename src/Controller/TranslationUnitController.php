<?php
namespace CAT\Controller;

use CAT\Service\TranslationService;

class TranslationUnitController
{
    private $service;

    public function __construct(TranslationService $service)
    {
        $this->service = $service;
    }

    public function list(): void
    {
        $page = $_GET['page'] ?? 1;
        $perPage = $_GET['per_page'] ?? 50;

        $units = $this->service->listTranslationUnits((int)$page, (int)$perPage);
        sendJson($units);
    }

    public function get(int $id): void
    {
        $unit = $this->service->getTranslationUnit($id);
        if (!$unit) {
            sendJson(['error' => 'Not Found'], 404);
        }
        sendJson($unit);
    }

    public function create(): void
    {
        $input = json_decode(file_get_contents('php://input'), true);
        if (empty($input['source_text']) || empty($input['source_language_id'])) {
            sendJson(['error' => 'Missing required fields'], 400);
        }
        $data = $this->service->createTranslationUnit($input['source_text'], $input['source_language_id']);
        sendJson($data, 201);
    }

    public function update(int $id): void
    {
        $input = json_decode(file_get_contents('php://input'), true);
        if (empty($input['source_text'])) {
            sendJson(['error' => 'Missing source_text'], 400);
        }
        $unit = $this->service->updateTranslationUnit($id, $input['source_text']);
        if (!$unit) {
            sendJson(['error' => 'Update failed'], 500);
        }
        sendJson($unit);
    }

    public function delete(int $id): void
    {
        $success = $this->service->deleteTranslationUnit($id);
        if (!$success) {
            sendJson(['error' => 'Delete failed'], 500);
        }
        sendJson(['status' => 'deleted']);
    }
}
