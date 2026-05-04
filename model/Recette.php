<?php

declare(strict_types=1);

namespace Model;

use Config\Database;

/**
 * Modèle pour les recettes importées de l'API.
 */
final class Recette
{
    public function all(): array
    {
        $sql = 'SELECT * FROM recettes ORDER BY id DESC';
        $stmt = Database::getConnection()->query($sql);
        return $stmt->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = Database::getConnection()->prepare('SELECT * FROM recettes WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row !== false ? $row : null;
    }

    /** @param array{nom: string, image_url: ?string, calories: float, proteines: float, glucides: float, lipides: float, source_api_id: ?string} $data */
    public function insert(array $data): int
    {
        $sql = 'INSERT INTO recettes (nom, image_url, calories, proteines, glucides, lipides, source_api_id) 
                VALUES (:nom, :image_url, :calories, :proteines, :glucides, :lipides, :source_api_id)';
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare($sql);
        $stmt->execute($data);
        return (int) $pdo->lastInsertId();
    }
}
