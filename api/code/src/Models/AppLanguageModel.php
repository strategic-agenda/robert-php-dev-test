<?php

namespace Kirilmaz\Interview\Models;

class AppLanguageModel extends Model {
    public function getLanguage (): string | null {
        return $this->redis()->get('language');
    }

    public function setLanguage (string $language): void {
        $this->redis()->set('language', $language);
    }
}
