<?php

namespace Kirilmaz\Interview\Controllers;

use Kirilmaz\Interview\Models\CustomerTranslationModel;

class CustomerTranslationGetAllGroupedByTranslationKeyController extends Controller {
    public function handle(): string {
        try {
            $translationModel = new CustomerTranslationModel();
            $translations = $translationModel->getAllGroupedByTranslationKey();

            if (!$translations) {
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
            'data' => $translations
        ]);
    }
}
