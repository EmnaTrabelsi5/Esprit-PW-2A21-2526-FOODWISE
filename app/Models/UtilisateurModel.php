<?php
declare(strict_types=1);

class UtilisateurModel
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM utilisateurs WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM utilisateurs WHERE email = :email');
        $stmt->execute([':email' => $email]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    public function authenticate(string $email, string $password): ?array
    {
        $user = $this->findByEmail($email);
        if ($user === null) {
            return null;
        }

        if (!password_verify($password, (string) $user['password_hash'])) {
            return null;
        }

        return $user;
    }

    public function create(string $nom, string $prenom, string $email, string $password): int
    {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $createdAt = date('Y-m-d H:i:s');
        $stmt = $this->pdo->prepare(
            'INSERT INTO utilisateurs (nom, prenom, email, password_hash, created_at) VALUES (:nom, :prenom, :email, :password_hash, :created_at)'
        );
        $stmt->execute([
            ':nom' => $nom,
            ':prenom' => $prenom,
            ':email' => $email,
            ':password_hash' => $hash,
            ':created_at' => $createdAt,
        ]);
        return (int) $this->pdo->lastInsertId();
    }

    public function update(int $id, string $nom, string $prenom, string $email, ?string $password = null): void
    {
        $params = [
            ':id' => $id,
            ':nom' => $nom,
            ':prenom' => $prenom,
            ':email' => $email,
        ];

        $sql = 'UPDATE utilisateurs SET nom = :nom, prenom = :prenom, email = :email';
        if ($password !== null && $password !== '') {
            $params[':password_hash'] = password_hash($password, PASSWORD_DEFAULT);
            $sql .= ', password_hash = :password_hash';
        }
        $sql .= ' WHERE id = :id';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
    }

    public function existsByEmail(string $email): bool
    {
        return $this->findByEmail($email) !== null;
    }

    public function existsByEmailExceptId(string $email, int $exceptId): bool
    {
        $stmt = $this->pdo->prepare('SELECT id FROM utilisateurs WHERE email = :email AND id != :id');
        $stmt->execute([':email' => $email, ':id' => $exceptId]);
        return $stmt->fetch() !== false;
    }
}
