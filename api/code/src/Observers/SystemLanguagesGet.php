<?php

namespace Kirilmaz\Interview\Observers;

use Kirilmaz\Interview\Models\SystemLanguageModel;

class SystemLanguagesGet implements \SplObserver {

    public function update(\SplSubject $event): void {
        if(!isset($event->request->uuid)) {
            $systemLanguagesModel = new SystemLanguageModel();
            $event->languages = $systemLanguagesModel->getAll();
        }
    }
}
