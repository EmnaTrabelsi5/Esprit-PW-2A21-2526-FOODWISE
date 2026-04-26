<?php

declare(strict_types=1);

namespace Controller;

/**
 * Génère les URLs du point d'entrée public.
 */
final class Url
{
    public static function index(): string
    {
        if (PHP_SAPI === 'cli') {
            return '/index.php';
        }
        return $_SERVER['SCRIPT_NAME'] ?? '/index.php';
    }

    public static function to(string $area, string $resource, string $action, array $query = []): string
    {
        $q = array_merge([
            'area' => $area,
            'resource' => $resource,
            'action' => $action,
        ], $query);
        return self::index() . '?' . http_build_query($q);
    }

    /**
     * Chemin URL vers un fichier sous le dossier public (CSS, JS, images),
     * correct que la racine web soit public/ ou un sous-dossier (ex. /module4/public/).
     */
    public static function asset(string $path): string
    {
        $path = ltrim(str_replace('\\', '/', $path), '/');
        if (PHP_SAPI === 'cli') {
            return '/assets/' . $path;
        }
        $script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '/index.php');
        $dir = dirname($script);
        if ($dir === '/' || $dir === '.' || $dir === '\\') {
            return '/assets/' . $path;
        }

        return $dir . '/assets/' . $path;
    }
}
