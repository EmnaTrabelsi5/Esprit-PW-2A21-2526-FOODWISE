<?php
declare(strict_types=1);

require __DIR__ . '/app/bootstrap.php';

echo "=== TEST VÉRIFICATION CODE DE RÉINITIALISATION ===\n\n";

$email = 'samirabdallah018@gmail.com';
$codeToTest = '134622'; // Le code qu'on vient de voir en base de données

echo "Email: " . $email . "\n";
echo "Code à tester: " . $codeToTest . "\n\n";

// Simuler la vérification
$userModel = new UtilisateurModel($pdo);
$user = $userModel->verifyResetCode($email, $codeToTest);

if ($user === null) {
    echo "❌ Vérification échouée\n";
} else {
    echo "✓ Vérification réussie!\n";
    echo "Utilisateur: " . $user['email'] . "\n";
}

echo "\n=== VÉRIFICATION DIRECTE EN BDD ===\n";

$stmt = $pdo->prepare('SELECT id, email, reset_code, reset_code_expires_at FROM utilisateurs WHERE email = ?');
$stmt->execute([$email]);
$userRecord = $stmt->fetch();

if ($userRecord) {
    echo "Email trouvé: " . $userRecord['email'] . "\n";
    echo "Code en BDD: " . ($userRecord['reset_code'] ?? 'NULL') . "\n";
    echo "Expire à: " . ($userRecord['reset_code_expires_at'] ?? 'NULL') . "\n";
    
    $expiresAt = strtotime($userRecord['reset_code_expires_at']);
    $now = time();
    
    echo "Temps actuel: " . date('Y-m-d H:i:s', $now) . "\n";
    echo "Expiration: " . date('Y-m-d H:i:s', $expiresAt) . "\n";
    
    if ($now > $expiresAt) {
        echo "❌ Code EXPIRÉ\n";
    } else {
        echo "✓ Code VALIDE (expire dans " . (($expiresAt - $now) / 60) . " minutes)\n";
    }
    
    // Tester la comparaison
    $stored = $userRecord['reset_code'];
    $provided = $codeToTest;
    
    echo "\n=== COMPARAISON DES CODES ===\n";
    echo "Stocké: '" . $stored . "' (type: " . gettype($stored) . ")\n";
    echo "Fourni: '" . $provided . "' (type: " . gettype($provided) . ")\n";
    echo "Égal ? " . ($stored === $provided ? 'OUI ✓' : 'NON ❌') . "\n";
    
    // Nettoyage
    $cleanedStored = trim(str_replace(['-', ' '], '', $stored));
    $cleanedProvided = trim(str_replace(['-', ' '], '', $provided));
    
    echo "\nAprès nettoyage:\n";
    echo "Stocké: '" . $cleanedStored . "'\n";
    echo "Fourni: '" . $cleanedProvided . "'\n";
    echo "Égal ? " . ($cleanedStored === $cleanedProvided ? 'OUI ✓' : 'NON ❌') . "\n";
} else {
    echo "❌ Utilisateur non trouvé\n";
}
?>
