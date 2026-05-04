<?php
declare(strict_types=1);

require __DIR__ . '/app/bootstrap.php';

echo "=== DEBUG RESET CODE ===\n\n";

// Récupérer tous les utilisateurs avec leur code de réinitialisation
$stmt = $pdo->query('SELECT id, email, reset_code, reset_code_expires_at FROM utilisateurs WHERE reset_code IS NOT NULL');
$users = $stmt->fetchAll();

if (empty($users)) {
    echo "❌ Aucun utilisateur avec code de réinitialisation\n";
} else {
    echo "✓ Utilisateurs avec code de réinitialisation :\n\n";
    foreach ($users as $user) {
        echo "ID: " . $user['id'] . "\n";
        echo "Email: " . $user['email'] . "\n";
        echo "Code stocké: " . ($user['reset_code'] ?? 'NULL') . "\n";
        echo "Expire à: " . ($user['reset_code_expires_at'] ?? 'NULL') . "\n";
        
        if ($user['reset_code_expires_at']) {
            $expiresAt = strtotime($user['reset_code_expires_at']);
            $now = time();
            $remaining = $expiresAt - $now;
            
            echo "Temps restant: ";
            if ($remaining > 0) {
                echo $remaining . " secondes (" . ceil($remaining / 60) . " minutes) ✓\n";
            } else {
                echo "EXPIRÉ ! (" . abs($remaining) . " secondes) ❌\n";
            }
        }
        echo "\n";
    }
}

echo "\n=== INFOS SYSTÈME ===\n";
echo "Heure serveur: " . date('Y-m-d H:i:s') . "\n";
echo "Timezone: " . date_default_timezone_get() . "\n";
?>
