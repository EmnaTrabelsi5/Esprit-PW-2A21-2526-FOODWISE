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
        $route = 'mealplanner/' . $area . '/' . $resource . '/' . $action;
        $q = array_merge(['route' => $route], $query);
        return self::index() . '?' . http_build_query($q);
    }

    /**
     * Chemin URL vers un fichier sous le dossier assets/mealplanner de FoodWise.
     */
    public static function asset(string $path): string
    {
        $path = ltrim(str_replace('\\', '/', $path), '/');
        $script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '/index.php');
        $dir = rtrim(dirname($script), '/');
        $base = ($dir === '' || $dir === '.') ? '' : $dir;
        return $base . '/assets/mealplanner/' . $path;
    }
}

