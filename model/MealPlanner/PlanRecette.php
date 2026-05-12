<?php

declare(strict_types=1);

namespace Model;

use Config\Database;

final class PlanRecette
{
    public function all(): array
    {
        $sql = 'SELECT pr.*, pa.titre AS plan_titre FROM plans_recettes pr
                INNER JOIN plans_alimentaires pa ON pa.id = pr.plan_alimentaire_id
                ORDER BY pr.plan_alimentaire_id, FIELD(pr.jour_semaine, \'lundi\',\'mardi\',\'mercredi\',\'jeudi\',\'vendredi\',\'samedi\',\'dimanche\'), pr.creneau, pr.id';
        $stmt = Database::getConnection()->query($sql);
        return $stmt->fetchAll();
    }

    public function forPlan(int $planId): array
    {
        $sql = 'SELECT * FROM plans_recettes WHERE plan_alimentaire_id = :pid
                ORDER BY FIELD(jour_semaine, \'lundi\',\'mardi\',\'mercredi\',\'jeudi\',\'vendredi\',\'samedi\',\'dimanche\'), creneau, id';
        $stmt = Database::getConnection()->prepare($sql);
        $stmt->execute(['pid' => $planId]);
        return $stmt->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = Database::getConnection()->prepare(
            'SELECT pr.*, pa.titre AS plan_titre FROM plans_recettes pr
             INNER JOIN plans_alimentaires pa ON pa.id = pr.plan_alimentaire_id
             WHERE pr.id = :id LIMIT 1'
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row !== false ? $row : null;
    }

    /** @param array{plan_alimentaire_id: int, jour_semaine: string, creneau: string, nom_recette: string, duree_minutes: ?int, notes: ?string} $data */
    public function insert(array $data): int
    {
        $sql = 'INSERT INTO plans_recettes (plan_alimentaire_id, jour_semaine, creneau, nom_recette, duree_minutes, notes)
                VALUES (:plan_alimentaire_id, :jour_semaine, :creneau, :nom_recette, :duree_minutes, :notes)';
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'plan_alimentaire_id' => $data['plan_alimentaire_id'],
            'jour_semaine' => $data['jour_semaine'],
            'creneau' => $data['creneau'],
            'nom_recette' => $data['nom_recette'],
            'duree_minutes' => $data['duree_minutes'],
            'notes' => $data['notes'],
        ]);
        return (int) $pdo->lastInsertId();
    }

    /** @param array{jour_semaine: string, creneau: string, nom_recette: string, duree_minutes: ?int, notes: ?string} $data */
    public function update(int $id, array $data): bool
    {
        $sql = 'UPDATE plans_recettes SET jour_semaine = :jour_semaine, creneau = :creneau, nom_recette = :nom_recette,
                duree_minutes = :duree_minutes, notes = :notes WHERE id = :id';
        $stmt = Database::getConnection()->prepare($sql);
        return $stmt->execute([
            'id' => $id,
            'jour_semaine' => $data['jour_semaine'],
            'creneau' => $data['creneau'],
            'nom_recette' => $data['nom_recette'],
            'duree_minutes' => $data['duree_minutes'],
            'notes' => $data['notes'],
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = Database::getConnection()->prepare('DELETE FROM plans_recettes WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }
}

