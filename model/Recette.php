<?php
/* model : Recette.php */
require_once __DIR__ . '/../config/config.php';

class Recette
{
    public ?int    $id_recette        = null;
    public string  $nom               = '';
    public ?string $description       = null;
    public int     $temps_prep        = 0;
    public int     $temps_cuisson     = 0;
    public int     $portions          = 1;
    public string  $niveau_difficulte = 'facile';
    public bool    $est_vegetarien    = false;
    public bool    $est_sans_gluten   = false;
    public bool    $est_vegan         = false;
    public ?string $image_url         = null;
    public string  $date_creation     = '';
    public int     $nbr_vue           = 0;
    public ?float  $calories_totales  = null;
    public ?int    $nb_ingredients    = null;

    private static function calSQ(): string
    {
        return "
            SELECT  ri.id_recette,
                    SUM((ri.quantite / 100.0) * i.calories_100g)  AS cal_totales,
                    SUM((ri.quantite / 100.0) * i.proteines_100g) AS proteines_totales,
                    SUM((ri.quantite / 100.0) * i.glucides_100g)  AS glucides_totales,
                    SUM((ri.quantite / 100.0) * i.lipides_100g)   AS lipides_totales,
                    COUNT(ri.id_ingredient)                        AS nb_ing
            FROM recette_ingredient ri
            JOIN ingredient i ON i.id_ingredient = ri.id_ingredient
            GROUP BY ri.id_recette
        ";
    }

    public static function getAll(array $filtres = [], int $page = 1, int $parPage = 12): array
    {
        $db     = config::getConnexion();
        $params = [];
        $where  = ['1=1'];

        if (!empty($filtres['q'])) {
            $where[]  = '(r.nom LIKE ? OR r.description LIKE ?)';
            $params[] = '%' . $filtres['q'] . '%';
            $params[] = '%' . $filtres['q'] . '%';
        }

        if (!empty($filtres['regime'])) {
            $colonnes = [
                'vegetarien'  => 'r.est_vegetarien',
                'vegan'       => 'r.est_vegan',
                'sans_gluten' => 'r.est_sans_gluten',
            ];
            if (isset($colonnes[$filtres['regime']])) {
                $where[] = $colonnes[$filtres['regime']] . ' = 1';
            }
        }

        if (!empty($filtres['difficulte'])) {
            $where[]  = 'r.niveau_difficulte = ?';
            $params[] = $filtres['difficulte'];
        }

        if (!empty($filtres['temps_max']) && is_numeric($filtres['temps_max'])) {
            $where[]  = '(r.temps_prep + r.temps_cuisson) <= ?';
            $params[] = (int)$filtres['temps_max'];
        }

        if (!empty($filtres['calories_max']) && is_numeric($filtres['calories_max'])) {
            $where[]  = 'COALESCE(cal.cal_totales, 0) <= ?';
            $params[] = (float)$filtres['calories_max'];
        }

        $tris = [
            'date_desc' => 'r.date_creation DESC',
            'nom_asc'   => 'r.nom ASC',
            'vues_desc' => 'r.nbr_vue DESC',
            'cal_desc'  => 'cal_totales DESC',
        ];
        $orderBy  = $tris[$filtres['trier_par'] ?? 'date_desc'] ?? 'r.date_creation DESC';
        $whereSQL = implode(' AND ', $where);
        $calSQ    = self::calSQ();

        $sqlCount  = "
            SELECT COUNT(*) AS total
            FROM recette r
            LEFT JOIN ($calSQ) cal ON cal.id_recette = r.id_recette
            WHERE $whereSQL
        ";
        $stmtCount = $db->prepare($sqlCount);
        $i = 1;
        foreach ($params as $v) { $stmtCount->bindValue($i++, $v); }
        $stmtCount->execute();
        $total      = (int)$stmtCount->fetchColumn();
        $totalPages = max(1, (int)ceil($total / $parPage));
        $page       = max(1, min($page, $totalPages));
        $offset     = ($page - 1) * $parPage;

        $sql = "
            SELECT r.*,
                   COALESCE(cal.cal_totales,       0) AS calories_totales,
                   COALESCE(cal.proteines_totales,  0) AS proteines_totales,
                   COALESCE(cal.glucides_totales,   0) AS glucides_totales,
                   COALESCE(cal.lipides_totales,    0) AS lipides_totales,
                   COALESCE(cal.nb_ing,             0) AS nb_ingredients
            FROM recette r
            LEFT JOIN ($calSQ) cal ON cal.id_recette = r.id_recette
            WHERE $whereSQL
            ORDER BY $orderBy
            LIMIT ? OFFSET ?
        ";
        $stmt = $db->prepare($sql);
        $i = 1;
        foreach ($params as $v) { $stmt->bindValue($i++, $v); }
        $stmt->bindValue($i++, $parPage, PDO::PARAM_INT);
        $stmt->bindValue($i,   $offset,  PDO::PARAM_INT);
        $stmt->execute();

        return [
            'recettes'    => $stmt->fetchAll(PDO::FETCH_OBJ),
            'total'       => $total,
            'total_pages' => $totalPages,
            'page'        => $page,
        ];
    }

