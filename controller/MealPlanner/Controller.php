<?php

declare(strict_types=1);

namespace Controller;

abstract class Controller
{
    protected function view(string $relativePath, array $data = []): void
    {
        extract($data, EXTR_SKIP);
        $viewFile = __DIR__ . '/../../view/MealPlanner/' . $relativePath . '.php';
        if (!is_file($viewFile)) {
            throw new \RuntimeException('Vue introuvable : ' . $relativePath);
        }
        require $viewFile;
    }

    protected function redirect(string $url): never
    {
        // Nettoyage de tout buffer de sortie avant redirection
        if (ob_get_level() > 0) {
            ob_end_clean();
        }
        
        // Construction d'une URL absolue pour le header Location
        if (!preg_match('~^https?://~i', $url)) {
            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
            
            if (str_starts_with($url, '/')) {
                $url = $protocol . '://' . $host . $url;
            } else {
                // Si l'URL est relative, on utilise le baseUrl (qui est index.php à la racine)
                $base = $this->baseUrl();
                $dir = dirname($base);
                $url = $protocol . '://' . $host . ($dir === '/' || $dir === '\\' ? '' : $dir) . '/' . ltrim($url, '/');
            }
        }

        header('Location: ' . $url, true, 302);
        exit;
    }

    protected function baseUrl(): string
    {
        return \Controller\Url::index();
    }

    protected function csrfToken(): string
    {
        if (empty($_SESSION['_csrf'])) {
            $_SESSION['_csrf'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['_csrf'];
    }

    protected function validateCsrf(?string $token): bool
    {
        return isset($_SESSION['_csrf'], $token) && hash_equals($_SESSION['_csrf'], $token);
    }
}

