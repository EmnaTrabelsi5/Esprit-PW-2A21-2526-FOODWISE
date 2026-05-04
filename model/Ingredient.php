<?php

declare(strict_types=1);

namespace Model;

use Config\Database;

/**
 * Modèle pour les ingrédients des recettes importées.
 */
final class Ingredient
{
    public function forRecette(int $recetteId): array
    {
        $sql = 'SELECT * FROM ingredients WHERE recette_id = :rid';
        $stmt = Database::getConnection()->prepare($sql);
        $stmt->execute(['rid' => $recetteId]);
        return $stmt->fetchAll();
    }

    /** @param array{recette_id: int, nom: string, quantite: ?string, unite: ?string} $data */
    public function insert(array $data): int
    {
        $sql = 'INSERT INTO ingredients (recette_id, nom, quantite, unite) 
                VALUES (:recette_id, :nom, :quantite, :unite)';
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare($sql);
        $stmt->execute($data);
        return (int) $pdo->lastInsertId();
    }
}
