<?php
// ============================================================
//  FoodWise · MentionModel.php
//  Gestion des @mentions — sans table users
// ============================================================

class MentionModel {

    private $pdo;
    private ?string $dbName = null;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
        $this->dbName = $this->detectDatabaseName();
    }

    // ----------------------------------------------------------
    //  Extraire tous les @username d'un texte
    //  Ex: "Bravo @jean_dupont !" → ['jean_dupont']
    // ----------------------------------------------------------
    public static function extractMentions(string $text): array {
        preg_match_all('/@([a-zA-ZÀ-ÿ0-9_\-]+)/u', $text, $matches);
        return array_unique($matches[1]);
    }

    // ----------------------------------------------------------
    //  Enregistrer les mentions après un INSERT de réponse
    // ----------------------------------------------------------
    public function saveMentions(int $response_id, array $usernames): void {
        if (empty($usernames)) return;

        $stmt = $this->pdo->prepare(
            "INSERT INTO mentions (response_id, mentioned_username)
             VALUES (:rid, :uname)"
        );
        foreach ($usernames as $username) {
            $stmt->execute([':rid' => $response_id, ':uname' => $username]);
        }
    }

    // ----------------------------------------------------------
    //  Créer une notification de mention
    //  Résolution via table utilisateurs (prenom_nom / nomprenom / nom / prenom)
    // ----------------------------------------------------------
    public function notifyMention(
        string $mentioned_username,
        int    $response_id,
        int    $actor_id,
        string $actor_name
    ): void {
        $normalized = strtolower(trim($mentioned_username));
        if ($normalized === '') return;

        $sql = "SELECT id FROM utilisateurs
                WHERE LOWER(CONCAT(COALESCE(prenom, ''), '_', COALESCE(nom, ''))) = :u1
                   OR LOWER(CONCAT(COALESCE(prenom, ''), COALESCE(nom, ''))) = :u2
                   OR LOWER(COALESCE(prenom, '')) = :u3
                   OR LOWER(COALESCE(nom, '')) = :u4
                LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':u1' => $normalized,
            ':u2' => $normalized,
            ':u3' => $normalized,
            ':u4' => $normalized,
        ]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) return; // username inconnu → pas de notif
        $mentioned_user_id = (int) $row['user_id'];

        // Ne pas notifier si on se mentionne soi-même
        if ($mentioned_user_id === $actor_id) return;

        $message = $actor_name . ' vous a mentionné dans une réponse';
        $insert = $this->pdo->prepare(
            "INSERT INTO notifications
                (user_id, type, response_id, actor_id, actor_name, message)
             VALUES
                (:uid, 'mention', :resp_id, :aid, :aname, :msg)"
        );
        $insert->execute([
            ':uid'     => $mentioned_user_id,
            ':resp_id' => $response_id,
            ':aid'     => $actor_id,
            ':aname'   => $actor_name,
            ':msg'     => $message,
        ]);
    }

    // ----------------------------------------------------------
    //  Récupérer tous les auteurs connus pour l'autocomplete @
    //  Source : table utilisateurs
    //  Retourne [{ user_id, username }, ...]
    // ----------------------------------------------------------
    public function getKnownAuthors(): array {
        $stmt = $this->pdo->query(
            "SELECT id AS user_id,
                    LOWER(TRIM(CONCAT(COALESCE(prenom, ''), '_', COALESCE(nom, '')))) AS username
             FROM utilisateurs
             WHERE TRIM(CONCAT(COALESCE(prenom, ''), COALESCE(nom, ''))) <> ''
             ORDER BY username"
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ============================================================
    //  Helpers schéma (évite SQLSTATE[42S22])
    // ============================================================

    private function detectDatabaseName(): ?string {
        try {
            $name = $this->pdo->query('SELECT DATABASE()')->fetchColumn();
            return $name ? (string)$name : null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function tableHasColumn(string $table, string $column): bool {
        if (!$this->dbName) return false;
        try {
            $stmt = $this->pdo->prepare(
                "SELECT 1
                 FROM INFORMATION_SCHEMA.COLUMNS
                 WHERE TABLE_SCHEMA = :db
                   AND TABLE_NAME   = :t
                   AND COLUMN_NAME  = :c
                 LIMIT 1"
            );
            $stmt->execute([':db' => $this->dbName, ':t' => $table, ':c' => $column]);
            return (bool)$stmt->fetchColumn();
        } catch (\Throwable $e) {
            return false;
        }
    }

}
?>
