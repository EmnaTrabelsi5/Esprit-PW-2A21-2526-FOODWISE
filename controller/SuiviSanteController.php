<?php
declare(strict_types=1);

require_once __DIR__ . '/../model/SuiviSante.php';

class SuiviSanteController
{
    private SuiviSante $model;

    public function __construct(PDO $pdo)
    {
        $this->model = new SuiviSante($pdo);
    }

    /**
     * Valider les données d'un suivi santé
     */
    public function validateSuiviData(array $data): array
    {
        $errors = [];
        $validIntensities = ['faible', 'moyen', 'élevé'];

        // Validation date
        if (empty($data['date'])) {
            $errors[] = 'La date est requise.';
        } elseif (!$this->isValidDate($data['date'])) {
            $errors[] = 'La date est invalide (format: YYYY-MM-DD).';
        }

        // Validation type d'activité
        $typeActivite = trim($data['type_activite'] ?? '');
        if (empty($typeActivite)) {
            $errors[] = 'Le type d\'activité est requis.';
        } elseif (strlen($typeActivite) > 100) {
            $errors[] = 'Le type d\'activité ne doit pas dépasser 100 caractères.';
        } elseif (preg_match('/[0-9]/', $typeActivite)) {
            $errors[] = 'Le type d\'activité ne doit pas contenir de chiffres.';
        } elseif (preg_match('/[^a-zA-ZÀ-ÿ\s\-]/u', $typeActivite)) {
            $errors[] = 'Le type d\'activité ne doit pas contenir de symboles ou caractères spéciaux.';
        }

        // Validation durée
        if (!isset($data['duree']) || $data['duree'] === '') {
            $errors[] = 'La durée est requise.';
        } elseif (!preg_match('/^\d+$/', $data['duree'])) {
            $errors[] = 'La durée doit être un nombre entier positif sans symbole ni lettre.';
        } elseif ((int)$data['duree'] <= 0) {
            $errors[] = 'La durée doit être strictement positive.';
        } elseif ((int)$data['duree'] > 1440) { // Plus de 24h
            $errors[] = 'La durée ne peut pas dépasser 1440 minutes (24 heures).';
        }

        // Validation calories brûlées
        if (!isset($data['calories_brulees']) || $data['calories_brulees'] === '') {
            $errors[] = 'Les calories brûlées sont requises.';
        } elseif (!preg_match('/^\d+(?:[.,]\d+)?$/', $data['calories_brulees'])) {
            $errors[] = 'Les calories brûlées doivent être un nombre positif sans symbole ni lettre.';
        } elseif ((float)str_replace(',', '.', $data['calories_brulees']) < 0) {
            $errors[] = 'Les calories brûlées doivent être positives ou nulles.';
        } elseif ((float)str_replace(',', '.', $data['calories_brulees']) > 99999.99) {
            $errors[] = 'Les calories brûlées sont invalides.';
        }

        // Validation intensité
        if (empty($data['intensite'])) {
            $errors[] = 'L\'intensité est requise.';
        } elseif (!in_array($data['intensite'], $validIntensities, true)) {
            $errors[] = 'L\'intensité doit être: faible, moyen ou élevé.';
        }

        // Validation quantité d'eau
        if (!isset($data['quantite_eau']) || $data['quantite_eau'] === '') {
            $errors[] = 'La quantité d\'eau est requise.';
        } elseif (!preg_match('/^\d+(?:[.,]\d+)?$/', $data['quantite_eau'])) {
            $errors[] = 'La quantité d\'eau doit être un nombre positif sans symbole ni lettre.';
        } elseif ((float)str_replace(',', '.', $data['quantite_eau']) < 0) {
            $errors[] = 'La quantité d\'eau doit être positive ou nulle.';
        } elseif ((float)str_replace(',', '.', $data['quantite_eau']) > 99999.99) {
            $errors[] = 'La quantité d\'eau est invalide.';
        }

        // Validation note (optionnelle)
        if (isset($data['note']) && strlen($data['note']) > 500) {
            $errors[] = 'La note ne doit pas dépasser 500 caractères.';
        }

        return $errors;
    }

    /**
     * Vérifier si une date est valide au format YYYY-MM-DD
     */
    private function isValidDate(string $date): bool
    {
        $dateObj = \DateTime::createFromFormat('Y-m-d', $date);
        return $dateObj !== false && $dateObj->format('Y-m-d') === $date;
    }

