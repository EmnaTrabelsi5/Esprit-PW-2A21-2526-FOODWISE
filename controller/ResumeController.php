<?php
declare(strict_types=1);

require_once __DIR__ . '/JournalController.php';
require_once __DIR__ . '/SuiviSanteController.php';

class ResumeController
{
    private JournalController $journalController;
    private SuiviSanteController $suiviController;

    public function __construct(PDO $pdo)
    {
        $this->journalController = new JournalController($pdo);
        $this->suiviController = new SuiviSanteController($pdo);
    }

    /**
     * Récupère le résumé complet de la journée pour un utilisateur
     */
    public function getResumeDuJour(int $userId, string $date): array
    {
        // Données Journal Alimentaire
        $journalData = [
            'calories_consommees' => $this->journalController->getDailyCalories($userId, $date),
            'macros' => $this->journalController->getMacroTotals($userId, $date),
            'nombre_entrees' => $this->journalController->getEntryCount($userId, $date),
            'streak' => $this->journalController->getStreak($userId)
        ];

        // Données Suivi Santé
        $santeData = [
            'calories_brulees' => $this->suiviController->getDailyCaloriesBurned($userId, $date),
            'eau_consommee' => $this->suiviController->getDailyWaterIntake($userId, $date),
            'duree_activite' => $this->suiviController->getDailyActivityDuration($userId, $date),
            'activites' => $this->suiviController->getDailySuivis($userId, $date)
        ];

        // Analyse globale
        $analyse = [
            'net_calories' => $journalData['calories_consommees'] - $santeData['calories_brulees'],
            'objectif_calories' => 1800, // Objectif par défaut
            'progression_calories' => $this->calculerProgression($journalData['calories_consommees'], 1800),
            'objectif_eau' => 2000, // 2L par défaut
            'progression_eau' => $this->calculerProgression($santeData['eau_consommee'], 2000)
        ];

        return [
            'date' => $date,
            'journal' => $journalData,
            'sante' => $santeData,
            'analyse' => $analyse
        ];
    }

    /**
     * Calcule le pourcentage de progression par rapport à un objectif
     */
    private function calculerProgression(float $valeur, float $objectif): float
    {
        if ($objectif <= 0) {
            return 0.0;
        }
        return min(100.0, ($valeur / $objectif) * 100);
    }

    /**
     * Récupère les objectifs personnalisés (extension future)
     */
    public function getObjectifs(int $userId): array
    {
        // Pour l'instant, objectifs par défaut
        return [
            'calories' => 1800,
            'eau' => 2000,
            'proteines' => 150,
            'glucides' => 200,
            'lipides' => 70
        ];
    }
}
