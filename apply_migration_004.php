<?php
declare(strict_types=1);

require __DIR__ . '/app/bootstrap.php';

echo "Exécution de la migration 004_add_privacy_columns.sql...\n";

try {
    $migrationSql = file_get_contents(__DIR__ . '/database/migrations/004_add_privacy_columns.sql');
    
    // Diviser le SQL en statements individuels et ignorer les commentaires
    $statements = array_filter(
        array_map('trim', preg_split('/;/', $migrationSql)),
        fn($stmt) => !empty(trim($stmt)) && !str_starts_with(trim($stmt), '--')
    );
    
    foreach ($statements as $statement) {
        $trimmed = trim($statement);
        if (!empty($trimmed)) {
            $pdo->exec($trimmed);
            echo "✓ Exécuté: " . substr($trimmed, 0, 60) . "...\n";
        }
    }
    
    echo "\n✅ Migration 004 exécutée avec succès !\n";
} catch (Exception $e) {
    echo "❌ Erreur lors de l'exécution de la migration:\n";
    echo $e->getMessage() . "\n";
    exit(1);
}
