<?php

require_once __DIR__ . '/../Config.php';

class OffreModel {

    private PDO $pdo;

    public function __construct() {
        $this->pdo =  config::getConnexion();
    }

    // ── Expirer automatiquement les offres échues ────────────
    public function expireOffres(): int {
        $stmt = $this->pdo->prepare(
            "UPDATE Offre
                SET statut = 'expire'
              WHERE statut IN ('disponible','epuise')
                AND date_expiration < NOW()"
        );
        $stmt->execute();
        return $stmt->rowCount();
    }

    // ── Épuiser si stock = 0 ─────────────────────────────────
    public function syncStockStatut(): void {
        $this->pdo->exec(
            "UPDATE Offre
                SET statut = 'epuise'
              WHERE stock = 0
                AND statut = 'disponible'
                AND date_expiration >= NOW()"
        );
        $this->pdo->exec(
            "UPDATE Offre
                SET statut = 'disponible'
              WHERE stock > 0
                AND statut = 'epuise'
                AND date_expiration >= NOW()"
        );
    }

    // ── Lister toutes les offres (avec filtre) ───────────────
    public function findAll(array $filtres = []): array {
        // Expire d'abord
        $this->expireOffres();
        $this->syncStockStatut();

        $sql    = "SELECT o.*, c.nom AS commercant_nom, c.ville AS commercant_ville,
                          c.adresse AS commercant_adresse
                     FROM Offre o
                     JOIN Commercant c ON o.commercant_id = c.id
                    WHERE 1=1";
        $params = [];

        if (!empty($filtres['ville'])) {
            $sql .= " AND LOWER(c.ville) LIKE LOWER(:ville)";
            $params[':ville'] = '%' . $filtres['ville'] . '%';
        }
        if (!empty($filtres['statut'])) {
            $sql .= " AND o.statut = :statut";
            $params[':statut'] = $filtres['statut'];
        }
        if (!empty($filtres['categorie'])) {
            $sql .= " AND o.categorie = :categorie";
            $params[':categorie'] = $filtres['categorie'];
        }
        if (!empty($filtres['commercant_id'])) {
            $sql .= " AND o.commercant_id = :commercant_id";
            $params[':commercant_id'] = (int)$filtres['commercant_id'];
        }
        if (!empty($filtres['search'])) {
            $sql .= " AND (LOWER(o.titre) LIKE LOWER(:search) OR LOWER(o.description) LIKE LOWER(:search2))";
            $params[':search']  = '%' . $filtres['search'] . '%';
            $params[':search2'] = '%' . $filtres['search'] . '%';
        }

        // Tri
        $orderMap = [
            'expiration' => 'o.date_expiration ASC',
            'prix_asc'   => 'o.prix_unitaire ASC',
            'prix_desc'  => 'o.prix_unitaire DESC',
            'recent'     => 'o.created_at DESC',
        ];
        $tri  = $filtres['tri'] ?? 'recent';
        $sql .= " ORDER BY " . ($orderMap[$tri] ?? $orderMap['recent']);

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    // ── Trouver par ID ───────────────────────────────────────
    public function findById(int $id): array|false {
        $this->expireOffres();
        $stmt = $this->pdo->prepare(
            "SELECT o.*, c.nom AS commercant_nom, c.ville AS commercant_ville,
                    c.adresse AS commercant_adresse, c.telephone AS commercant_tel
               FROM Offre o
               JOIN Commercant c ON o.commercant_id = c.id
              WHERE o.id = :id"
        );
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    // ── Créer une offre ──────────────────────────────────────
    public function create(array $data): int|false {
        $sql = "INSERT INTO Offre
                    (commercant_id, titre, description, prix_unitaire, unite,
                     stock, stock_initial, statut, categorie, image,
                     date_debut, date_expiration)
                VALUES
                    (:commercant_id, :titre, :description, :prix_unitaire, :unite,
                     :stock, :stock_initial, :statut, :categorie, :image,
                     :date_debut, :date_expiration)";

        $stock  = (int)($data['stock'] ?? 0);
        $statut = $stock > 0 ? 'disponible' : 'epuise';
        // Vérifie si la date d'expiration est déjà passée
        if (!empty($data['date_expiration']) && strtotime($data['date_expiration']) < time()) {
            $statut = 'expire';
        }

        $stmt = $this->pdo->prepare($sql);
        $ok   = $stmt->execute([
            ':commercant_id'   => (int)$data['commercant_id'],
            ':titre'           => trim($data['titre']),
            ':description'     => $data['description']    ?? null,
            ':prix_unitaire'   => (float)$data['prix_unitaire'],
            ':unite'           => $data['unite']           ?? 'kg',
            ':stock'           => $stock,
            ':stock_initial'   => $stock,
            ':statut'          => $statut,
            ':categorie'       => $data['categorie']       ?? 'autre',
            ':image'           => $data['image']           ?? null,
            ':date_debut'      => $data['date_debut']      ?? date('Y-m-d H:i:s'),
            ':date_expiration' => $data['date_expiration'],
        ]);
        return $ok ? (int)$this->pdo->lastInsertId() : false;
    }

    // ── Modifier une offre ───────────────────────────────────
    public function update(int $id, array $data): bool {
        $stock  = (int)($data['stock'] ?? 0);
        $statut = $data['statut'] ?? ($stock > 0 ? 'disponible' : 'epuise');

        $sql = "UPDATE Offre SET
                    commercant_id   = :commercant_id,
                    titre           = :titre,
                    description     = :description,
                    prix_unitaire   = :prix_unitaire,
                    unite           = :unite,
                    stock           = :stock,
                    statut          = :statut,
                    categorie       = :categorie,
                    image           = :image,
                    date_debut      = :date_debut,
                    date_expiration = :date_expiration
                WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':commercant_id'   => (int)$data['commercant_id'],
            ':titre'           => trim($data['titre']),
            ':description'     => $data['description']    ?? null,
            ':prix_unitaire'   => (float)$data['prix_unitaire'],
            ':unite'           => $data['unite']           ?? 'kg',
            ':stock'           => $stock,
            ':statut'          => $statut,
            ':categorie'       => $data['categorie']       ?? 'autre',
            ':image'           => $data['image']           ?? null,
            ':date_debut'      => $data['date_debut']      ?? date('Y-m-d H:i:s'),
            ':date_expiration' => $data['date_expiration'],
            ':id'              => $id,
        ]);
    }

