<?php

namespace Kirilmaz\Interview\Controllers;

class CustomerTranslationSaveController {
    public function handle () {
        return $this->response([
            'success' => true,
            'message' => 'response from translation save controller'
        ]);
    }
}
