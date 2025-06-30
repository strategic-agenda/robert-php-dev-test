<?php

namespace Kirilmaz\Interview\Observers;

use Kirilmaz\Interview\Models\Model;

class SystemCacheSet implements \SplObserver {
    /**
     * @throws \Exception
     */
    public function update(\SplSubject $event): void {
        if (isset($event->request->uuid) && $event->request->uuid) {
            /**
             * Set cache to redis
             */
            $model = new Model();
            $model->setTranslationCache('translations', 'system', [$event->language => $event->translations]);

            unset($systemLanguageModel);
            unset($cacheLanguage);
        }
    }
}
