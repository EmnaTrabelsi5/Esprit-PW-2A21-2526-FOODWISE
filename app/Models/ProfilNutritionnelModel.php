<?php
declare(strict_types=1);

class ProfilNutritionnelModel
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function findByUserId(int $userId): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM profils_nutritionnels WHERE utilisateur_id = :utilisateur_id');
        $stmt->execute([':utilisateur_id' => $userId]);
        $profile = $stmt->fetch();
        return $profile === false ? null : $profile;
    }

    public function findAll(): array
    {
        $stmt = $this->pdo->query(
            'SELECT p.*, u.nom, u.prenom, u.email
             FROM profils_nutritionnels p
             JOIN utilisateurs u ON u.id = p.utilisateur_id
             ORDER BY u.nom ASC'
        );
        return $stmt->fetchAll();
    }

    public function existsForUser(int $userId): bool
    {
        return $this->findByUserId($userId) !== null;
    }

    public function save(int $userId, array $data): void
    {
        $profile = $this->findByUserId($userId);
        $scoreCompletion = $this->computeCompletionScore($data);
        $payload = [
            ':utilisateur_id' => $userId,
            ':poids_kg' => $data['poids_kg'],
            ':taille_cm' => $data['taille_cm'],
            ':objectif' => $data['objectif'],
            ':allergies' => $data['allergies'],
            ':regimes' => $data['regimes'],
            ':intolerances' => $data['intolerances'],
            ':score_completion' => $scoreCompletion,
            ':updated_at' => date('Y-m-d H:i:s'),
        ];

        if ($profile === null) {
            $stmt = $this->pdo->prepare(
                'INSERT INTO profils_nutritionnels (utilisateur_id, poids_kg, taille_cm, objectif, allergies, regimes, intolerances, score_completion, updated_at)
                 VALUES (:utilisateur_id, :poids_kg, :taille_cm, :objectif, :allergies, :regimes, :intolerances, :score_completion, :updated_at)'
            );
        } else {
            $stmt = $this->pdo->prepare(
                'UPDATE profils_nutritionnels
                 SET poids_kg = :poids_kg,
                     taille_cm = :taille_cm,
                     objectif = :objectif,
                     allergies = :allergies,
                     regimes = :regimes,
                     intolerances = :intolerances,
                     score_completion = :score_completion,
                     updated_at = :updated_at
                 WHERE utilisateur_id = :utilisateur_id'
            );
        }

        $stmt->execute($payload);
    }

    public function deleteByUserId(int $userId): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM profils_nutritionnels WHERE utilisateur_id = :utilisateur_id');
        $stmt->execute([':utilisateur_id' => $userId]);
    }

    private function computeCompletionScore(array $data): int
    {
        $count = 0;
        $fields = ['poids_kg', 'taille_cm', 'objectif', 'allergies', 'regimes', 'intolerances'];
        foreach ($fields as $field) {
            if (isset($data[$field]) && trim((string) $data[$field]) !== '') {
                $count += 1;
            }
        }

        return (int) round(100 * ($count / count($fields)));
    }
}