    public static function getById(int $id): object|false
    {
        $db  = config::getConnexion();
        $sql = "
            SELECT r.*,
                   COALESCE(SUM((ri.quantite / 100.0) * i.calories_100g),  0) AS calories_totales,
                   COALESCE(SUM((ri.quantite / 100.0) * i.proteines_100g), 0) AS proteines_totales,
                   COALESCE(SUM((ri.quantite / 100.0) * i.glucides_100g),  0) AS glucides_totales,
                   COALESCE(SUM((ri.quantite / 100.0) * i.lipides_100g),   0) AS lipides_totales,
                   COUNT(ri.id_ingredient)                                     AS nb_ingredients
            FROM recette r
            LEFT JOIN recette_ingredient ri ON ri.id_recette   = r.id_recette
            LEFT JOIN ingredient i          ON i.id_ingredient = ri.id_ingredient
            WHERE r.id_recette = ?
            GROUP BY r.id_recette
        ";
        $stmt = $db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    public static function incrementViews(int $id): void
    {
        $db = config::getConnexion();
        $db->prepare("UPDATE recette SET nbr_vue = nbr_vue + 1 WHERE id_recette = ?")
           ->execute([$id]);
    }

    public static function add(array $data): int
    {
        $db  = config::getConnexion();
        $sql = "
            INSERT INTO recette
            (nom, description, temps_prep, temps_cuisson, portions,
            niveau_difficulte, est_vegetarien, est_vegan, est_sans_gluten,
            image_url, date_creation, id_user)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, CURDATE(), ?)
        ";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([
            $data['nom'],
            $data['description']            ?? null,
            (int)($data['temps_prep']       ?? 0),
            (int)($data['temps_cuisson']    ?? 0),
            (int)($data['portions']         ?? 1),
            $data['niveau_difficulte']      ?? 'facile',
            $data['est_vegetarien'],
            $data['est_vegan'],
            $data['est_sans_gluten'],
            $data['image_url']              ?? null,
            $data['id_user']                ?? null,
        ]);
        return (int)$db->lastInsertId();
    }

    public static function update(int $id, array $data): bool
    {
        $db  = config::getConnexion();
        $sql = "
            UPDATE recette SET
                nom               = ?,
                description       = ?,
                temps_prep        = ?,
                temps_cuisson     = ?,
                portions          = ?,
                niveau_difficulte = ?,
                est_vegetarien    = ?,
                est_vegan         = ?,
                est_sans_gluten   = ?,
                image_url         = ?
            WHERE id_recette = ?
        ";
        $stmt = $db->prepare($sql);
        $stmt->execute([
            $data['nom'],
            $data['description']            ?? null,
            (int)($data['temps_prep']       ?? 0),
            (int)($data['temps_cuisson']    ?? 0),
            (int)($data['portions']         ?? 1),
            $data['niveau_difficulte']      ?? 'facile',
            $data['est_vegetarien'],
            $data['est_vegan'],
            $data['est_sans_gluten'],
            $data['image_url']              ?? null,
            $id,
        ]);
        return $stmt->rowCount() > 0;
    }

    public static function delete(int $id): bool
    {
        $db = config::getConnexion();
        try {
            $db->beginTransaction();
            $db->prepare("DELETE FROM recette_ingredient WHERE id_recette = ?")
               ->execute([$id]);
            $stmt = $db->prepare("DELETE FROM recette WHERE id_recette = ?");
            $stmt->execute([$id]);
            $db->commit();
            return $stmt->rowCount() > 0;
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }

    
public static function valider(array $data): array
    {
        $erreurs = [];

        /* ── Nom ── */
        $nom = trim($data['nom'] ?? '');
        if ($nom === '') {
            $erreurs['nom'] = 'Le nom est obligatoire.';
        } elseif (strlen($nom) > 100) {
            $erreurs['nom'] = 'Le nom ne doit pas dépasser 100 caractères.';
        }

        /* ── Description ── */
        if (!empty($data['description']) && strlen($data['description']) > 1000) {
            $erreurs['description'] = 'La description ne doit pas dépasser 1000 caractères.';
        }

        /* ── Temps de préparation ── */
        if (!isset($data['temps_prep']) || !is_numeric($data['temps_prep']) || (int)$data['temps_prep'] < 1) {
            $erreurs['temps_prep'] = 'Le temps de préparation doit être au moins 1 minute.';
        }

        /* ── Temps de cuisson ── */
        if (isset($data['temps_cuisson']) && $data['temps_cuisson'] !== ''
            && (!is_numeric($data['temps_cuisson']) || (int)$data['temps_cuisson'] < 0)) {
            $erreurs['temps_cuisson'] = 'Le temps de cuisson ne peut pas être négatif.';
        }

        /* ── Portions ── */
        if (!isset($data['portions']) || !is_numeric($data['portions']) || (int)$data['portions'] < 1) {
            $erreurs['portions'] = 'Le nombre de portions doit être au moins 1.';
        }

        /* ── Niveau de difficulté ── */
        if (!in_array($data['niveau_difficulte'] ?? '', ['facile', 'moyen', 'difficile'])) {
            $erreurs['niveau_difficulte'] = 'Veuillez choisir un niveau de difficulté.';
        }

        return $erreurs;
    }


    public static function getStats(): array
    {
        $db  = config::getConnexion();
        $sql = "
            SELECT
                COUNT(*)                                         AS total,
                SUM(est_vegetarien)                              AS vegetariennes,
                SUM(est_sans_gluten)                             AS sans_gluten,
                SUM(est_vegan)                                   AS veganes,
                SUM(
                    MONTH(date_creation) = MONTH(CURDATE())
                    AND YEAR(date_creation) = YEAR(CURDATE())
                )                                                AS ce_mois
            FROM recette
        ";
        return (array)$db->query($sql)->fetch(PDO::FETCH_ASSOC);
    }

public static function getOffresParRecette(int $idRecette): array
    {
        $db  = config::getConnexion();
 
        /*
         * Sous-requête : pour chaque ingrédient, trouver
         * le prix minimum parmi les offres actives correspondantes.
         * On filtre statut = 'disponible' et date_expiration > NOW().
         */
        $sql = "
            SELECT
                i.id_ingredient,
                i.nom                          AS ingredient_nom,
                ri.quantite,
                ri.unite,
                ri.est_optionnel,
 
                o.id                           AS offre_id,
                o.titre                        AS offre_titre,
                o.prix_unitaire,
                o.unite                        AS offre_unite,
                o.stock,
                o.statut                       AS offre_statut,
                o.date_expiration,
                o.image                        AS offre_image,
 
                c.id                           AS commercant_id,
                c.nom                          AS commercant_nom,
                c.ville                        AS commercant_ville,
                c.adresse                      AS commercant_adresse,
                c.telephone                    AS commercant_tel
 
            FROM recette_ingredient ri
            JOIN ingredient i
                ON i.id_ingredient = ri.id_ingredient
 
            
            LEFT JOIN Offre o
            ON (
                o.titre LIKE CONCAT('%', i.nom, '%')
                OR o.titre LIKE CONCAT('%', i.nom, 's%')
                OR o.titre LIKE CONCAT('%', TRIM(TRAILING 's' FROM i.nom), '%')
                 OR SOUNDEX(o.titre) = SOUNDEX(i.nom)
                )
                AND o.statut        = 'disponible'
                AND o.date_expiration > NOW()
 
            LEFT JOIN Commercant c
                ON c.id = o.commercant_id
 
            WHERE ri.id_recette = ?
 
            /* Garder uniquement l'offre la moins chère par ingrédient */
            AND (
                o.prix_unitaire = (
                    SELECT MIN(o2.prix_unitaire)
                    FROM Offre o2
                    WHERE (
                        o2.titre LIKE CONCAT('%', i.nom, '%')
                        OR o2.titre LIKE CONCAT('%', i.nom, 's%')
                        OR o2.titre LIKE CONCAT('%', TRIM(TRAILING 's' FROM i.nom), '%')
                        OR SOUNDEX(o2.titre) = SOUNDEX(i.nom)
                    )
                      AND o2.statut        = 'disponible'
                      AND o2.date_expiration > NOW()
                )
                OR o.id IS NULL
            )
 
            ORDER BY ri.ordre_affichage ASC
        ";
 
        $stmt = $db->prepare($sql);
        $stmt->execute([$idRecette]);
        $rows = $stmt->fetchAll(PDO::FETCH_OBJ);
 
        /*
         * On structure le résultat en deux groupes :
         *  - avec_offre  : ingrédients qui ont au moins une offre active
         *  - sans_offre  : ingrédients sans offre trouvée
         *
         * On calcule aussi le coût total estimé de la liste.
         */
        $avecOffre  = [];
        $sansOffre  = [];
        $coutTotal  = 0.0;
        $economies  = 0;  // nombre d'ingrédients couverts par une offre
 
        foreach ($rows as $row) {
            if (!empty($row->offre_id)) {
                $avecOffre[]  = $row;
                /* Coût estimé = (quantite / 1000) * prix_unitaire (prix au kg/L) */
                $coutTotal   += ($row->quantite / 1000) * $row->prix_unitaire;
                $economies++;
            } else {
                $sansOffre[] = $row;
            }
        }
 
        return [
            'avec_offre' => $avecOffre,
            'sans_offre' => $sansOffre,
            'cout_total' => round($coutTotal, 2),
            'economies'  => $economies,
            'total_ing'  => count($rows),
        ];
    }

}



