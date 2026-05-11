<?php
<<<<<<< HEAD
class config
{
    private static $pdo = null;

    public static function getConnexion()
    {
        if (self::$pdo == null) {

            try {
                self::$pdo = new PDO(
                    "mysql:host=localhost;dbname=foodwise;charset=utf8mb4",
                    "root",
                    ""
                );

                self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);

            } catch (Exception $e) {
                die('Erreur DB: ' . $e->getMessage());
            }
        }

        return self::$pdo;
    }
}
=======
declare(strict_types=1);

require_once __DIR__ . '/settings.php';

$dsn = sprintf('mysql:host=%s;dbname=%s;charset=%s', DB_HOST, DB_NAME, DB_CHARSET);
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $exception) {
    header('Content-Type: text/plain; charset=utf-8');
    exit('Erreur de connexion à la base de données : ' . $exception->getMessage());
}

return $pdo;
>>>>>>> 7601b90fdab6bf6325a2b078d25608a292b8ddc1
