<?php

namespace Kirilmaz\Interview\Controllers;

use Kirilmaz\Interview\Observers\SystemCacheSet;
use Kirilmaz\Interview\Observers\SystemLanguageSet;
use Kirilmaz\Interview\Observers\SystemLanguagesGet;
use Kirilmaz\Interview\Observers\SystemTranslationsGet;
use Kirilmaz\Interview\Observers\SystemTranslationObserver;
use Kirilmaz\Interview\Observers\SystemTranslationsSet;
use Kirilmaz\Interview\Observers\TranslationObserver;

class SystemTranslationsController extends Controller {
    public function handle($request): string {
        try {
            $event = new SystemTranslationObserver($request);

            $event->attach(new SystemLanguageSet());
            $event->attach(new SystemTranslationsSet());
            $event->attach(new SystemCacheSet());
            $event->attach(new SystemLanguagesGet());

            $event->notify();

            if (isset($request->uuid)) {
                return $this->response([
                    'success' => true,
                    'message' => 'OK',
                    'data' => []
                ]);
            }

        } catch (\Exception $exception) {
            return $this->response([
                'success' => false,
                'message' => $exception->getMessage(),
                'data' => []
            ]);
        }

        return $this->response([
            'success' => true,
            'message' => 'OK',
            'data' => $event->getTranslations()
        ]);

        unset($translations);
        unset($event);
    }
}
