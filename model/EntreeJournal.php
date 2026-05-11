<?php
declare(strict_types=1);

class EntreeJournal
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function create(array $data): bool
    {
        $sql = 'INSERT INTO entrees_journal
            (user_id, date, time, meal_type, food, quantity, calories, proteins, carbs, fats, note)
            VALUES (:user_id, :date, :time, :meal_type, :food, :quantity, :calories, :proteins, :carbs, :fats, :note)';

        $statement = $this->pdo->prepare($sql);
        return $statement->execute([
            ':user_id' => $data['user_id'],
            ':date' => $data['date'],
            ':time' => $data['time'] ?: null,
            ':meal_type' => $data['meal_type'],
            ':food' => $data['food'],
            ':quantity' => $data['quantity'],
            ':calories' => $data['calories'],
            ':proteins' => $data['proteins'],
            ':carbs' => $data['carbs'],
            ':fats' => $data['fats'],
            ':note' => $data['note'],
        ]);
    }

    public function update(int $id, array $data): bool
    {
        $sql = 'UPDATE entrees_journal SET
            date = :date,
            time = :time,
            meal_type = :meal_type,
            food = :food,
            quantity = :quantity,
            calories = :calories,
            proteins = :proteins,
            carbs = :carbs,
            fats = :fats,
            note = :note,
            updated_at = NOW()
            WHERE id = :id';

        $statement = $this->pdo->prepare($sql);
        return $statement->execute([
            ':id' => $id,
            ':date' => $data['date'],
            ':time' => $data['time'] ?: null,
            ':meal_type' => $data['meal_type'],
            ':food' => $data['food'],
            ':quantity' => $data['quantity'],
            ':calories' => $data['calories'],
            ':proteins' => $data['proteins'],
            ':carbs' => $data['carbs'],
            ':fats' => $data['fats'],
            ':note' => $data['note'],
        ]);
    }

    public function delete(int $id): bool
    {
        $sql = 'DELETE FROM entrees_journal WHERE id = :id';
        $statement = $this->pdo->prepare($sql);
        return $statement->execute([':id' => $id]);
    }

    public function findById(int $id): ?array
    {
        $sql = 'SELECT * FROM entrees_journal WHERE id = :id';
        $statement = $this->pdo->prepare($sql);
        $statement->execute([':id' => $id]);
<<<<<<< HEAD
        $entry = $statement->fetch(PDO::FETCH_ASSOC);
=======
        $entry = $statement->fetch();
>>>>>>> 7601b90fdab6bf6325a2b078d25608a292b8ddc1

        return $entry === false ? null : $entry;
    }

    public function findAllByUser(int $userId, string $order = 'DESC'): array
    {
        $order = strtoupper($order) === 'ASC' ? 'ASC' : 'DESC';
        $timeOrder = $order === 'ASC' ? 'ASC' : 'DESC';

        $sql = "SELECT * FROM entrees_journal WHERE user_id = :user_id ORDER BY date {$order}, time {$timeOrder}";
        $statement = $this->pdo->prepare($sql);
        $statement->execute([':user_id' => $userId]);
<<<<<<< HEAD
        return $statement->fetchAll(PDO::FETCH_ASSOC);
=======
        return $statement->fetchAll();
>>>>>>> 7601b90fdab6bf6325a2b078d25608a292b8ddc1
    }

    public function findAll(): array
    {
        $sql = 'SELECT * FROM entrees_journal ORDER BY date DESC, time DESC';
<<<<<<< HEAD
        return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
=======
        return $this->pdo->query($sql)->fetchAll();
>>>>>>> 7601b90fdab6bf6325a2b078d25608a292b8ddc1
    }

    public function findAllByUserAndDate(int $userId, string $date): array
    {
        $sql = 'SELECT * FROM entrees_journal WHERE user_id = :user_id AND date = :date ORDER BY time ASC';
        $statement = $this->pdo->prepare($sql);
        $statement->execute([':user_id' => $userId, ':date' => $date]);
<<<<<<< HEAD
        return $statement->fetchAll(PDO::FETCH_ASSOC);
=======
        return $statement->fetchAll();
>>>>>>> 7601b90fdab6bf6325a2b078d25608a292b8ddc1
    }

    public function countEntries(): int
    {
        $sql = 'SELECT COUNT(*) FROM entrees_journal';
        return (int) $this->pdo->query($sql)->fetchColumn();
    }

    public function countUsers(): int
    {
        $sql = 'SELECT COUNT(DISTINCT user_id) FROM entrees_journal';
        return (int) $this->pdo->query($sql)->fetchColumn();
    }

    public function totalCaloriesByUser(int $userId, string $date): int
    {
        $sql = 'SELECT COALESCE(SUM(calories), 0) FROM entrees_journal WHERE user_id = :user_id AND date = :date';
        $statement = $this->pdo->prepare($sql);
        $statement->execute([':user_id' => $userId, ':date' => $date]);
        return (int) $statement->fetchColumn();
    }

    public function totalCaloriesAll(string $date): int
    {
        $sql = 'SELECT COALESCE(SUM(calories), 0) FROM entrees_journal WHERE date = :date';
        $statement = $this->pdo->prepare($sql);
        $statement->execute([':date' => $date]);
        return (int) $statement->fetchColumn();
    }

    public function getMacroTotals(int $userId, string $date): array
    {
        $sql = 'SELECT
            COALESCE(SUM(proteins), 0) AS proteins,
            COALESCE(SUM(carbs), 0) AS carbs,
            COALESCE(SUM(fats), 0) AS fats
            FROM entrees_journal
            WHERE user_id = :user_id AND date = :date';

        $statement = $this->pdo->prepare($sql);
        $statement->execute([':user_id' => $userId, ':date' => $date]);
<<<<<<< HEAD
        $result = $statement->fetch(PDO::FETCH_ASSOC);

        return [
            'proteins' => (float) ($result['proteins'] ?? 0),
            'carbs'    => (float) ($result['carbs']    ?? 0),
            'fats'     => (float) ($result['fats']     ?? 0),
=======
        $result = $statement->fetch();

        return [
            'proteins' => (float) $result['proteins'],
            'carbs' => (float) $result['carbs'],
            'fats' => (float) $result['fats'],
>>>>>>> 7601b90fdab6bf6325a2b078d25608a292b8ddc1
        ];
    }

    public function getEntryDatesForUser(int $userId): array
    {
        $sql = 'SELECT DISTINCT date FROM entrees_journal WHERE user_id = :user_id ORDER BY date DESC';
        $statement = $this->pdo->prepare($sql);
        $statement->execute([':user_id' => $userId]);
<<<<<<< HEAD
        return array_column($statement->fetchAll(PDO::FETCH_ASSOC), 'date');
=======
        return array_column($statement->fetchAll(), 'date');
>>>>>>> 7601b90fdab6bf6325a2b078d25608a292b8ddc1
    }

    /**
     * Récupère le résumé nutritionnel des 7 derniers jours pour un utilisateur
     * Retourne un tableau de 7 entrées (une par jour), du plus ancien au plus récent
     */
    public function getLast7DaysSummary(int $userId): array
    {
        $sql = 'SELECT
            date,
            COALESCE(SUM(calories), 0)  AS calories,
            COALESCE(SUM(proteins), 0)  AS proteins,
            COALESCE(SUM(carbs), 0)     AS carbs,
            COALESCE(SUM(fats), 0)      AS fats,
            COUNT(*)                    AS meal_count
            FROM entrees_journal
            WHERE user_id = :user_id
              AND date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
              AND date < CURDATE()
            GROUP BY date
            ORDER BY date ASC';

        $statement = $this->pdo->prepare($sql);
        $statement->execute([':user_id' => $userId]);
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }
}
