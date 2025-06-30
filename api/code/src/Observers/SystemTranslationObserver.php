<?php

namespace Kirilmaz\Interview\Observers;

class SystemTranslationObserver extends Event {
    public object $request;
    public array $response;
    public string $language;
    public array $languages;
    public array $translations;

    public function __construct(object $request) {
        parent::__construct();
        $this->request = $request;
        $this->language = 'eng';
        $this->response = [];
        $this->languages = [];
        $this->translations = [];
    }

    public function getTranslations (): array {
        return $this->translations;
    }

    public function getLanguages (): array {
        return $this->languages;
    }
}
