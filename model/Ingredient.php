<?php
/**
 * FoodWise — Model : Ingredient
 * model/Ingredient.php
 *
 * Responsabilités :
 *   - Toute interaction avec la table `ingredient`
 *   - Validation métier (règles propres à FoodWise)
 *   - Aucun HTML, aucun echo
 */

require_once __DIR__ . '/../config/config.php';

class Ingredient
{
    /* ════════════════════════════════════════════════
       PROPRIÉTÉS
       ════════════════════════════════════════════════ */
    public ?int    $id_ingredient  = null;
    public string  $nom            = '';
    public ?string $categorie      = null;
    public float   $calories_100g  = 0;
    public float   $proteines_100g = 0;
    public float   $glucides_100g  = 0;
    public float   $lipides_100g   = 0;
    public bool    $est_allergene  = false;
    public bool    $est_disponible = true;
    public string  $unite_defaut   = 'g';

    /* Catégories autorisées */
    public const CATEGORIES = [
        'Légume', 'Fruit', 'Céréale', 'Protéine',
        'Produit laitier', 'Corps gras', 'Épice',
        'Légumineuse', 'Boisson', 'Autre'
    ];

    /* Unités autorisées */
    public const UNITES = ['g', 'kg', 'ml', 'L', 'unité', 'tsp', 'tbsp'];

    /* ════════════════════════════════════════════════
       READ — Liste paginée avec filtres
       ════════════════════════════════════════════════ */

    public static function getAll(array $filtres = [], int $page = 1, int $parPage = 15): array
    {
        $db     = config::getConnexion();
        $params = [];
        $where  = ['1=1'];

        if (!empty($filtres['q'])) {
            $where[]  = '(nom LIKE ? OR categorie LIKE ?)';
            $params[] = '%' . $filtres['q'] . '%';
            $params[] = '%' . $filtres['q'] . '%';
        }

        if (!empty($filtres['categorie'])) {
            $where[]  = 'categorie = ?';
            $params[] = $filtres['categorie'];
        }

        if (isset($filtres['est_allergene']) && $filtres['est_allergene'] !== '') {
            $where[]  = 'est_allergene = ?';
            $params[] = (int)$filtres['est_allergene'];
        }

        if (isset($filtres['est_disponible']) && $filtres['est_disponible'] !== '') {
            $where[]  = 'est_disponible = ?';
            $params[] = (int)$filtres['est_disponible'];
        }

        $tris = [
            'nom_asc'   => 'nom ASC',
            'nom_desc'  => 'nom DESC',
            'cal_desc'  => 'calories_100g DESC',
            'cal_asc'   => 'calories_100g ASC',
        ];
        $orderBy  = $tris[$filtres['trier_par'] ?? 'nom_asc'] ?? 'nom ASC';
        $whereSQL = implode(' AND ', $where);

        /* Compte total */
        $stmtCount = $db->prepare("SELECT COUNT(*) FROM ingredient WHERE $whereSQL");
        $i = 1;
        foreach ($params as $v) { $stmtCount->bindValue($i++, $v); }
        $stmtCount->execute();
        $total      = (int)$stmtCount->fetchColumn();
        $totalPages = max(1, (int)ceil($total / $parPage));
        $page       = max(1, min($page, $totalPages));
        $offset     = ($page - 1) * $parPage;

        /* Requête principale */
        $stmt = $db->prepare(
            "SELECT * FROM ingredient WHERE $whereSQL ORDER BY $orderBy LIMIT ? OFFSET ?"
        );
        $i = 1;
        foreach ($params as $v) { $stmt->bindValue($i++, $v); }
        $stmt->bindValue($i++, $parPage, PDO::PARAM_INT);
        $stmt->bindValue($i,   $offset,  PDO::PARAM_INT);
        $stmt->execute();

        return [
            'ingredients' => $stmt->fetchAll(PDO::FETCH_OBJ),
            'total'       => $total,
            'total_pages' => $totalPages,
            'page'        => $page,
        ];
    }

    /* ════════════════════════════════════════════════
       READ — Un ingrédient par ID
       ════════════════════════════════════════════════ */

