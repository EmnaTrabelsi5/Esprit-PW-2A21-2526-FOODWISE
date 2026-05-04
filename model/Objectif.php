<?php

declare(strict_types=1);

namespace Model;

use Config\Database;

final class Objectif
{
    private ?bool $hasPlanIdObjColumn = null;

    public function all(): array
    {
        $sql = 'SELECT * FROM objectif ORDER BY id_obj DESC';
        $stmt = Database::getConnection()->query($sql);
        return $stmt->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = Database::getConnection()->prepare('SELECT * FROM objectif WHERE id_obj = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row !== false ? $row : null;
    }

    /** @param array{type: string, calories_cible: int, macros: string} $data */
    public function insert(array $data): int
    {
        $sql = 'INSERT INTO objectif (type, calories_cible, macros) VALUES (:type, :calories_cible, :macros)';
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'type' => $data['type'],
            'calories_cible' => $data['calories_cible'],
            'macros' => $data['macros'],
        ]);
        return (int) $pdo->lastInsertId();
    }

    /** @param array{type: string, calories_cible: int, macros: string} $data */
    public function update(int $id, array $data): bool
    {
        $sql = 'UPDATE objectif SET type = :type, calories_cible = :calories_cible, macros = :macros WHERE id_obj = :id';
        $stmt = Database::getConnection()->prepare($sql);
        return $stmt->execute([
            'id' => $id,
            'type' => $data['type'],
            'calories_cible' => $data['calories_cible'],
            'macros' => $data['macros'],
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = Database::getConnection()->prepare('DELETE FROM objectif WHERE id_obj = :id');
        return $stmt->execute(['id' => $id]);
    }

    public function linkedPlans(int $objectifId): array
    {
        if (!$this->hasPlanIdObjColumn()) {
            return [];
        }
        $stmt = Database::getConnection()->prepare(
            'SELECT id, titre, date_debut, date_fin, statut, calories_cible
             FROM plans_alimentaires
             WHERE id_obj = :id_obj
             ORDER BY date_debut DESC, id DESC'
        );
        $stmt->execute(['id_obj' => $objectifId]);
        return $stmt->fetchAll();
    }

    public function compareNutritionFromRecetteTable(int $objectifId): array
    {
        if (!$this->hasPlanIdObjColumn()) {
            return ['calories' => 0, 'proteines' => 0.0, 'glucides' => 0.0, 'lipides' => 0.0];
        }
        $pdo = Database::getConnection();
        $recetteTable = $this->resolveRecetteTableName();
        if ($recetteTable === null) {
            return ['calories' => 0, 'proteines' => 0.0, 'glucides' => 0.0, 'lipides' => 0.0];
        }

        $columns = $this->resolveRecetteColumns($recetteTable);
        if ($columns['name'] === null) {
            return ['calories' => 0, 'proteines' => 0.0, 'glucides' => 0.0, 'lipides' => 0.0];
        }

        $calExpr = $columns['calories'] !== null ? 'COALESCE(SUM(r.`' . $columns['calories'] . '`), 0)' : '0';
        $proExpr = $columns['proteines'] !== null ? 'COALESCE(SUM(r.`' . $columns['proteines'] . '`), 0)' : '0';
        $gluExpr = $columns['glucides'] !== null ? 'COALESCE(SUM(r.`' . $columns['glucides'] . '`), 0)' : '0';
        $lipExpr = $columns['lipides'] !== null ? 'COALESCE(SUM(r.`' . $columns['lipides'] . '`), 0)' : '0';

        $sql = 'SELECT ' . $calExpr . ' AS calories_total,
                       ' . $proExpr . ' AS proteines_total,
                       ' . $gluExpr . ' AS glucides_total,
                       ' . $lipExpr . ' AS lipides_total
                FROM plans_alimentaires pa
                INNER JOIN plans_recettes pr ON pr.plan_alimentaire_id = pa.id
                LEFT JOIN `' . $recetteTable . '` r ON r.`' . $columns['name'] . '` = pr.nom_recette
                WHERE pa.id_obj = :id_obj';
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id_obj' => $objectifId]);
        $row = $stmt->fetch();
        if ($row === false) {
            return ['calories' => 0, 'proteines' => 0.0, 'glucides' => 0.0, 'lipides' => 0.0];
        }

        return [
            'calories' => (int) round((float) $row['calories_total']),
            'proteines' => round((float) $row['proteines_total'], 2),
            'glucides' => round((float) $row['glucides_total'], 2),
            'lipides' => round((float) $row['lipides_total'], 2),
        ];
    }

    private function resolveRecetteTableName(): ?string
    {
        $sql = 'SELECT TABLE_NAME
                FROM information_schema.TABLES
                WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME IN (\'recette\', \'recettes\')
                ORDER BY FIELD(TABLE_NAME, \'recette\', \'recettes\')
                LIMIT 1';
        $row = Database::getConnection()->query($sql)->fetch();
        if ($row === false || !isset($row['TABLE_NAME'])) {
            return null;
        }
        return (string) $row['TABLE_NAME'];
    }

    /** @return array{name: ?string, calories: ?string, proteines: ?string, glucides: ?string, lipides: ?string} */
    private function resolveRecetteColumns(string $table): array
    {
        $stmt = Database::getConnection()->prepare(
            'SELECT COLUMN_NAME
             FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table_name'
        );
        $stmt->execute(['table_name' => $table]);
        $rows = $stmt->fetchAll();
        $available = [];
        foreach ($rows as $r) {
            $available[] = strtolower((string) $r['COLUMN_NAME']);
        }

        $pick = static function (array $candidates) use ($available): ?string {
            foreach ($candidates as $candidate) {
                if (in_array($candidate, $available, true)) {
                    return $candidate;
                }
            }
            return null;
        };

        return [
            'name' => $pick(['nom_recette', 'nom', 'titre', 'name']),
            'calories' => $pick(['calories', 'kcal', 'calorie']),
            'proteines' => $pick(['proteines', 'proteines_g', 'protein', 'proteins']),
            'glucides' => $pick(['glucides', 'glucides_g', 'carbs']),
            'lipides' => $pick(['lipides', 'lipides_g', 'fats']),
        ];
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
