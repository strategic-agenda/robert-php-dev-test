<?php

namespace Kirilmaz\Interview\Controllers;

use Kirilmaz\Interview\Models\CustomerTranslationModel;

class CustomerTranslationDetailController extends Controller {
    public function handle ($request): string {
        try {
            $translationModel = new CustomerTranslationModel();
            $translation = $translationModel->get($request->uuid);
            $translation = $translationModel->getByTranslationKey($translation->translation_key);

            if (empty((array) $translation)) {
                $message = 'No record found';
            } else {
                $message = 'OK';
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
            'message' => $message,
            'data' => $translation
        ]);

        unset($translation);
    }
}
