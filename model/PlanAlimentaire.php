<?php

declare(strict_types=1);

namespace Model;

use Config\Database;

final class PlanAlimentaire
{
    private ?bool $hasObjectifTable = null;
    private ?bool $hasPlanIdObjColumn = null;

    public function all(): array
    {
        $sql = ($this->hasObjectifTable() && $this->hasPlanIdObjColumn())
            ? 'SELECT pa.*, o.type AS objectif_type FROM plans_alimentaires pa
               LEFT JOIN objectif o ON o.id_obj = pa.id_obj
               ORDER BY pa.date_debut DESC, pa.id DESC'
            : 'SELECT pa.*, NULL AS objectif_type FROM plans_alimentaires pa
               ORDER BY pa.date_debut DESC, pa.id DESC';
        $stmt = Database::getConnection()->query($sql);
        return $stmt->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = Database::getConnection()->prepare(
            ($this->hasObjectifTable() && $this->hasPlanIdObjColumn())
                ? 'SELECT pa.*, o.type AS objectif_type FROM plans_alimentaires pa
                   LEFT JOIN objectif o ON o.id_obj = pa.id_obj
                   WHERE pa.id = :id LIMIT 1'
                : 'SELECT pa.*, NULL AS objectif_type FROM plans_alimentaires pa
                   WHERE pa.id = :id LIMIT 1'
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row !== false ? $row : null;
    }

    /** @param array{titre: string, description: ?string, date_debut: string, date_fin: string, calories_cible: ?int, statut: string, id_obj: ?int} $data */
    public function insert(array $data): int
    {
        if ($this->hasPlanIdObjColumn()) {
            $sql = 'INSERT INTO plans_alimentaires (id_obj, titre, description, date_debut, date_fin, calories_cible, statut)
                    VALUES (:id_obj, :titre, :description, :date_debut, :date_fin, :calories_cible, :statut)';
            $params = [
                'id_obj' => $data['id_obj'],
                'titre' => $data['titre'],
                'description' => $data['description'],
                'date_debut' => $data['date_debut'],
                'date_fin' => $data['date_fin'],
                'calories_cible' => $data['calories_cible'],
                'statut' => $data['statut'],
            ];
        } else {
            $sql = 'INSERT INTO plans_alimentaires (titre, description, date_debut, date_fin, calories_cible, statut)
                    VALUES (:titre, :description, :date_debut, :date_fin, :calories_cible, :statut)';
            $params = [
                'titre' => $data['titre'],
                'description' => $data['description'],
                'date_debut' => $data['date_debut'],
                'date_fin' => $data['date_fin'],
                'calories_cible' => $data['calories_cible'],
                'statut' => $data['statut'],
            ];
        }
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return (int) $pdo->lastInsertId();
    }

    /** @param array{titre: string, description: ?string, date_debut: string, date_fin: string, calories_cible: ?int, statut: string, id_obj: ?int} $data */
    public function update(int $id, array $data): bool
    {
        if ($this->hasPlanIdObjColumn()) {
            $sql = 'UPDATE plans_alimentaires SET id_obj = :id_obj, titre = :titre, description = :description, date_debut = :date_debut,
                    date_fin = :date_fin, calories_cible = :calories_cible, statut = :statut WHERE id = :id';
            $params = [
                'id' => $id,
                'id_obj' => $data['id_obj'],
                'titre' => $data['titre'],
                'description' => $data['description'],
                'date_debut' => $data['date_debut'],
                'date_fin' => $data['date_fin'],
                'calories_cible' => $data['calories_cible'],
                'statut' => $data['statut'],
            ];
        } else {
            $sql = 'UPDATE plans_alimentaires SET titre = :titre, description = :description, date_debut = :date_debut,
                    date_fin = :date_fin, calories_cible = :calories_cible, statut = :statut WHERE id = :id';
            $params = [
                'id' => $id,
                'titre' => $data['titre'],
                'description' => $data['description'],
                'date_debut' => $data['date_debut'],
                'date_fin' => $data['date_fin'],
                'calories_cible' => $data['calories_cible'],
                'statut' => $data['statut'],
            ];
        }
        $stmt = Database::getConnection()->prepare($sql);
        return $stmt->execute($params);
    }

    public function delete(int $id): bool
    {
        $stmt = Database::getConnection()->prepare('DELETE FROM plans_alimentaires WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }

    private function hasObjectifTable(): bool
    {
        if ($this->hasObjectifTable !== null) {
            return $this->hasObjectifTable;
        }
        $stmt = Database::getConnection()->query(
            "SELECT COUNT(*) AS c
             FROM information_schema.TABLES
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'objectif'"
        );
        $row = $stmt->fetch();
        $this->hasObjectifTable = ((int) ($row['c'] ?? 0)) > 0;
        return $this->hasObjectifTable;
    }

    private function hasPlanIdObjColumn(): bool
    {
        if ($this->hasPlanIdObjColumn !== null) {
            return $this->hasPlanIdObjColumn;
        }
        $stmt = Database::getConnection()->query(
            "SELECT COUNT(*) AS c
             FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = 'plans_alimentaires'
               AND COLUMN_NAME = 'id_obj'"
        );
        $row = $stmt->fetch();
        $this->hasPlanIdObjColumn = ((int) ($row['c'] ?? 0)) > 0;
        return $this->hasPlanIdObjColumn;
    }
}
