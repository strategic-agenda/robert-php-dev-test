<?php

namespace Kirilmaz\Interview\Core;

use Kirilmaz\Interview\Traits\QueryTrait;

class Pdo {
    use QueryTrait;

    private \PDO $_pdo;
    private \PDOStatement $_query;
    private object $_config;
    private array $_drivers = [
        'mysql'
    ];

    /**
     * @throws \Exception
     */
    public function __construct($config) {
        $this->_config = $config;
        $this->connect($config);
    }

    public function close(): void {
        $this->_pdo = null;
    }

    /**
     * @throws \Exception
     */
    private function connect($config): void {
        try {
            $this->_pdo = new \PDO(
                $config->driver . ':host=' . $config->host . ';dbname=' . $config->database . ';charset=utf8',
                $config->username,
                $config->password
            );
            $this->_pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        } catch (\PDOException $e) {
            throw new \Exception($e->getMessage());
        }
    }
}
