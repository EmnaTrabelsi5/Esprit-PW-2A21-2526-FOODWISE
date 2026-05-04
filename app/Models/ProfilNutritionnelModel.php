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
            'SELECT p.*, u.id as utilisateur_id, u.nom, u.prenom, u.email, u.status, u.suspended_until, u.ban_reason, u.photo_profil
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
            ':show_weight' => isset($data['show_weight']) ? 1 : 0,
            ':show_height' => isset($data['show_height']) ? 1 : 0,
            ':show_diet' => isset($data['show_diet']) ? 1 : 0,
            ':show_allergies' => isset($data['show_allergies']) ? 1 : 0,
            ':show_goal' => isset($data['show_goal']) ? 1 : 0,
            ':updated_at' => date('Y-m-d H:i:s'),
        ];

        if ($profile === null) {
            $stmt = $this->pdo->prepare(
                'INSERT INTO profils_nutritionnels (utilisateur_id, poids_kg, taille_cm, objectif, allergies, regimes, intolerances, score_completion, show_weight, show_height, show_diet, show_allergies, show_goal, updated_at)
                 VALUES (:utilisateur_id, :poids_kg, :taille_cm, :objectif, :allergies, :regimes, :intolerances, :score_completion, :show_weight, :show_height, :show_diet, :show_allergies, :show_goal, :updated_at)'
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
                     show_weight = :show_weight,
                     show_height = :show_height,
                     show_diet = :show_diet,
                     show_allergies = :show_allergies,
                     show_goal = :show_goal,
                     updated_at = :updated_at
                 WHERE utilisateur_id = :utilisateur_id'
            );
        }

        $stmt->execute($payload);
    }

    /**
     * Retourne un profil en respectant les paramètres de confidentialité
     */
    public function getPublicProfile(int $userId): ?array
    {
        $profile = $this->findByUserId($userId);
        if ($profile === null) {
            return null;
        }

        // Respecter les paramètres de confidentialité
        if (!($profile['show_weight'] ?? 0)) {
            $profile['poids_kg'] = null;
        }
        if (!($profile['show_height'] ?? 0)) {
            $profile['taille_cm'] = null;
        }
        if (!($profile['show_diet'] ?? 0)) {
            $profile['regimes'] = null;
            $profile['intolerances'] = null;
        }
        if (!($profile['show_allergies'] ?? 0)) {
            $profile['allergies'] = null;
        }
        if (!($profile['show_goal'] ?? 0)) {
            $profile['objectif'] = null;
        }

        return $profile;
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
            // Pour allergies, regimes, intolerances, vide est considéré comme complet (aucun)
            if (in_array($field, ['allergies', 'regimes', 'intolerances'])) {
                $count += 1;
            } elseif (isset($data[$field]) && trim((string) $data[$field]) !== '') {
                $count += 1;
            }
        }

        return (int) round(100 * ($count / count($fields)));
    }
}
