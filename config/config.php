<?php
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
