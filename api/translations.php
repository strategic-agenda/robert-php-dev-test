<?php
// RESTful API endpoints for managing translation units in the CAT tool
// Uses Laravel framework for routing, validation, and Eloquent ORM

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\TranslationUnit;
use App\Models\Translation;
use App\Models\TranslationHistory;
use Illuminate\Support\Facades\DB;

// Middleware for authentication (e.g., JWT or API token)
Route::middleware('auth:api')->group(function () {

    // 1. List All Translation Units
    Route::get('/translation-units', function (Request $request) {
        $validator = Validator::make($request->all(), [
            'page' => 'integer|min:1',
            'per_page' => 'integer|min:1|max:100',
            'source_language' => 'string|size:2',
            'target_language' => 'string|size:2',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        $query = TranslationUnit::with(['translations' => function ($query) use ($request) {
            if ($request->has('target_language')) {
                $query->where('target_language', $request->input('target_language'));
            }
        }]);

        if ($request->has('source_language')) {
            $query->where('source_language', $request->input('source_language'));
        }

        $perPage = $request->input('per_page', 10);
        $units = $query->paginate($perPage);

        return response()->json([
            'data' => $units->items(),
            'meta' => [
                'current_page' => $units->currentPage(),
                'per_page' => $units->perPage(),
                'total' => $units->total(),
            ],
        ], 200);
    });

    // 2. Retrieve a Specific Translation Unit
    Route::get('/translation-units/{id}', function ($id) {
        $unit = TranslationUnit::with(['translations', 'history'])->find($id);

        if (!$unit) {
            return response()->json(['error' => 'Translation unit not found'], 404);
        }

        return response()->json(['data' => $unit], 200);
    });

    // 3. Create a Translation Unit
    Route::post('/translation-units', function (Request $request) {
        $validator = Validator::make($request->all(), [
            'source_text' => 'required|string|max:65535',
            'source_language' => 'required|string|size:2',
            'project_id' => 'required|exists:projects,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        $unit = TranslationUnit::create([
            'source_text' => $request->input('source_text'),
            'source_language' => $request->input('source_language'),
            'project_id' => $request->input('project_id'),
        ]);

        return response()->json(['data' => $unit], 201);
    });

    // 4. Add a Translation to a Translation Unit
    Route::post('/translation-units/{id}/translations', function (Request $request, $id) {
        $unit = TranslationUnit::find($id);
        if (!$unit) {
            return response()->json(['error' => 'Translation unit not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'target_language' => 'required|string|size:2',
            'target_text' => 'required|string|max:65535',
            'translator_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        $existingTranslation = Translation::where('translation_unit_id', $id)
            ->where('target_language', $request->input('target_language'))
            ->first();

        if ($existingTranslation) {
            return response()->json(['error' => 'Translation for this language already exists'], 422);
        }

        $translation = Translation::create([
            'translation_unit_id' => $id,
            'target_text' => $request->input('target_text'),
            'target_language' => $request->input('target_language'),
            'translator_id' => $request->input('translator_id'),
            'status' => 'draft',
        ]);

        TranslationHistory::create([
            'translation_id' => $translation->id,
            'previous_text' => $request->input('target_text'),
            'changed_by' => $request->input('translator_id'),
            'change_reason' => 'Initial translation',
        ]);

        return response()->json(['data' => $translation], 201);
    });

    // 5. Update a Translation
    Route::put('/translation-units/{id}/translations/{target_language}', function (Request $request, $id, $target_language) {
        $translation = Translation::where('translation_unit_id', $id)
            ->where('target_language', $target_language)
            ->first();

        if (!$translation) {
            return response()->json(['error' => 'Translation not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'target_text' => 'required|string|max:65535',
            'translator_id' => 'required|exists:users,id',
            'reason' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        DB::transaction(function () use ($translation, $request) {
            TranslationHistory::create([
                'translation_id' => $translation->id,
                'previous_text' => $translation->target_text,
                'changed_by' => $request->input('translator_id'),
                'change_reason' => $request->input('reason'),
            ]);

            $translation->update([
                'target_text' => $request->input('target_text'),
                'translator_id' => $request->input('translator_id'),
                'updated_at' => now(),
            ]);
        });

        return response()->json(['data' => $translation], 200);
    });

    // 6. Delete a Translation Unit
    Route::delete('/translation-units/{id}', function ($id) {
        $unit = TranslationUnit::find($id);

        if (!$unit) {
            return response()->json(['error' => 'Translation unit not found'], 404);
        }

        $unit->delete(); // Soft delete

        return response()->json([], 204);
    });
});
?>