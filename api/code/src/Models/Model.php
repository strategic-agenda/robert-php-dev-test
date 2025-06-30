<?php

namespace Kirilmaz\Interview\Models;

use Kirilmaz\Interview\Core\Pdo;
use Kirilmaz\Interview\Traits\QueryTrait;

// TODO: Implement pagination

class Model {
    private object $_pdo;
    private object $_redis;

    use QueryTrait;

    /**
     * @throws \Exception
     */
    public function __construct() {
        // TODO: Proper implement needed
        $configMysql = file_get_contents(__DIR__ . './../../../configs/mysql.json');
        $configMysql = (object) json_decode($configMysql, true);
        $this->_pdo = new Pdo($configMysql);

        $configRedis = file_get_contents(__DIR__ . './../../../configs/redis.json');
        $configRedis = json_decode($configRedis, true);
        $this->_redis = new \Predis\Client($configRedis);
    }

    /**
     * @return Pdo
     */
    public function pdo(): Pdo {
        return $this->_pdo;
    }

    /**
     * @return \Predis\Client
     */
    public function redis (): \Predis\Client {
        return $this->_redis;
    }

    /**
     * @param array $translations
     * @return void
     */
    public function setTranslationCache (string $key, string $field, array $translations): void {
        $this->redis()->hset($key, $field, json_encode($translations));
    }

    /**
     * @param string $type
     * @return array
     */
    public function getTranslationCache (string $key, string $type): array {
        $translations = $this->redis()->hget($key, strtolower($type));
        return $translations ? json_decode($translations, true) : [];
    }
}
