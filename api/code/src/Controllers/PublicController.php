<?php

namespace Kirilmaz\Interview\Controllers;

class PublicController extends Controller {
    public function handle (): string {
        return $this->response([
            'success' => true,
            'message' => 'Interview API v1.0.0'
        ]);
    }
}
