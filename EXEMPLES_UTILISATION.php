<?php
/**
 * EXEMPLES D'UTILISATION - SuiviSante API
 * 
 * Ce fichier contient des exemples pratiques d'utilisation
 * du controller et du model SuiviSante
 */

declare(strict_types=1);

// ============================================
// INITIALISATION
// ============================================

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/controller/SuiviSanteController.php';
require_once __DIR__ . '/model/SuiviSante.php';

$pdo = require __DIR__ . '/config/config.php';
$controller = new SuiviSanteController($pdo);
$model = new SuiviSante($pdo);

// ============================================
// EXEMPLE 1: AJOUTER UN SUIVI
// ============================================

echo "=== EXEMPLE 1: Ajouter un suivi ===\n\n";

$newData = [
    'user_id' => 1,
    'date' => '2024-04-21',
    'type_activite' => 'Course',
    'duree' => 30,
    'calories_brulees' => 250.50,
    'intensite' => 'moyen',
    'quantite_eau' => 500,
    'note' => 'Bonne séance'
];

// Valider les données
$errors = $controller->validateSuiviData($newData);

if (!empty($errors)) {
    echo "Erreurs de validation:\n";
    foreach ($errors as $error) {
        echo "  - $error\n";
    }
} else {
    // Ajouter le suivi
    if ($controller->addSuivi($newData)) {
        echo "✓ Suivi ajouté avec succès!\n";
    } else {
        echo "✗ Erreur lors de l'ajout\n";
    }
}

echo "\n";

// ============================================
// EXEMPLE 2: MODIFIER UN SUIVI
// ============================================

echo "=== EXEMPLE 2: Modifier un suivi ===\n\n";

$suiviId = 1;
$updateData = [
    'user_id' => 1,
    'date' => '2024-04-21',
    'type_activite' => 'Course améliorée',
    'duree' => 45,
    'calories_brulees' => 350.00,
    'intensite' => 'élevé',
    'quantite_eau' => 750,
    'note' => 'Meilleure performance'
];

if ($controller->updateSuivi($suiviId, $updateData)) {
    echo "✓ Suivi modifié avec succès!\n";
} else {
    echo "✗ Erreur lors de la modification\n";
}

echo "\n";

// ============================================
// EXEMPLE 3: RÉCUPÉRER UN SUIVI PAR ID
// ============================================

echo "=== EXEMPLE 3: Récupérer un suivi ===\n\n";

$suivi = $controller->getSuivi(1);

if ($suivi) {
    echo "ID: " . $suivi['id'] . "\n";
    echo "Activité: " . $suivi['type_activite'] . "\n";
    echo "Date: " . $suivi['date'] . "\n";
    echo "Durée: " . $suivi['duree'] . " min\n";
    echo "Calories: " . $suivi['calories_brulees'] . " kcal\n";
    echo "Intensité: " . $suivi['intensite'] . "\n";
    echo "Eau: " . $suivi['quantite_eau'] . " mL\n";
} else {
    echo "Suivi non trouvé\n";
}

echo "\n";

// ============================================
// EXEMPLE 4: RÉCUPÉRER LES SUIVIS DU JOUR
// ============================================

echo "=== EXEMPLE 4: Suivis du jour ===\n\n";

$userId = 1;
$today = date('Y-m-d');
$dailySuivis = $controller->getDailySuivis($userId, $today);

echo "Suivis de $today pour l'utilisateur $userId:\n";
echo "Nombre: " . count($dailySuivis) . "\n\n";

foreach ($dailySuivis as $index => $suivi) {
    echo ($index + 1) . ". " . $suivi['type_activite'] . "\n";
    echo "   - Durée: " . $suivi['duree'] . " min\n";
    echo "   - Calories: " . $suivi['calories_brulees'] . " kcal\n";
    echo "   - Intensité: " . $suivi['intensite'] . "\n";
}

echo "\n";

// ============================================
// EXEMPLE 5: RÉCUPÉRER LES STATS DU JOUR
// ============================================

