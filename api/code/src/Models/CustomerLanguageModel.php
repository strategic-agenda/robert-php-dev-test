<?php

namespace Kirilmaz\Interview\Models;

class CustomerLanguageModel extends Model {
    /**
     * @throws \Exception
     */
    public function getAll(): array {
        // TODO: Implement pagination
        return $this->pdo()
            ->prepare("SELECT * FROM `languages` WHERE `active` = :active")
            ->execute(['active' => true])
            ->all();
    }
}
