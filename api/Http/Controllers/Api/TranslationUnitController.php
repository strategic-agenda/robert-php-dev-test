<?php

declare(strict_types=1);

namespace Api\Http\Controllers\Api;

use Api\Services\TranslationUnitService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Api\Http\Resources\TranslationUnitResource;
use Api\Http\Resources\TranslationHistoryResource;

class TranslationUnitController
{
    public function __construct(
        private readonly TranslationUnitService $service
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['source_language', 'target_language', 'project_id']);
        $perPage = (int) $request->input('per_page', 10);
        
        $translationUnits = $this->service->getAll($filters, $perPage);

        return response()->json([
            'data' => TranslationUnitResource::collection($translationUnits),
            'meta' => [
                'total' => $translationUnits->total(),
                'per_page' => $translationUnits->perPage(),
                'current_page' => $translationUnits->currentPage(),
                'last_page' => $translationUnits->lastPage()
            ]
        ]);
    }

    public function show(string $id): JsonResponse
    {
        $translationUnit = $this->service->findById($id);
        return response()->json(['data' => new TranslationUnitResource($translationUnit)]);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'source_text' => 'required|string',
            'source_language' => 'required|string|regex:/^[a-z]{2}(-[A-Z]{2})?$/',
            'target_language' => 'required|string|regex:/^[a-z]{2}(-[A-Z]{2})?$/',
            'project_id' => 'required|string',
            'target_text' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $translationUnit = $this->service->create($request->all());
        return response()->json(['data' => new TranslationUnitResource($translationUnit)], 201);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'target_text' => 'required|string',
            'changed_by' => 'required|string',
            'change_reason' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $translationUnit = $this->service->update($id, $request->all());
        return response()->json(['data' => new TranslationUnitResource($translationUnit)]);
    }

    public function destroy(string $id): JsonResponse
    {
        $this->service->delete($id);
        return response()->json(['message' => 'Translation unit deleted successfully']);
    }

    public function history(string $id): JsonResponse
    {
        $history = $this->service->getHistory($id);
        return response()->json(['data' => TranslationHistoryResource::collection($history)]);
    }
} 