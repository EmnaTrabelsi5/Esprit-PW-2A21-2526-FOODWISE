<?php
declare(strict_types=1);

require_once __DIR__ . '/../model/EntreeJournal.php';

class JournalController
{
    private EntreeJournal $model;

    public function __construct(PDO $pdo)
    {
        $this->model = new EntreeJournal($pdo);
    }

    public function validateEntryData(array $data): array
    {
        $errors = [];
        $mealTypes = ['Petit-déjeuner', 'Déjeuner', 'Collation', 'Dîner'];

        if (empty($data['date'])) {
            $errors[] = 'La date est requise.';
        }

        if (!in_array($data['meal_type'], $mealTypes, true)) {
            $errors[] = 'Le type de repas est invalide.';
        }

        if (empty(trim($data['food']))) {
            $errors[] = 'Le champ aliment est requis.';
        }

        if (empty(trim($data['quantity']))) {
            $errors[] = 'La quantité est requise.';
        }

        if (!is_numeric($data['calories']) || (int) $data['calories'] < 0) {
            $errors[] = 'Les calories doivent être un nombre positif.';
        }

        foreach (['proteins', 'carbs', 'fats'] as $macro) {
            if (!is_numeric($data[$macro]) || (float) $data[$macro] < 0) {
                $errors[] = ucfirst($macro) . ' doit être un nombre positif ou 0.';
                break;
            }
        }

        return $errors;
    }

    public function createEntry(array $data): bool
    {
        return $this->model->create($data);
    }

    public function updateEntry(int $id, array $data): bool
    {
        return $this->model->update($id, $data);
    }

    public function deleteEntry(int $id): bool
    {
        return $this->model->delete($id);
    }

    public function getEntry(int $id): ?array
    {
        return $this->model->findById($id);
    }

    public function listEntriesForUser(int $userId): array
    {
        return $this->model->findAllByUser($userId);
    }

    public function listEntriesForAdmin(): array
    {
        return $this->model->findAll();
    }

    public function getDailyCalories(int $userId, string $date): int
    {
        return $this->model->totalCaloriesByUser($userId, $date);
    }

    public function getDailyCaloriesAll(string $date): int
    {
        return $this->model->totalCaloriesAll($date);
    }

    public function getMacroTotals(int $userId, string $date): array
    {
        return $this->model->getMacroTotals($userId, $date);
    }

    public function getEntryCount(int $userId, string $date): int
    {
        return count($this->model->findAllByUserAndDate($userId, $date));
    }

    public function getStreak(int $userId): int
    {
        $dates = $this->model->getEntryDatesForUser($userId);
        if (empty($dates)) {
            return 0;
        }

        $current = new DateTimeImmutable(date('Y-m-d'));
        $streak = 0;

        foreach ($dates as $dateString) {
            $date = DateTimeImmutable::createFromFormat('Y-m-d', $dateString);
            if ($date === false) {
                continue;
            }

            if ($date->format('Y-m-d') === $current->format('Y-m-d')) {
                $streak++;
                $current = $current->sub(new DateInterval('P1D'));
                continue;
            }

            if ($date->format('Y-m-d') < $current->format('Y-m-d')) {
                break;
            }
        }

        return $streak;
    }

    public function getGlobalSummary(): array
    {
        return [
            'entries' => $this->model->countEntries(),
            'users' => $this->model->countUsers(),
            'todayCalories' => $this->model->totalCaloriesAll(date('Y-m-d')),
        ];
    }
}
