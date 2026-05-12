<?php
require_once __DIR__ . '/../config/config.php';

class Favori
{
    /* Ajouter un favori — retourne false si déjà existant */
    public static function add(int $userId, int $recetteId): bool
    {
        $db = config::getConnexion();
        try {
            $stmt = $db->prepare(
                'INSERT INTO favori (utilisateur_id, id_recette) VALUES (?, ?)'
            );
            return $stmt->execute([$userId, $recetteId]);
        } catch (PDOException $e) {
            return false; /* Violation UNIQUE KEY = déjà en favori */
        }
    }

    /* Retirer un favori */
    public static function remove(int $userId, int $recetteId): bool
    {
        $db   = config::getConnexion();
        $stmt = $db->prepare(
            'DELETE FROM favori WHERE utilisateur_id = ? AND id_recette = ?'
        );
        $stmt->execute([$userId, $recetteId]);
        return $stmt->rowCount() > 0;
    }

    /* Toggle : ajoute si absent, retire si présent */
    public static function toggle(int $userId, int $recetteId): string
    {
        if (self::isFavori($userId, $recetteId)) {
            self::remove($userId, $recetteId);
            return 'removed';
        } else {
            self::add($userId, $recetteId);
            return 'added';
        }
    }

    /* Vérifier si une recette est en favori */
    public static function isFavori(int $userId, int $recetteId): bool
    {
        $db   = config::getConnexion();
        $stmt = $db->prepare(
            'SELECT COUNT(*) FROM favori WHERE utilisateur_id = ? AND id_recette = ?'
        );
        $stmt->execute([$userId, $recetteId]);
        return (int)$stmt->fetchColumn() > 0;
    }

    /* Récupérer tous les IDs de recettes favorites d'un utilisateur */
    public static function getIdsByUser(int $userId): array
    {
        $db   = config::getConnexion();
        $stmt = $db->prepare(
            'SELECT id_recette FROM favori WHERE utilisateur_id = ?'
        );
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /* Récupérer les recettes favorites complètes d'un utilisateur */
    public static function getRecettesByUser(int $userId): array
    {
        $db   = config::getConnexion();
        $stmt = $db->prepare(
            'SELECT r.* FROM recette r
             JOIN favori f ON f.id_recette = r.id_recette
             WHERE f.utilisateur_id = ?
             ORDER BY f.created_at DESC'
        );
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
}
