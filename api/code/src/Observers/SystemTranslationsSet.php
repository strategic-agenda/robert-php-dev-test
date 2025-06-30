<?php

namespace Kirilmaz\Interview\Observers;

use Kirilmaz\Interview\Models\SystemTranslationModel;

class SystemTranslationsSet implements \SplObserver {
    /**
     * @throws \Exception
     */
    public function update(\SplSubject $event): void {
        $systemTranslationModel = new SystemTranslationModel();
        $event->translations = $systemTranslationModel->getByLanguage($event->language);

        unset($systemTranslationModel);
    }
}
