<?php
require_once 'config/config.php';

try {
    $pdo = config::getConnexion();
    echo "Connexion DB réussie.\n";

    // Lire le fichier SQL
    $sql = file_get_contents('../projet_M5/database.sql');
    if (!$sql) {
        die("Erreur: Impossible de lire database.sql\n");
    }

    // Remplacer foodlog par foodwise
    $sql = str_replace('foodlog', 'foodwise', $sql);

    // Exécuter les requêtes
    $pdo->exec($sql);
    echo "Base de données et tables créées avec succès.\n";

} catch (Exception $e) {
    echo "Erreur: " . $e->getMessage() . "\n";
}
?>