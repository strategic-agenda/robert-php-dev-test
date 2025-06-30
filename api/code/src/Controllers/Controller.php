<?php

namespace Kirilmaz\Interview\Controllers;

class Controller {
    public function response (array $data): string {
        return json_encode($data);
    }
}
