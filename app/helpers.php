<?php
declare(strict_types=1);

function redirect(string $uri): void
{
    header('Location: ' . $uri);
    exit;
}

function oldValue(array $old, string $key): string
{
    return htmlspecialchars((string) ($old[$key] ?? ''), ENT_QUOTES, 'UTF-8');
}

function parseCommaList(string $value): array
{
    $items = array_filter(array_map('trim', explode(',', $value)), static fn($item) => $item !== '');
    return array_values($items);
}

function formatCommaList(array $items): string
{
    return implode(', ', array_map('trim', $items));
}

function validateEmail(string $value): bool
{
    return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
}

function sanitizeString(string $value): string
{
    return trim($value);
}

function buildRoute(string $route, array $params = []): string
{
    $url = '?route=' . urlencode($route);
    foreach ($params as $key => $value) {
        $url .= '&' . urlencode((string) $key) . '=' . urlencode((string) $value);
    }

    return $url;
}
