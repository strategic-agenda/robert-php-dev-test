<?php

namespace Kirilmaz\Interview\Controllers;

use Kirilmaz\Interview\Models\CustomerLanguageModel;

class CustomerLanguageListController extends Controller {
    public function handle(): string {
        try {
            $languageModel = new CustomerLanguageModel();
            $languages = $languageModel->getAll();

            if (!$languages) {
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
            'data' => $languages
        ]);
    }
}
