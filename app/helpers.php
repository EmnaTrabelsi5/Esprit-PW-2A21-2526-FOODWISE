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

function generateAvatarSVG(string $name): string
{
    // Extraire les initiales
    $parts = explode(' ', trim($name));
    $initials = '';
    foreach (array_slice($parts, 0, 2) as $part) {
        if (!empty($part)) {
            $initials .= strtoupper($part[0]);
        }
    }
    $initials = substr($initials, 0, 2) ?: 'U';

    // Générer une couleur basée sur le nom
    $hash = abs(crc32($name));
    $colors = ['#667eea', '#764ba2', '#f093fb', '#4facfe', '#00f2fe', '#43e97b', '#fa709a', '#fee140'];
    $bgColor = $colors[$hash % count($colors)];

    // Créer un SVG
    $svg = <<<SVG
<svg width="150" height="150" xmlns="http://www.w3.org/2000/svg">
  <rect width="150" height="150" fill="{$bgColor}" rx="8"/>
  <text x="50%" y="50%" font-size="60" font-weight="bold" text-anchor="middle" dy=".3em" fill="white" font-family="Arial">
    {$initials}
  </text>
</svg>
SVG;

    return 'data:image/svg+xml;base64,' . base64_encode($svg);
}

function logModification(PDO $pdo, int $userId, ?int $adminId, string $entityType, int $entityId, string $fieldName, $oldValue, $newValue): void
{
    // Convertir en string pour comparer proprement
    $oldStr = ($oldValue === null) ? '' : (string) $oldValue;
    $newStr = ($newValue === null) ? '' : (string) $newValue;
    
    // Ne pas enregistrer si identiques
    if ($oldStr === $newStr) {
        error_log("LOG SKIP: $fieldName - $oldStr === $newStr");
        return;
    }

    try {
        $stmt = $pdo->prepare('
            INSERT INTO modifications_log (utilisateur_id, admin_id, entity_type, entity_id, field_name, old_value, new_value)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ');
        
        $result = $stmt->execute([
            $userId,
            $adminId,
            $entityType,
            $entityId,
            $fieldName,
            $oldStr,
            $newStr,
        ]);
        
        error_log("LOG INSERT: userId=$userId, admin=$adminId, field=$fieldName, old='$oldStr', new='$newStr', success=" . ($result ? 'YES' : 'NO'));
    } catch (PDOException $e) {
        error_log("LOG ERROR: " . $e->getMessage());
    }
}

