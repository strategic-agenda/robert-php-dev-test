<?php

namespace Kirilmaz\Interview\Core;

class Server {
    /*
     * @var $_SERVER
     */
    protected array $server;

    public function __construct() {
        $this->server = $_SERVER;
    }

    public function get(string $key): string {
        return $this->server[$key];
    }

    public function getAll(): array {
        return $this->server;
    }
}
