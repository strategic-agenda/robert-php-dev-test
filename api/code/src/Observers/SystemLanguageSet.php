<?php

namespace Kirilmaz\Interview\Observers;

use Kirilmaz\Interview\Models\SystemLanguageModel;

class SystemLanguageSet implements \SplObserver {
    /**
     * @throws \Exception
     */
    public function update(\SplSubject $event): void {
        if (isset($event->request->uuid) && $event->request->uuid) {
            $systemLanguageModel = new SystemLanguageModel();
            $cacheLanguage = $systemLanguageModel->redis()->get('language');

            if (!isset($cacheLanguage)) {
                $systemLanguageModel->redis()->set('language', 'eng');
            }

            $requestedLanguage = $systemLanguageModel->get($event->request->uuid);
            if ($cacheLanguage !== $requestedLanguage->iso_code) {
                $cacheLanguage = $requestedLanguage->iso_code;
                $systemLanguageModel->redis()->del('translations');
                $systemLanguageModel->redis()->set('language', $cacheLanguage);
            }

            $event->language = $cacheLanguage;

            unset($systemLanguageModel);
            unset($cacheLanguage);
        }
    }
}