echo "=== EXEMPLE 5: Statistiques du jour ===\n\n";

$caloriesBurned = $controller->getDailyCaloriesBurned($userId, $today);
$waterIntake = $controller->getDailyWaterIntake($userId, $today);
$activityDuration = $controller->getDailyActivityDuration($userId, $today);

echo "Calories brûlées: " . $caloriesBurned . " kcal\n";
echo "Eau consommée: " . ($waterIntake / 1000) . " L\n";
echo "Durée d'activité: " . $activityDuration . " min (" . floor($activityDuration / 60) . "h " . ($activityDuration % 60) . "min)\n";

echo "\n";

// ============================================
// EXEMPLE 6: RAPPORT COMBINÉ (AVEC JOURNAL)
// ============================================

echo "=== EXEMPLE 6: Rapport combiné santé/nutrition ===\n\n";

require_once __DIR__ . '/controller/JournalController.php';
$journalController = new JournalController($pdo);

$report = $controller->getDailyHealthReport($userId, $today, $journalController);

echo "Rapport pour $today:\n";
echo "Calories consommées: " . $report['calories_consumed'] . " kcal\n";
echo "Calories brûlées: " . $report['total_calories_burned'] . " kcal\n";
echo "Bilan calorique: " . ($report['balance'] > 0 ? '+' : '') . $report['balance'] . " kcal\n";
echo "Eau consommée: " . ($report['total_water'] / 1000) . " L\n";
echo "Durée activité: " . $report['total_activity_duration'] . " min\n";

echo "\n";

// ============================================
// EXEMPLE 7: STATISTIQUES GLOBALES (ADMIN)
// ============================================

echo "=== EXEMPLE 7: Statistiques globales ===\n\n";

$globalStats = $controller->getGlobalStats();

echo "Total des suivis: " . $globalStats['total_followups'] . "\n";
echo "Utilisateurs actifs: " . $globalStats['total_users'] . "\n";
echo "Calories brûlées (total): " . $globalStats['total_calories_burned'] . " kcal\n";
echo "Eau consommée (total): " . ($globalStats['total_water'] / 1000) . " L\n";
echo "Jours suivis: " . $globalStats['days_tracked'] . "\n";
echo "Durée totale: " . $globalStats['total_duration'] . " min\n";

echo "\n";

// ============================================
// EXEMPLE 8: STATS PAR TYPE D'ACTIVITÉ
// ============================================

echo "=== EXEMPLE 8: Stats par type d'activité ===\n\n";

$statsByActivity = $controller->getStatsByActivityType();

echo "Activité | Nombre | Durée tot. | Calories tot. | Moy/séance\n";
echo str_repeat("-", 60) . "\n";

foreach ($statsByActivity as $stat) {
    printf(
        "%s | %d | %d min | %.2f kcal | %.2f kcal\n",
        str_pad($stat['type_activite'], 10),
        $stat['count'],
        $stat['total_duration'],
        $stat['total_calories'],
        $stat['avg_calories']
    );
}

echo "\n";

// ============================================
// EXEMPLE 9: STATS PAR INTENSITÉ
// ============================================

echo "=== EXEMPLE 9: Stats par intensité ===\n\n";

$statsByIntensity = $controller->getStatsByIntensity();

echo "Intensité | Sessions | Durée moy. | Calories moy.\n";
echo str_repeat("-", 50) . "\n";

foreach ($statsByIntensity as $stat) {
    printf(
        "%s | %d | %.0f min | %.2f kcal\n",
        str_pad($stat['intensite'], 10),
        $stat['count'],
        $stat['avg_duration'],
        $stat['avg_calories']
    );
}

echo "\n";

// ============================================
// EXEMPLE 10: TOP 5 ACTIVITÉS
// ============================================

echo "=== EXEMPLE 10: Top 5 activités fréquentes ===\n\n";

$topActivities = $controller->getMostFrequentActivities(5);

