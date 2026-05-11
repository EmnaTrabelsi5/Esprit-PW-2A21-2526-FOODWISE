<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function redirectTo(string $path): void
{
    header('Location: ' . $path);
    exit;
}

function requireAdmin(): void
{
    if (empty($_SESSION['user_role'])) {
        redirectTo('/test-acces.php');
    }

    if ($_SESSION['user_role'] !== 'admin') {
        redirectTo('/test-acces.php?access=forbidden');
    }
}

function requireClient(): void
{
    if (empty($_SESSION['user_role'])) {
        redirectTo('/test-acces.php');
    }

    if ($_SESSION['user_role'] !== 'client') {
        redirectTo('/test-acces.php?access=forbidden');
    }
}
