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

function appBasePath(): string
{
    $script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '/index.php');
    $dir = rtrim(dirname($script), '/');
    // Si on est à la racine, dirname retourne \ ou /
    if ($dir === '/' || $dir === '\\' || $dir === '.') {
        return '';
    }
    return $dir;
}

function assetUrl(string $path): string
{
    $cleanPath = ltrim(str_replace('\\', '/', $path), '/');
    $base = appBasePath();
    return $base . '/assets/' . $cleanPath;
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
    } catch (PDOException $e) {
        error_log("LOG ERROR: " . $e->getMessage());
    }
}

/**
 * Calcule l'Indice de Masse Corporelle (IMC)
 * IMC = poids (kg) / (taille (m))²
 * 
 * @param float $poids_kg Poids en kilogrammes
 * @param int $taille_cm Taille en centimètres
 * @return float L'IMC arrondi à 2 décimales
 */
function calculateIMC(float $poids_kg, int $taille_cm): float
{
    if ($taille_cm <= 0 || $poids_kg <= 0) {
        return 0;
    }
    $taille_m = $taille_cm / 100;
    return round($poids_kg / ($taille_m * $taille_m), 2);
}

/**
 * Interprète l'IMC et retourne une catégorie
 * 
 * @param float $imc L'Indice de Masse Corporelle
 * @return array ['categorie' => string, 'couleur' => string, 'description' => string]
 */
function interpretIMC(float $imc): array
{
    if ($imc < 18.5) {
        return [
            'categorie' => 'Insuffisance pondérale',
            'couleur' => 'info',
            'description' => 'Poids insuffisant'
        ];
    } elseif ($imc < 25) {
        return [
            'categorie' => 'Normal',
            'couleur' => 'success',
            'description' => 'Poids normal'
        ];
    } elseif ($imc < 30) {
        return [
            'categorie' => 'Surpoids',
            'couleur' => 'warning',
            'description' => 'Surpoids léger'
        ];
    } elseif ($imc < 35) {
        return [
            'categorie' => 'Obésité classe I',
            'couleur' => 'alert',
            'description' => 'Obésité de classe I'
        ];
    } elseif ($imc < 40) {
        return [
            'categorie' => 'Obésité classe II',
            'couleur' => 'alert',
            'description' => 'Obésité de classe II'
        ];
    } else {
        return [
            'categorie' => 'Obésité classe III',
            'couleur' => 'alert',
            'description' => 'Obésité sévère'
        ];
    }
}


