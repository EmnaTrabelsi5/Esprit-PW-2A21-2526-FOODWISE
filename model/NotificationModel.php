<?php
// ============================================================
//  FoodWise · NotificationModel.php
//  Toutes les requêtes SQL liées aux notifications
// ============================================================

class NotificationModel {

    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    // ----------------------------------------------------------
    //  Récupérer les notifications d'un utilisateur
    //  $limit : nombre max retourné (défaut 20)
    //  $unread_only : si true, uniquement les non lues
    // ----------------------------------------------------------
    public function getByUser(int $user_id, int $limit = 20, bool $unread_only = false): array {
        $where = $unread_only ? 'AND is_read = 0' : '';
        $sql = "SELECT * FROM notifications
                WHERE user_id = :user_id $where
                ORDER BY created_at DESC
                LIMIT :limit";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindValue(':limit',   $limit,   PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ----------------------------------------------------------
    //  Compter les notifications non lues
    // ----------------------------------------------------------
    public function countUnread(int $user_id): int {
        $stmt = $this->pdo->prepare(
            "SELECT COUNT(*) FROM notifications WHERE user_id = :uid AND is_read = 0"
        );
        $stmt->execute([':uid' => $user_id]);
        return (int) $stmt->fetchColumn();
    }

    // ----------------------------------------------------------
    //  Marquer une notification comme lue
    // ----------------------------------------------------------
    public function markAsRead(int $id, int $user_id): bool {
        $stmt = $this->pdo->prepare(
            "UPDATE notifications SET is_read = 1 WHERE id = :id AND user_id = :uid"
        );
        return $stmt->execute([':id' => $id, ':uid' => $user_id]);
    }

    // ----------------------------------------------------------
    //  Marquer toutes les notifications comme lues
    // ----------------------------------------------------------
    public function markAllAsRead(int $user_id): bool {
        $stmt = $this->pdo->prepare(
            "UPDATE notifications SET is_read = 1 WHERE user_id = :uid AND is_read = 0"
        );
        return $stmt->execute([':uid' => $user_id]);
    }

    // ----------------------------------------------------------
    //  Créer une notification "nouvelle réponse à un avis"
    //  Appelé depuis ResponseController lors d'un POST
    // ----------------------------------------------------------
    public function createNewResponse(int $review_owner_id, int $review_id, int $actor_id, string $actor_name): bool {
        // Ne pas notifier si l'auteur répond à son propre avis
        if ($review_owner_id === $actor_id) return true;

        $message = $actor_name . ' a répondu à votre avis';
        $stmt = $this->pdo->prepare(
            "INSERT INTO notifications (user_id, type, review_id, actor_id, actor_name, message)
             VALUES (:uid, 'new_response', :rid, :aid, :aname, :msg)"
        );
        return $stmt->execute([
            ':uid'   => $review_owner_id,
            ':rid'   => $review_id,
            ':aid'   => $actor_id,
            ':aname' => $actor_name,
            ':msg'   => $message,
        ]);
    }

    // ----------------------------------------------------------
    //  Créer une notification "réaction à une réponse"
    //  Appelé depuis ResponseController lors d'un POST?action=reaction
    // ----------------------------------------------------------
    public function createReaction(int $response_owner_id, int $response_id, int $actor_id, string $actor_name, string $type): bool {
        // Ne pas notifier si on réagit à sa propre réponse
        if ($response_owner_id === $actor_id) return true;

        $label   = $type === 'like' ? 'aimé' : 'pas aimé';
        $message = $actor_name . ' a ' . $label . ' votre réponse';
        $stmt = $this->pdo->prepare(
            "INSERT INTO notifications (user_id, type, response_id, actor_id, actor_name, message)
             VALUES (:uid, 'reaction', :resp_id, :aid, :aname, :msg)"
        );
        return $stmt->execute([
            ':uid'     => $response_owner_id,
            ':resp_id' => $response_id,
            ':aid'     => $actor_id,
            ':aname'   => $actor_name,
            ':msg'     => $message,
        ]);
    }

    // ----------------------------------------------------------
    //  Supprimer les notifications de plus de 30 jours (nettoyage)
    // ----------------------------------------------------------
    public function purgeOld(): void {
        $this->pdo->exec(
            "DELETE FROM notifications WHERE created_at < NOW() - INTERVAL 30 DAY"
        );
    }
}
?>