    /**
     * Ajouter un nouveau suivi santé
     */
    public function addSuivi(array $data): bool
    {
        if ($this->model->existsForUserDateAndActivity((int)$data['user_id'], $data['date'], $data['type_activite'])) {
            throw new RuntimeException('Vous avez déjà enregistré ce type d\'activité pour cette date.');
        }

        return $this->model->add($data);
    }

    /**
     * Modifier un suivi santé
     */
    public function updateSuivi(int $id, array $data): bool
    {
        return $this->model->update($id, $data);
    }

    /**
     * Supprimer un suivi santé
     */
    public function deleteSuivi(int $id): bool
    {
        return $this->model->delete($id);
    }

    /**
     * Récupérer tous les suivis (admin)
     */
    public function getAllSuivis(): array
    {
        return $this->model->getAll();
    }

    /**
     * Récupérer les suivis d'un utilisateur
     */
    public function getSuivisByUser(int $userId, string $order = 'DESC'): array
    {
        return $this->model->getAllByUser($userId, $order);
    }

    /**
     * Récupérer un suivi par ID
     */
    public function getSuivi(int $id): ?array
    {
        return $this->model->getById($id);
    }

    /**
     * Récupérer un suivi santé par ID
     */
    public function getSuiviById(int $id): ?array
    {
        return $this->model->getById($id);
    }

    /**
     * Récupérer les suivis du jour d'un utilisateur
     */
    public function getDailySuivis(int $userId, string $date): array
    {
        return $this->model->getByUserAndDate($userId, $date);
    }

    /**
     * Récupérer les calories totales brûlées pour un jour
     */
    public function getDailyCaloriesBurned(int $userId, string $date): float
    {
        return $this->model->getTotalCaloriesBurnedByUserAndDate($userId, $date);
    }

    /**
     * Récupérer la quantité d'eau totale pour un jour
     */
    public function getDailyWaterIntake(int $userId, string $date): float
    {
        return $this->model->getTotalWaterByUserAndDate($userId, $date);
    }

    /**
     * Récupérer la durée totale d'activité pour un jour
     */
    public function getDailyActivityDuration(int $userId, string $date): int
    {
        return $this->model->getTotalActivityDurationByUserAndDate($userId, $date);
    }

    /**
     * Récupérer les statistiques globales
     */
    public function getGlobalStats(): array
    {
        return $this->model->getGlobalStats();
    }

    /**
     * Récupérer les statistiques par type d'activité
     */
    public function getStatsByActivityType(): array
    {
        return $this->model->getStatsByActivityType();
    }

    /**
     * Récupérer les statistiques par intensité
     */
    public function getStatsByIntensity(): array
    {
        return $this->model->getStatsByIntensity();
    }

    /**
     * Récupérer les activités les plus fréquentes
     */
    public function getMostFrequentActivities(int $limit = 5): array
    {
        return $this->model->getMostFrequentActivities($limit);
    }

    /**
     * Compter les suivis santé
     */
    public function countSuivis(): int
    {
        return $this->model->count();
    }

    /**
     * Compter les utilisateurs actifs
     */
    public function countActiveUsers(): int
    {
        return $this->model->countActiveUsers();
    }

    /**
     * Formater un rapport combiné Jour (calories consommées vs brûlées)
     */
    public function getDailyHealthReport(int $userId, string $date, $journalController = null): array
    {
        $suivis = $this->getDailySuivis($userId, $date);
        $totalCaloriesBurned = $this->getDailyCaloriesBurned($userId, $date);
        $totalWater = $this->getDailyWaterIntake($userId, $date);
        $totalActivityDuration = $this->getDailyActivityDuration($userId, $date);

        $report = [
            'date' => $date,
            'suivis' => $suivis,
            'total_calories_burned' => round($totalCaloriesBurned, 2),
            'total_water' => round($totalWater, 2),
            'total_activity_duration' => $totalActivityDuration,
            'suivis_count' => count($suivis),
        ];

        // Intégration avec Journal si le controller est fourni
        if ($journalController) {
            $report['calories_consumed'] = $journalController->getDailyCalories($userId, $date);
            $report['balance'] = $report['calories_consumed'] - $report['total_calories_burned'];
        }

        return $report;
    }

    /**
     * Vérifier si l'utilisateur a accès au suivi
     */
    public function userHasAccessToSuivi(int $userId, int $suiviId): bool
    {
        $suivi = $this->model->getById($suiviId);
        return $suivi !== null && (int)$suivi['user_id'] === $userId;
    }
}
