<?php

namespace Kirilmaz\Interview\Models;

class SystemTranslationModel extends Model {
    /**
     * @throws \Exception
     */
    public function getAll(): array {
        /**
         * Get translation data from database
         */
        $translations = $this->pdo()
            ->prepare("SELECT * FROM `translations` WHERE `system` = :system ORDER BY `translation_key`")
            ->execute(['system' => true])
            ->all();

        return $translations;
    }

    public function getByLanguage (string $language): array {
        /**
         * Get redis cache for language
         */
        $language = $this->redis()->get('language');

        $translations = $this->pdo()
            ->prepare("SELECT * FROM `translations` WHERE `iso_code` = :isoCode AND `system` = :system ORDER BY `translation_key`")
            ->execute(['isoCode' => $language, 'system' => true])
            ->all();

        return $translations;
    }
}