    public static function getById(int $id): object|false
    {
        $db   = config::getConnexion();
        $stmt = $db->prepare('SELECT * FROM ingredient WHERE id_ingredient = ?');
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    /* ════════════════════════════════════════════════
       CREATE
       ════════════════════════════════════════════════ */

    public static function add(array $data): int
    {
        $db   = config::getConnexion();
        $sql  = "
            INSERT INTO ingredient
                (nom, categorie, calories_100g, proteines_100g, glucides_100g,
                 lipides_100g, est_allergene, est_disponible, unite_defaut)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ";
        $stmt = $db->prepare($sql);
        $stmt->execute([
            trim($data['nom']),
            $data['categorie']      ?? null,
            (float)($data['calories_100g']  ?? 0),
            (float)($data['proteines_100g'] ?? 0),
            (float)($data['glucides_100g']  ?? 0),
            (float)($data['lipides_100g']   ?? 0),
            !empty($data['est_allergene'])  ? 1 : 0,
            !empty($data['est_disponible']) ? 1 : 0,
            $data['unite_defaut'] ?? 'g',
        ]);
        return (int)$db->lastInsertId();
    }

    /* ════════════════════════════════════════════════
       UPDATE
       ════════════════════════════════════════════════ */

    public static function update(int $id, array $data): bool
    {
        $db   = config::getConnexion();
        $sql  = "
            UPDATE ingredient SET
                nom            = ?,
                categorie      = ?,
                calories_100g  = ?,
                proteines_100g = ?,
                glucides_100g  = ?,
                lipides_100g   = ?,
                est_allergene  = ?,
                est_disponible = ?,
                unite_defaut   = ?
            WHERE id_ingredient = ?
        ";
        $stmt = $db->prepare($sql);
        $stmt->execute([
            trim($data['nom']),
            $data['categorie']      ?? null,
            (float)($data['calories_100g']  ?? 0),
            (float)($data['proteines_100g'] ?? 0),
            (float)($data['glucides_100g']  ?? 0),
            (float)($data['lipides_100g']   ?? 0),
            !empty($data['est_allergene'])  ? 1 : 0,
            !empty($data['est_disponible']) ? 1 : 0,
            $data['unite_defaut'] ?? 'g',
            $id,
        ]);
        return $stmt->rowCount() > 0;
    }

    /* ════════════════════════════════════════════════
       DELETE
       ════════════════════════════════════════════════ */

    /**
     * Vérifie d'abord si l'ingrédient est utilisé dans des recettes.
     * Si oui, lève une exception pour empêcher la suppression.
     */
    public static function delete(int $id): bool
    {
        $db = config::getConnexion();

        /* Vérifier les dépendances */
        $stmt = $db->prepare(
            'SELECT COUNT(*) FROM recette_ingredient WHERE id_ingredient = ?'
        );
        $stmt->execute([$id]);
        if ((int)$stmt->fetchColumn() > 0) {
            throw new Exception(
                'Impossible de supprimer cet ingrédient : il est utilisé dans une ou plusieurs recettes.'
            );
        }

        /* Supprimer aussi les règles de substitution liées */
        $db->prepare('DELETE FROM substitut WHERE id_ingredient_source = ? OR id_ingredient_sub = ?')
           ->execute([$id, $id]);

        $stmt2 = $db->prepare('DELETE FROM ingredient WHERE id_ingredient = ?');
        $stmt2->execute([$id]);
        return $stmt2->rowCount() > 0;
    }

    /* ════════════════════════════════════════════════
       VALIDATION MÉTIER (appelée par le Controller)
       Retourne tableau ASSOCIATIF ['champ' => 'message']
       ════════════════════════════════════════════════ */

    public static function valider(array $data): array
    {
        $erreurs = [];

        /* ── Nom ── */
        $nom = trim($data['nom'] ?? '');
        if ($nom === '') {
            $erreurs['nom'] = 'Le nom de l\'ingrédient est obligatoire.';
        } elseif (strlen($nom) > 100) {
            $erreurs['nom'] = 'Le nom ne doit pas dépasser 100 caractères.';
        }

        /* ── Catégorie ── */
        if (!empty($data['categorie']) && !in_array($data['categorie'], self::CATEGORIES)) {
            $erreurs['categorie'] = 'Catégorie invalide.';
        }

        /* ── Valeurs nutritionnelles ── */
        foreach (['calories_100g', 'proteines_100g', 'glucides_100g', 'lipides_100g'] as $champ) {
            $val = $data[$champ] ?? '';
            if ($val !== '' && (!is_numeric($val) || (float)$val < 0)) {
                $labels = [
                    'calories_100g'  => 'calories',
                    'proteines_100g' => 'protéines',
                    'glucides_100g'  => 'glucides',
                    'lipides_100g'   => 'lipides',
                ];
                $erreurs[$champ] = 'La valeur des ' . $labels[$champ] . ' doit être un nombre positif.';
            }
        }

        /* ── Cohérence macros : protéines + glucides + lipides ≤ 100g ── */
        $prot = (float)($data['proteines_100g'] ?? 0);
        $gluc = (float)($data['glucides_100g']  ?? 0);
        $lip  = (float)($data['lipides_100g']   ?? 0);
        if (($prot + $gluc + $lip) > 100) {
            $erreurs['proteines_100g'] = 'La somme protéines + glucides + lipides ne peut pas dépasser 100g.';
        }

        /* ── Unité par défaut ── */
        if (!empty($data['unite_defaut']) && !in_array($data['unite_defaut'], self::UNITES)) {
            $erreurs['unite_defaut'] = 'Unité invalide.';
        }

        return $erreurs;
    }

    /* ════════════════════════════════════════════════
       STATISTIQUES back-office
       ════════════════════════════════════════════════ */

    public static function getStats(): array
    {
        $db  = config::getConnexion();
        $sql = "
            SELECT
                COUNT(*)                AS total,
                SUM(est_allergene)      AS allergenes,
                SUM(est_disponible)     AS disponibles,
                SUM(1 - est_disponible) AS indisponibles
            FROM ingredient
        ";
        return (array)$db->query($sql)->fetch(PDO::FETCH_ASSOC);
    }

    /* ════════════════════════════════════════════════
       UTILITAIRE — Vérifier si le nom existe déjà
       ════════════════════════════════════════════════ */

    public static function nomExiste(string $nom, ?int $excludeId = null): bool
    {
        $db  = config::getConnexion();
        $sql = 'SELECT COUNT(*) FROM ingredient WHERE nom = ?';
        $par = [trim($nom)];
        if ($excludeId !== null) {
            $sql .= ' AND id_ingredient != ?';
            $par[] = $excludeId;
        }
        $stmt = $db->prepare($sql);
        $stmt->execute($par);
        return (int)$stmt->fetchColumn() > 0;
    }
}

