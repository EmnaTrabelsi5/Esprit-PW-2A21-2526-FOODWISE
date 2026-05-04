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

        // Vérifier si l'utilisateur est banni
        if (($user['status'] ?? 'active') === 'banned') {
            return null;
        }

        // Vérifier si la suspension a expiré et la lever automatiquement
        if (($user['status'] ?? 'active') === 'suspended') {
            $suspendedUntil = $user['suspended_until'] ?? null;
            if ($suspendedUntil !== null && strtotime($suspendedUntil) <= time()) {
                $this->liftSuspension((int) $user['id']);
            } else {
                // Suspension toujours active
                return null;
            }
        }

        return $user;
    }

    public function authenticateAsAdmin(string $email, string $password): ?array
    {
        $user = $this->authenticate($email, $password);
        if ($user === null) {
            return null;
        }

        // Vérifier que l'utilisateur a le rôle 'admin'
        if (($user['role'] ?? 'user') !== 'admin') {
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

    public function updateProfilePhoto(int $id, string $photoPath): void
    {
        $stmt = $this->pdo->prepare('UPDATE utilisateurs SET photo_profil = :photo_profil WHERE id = :id');
        $stmt->execute([':photo_profil' => $photoPath, ':id' => $id]);
    }

    public function deleteProfile(int $id): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM utilisateurs WHERE id = :id');
        $stmt->execute([':id' => $id]);
    }

    /**
     * Sauvegarder un code de réinitialisation de mot de passe
     */
    public function saveResetCode(int $userId, string $resetCode): void
    {
        $expiresAt = date('Y-m-d H:i:s', time() + (30 * 60)); // 30 minutes
        $stmt = $this->pdo->prepare(
            'UPDATE utilisateurs SET reset_code = :reset_code, reset_code_expires_at = :expires_at WHERE id = :id'
        );
        $stmt->execute([
            ':reset_code' => $resetCode,
            ':expires_at' => $expiresAt,
            ':id' => $userId,
        ]);
    }

    /**
     * Vérifier si un code de réinitialisation est valide
     */
    public function verifyResetCode(string $email, string $resetCode): ?array
    {
        $user = $this->findByEmail($email);
        if ($user === null) {
            error_log('Reset code verification failed: user not found for email ' . $email);
            return null;
        }

        $storedCode = $user['reset_code'] ?? null;
        $expiresAt = $user['reset_code_expires_at'] ?? null;

        if ($storedCode === null || $expiresAt === null) {
            error_log('Reset code verification failed: no code or expires_at set for user ' . $user['id']);
            return null;
        }

        // Nettoyer le code saisi (supprimer espaces, tirets, etc.)
        $cleanedResetCode = trim(str_replace(['-', ' '], '', $resetCode));
        $cleanedStoredCode = trim(str_replace(['-', ' '], '', $storedCode));

        error_log('Reset code check - Stored: ' . $cleanedStoredCode . ', Provided: ' . $cleanedResetCode);

        if ($cleanedStoredCode !== $cleanedResetCode) {
            error_log('Reset code verification failed: code mismatch');
            return null;
        }

        $expirationTime = strtotime($expiresAt);
        if ($expirationTime === false) {
            error_log('Reset code verification failed: invalid expiration time format');
            return null;
        }

        $currentTime = time();
        error_log('Reset code check - Current time: ' . date('Y-m-d H:i:s', $currentTime) . ', Expires at: ' . date('Y-m-d H:i:s', $expirationTime));

        if ($currentTime > $expirationTime) {
            error_log('Reset code verification failed: code expired');
            return null;
        }

        return $user;
    }

    /**
     * Mettre à jour le mot de passe avec un code valide
     */
    public function updatePasswordByResetCode(int $userId, string $newPassword): void
    {
        $hash = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $this->pdo->prepare(
            'UPDATE utilisateurs SET password_hash = :password_hash, reset_code = NULL, reset_code_expires_at = NULL WHERE id = :id'
        );
        $stmt->execute([
            ':password_hash' => $hash,
            ':id' => $userId,
        ]);
    }

    /**
     * Supprimer le code de réinitialisation
     */
    public function clearResetCode(int $userId): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE utilisateurs SET reset_code = NULL, reset_code_expires_at = NULL WHERE id = :id'
        );
        $stmt->execute([':id' => $userId]);
    }

    /**
     * Suspendre un utilisateur pour une durée déterminée
     * @param int $userId
     * @param int $days Nombre de jours de suspension
     */
    public function suspendUser(int $userId, int $days): void
    {
        $suspendedUntil = date('Y-m-d H:i:s', time() + ($days * 24 * 60 * 60));
        $stmt = $this->pdo->prepare(
            'UPDATE utilisateurs SET status = :status, suspended_until = :suspended_until WHERE id = :id'
        );
        $stmt->execute([
            ':status' => 'suspended',
            ':suspended_until' => $suspendedUntil,
            ':id' => $userId,
        ]);
    }

    /**
     * Lever la suspension d'un utilisateur
     */
    public function liftSuspension(int $userId): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE utilisateurs SET status = :status, suspended_until = NULL WHERE id = :id'
        );
        $stmt->execute([
            ':status' => 'active',
            ':id' => $userId,
        ]);
    }

    /**
     * Bannir un utilisateur de manière permanente
     */
    public function banUser(int $userId, string $reason = ''): void
    {
        $bannedAt = date('Y-m-d H:i:s');
        $stmt = $this->pdo->prepare(
            'UPDATE utilisateurs SET status = :status, ban_reason = :ban_reason, banned_at = :banned_at WHERE id = :id'
        );
        $stmt->execute([
            ':status' => 'banned',
            ':ban_reason' => $reason,
            ':banned_at' => $bannedAt,
            ':id' => $userId,
        ]);
    }

    /**
     * Retirer le bannissement d'un utilisateur
     */
    public function unbanUser(int $userId): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE utilisateurs SET status = :status, ban_reason = NULL, banned_at = NULL WHERE id = :id'
        );
        $stmt->execute([
            ':status' => 'active',
            ':id' => $userId,
        ]);
    }

    /**
     * Obtenir le statut d'un utilisateur avec détails
     */
    public function getUserStatus(int $userId): ?array
    {
        $user = $this->findById($userId);
        if ($user === null) {
            return null;
        }

        $status = $user['status'] ?? 'active';
        $suspendedUntil = $user['suspended_until'] ?? null;
        
        // Vérifier et lever les suspensions expirées
        if ($status === 'suspended' && $suspendedUntil !== null && strtotime($suspendedUntil) <= time()) {
            $this->liftSuspension($userId);
            $status = 'active';
        }

        return [
            'id' => $user['id'],
            'email' => $user['email'],
            'status' => $status,
            'suspended_until' => $user['suspended_until'],
            'ban_reason' => $user['ban_reason'],
            'banned_at' => $user['banned_at'],
            'is_suspended' => $status === 'suspended',
            'is_banned' => $status === 'banned',
            'suspension_days_left' => $this->calculateSuspensionDaysLeft($suspendedUntil),
        ];
    }

    /**
     * Calculer le nombre de jours restants de suspension
     */
    private function calculateSuspensionDaysLeft(?string $suspendedUntil): ?int
    {
        if ($suspendedUntil === null) {
            return null;
        }

        $suspensionTime = strtotime($suspendedUntil);
        if ($suspensionTime === false) {
            return null;
        }

        $daysLeft = ceil(($suspensionTime - time()) / (24 * 60 * 60));
        return max(0, (int) $daysLeft);
    }
}
