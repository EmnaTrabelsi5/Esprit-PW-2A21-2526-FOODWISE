<?php

declare(strict_types=1);

namespace Config;

use PDO;
use PDOException;

/**
 * Configuration applicative et Connexion à la base de données.
 */
class Config
{
    public static function get(): array
    {
        return [
            'db' => [
                'dsn' => 'mysql:host=127.0.0.1;dbname=mealplanner;charset=utf8mb4',
                'user' => 'root',
                'pass' => '',
                'options' => [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ],
            ],
            'app' => [
                'base_path' => dirname(__DIR__),
            ],
        ];
    }
}

final class Database
{
    private static ?PDO $connection = null;

    public static function getConnection(): PDO
    {
        if (self::$connection instanceof PDO) {
            return self::$connection;
        }

        $cfg = Config::get();
        $db = $cfg['db'];

        try {
            self::$connection = new PDO(
                $db['dsn'],
                $db['user'],
                $db['pass'],
                $db['options'] ?? []
            );
        } catch (PDOException $e) {
            throw new \RuntimeException('Échec de connexion PDO : ' . $e->getMessage(), 0, $e);
        }

        return self::$connection;
    }
}
