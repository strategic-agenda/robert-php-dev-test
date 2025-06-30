<?php

namespace Kirilmaz\Interview\Models;

class SystemLanguageModel extends Model {
    /**
     * @throws \Exception
     */
    public function get (string $uuid) {
        return $this->pdo()
            ->prepare("SELECT * FROM `languages` WHERE `uuid`= :uuid AND `active` = :active")
            ->execute(['uuid' => $uuid, 'active' => true])
            ->first();
    }

    /**
     * @throws \Exception
     */
    public function getAll(): array {
        return $this->pdo()
            ->prepare("SELECT * FROM `languages` WHERE `active` = :active")
            ->execute(['active' => true])
            ->all();
    }
}
