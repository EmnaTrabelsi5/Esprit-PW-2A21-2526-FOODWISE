<?php

declare(strict_types=1);

ob_start();
session_start();

require_once __DIR__ . '/config/Config.php';

// Autoloader simple pour l'architecture MVC
spl_autoload_register(function (string $class): void {
    $parts = explode('\\', $class);
    $namespace = $parts[0] ?? '';
    $relativeClass = implode('/', array_slice($parts, 1));
    
    $baseDir = '';
    switch ($namespace) {
        case 'Controller':
            $baseDir = __DIR__ . '/controller/';
            break;
        case 'Model':
            $baseDir = __DIR__ . '/model/';
            break;
    }

    if ($baseDir !== '') {
        $file = $baseDir . $relativeClass . '.php';
        if (is_file($file)) {
            require_once $file;
        }
    }
});

use Controller\Router;

$router = new Router();
$router->dispatch();
