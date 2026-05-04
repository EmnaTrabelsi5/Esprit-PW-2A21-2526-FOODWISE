<?php
declare(strict_types=1);

require __DIR__ . '/app/bootstrap.php';

echo "Exécution de la migration 002_add_user_sanctions.sql...\n";

try {
    $migrationSql = file_get_contents(__DIR__ . '/database/migrations/002_add_user_sanctions.sql');
    
    // Diviser le SQL en statements individuels
    $statements = array_filter(
        array_map('trim', explode(';', $migrationSql)),
        fn($stmt) => !empty($stmt)
    );
    
    foreach ($statements as $statement) {
        if (!empty(trim($statement))) {
            $pdo->exec($statement);
            echo "✓ Exécuté: " . substr($statement, 0, 50) . "...\n";
        }
    }
    
    echo "\n✅ Migration exécutée avec succès !\n";
} catch (Exception $e) {
    echo "❌ Erreur lors de l'exécution de la migration:\n";
    echo $e->getMessage() . "\n";
    exit(1);
}