    // ── Clôturer (expire manuellement) ──────────────────────
    public function cloture(int $id): bool {
        $stmt = $this->pdo->prepare(
            "UPDATE Offre SET statut = 'expire' WHERE id = :id"
        );
        return $stmt->execute([':id' => $id]);
    }

    // ── Supprimer ─────────────────────────────────────────── 
    public function delete(int $id): bool {
        $stmt = $this->pdo->prepare("DELETE FROM Offre WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    // ── Stats pour le dashboard ──────────────────────────────
    public function getStats(): array {
        $this->expireOffres();
        $stmt = $this->pdo->query(
            "SELECT
                COUNT(*)                                           AS total,
                SUM(statut = 'disponible')                        AS disponibles,
                SUM(statut = 'epuise')                            AS epuisees,
                SUM(statut = 'expire')                            AS expirees,
                SUM(date_expiration BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 2 DAY)
                    AND statut = 'disponible')                    AS expire_bientot
             FROM Offre"
        );
        return $stmt->fetch();
    }

    // ── Valider les données ──────────────────────────────────
    public function validate(array $data): array {
        $errors = [];

        if (empty(trim($data['titre'] ?? '')))
            $errors['titre'] = 'Le titre est obligatoire.';

        if (empty($data['commercant_id']))
            $errors['commercant_id'] = 'Le commerçant est obligatoire.';

        if (!isset($data['prix_unitaire']) || $data['prix_unitaire'] === '' || (float)$data['prix_unitaire'] < 0)
            $errors['prix_unitaire'] = 'Le prix doit être un nombre positif.';

        if (!isset($data['stock']) || (int)$data['stock'] < 0)
            $errors['stock'] = 'Le stock doit être un entier positif ou nul.';

        if (empty($data['date_expiration']))
            $errors['date_expiration'] = "La date d'expiration est obligatoire.";

        if (!empty($data['date_debut']) && !empty($data['date_expiration'])) {
            if (strtotime($data['date_expiration']) <= strtotime($data['date_debut']))
                $errors['date_expiration'] = "La date d'expiration doit être postérieure à la date de début.";
        }

        return $errors;
    }
}
