<?php
declare(strict_types=1);

class MessageModel
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Envoie un message d'un utilisateur à un autre
     */
    public function sendMessage(int $senderId, int $receiverId, string $content): bool
    {
        // Validation : le contenu ne doit pas être vide
        if (trim($content) === '') {
            return false;
        }

        // Validation : un utilisateur ne peut pas s'envoyer un message à lui-même
        if ($senderId === $receiverId) {
            return false;
        }

        $stmt = $this->pdo->prepare(
            'INSERT INTO messages (sender_id, receiver_id, content, created_at) 
             VALUES (:sender_id, :receiver_id, :content, NOW())'
        );

        return $stmt->execute([
            ':sender_id' => $senderId,
            ':receiver_id' => $receiverId,
            ':content' => trim($content),
        ]);
    }

    /**
     * Récupère la conversation entre deux utilisateurs
     */
    public function getConversation(int $user1, int $user2, int $limit = 50): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT m.*, 
                    u_sender.prenom AS sender_prenom, u_sender.nom AS sender_nom, u_sender.photo_profil AS sender_photo,
                    u_receiver.prenom AS receiver_prenom, u_receiver.nom AS receiver_nom, u_receiver.photo_profil AS receiver_photo
             FROM messages m
             LEFT JOIN utilisateurs u_sender ON m.sender_id = u_sender.id
             LEFT JOIN utilisateurs u_receiver ON m.receiver_id = u_receiver.id
             WHERE (m.sender_id = :user1 AND m.receiver_id = :user2)
                OR (m.sender_id = :user2 AND m.receiver_id = :user1)
             ORDER BY m.created_at ASC
             LIMIT :limit'
        );

        $stmt->bindValue(':user1', $user1, PDO::PARAM_INT);
        $stmt->bindValue(':user2', $user2, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Compte les messages non lus pour un utilisateur
     */
    public function getUnreadCount(int $userId): int
    {
        $stmt = $this->pdo->prepare(
            'SELECT COUNT(*) as count FROM messages WHERE receiver_id = :user_id AND is_read = 0'
        );
        $stmt->execute([':user_id' => $userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int) ($result['count'] ?? 0);
    }

    /**
     * Marque un message comme lu
     */
    public function markAsRead(int $messageId): bool
    {
        $stmt = $this->pdo->prepare('UPDATE messages SET is_read = 1 WHERE id = :id');
        return $stmt->execute([':id' => $messageId]);
    }

    /**
     * Marque tous les messages d'une conversation comme lus
     */
    public function markConversationAsRead(int $userId, int $senderId): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE messages SET is_read = 1 
             WHERE receiver_id = :receiver_id AND sender_id = :sender_id AND is_read = 0'
        );
        return $stmt->execute([
            ':receiver_id' => $userId,
            ':sender_id' => $senderId,
        ]);
    }

    /**
     * Récupère la liste des utilisateurs avec qui l'utilisateur a une conversation
     */
    public function getConversationList(int $userId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT DISTINCT
                    CASE 
                        WHEN sender_id = :user_id THEN receiver_id
                        ELSE sender_id
                    END as user_id,
                    u.prenom, u.nom, u.photo_profil, u.email,
                    (SELECT MAX(created_at) FROM messages 
                     WHERE (sender_id = :user_id AND receiver_id = u.id)
                        OR (sender_id = u.id AND receiver_id = :user_id)) as last_message_date,
                    (SELECT content FROM messages 
                     WHERE (sender_id = :user_id AND receiver_id = u.id)
                        OR (sender_id = u.id AND receiver_id = :user_id)
                     ORDER BY created_at DESC LIMIT 1) as last_message,
                    (SELECT COUNT(*) FROM messages 
                     WHERE receiver_id = :user_id AND sender_id = u.id AND is_read = 0) as unread_count
             FROM messages m
             JOIN utilisateurs u ON (u.id = m.sender_id OR u.id = m.receiver_id)
             WHERE (m.sender_id = :user_id OR m.receiver_id = :user_id) AND u.id != :user_id
             GROUP BY user_id
             ORDER BY last_message_date DESC'
        );
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Récupère tous les utilisateurs (pour l\'annuaire)
     */
    public function getAllUsers(int $excludeUserId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, prenom, nom, photo_profil, email 
             FROM utilisateurs 
             WHERE id != :exclude_id AND role = "user"
             ORDER BY prenom, nom'
        );
        $stmt->execute([':exclude_id' => $excludeUserId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
}
