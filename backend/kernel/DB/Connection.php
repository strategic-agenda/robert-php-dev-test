<?php

declare(strict_types=1);

namespace Kernel\DB;

use PDO;

class Connection
{
    /**
     * @var PDO|null
     */
    private static ?PDO $instance = null;

    /**
     * @return PDO
     */
    public static function getConnection(): PDO
    {
        if (self::$instance) {
            return self::$instance;
        }

        self::$instance = new PDO($_ENV['DB_DSN'], $_ENV['DB_USERNAME'], $_ENV['DB_PASSWORD']);
        self::$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        // self::$instance->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

        return self::$instance;
    }
}