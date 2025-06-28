<?php

namespace App\Http\Controllers;

use App\Models\TranslationUnit;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class TranslationUnitController extends Controller
{
    /**
     * List translation units with pagination (10 per page)
     */
    public function index()
    {
        $units = TranslationUnit::with('versions')->paginate(10);

        return response()->json($units);
    }

    /**
     * Store a new translation unit
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'document_id' => 'required|integer',
            'segment_index' => 'required|integer',
            'source_text' => 'required|string',
            'source_locale' => 'required|string|size:2',
            'target_locale' => 'required|string|size:2',
            'translated_text' => 'nullable|string',
        ]);

        $unit = TranslationUnit::create($data);

        if (!empty($data['translated_text'])) {
            $unit->versions()->create([
                'translated_text' => $data['translated_text'],
                'edited_by' => auth()->id() ?? null,
                'version_number' => 1,
            ]);
        }

        return response()->json($unit->load('versions'), 201);
    }

    /**
     * Retrieve a single translation unit
     */
    public function show(TranslationUnit $translationUnit)
    {
        return response()->json($translationUnit->load('versions'));
    }

    /**
     * Update a translation unit and append a new version if translated_text provided
     */
    public function update(Request $request, TranslationUnit $translationUnit)
    {
        $data = $request->validate([
            'source_text' => 'sometimes|string',
            'source_locale' => 'sometimes|string|size:2',
            'target_locale' => 'sometimes|string|size:2',
            'translated_text' => 'nullable|string',
        ]);

        $translationUnit->update(
            array_filter($request->only(['source_text', 'source_locale', 'target_locale']))
        );

        if (!empty($data['translated_text'])) {
            $latest = $translationUnit->versions()->first();
            $versionNumber = $latest ? $latest->version_number + 1 : 1;

            $translationUnit->versions()->create([
                'translated_text' => $data['translated_text'],
                'edited_by' => auth()->id() ?? null,
                'version_number' => $versionNumber,
            ]);
        }

        return response()->json($translationUnit->load('versions'));
    }

    /**
     * Delete a translation unit and its history
     */
    public function destroy(TranslationUnit $translationUnit)
    {
        $translationUnit->delete();

        return response()->noContent();
    }
}
