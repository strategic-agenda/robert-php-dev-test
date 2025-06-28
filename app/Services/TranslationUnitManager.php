<?php

namespace App\Services;

use App\Models\TranslationUnit;
use App\Models\TranslationUnitVersion;

class TranslationUnitManager
{
    /**
     * Create and store a new translation unit
     *
     * @param array $data // keys: document_id, segment_index, source_text, source_locale, target_locale
     * @return TranslationUnit
     */
    public function add(array $data): TranslationUnit
    {
        return TranslationUnit::create($data);
    }

    /**
     * Retrieve a translation unit by ID, including its versions
     *
     * @param int $id
     * @return TranslationUnit|null
     */
    public function getById(int $id): ?TranslationUnit
    {
        return TranslationUnit::with('versions')->find($id);
    }

    /**
     * Update a translation unit by adding a new version in history
     *
     * @param int $unitId
     * @param string $translatedText
     * @param int $userId
     * @return TranslationUnitVersion|null
     */
    public function update(int $unitId, string $translatedText, int $userId): ?TranslationUnitVersion
    {
        $unit = TranslationUnit::find($unitId);
        if (!$unit) {
            return null;
        }

        // determine next version number
        $latest = $unit->versions()->first();
        $versionNumber = $latest ? $latest->version_number + 1 : 1;

        return $unit->versions()->create([
            'translated_text' => $translatedText,
            'edited_by' => $userId,
            'version_number' => $versionNumber,
        ]);
    }
}
