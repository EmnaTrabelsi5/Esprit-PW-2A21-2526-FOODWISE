<?php
/**
 * Script de test pour le contrôle d'accès
 * Usage: test-acces.php?role=admin ou test-acces.php?role=client
 */

session_start();

$role = $_GET['role'] ?? '';

// Définir le rôle pour le test
if ($role === 'admin') {
    // Réinitialiser la session pour éviter les conflits
    session_destroy();
    session_start();
    $_SESSION['user_role'] = 'admin';
    $_SESSION['user_id'] = 1;
    echo "✅ Rôle défini: <strong>ADMIN</strong><br><br>";
    echo "<a href='view/back/dashboard-admin.php'>Tester page Admin</a><br>";
    echo "<a href='view/front/journal-alimentaire.php'>Tester page Client</a> (doit rediriger vers Admin)";
} elseif ($role === 'client') {
    // Réinitialiser la session pour éviter les conflits
    session_destroy();
    session_start();
    $_SESSION['user_role'] = 'client';
    $_SESSION['user_id'] = 1;
    echo "✅ Rôle défini: <strong>CLIENT</strong><br><br>";
    echo "<a href='view/back/dashboard-admin.php'>Tester page Admin</a> (doit être bloqué)<br>";
    echo "<a href='view/front/journal-alimentaire.php'>Tester page Client</a>";
} elseif ($role === 'logout') {
    session_destroy();
    echo "✅ Session détruite<br><br>";
    echo "<a href='test-acces.php'>Recommencer</a>";
} else {
    echo "🧪 <strong>Test du contrôle d'accès</strong><br><br>";
    echo "Session active: <strong>" . ($_SESSION['user_role'] ?? 'Aucune') . "</strong><br><br>";
    echo "<a href='test-acces.php?role=admin'>1. Simuler connexion ADMIN</a><br>";
    echo "<a href='test-acces.php?role=client'>2. Simuler connexion CLIENT</a><br>";
    echo "<a href='test-acces.php?role=logout'>3. Détruire la session</a>";
}
?>

<style>
body { font-family: Arial; padding: 40px; line-height: 1.8; }
a { display: inline-block; margin: 5px 0; padding: 10px 20px; background: #1976D2; color: white; text-decoration: none; border-radius: 4px; }
a:hover { background: #1565C0; }
strong { color: #1976D2; }
</style>