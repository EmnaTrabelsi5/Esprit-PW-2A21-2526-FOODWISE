<?php
declare(strict_types=1);

class SuiviSante
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Ajouter un nouveau suivi santé
     */
    public function add(array $data): bool
    {
        $sql = 'INSERT INTO suivi_sante
            (user_id, date, type_activite, duree, calories_brulees, intensite, quantite_eau, note)
            VALUES (:user_id, :date, :type_activite, :duree, :calories_brulees, :intensite, :quantite_eau, :note)';

        $statement = $this->pdo->prepare($sql);
        return $statement->execute([
            ':user_id' => $data['user_id'],
            ':date' => $data['date'],
            ':type_activite' => $data['type_activite'],
            ':duree' => (int)$data['duree'],
            ':calories_brulees' => (float)$data['calories_brulees'],
            ':intensite' => $data['intensite'],
            ':quantite_eau' => (float)$data['quantite_eau'],
            ':note' => $data['note'] ?: null,
        ]);
    }

    /**
     * Modifier un suivi santé existant
     */
    public function update(int $id, array $data): bool
    {
        $sql = 'UPDATE suivi_sante SET
            date = :date,
            type_activite = :type_activite,
            duree = :duree,
            calories_brulees = :calories_brulees,
            intensite = :intensite,
            quantite_eau = :quantite_eau,
            note = :note,
            updated_at = NOW()
            WHERE id = :id';

        $statement = $this->pdo->prepare($sql);
        return $statement->execute([
            ':id' => $id,
            ':date' => $data['date'],
            ':type_activite' => $data['type_activite'],
            ':duree' => (int)$data['duree'],
            ':calories_brulees' => (float)$data['calories_brulees'],
            ':intensite' => $data['intensite'],
            ':quantite_eau' => (float)$data['quantite_eau'],
            ':note' => $data['note'] ?: null,
        ]);
    }

    /**
     * Supprimer un suivi santé
     */
    public function delete(int $id): bool
    {
        $sql = 'DELETE FROM suivi_sante WHERE id = :id';
        $statement = $this->pdo->prepare($sql);
        return $statement->execute([':id' => $id]);
    }

    /**
     * Récupérer tous les suivis santé (admin)
     */
    public function getAll(): array
    {
        $sql = 'SELECT * FROM suivi_sante ORDER BY date DESC, created_at DESC';
        return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupérer les suivis d'un utilisateur
     */
    public function getAllByUser(int $userId, string $order = 'DESC'): array
    {
        $order = strtoupper($order) === 'ASC' ? 'ASC' : 'DESC';
        $createdAtOrder = $order === 'ASC' ? 'ASC' : 'DESC';

        $sql = "SELECT * FROM suivi_sante WHERE user_id = :user_id ORDER BY date {$order}, created_at {$createdAtOrder}";
        $statement = $this->pdo->prepare($sql);
        $statement->execute([':user_id' => $userId]);
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupérer un suivi par ID
     */
    public function getById(int $id): ?array
    {
        $sql = 'SELECT * FROM suivi_sante WHERE id = :id';
        $statement = $this->pdo->prepare($sql);
        $statement->execute([':id' => $id]);
        $result = $statement->fetch(PDO::FETCH_ASSOC);
        return $result === false ? null : $result;
    }

    /**
     * Récupérer les suivis d'un utilisateur pour une date spécifique
     */
    public function getByUserAndDate(int $userId, string $date): array
    {
        $sql = 'SELECT * FROM suivi_sante WHERE user_id = :user_id AND date = :date ORDER BY created_at ASC';
        $statement = $this->pdo->prepare($sql);
        $statement->execute([':user_id' => $userId, ':date' => $date]);
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupérer les calories brûlées totales pour un utilisateur et une date
     */
    public function getTotalCaloriesBurnedByUserAndDate(int $userId, string $date): float
    {
        $sql = 'SELECT COALESCE(SUM(calories_brulees), 0) FROM suivi_sante 
                WHERE user_id = :user_id AND date = :date';
        $statement = $this->pdo->prepare($sql);
        $statement->execute([':user_id' => $userId, ':date' => $date]);
        return (float)$statement->fetchColumn();
    }

    /**
     * Récupérer la quantité d'eau totale pour un utilisateur et une date
     */
    public function getTotalWaterByUserAndDate(int $userId, string $date): float
    {
        $sql = 'SELECT COALESCE(SUM(quantite_eau), 0) FROM suivi_sante 
                WHERE user_id = :user_id AND date = :date AND quantite_eau > 0';
        $statement = $this->pdo->prepare($sql);
        $statement->execute([':user_id' => $userId, ':date' => $date]);
        return (float)$statement->fetchColumn();
    }

    /**
     * Récupérer la durée totale d'activité pour un utilisateur et une date
     */
    public function getTotalActivityDurationByUserAndDate(int $userId, string $date): int
    {
        $sql = 'SELECT COALESCE(SUM(duree), 0) FROM suivi_sante 
                WHERE user_id = :user_id AND date = :date';
        $statement = $this->pdo->prepare($sql);
        $statement->execute([':user_id' => $userId, ':date' => $date]);
        return (int)$statement->fetchColumn();
    }

    /**
     * Récupérer les stats globales (admin)
     */
    public function getGlobalStats(): array
    {
        $sql = 'SELECT
            COUNT(DISTINCT id) as total_followups,
            COUNT(DISTINCT user_id) as total_users,
            COALESCE(SUM(duree), 0) as total_duration,
            COALESCE(SUM(calories_brulees), 0) as total_calories_burned,
            COALESCE(SUM(quantite_eau), 0) as total_water,
            COUNT(DISTINCT DATE(date)) as days_tracked
            FROM suivi_sante';

        $result = $this->pdo->query($sql)->fetch(PDO::FETCH_ASSOC);
        return $result ?: [
            'total_followups' => 0, 'total_users' => 0, 'total_duration' => 0,
            'total_calories_burned' => 0, 'total_water' => 0, 'days_tracked' => 0,
        ];
    }

    /**
     * Récupérer les stats par type d'activité
     */
    public function getStatsByActivityType(): array
    {
        $sql = 'SELECT type_activite, COUNT(*) as count,
            COALESCE(SUM(duree), 0) as total_duration,
            COALESCE(SUM(calories_brulees), 0) as total_calories,
            ROUND(AVG(calories_brulees), 2) as avg_calories
            FROM suivi_sante GROUP BY type_activite ORDER BY total_calories DESC';
        return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupérer les stats par intensité
     */
    public function getStatsByIntensity(): array
    {
        $sql = 'SELECT intensite, COUNT(*) as count,
            COALESCE(AVG(duree), 0) as avg_duration,
            COALESCE(AVG(calories_brulees), 0) as avg_calories
            FROM suivi_sante GROUP BY intensite
            ORDER BY FIELD(intensite, "élevé", "moyen", "faible")';
        return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Compter les suivis santé
     */
    public function count(): int
    {
        $sql = 'SELECT COUNT(*) FROM suivi_sante';
        return (int)$this->pdo->query($sql)->fetchColumn();
    }

    /**
     * Compter les utilisateurs actifs
     */
    public function countActiveUsers(): int
    {
        $sql = 'SELECT COUNT(DISTINCT user_id) FROM suivi_sante';
        return (int)$this->pdo->query($sql)->fetchColumn();
    }

    /**
     * Vérifier si un suivi existe déjà pour un utilisateur et une date avec le même type d'activité
     */
    public function existsForUserDateAndActivity(int $userId, string $date, string $typeActivite, ?int $excludeId = null): bool
    {
        $sql = 'SELECT COUNT(*) FROM suivi_sante 
                WHERE user_id = :user_id AND date = :date AND type_activite = :type_activite';
        
        if ($excludeId) {
            $sql .= ' AND id != :exclude_id';
        }

        $statement = $this->pdo->prepare($sql);
        $params = [
            ':user_id' => $userId,
            ':date' => $date,
            ':type_activite' => $typeActivite,
        ];

        if ($excludeId) {
            $params[':exclude_id'] = $excludeId;
        }

        $statement->execute($params);
        return (int)$statement->fetchColumn() > 0;
    }

    /**
     * Obtenir les activités les plus fréquentes
     */
    public function getMostFrequentActivities(int $limit = 5): array
    {
        $sql = 'SELECT type_activite, COUNT(*) as frequency,
            ROUND(AVG(calories_brulees), 2) as avg_calories_burned,
            ROUND(AVG(duree), 0) as avg_duration
            FROM suivi_sante GROUP BY type_activite
            ORDER BY frequency DESC LIMIT :limit';
        $statement = $this->pdo->prepare($sql);
        $statement->bindValue(':limit', $limit, PDO::PARAM_INT);
        $statement->execute();
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }
}
