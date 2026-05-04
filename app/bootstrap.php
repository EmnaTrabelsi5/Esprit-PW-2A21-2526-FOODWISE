<?php
declare(strict_types=1);

// Charger le fichier .env
function loadEnvFile(): void
{
    $envFile = dirname(__DIR__) . '/.env';
    if (file_exists($envFile)) {
        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if (is_array($lines)) {
            foreach ($lines as $line) {
                if (strpos(trim($line), '#') === 0 || strpos($line, '=') === false) {
                    continue; // Ignorer les commentaires et les lignes sans '='
                }
                [$key, $value] = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);
                if (!empty($key) && !getenv($key)) {
                    putenv("{$key}={$value}");
                }
            }
        }
    }
}

loadEnvFile();

require __DIR__ . '/Config.php';
require __DIR__ . '/helpers.php';
require __DIR__ . '/AllergenMappings.php';
require __DIR__ . '/MailerService.php';
require __DIR__ . '/Models/UtilisateurModel.php';
require __DIR__ . '/Models/ProfilNutritionnelModel.php';
require __DIR__ . '/Models/MessageModel.php';
require __DIR__ . '/Controllers/FrontController.php';
require __DIR__ . '/Controllers/BackController.php';
require __DIR__ . '/Controllers/MessageController.php';

session_start();
$pdo = getPdoConnection();
