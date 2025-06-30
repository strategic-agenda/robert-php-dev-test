<?php

namespace Kirilmaz\Interview\Models;

/**
 * Translations records for users
 * The
 */
class CustomerTranslationModel extends Model {
    /**
     * @throws \Exception
     * @var string $uuid
     */
    public function get (string $uuid): object {
        return $this->pdo()
            ->prepare("SELECT * FROM `translations` WHERE `system` = :system AND `uuid` = :uuid")
            ->execute(['uuid' => $uuid, 'system' => 0])
            ->first();
    }

    /**
     * @throws \Exception
     */
    public function getAll(): array {
        return $this->pdo()
            ->prepare("SELECT * FROM `translations` WHERE `system` = :system ORDER BY `translation_key`")
            ->execute(['system' => 0])
            ->all();
    }

    /**
     * @throws \Exception
     */
    public function getAllGroupedByTranslationKey(): array {
        return $this->pdo()
            ->prepare("SELECT * FROM `translations` WHERE `system` = :system GROUP BY `translation_key` ORDER BY `translation_key`")
            ->execute(['system' => 0])
            ->all();
    }

    public function getByTranslationKey(string $translationKey): array {
        return $this->pdo()
            ->prepare("SELECT * FROM `translations` WHERE `system` = :system AND `translation_key` = :translationKey")
            ->execute(['translationKey' => $translationKey, 'system' => 0])
            ->all();
    }
}
