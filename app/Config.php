<?php
declare(strict_types=1);

const DEFAULT_ROUTE = 'module2.front.mon_profil';

function getEnvValue(string $key, string $default): string
{
    $value = getenv($key);
    return $value === false ? $default : $value;
}

function getPdoConnection(): PDO
{
    $driver = getEnvValue('DB_DRIVER', 'mysql');
    $host = getEnvValue('DB_HOST', '127.0.0.1');
    $port = getEnvValue('DB_PORT', '3306');
    $dbName = getEnvValue('DB_NAME', 'foodwise');
    $dbUser = getEnvValue('DB_USER', 'root');
    $dbPass = getEnvValue('DB_PASS', '');

    if ($driver === 'sqlite') {
        $dbFile = __DIR__ . '/../data/database.sqlite';
        if (!file_exists($dbFile)) {
            $dir = dirname($dbFile);
            if (!is_dir($dir)) {
                mkdir($dir, 0777, true);
            }
        }

        $pdo = new PDO('sqlite:' . $dbFile, null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);

        if (filesize($dbFile) === 0) {
            initializeDatabase($pdo);
        }

        return $pdo;
    }

    $pdo = new PDO(
        sprintf('mysql:host=%s;port=%s;charset=utf8mb4', $host, $port),
        $dbUser,
        $dbPass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );

    $pdo->exec(sprintf(
        'CREATE DATABASE IF NOT EXISTS `%s` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci',
        $dbName
    ));

    $pdo->exec(sprintf('USE `%s`', $dbName));
    initializeDatabase($pdo);

    return $pdo;
}

function initializeDatabase(PDO $pdo): void
{
    $schema = file_get_contents(__DIR__ . '/../database/schema.sql');
    if ($schema === false) {
        throw new RuntimeException('Unable to read the database schema file.');
    }

    $pdo->exec($schema);
}
