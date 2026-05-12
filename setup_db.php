<?php
require_once 'config/config.php';

try {
    $pdo = config::getConnexion();
    echo "Connexion DB foodwise reussie.\n";

    $sqlFiles = [
        dirname(__DIR__) . '/foodwise(1).sql',
        dirname(__DIR__) . '/mealplanner.sql',
    ];

    foreach ($sqlFiles as $sqlPath) {
        if (!is_file($sqlPath)) {
            echo "Fichier introuvable: $sqlPath\n";
            continue;
        }

        $sql = file_get_contents($sqlPath);
        if ($sql === false || trim($sql) === '') {
            echo "Fichier SQL vide ou illisible: $sqlPath\n";
            continue;
        }

        // Import idempotent: evite les erreurs si les objets existent deja.
        $sql = preg_replace('/\bCREATE TABLE\b/i', 'CREATE TABLE IF NOT EXISTS', $sql);
        $sql = preg_replace('/\bINSERT INTO\b/i', 'INSERT IGNORE INTO', $sql);
        $sql = preg_replace('/\bCREATE DATABASE\b.*?;/i', '', $sql);
        $sql = preg_replace('/\bUSE\b\s+`?.+?`?\s*;/i', '', $sql);
        $sql = str_replace('START TRANSACTION;', '', $sql);
        $sql = str_replace('COMMIT;', '', $sql);
        $sql = preg_replace('/^ALTER TABLE .*ADD CONSTRAINT.*;$/mi', '', $sql);

        $statements = array_filter(array_map('trim', explode(";\n", $sql)));
        foreach ($statements as $statement) {
            if ($statement === '') {
                continue;
            }

            try {
                $pdo->exec($statement);
            } catch (Throwable $e) {
                // On continue pour importer le maximum sans interrompre le script.
                echo "Avertissement SQL: " . $e->getMessage() . "\n";
            }
        }

        echo "Import termine: " . basename($sqlPath) . "\n";
    }

    echo "Integration des bases terminee.\n";

} catch (Exception $e) {
    echo "Erreur: " . $e->getMessage() . "\n";
}
?>
