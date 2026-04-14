<?php

require_once __DIR__ . '/../Config.php';
class CommercantModel {

    private PDO $pdo;

    public function __construct() {
        $this->pdo =  config::getConnexion();
    }

    // ── Lister tous les commerçants ──────────────────────────
    public function findAll(string $ville = '', string $statut = ''): array {
        $sql    = "SELECT * FROM Commercant WHERE 1=1";
        $params = [];

        if ($ville !== '') {
            $sql .= " AND LOWER(ville) LIKE LOWER(:ville)";
            $params[':ville'] = '%' . $ville . '%';
        }
        if ($statut !== '') {
            $sql .= " AND statut = :statut";
            $params[':statut'] = $statut;
        }

        $sql .= " ORDER BY date_inscription DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    // ── Trouver par ID ───────────────────────────────────────
public function getById($id) {
    $sql = "SELECT * FROM commercant WHERE id = :id";
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute(['id' => $id]);

    $commercant = $stmt->fetch(PDO::FETCH_ASSOC);

    return $commercant;
}

    // ── Trouver par email ────────────────────────────────────
    public function findByEmail(string $email): array|false {
        $stmt = $this->pdo->prepare("SELECT * FROM Commercant WHERE email = :email");
        $stmt->execute([':email' => $email]);
        return $stmt->fetch();
    }

    // ── Créer un commerçant ──────────────────────────────────
    public function create(array $data): int|false {
        $sql = "INSERT INTO Commercant
                    (nom, email, telephone, adresse, ville, code_postal, description, logo, statut)
                VALUES
                    (:nom, :email, :telephone, :adresse, :ville, :code_postal, :description, :logo, :statut)";
        $stmt = $this->pdo->prepare($sql);
        $ok   = $stmt->execute([
            ':nom'         => trim($data['nom']),
            ':email'       => strtolower(trim($data['email'])),
            ':telephone'   => $data['telephone']   ?? null,
            ':adresse'     => trim($data['adresse']),
            ':ville'       => trim($data['ville']),
            ':code_postal' => $data['code_postal']  ?? null,
            ':description' => $data['description']  ?? null,
            ':logo'        => $data['logo']          ?? null,
            ':statut'      => $data['statut']        ?? 'actif',
        ]);
        return $ok ? (int)$this->pdo->lastInsertId() : false;
    }

    // ── Modifier un commerçant ───────────────────────────────
    public function update(int $id, array $data): bool {
        $sql = "UPDATE Commercant SET
                    nom         = :nom,
                    email       = :email,
                    telephone   = :telephone,
                    adresse     = :adresse,
                    ville       = :ville,
                    code_postal = :code_postal,
                    description = :description,
                    logo        = :logo,
                    statut      = :statut
                WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':nom'         => trim($data['nom']),
            ':email'       => strtolower(trim($data['email'])),
            ':telephone'   => $data['telephone']   ?? null,
            ':adresse'     => trim($data['adresse']),
            ':ville'       => trim($data['ville']),
            ':code_postal' => $data['code_postal']  ?? null,
            ':description' => $data['description']  ?? null,
            ':logo'        => $data['logo']          ?? null,
            ':statut'      => $data['statut']        ?? 'actif',
            ':id'          => $id,
        ]);
    }

    // ── Supprimer un commerçant ──────────────────────────────
    public function delete(int $id): bool {
        $stmt = $this->pdo->prepare("DELETE FROM Commercant WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    // ── Liste des villes distinctes (pour filtres) ───────────
    public function getVilles(): array {
        $stmt = $this->pdo->query("SELECT DISTINCT ville FROM Commercant ORDER BY ville");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    // ── Commerçant avec ses offres actives ───────────────────
    /*public function findWithOffres(int $id): array|false {
        $commercant = $this->findById($id);
        if (!$commercant) return false;

        $stmt = $this->pdo->prepare(
            "SELECT * FROM Offre
              WHERE commercant_id = :id
                AND statut IN ('disponible','epuise')
              ORDER BY date_expiration ASC"
        );
        $stmt->execute([':id' => $id]);
        $commercant['offres'] = $stmt->fetchAll();
        return $commercant;
    }*/

    // ── Valider les données du formulaire ────────────────────
    public function validate(array $data): array {
        $errors = [];

        if (empty(trim($data['nom'] ?? '')))
            $errors['nom'] = 'Le nom est obligatoire.';

        if (empty(trim($data['email'] ?? '')))
            $errors['email'] = "L'email est obligatoire.";
        elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL))
            $errors['email'] = "Format d'email invalide.";

        if (empty(trim($data['adresse'] ?? '')))
            $errors['adresse'] = "L'adresse est obligatoire.";

        if (empty(trim($data['ville'] ?? '')))
            $errors['ville'] = "La ville est obligatoire.";

        return $errors;
    }
}
