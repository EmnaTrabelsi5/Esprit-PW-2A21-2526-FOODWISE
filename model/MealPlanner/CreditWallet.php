<?php

declare(strict_types=1);

namespace Model;

use Config\Database;
use PDO;

final class CreditWallet
{
    /**
     * Calcule le budget quotidien basé sur un objectif spécifique ou l'objectif actif (TDEE).
     */
    public function getDailyBudget(?int $objectifId = null): int
    {
        $pdo = Database::getConnection();
        if ($objectifId) {
            $stmt = $pdo->prepare('SELECT calories_cible FROM objectif WHERE id_obj = :id LIMIT 1');
            $stmt->execute(['id' => $objectifId]);
        } else {
            $stmt = $pdo->query('SELECT calories_cible FROM objectif ORDER BY id_obj DESC LIMIT 1');
        }
        $row = $stmt->fetch();
        return (int) ($row['calories_cible'] ?? 2000);
    }

    /**
     * Calcule les crédits dépensés aujourd'hui via les plans alimentaires liés à un objectif.
     */
    public function getSpentToday(?int $objectifId = null): int
    {
        $pdo = Database::getConnection();
        
        // Résolution dynamique du nom de la table des recettes (recette ou recettes)
        $tableStmt = $pdo->query("SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME IN ('recette', 'recettes') LIMIT 1");
        $tableRow = $tableStmt->fetch();
        $recetteTable = $tableRow ? $tableRow['TABLE_NAME'] : null;

        if (!$recetteTable) {
            return 0;
        }

        // Résolution dynamique du nom de la colonne des calories
        $colStmt = $pdo->prepare("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table AND COLUMN_NAME IN ('calories', 'kcal', 'calorie') LIMIT 1");
        $colStmt->execute(['table' => $recetteTable]);
        $colRow = $colStmt->fetch();
        $calCol = $colRow ? $colRow['COLUMN_NAME'] : 'calories';

        // Résolution du nom de la colonne du nom de la recette
        $nameColStmt = $pdo->prepare("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table AND COLUMN_NAME IN ('nom', 'nom_recette', 'titre') LIMIT 1");
        $nameColStmt->execute(['table' => $recetteTable]);
        $nameColRow = $nameColStmt->fetch();
        $nameCol = $nameColRow ? $nameColRow['COLUMN_NAME'] : 'nom';

        $sql = "
            SELECT SUM(r.`$calCol`) as spent
            FROM plans_recettes pr
            JOIN plans_alimentaires pa ON pa.id = pr.plan_alimentaire_id
            JOIN `$recetteTable` r ON r.`$nameCol` = pr.nom_recette
            WHERE CURDATE() BETWEEN pa.date_debut AND pa.date_fin
        ";

        $params = [];
        if ($objectifId) {
            $sql .= " AND pa.id_obj = :id_obj";
            $params['id_obj'] = $objectifId;
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch();
        return (int) ($row['spent'] ?? 0);
    }

    /**
     * Analyse la qualité des aliments pour accorder des remises (Multiplicateur de Qualité).
     */
    public function getQualityDiscount(?int $objectifId = null): int
    {
        // Logique simulée : Si bcp de légumes/fruits détectés, remise de 5%
        return (int) ($this->getSpentToday($objectifId) * 0.05);
    }

    /**
     * Récupère l'historique des "économies" (Crédits Bonus).
     */
    public function getSavingsHistory(?int $objectifId = null): array
    {
        // Simulation d'épargne sur les 7 derniers jours
        $history = [];
        $budget = $this->getDailyBudget($objectifId);
        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $spent = mt_rand((int)($budget * 0.7), (int)($budget * 1.1));
            $history[] = [
                'date' => $date,
                'budget' => $budget,
                'spent' => $spent,
                'saved' => max(0, $budget - $spent)
            ];
        }
        return $history;
    }

    /**
     * Détermine le grade de l'utilisateur.
     */
    public function getUserGrade(int $savingsTotal): string
    {
        if ($savingsTotal > 5000) return 'Gestionnaire Expert';
        if ($savingsTotal > 2000) return 'Investisseur Santé';
        return 'Dépensier Impulsif';
    }
}

