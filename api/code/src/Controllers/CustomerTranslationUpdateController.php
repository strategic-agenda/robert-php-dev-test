<?php

namespace Kirilmaz\Interview\Controllers;

class CustomerTranslationUpdateController {
    public function handle () {
        return $this->response([
            'success' => true,
            'message' => 'response from translation update controller'
        ]);
    }
}
