<?php
require __DIR__ . '/app/bootstrap.php';

try {
    $email = 'admin@foodwise.com';
    $password = 'admin123';
    $hash = password_hash($password, PASSWORD_DEFAULT);

    $pdo = getPdoConnection();
    
    // Supprimer l'ancien compte s'il existe
    $stmt = $pdo->prepare('DELETE FROM utilisateurs WHERE email = ?');
    $stmt->execute([$email]);

    // Créer le nouveau compte
    $stmt = $pdo->prepare('INSERT INTO utilisateurs (nom, prenom, email, password_hash, role) VALUES (?, ?, ?, ?, ?)');
    $stmt->execute(['Admin', 'FoodWise', $email, $hash, 'admin']);

    echo "<h2 style='color:green'>✅ Admin créé avec succès!</h2>";
    echo "<p><strong>Email:</strong> " . htmlspecialchars($email) . "</p>";
    echo "<p><strong>Mot de passe:</strong> " . htmlspecialchars($password) . "</p>";
    echo "<p><strong>Hash généré:</strong> " . htmlspecialchars($hash) . "</p>";
    echo "<hr>";
    echo "<p><a href='?route=module2.front.connexion'>Aller à la connexion</a></p>";
} catch (Exception $e) {
    echo "<h2 style='color:red'>❌ Erreur: " . htmlspecialchars($e->getMessage()) . "</h2>";
}
?>