foreach ($topActivities as $index => $activity) {
    echo ($index + 1) . ". " . $activity['type_activite'] . "\n";
    echo "   Fréquence: " . $activity['frequency'] . " fois\n";
    echo "   Calories/session: " . $activity['avg_calories_burned'] . " kcal\n";
    echo "   Durée moy: " . $activity['avg_duration'] . " min\n\n";
}

echo "\n";

// ============================================
// EXEMPLE 11: RÉCUPÉRER TOUS LES SUIVIS (ADMIN)
// ============================================

echo "=== EXEMPLE 11: Tous les suivis (Admin) ===\n\n";

$allSuivis = $controller->getAllSuivis();

echo "Total: " . count($allSuivis) . " suivis\n";
echo "Premiers 5:\n\n";

$count = 0;
foreach ($allSuivis as $suivi) {
    if ($count++ >= 5) break;
    echo "- " . $suivi['type_activite'] . " (" . $suivi['date'] . ") - User #" . $suivi['user_id'] . "\n";
}

echo "\n";

// ============================================
// EXEMPLE 12: COMPTER LES UTILISATEURS ACTIFS
// ============================================

echo "=== EXEMPLE 12: Compter utilisateurs actifs ===\n\n";

$activeUsersCount = $controller->countActiveUsers();
$totalFollowupsCount = $controller->countSuivis();

echo "Utilisateurs avec au moins un suivi: " . $activeUsersCount . "\n";
echo "Nombre total de suivis: " . $totalFollowupsCount . "\n";

echo "\n";

// ============================================
// EXEMPLE 13: SUPPRIMER UN SUIVI
// ============================================

echo "=== EXEMPLE 13: Supprimer un suivi ===\n\n";

// Exemple - à adapter avec un vrai ID
$idToDelete = 999; // ID qui n'existe probablement pas

if ($controller->deleteSuivi($idToDelete)) {
    echo "✓ Suivi supprimé avec succès\n";
} else {
    echo "✗ Erreur lors de la suppression (ou suivi inexistant)\n";
}

echo "\n";

// ============================================
// EXEMPLE 14: VÉRIFIER L'ACCÈS UTILISATEUR
// ============================================

echo "=== EXEMPLE 14: Vérifier l'accès utilisateur ===\n\n";

$followUpId = 1;
$userId = 1;

if ($controller->userHasAccessToSuivi($userId, $followUpId)) {
    echo "✓ L'utilisateur $userId a accès au suivi #$followUpId\n";
} else {
    echo "✗ L'utilisateur $userId n'a PAS accès au suivi #$followUpId\n";
}

echo "\n";

// ============================================
// EXEMPLE 15: UTILISER LE MODEL DIRECTEMENT
// ============================================

echo "=== EXEMPLE 15: Utiliser le Model directement ===\n\n";

// Ajouter directement avec le model
$modelData = [
    'user_id' => 2,
    'date' => '2024-04-20',
    'type_activite' => 'Natation',
    'duree' => 45,
    'calories_brulees' => 350,
    'intensite' => 'moyen',
    'quantite_eau' => 1000,
    'note' => 'Excellent entraînement'
];

if ($model->add($modelData)) {
    echo "✓ Suivi ajouté via le model\n";
}

// Récupérer directement avec le model
$suivisModel = $model->getByUserAndDate(2, '2024-04-20');
echo "Nombre de suivis le 2024-04-20 pour user #2: " . count($suivisModel) . "\n";

echo "\n";

// ============================================
// RÉSUMÉ
// ============================================

echo "=== RÉSUMÉ DES OPÉRATIONS ===\n\n";
echo "Vous pouvez maintenant:\n";
echo "✓ Ajouter, modifier et supprimer des suivis\n";
echo "✓ Récupérer les suivis par jour\n";
echo "✓ Consulter des statistiques détaillées\n";
echo "✓ Intégrer avec le Journal Alimentaire\n";
echo "✓ Générer des rapports d'activité\n";
echo "\n";
echo "Pour utiliser en production, intégrez à vos contrôleurs.\n";
?>
