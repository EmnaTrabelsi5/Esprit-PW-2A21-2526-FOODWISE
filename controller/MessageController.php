<?php
declare(strict_types=1);

class MessageController
{
    private PDO $pdo;
    private MessageModel $messageModel;
    private UtilisateurModel $userModel;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->messageModel = new MessageModel($pdo);
        $this->userModel = new UtilisateurModel($pdo);
    }

    /**
     * Vérifie si l'utilisateur est connecté
     */
    private function getCurrentUser(): ?array
    {
        if (!isset($_SESSION['user_id'])) {
            return null;
        }
        return $this->userModel->findById((int) $_SESSION['user_id']);
    }

    /**
     * Affiche l'annuaire des utilisateurs
     */
    public function usersList(): void
    {
        $user = $this->getCurrentUser();
        if ($user === null) {
            redirect(buildRoute('module2.front.connexion'));
        }

        $users = $this->messageModel->getAllUsers((int) $user['id']);
        
        $pageTitle = 'Annuaire des utilisateurs';
        $activeNav = 'messages';
        require __DIR__ . '/../views/module2/front/users_list.php';
    }

    /**
     * Affiche la conversation avec un utilisateur
     */
    public function chat(): void
    {
        $user = $this->getCurrentUser();
        if ($user === null) {
            redirect(buildRoute('module2.front.connexion'));
        }

        $errors = [];
        $recipientId = isset($_GET['recipient_id']) ? (int) $_GET['recipient_id'] : null;

        if ($recipientId === null || $recipientId === (int) $user['id']) {
            redirect(buildRoute('module2.front.users_list'));
        }

        // Vérifier que le destinataire existe
        $recipient = $this->userModel->findById($recipientId);
        if ($recipient === null) {
            redirect(buildRoute('module2.front.users_list'));
        }

        // Récupérer la conversation
        $conversation = $this->messageModel->getConversation((int) $user['id'], $recipientId);

        // Marquer les messages comme lus
        $this->messageModel->markConversationAsRead((int) $user['id'], $recipientId);

        // Traiter l'envoi de message
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $content = isset($_POST['content']) ? trim((string) $_POST['content']) : '';

            if ($content === '') {
                $errors['content'] = 'Le message ne peut pas être vide.';
            } else {
                if ($this->messageModel->sendMessage((int) $user['id'], $recipientId, $content)) {
                    // Rafraîchir la conversation
                    $conversation = $this->messageModel->getConversation((int) $user['id'], $recipientId);
                } else {
                    $errors['global'] = 'Erreur lors de l\'envoi du message.';
                }
            }
        }

        $pageTitle = 'Conversation avec ' . htmlspecialchars($recipient['prenom'] . ' ' . $recipient['nom'], ENT_QUOTES, 'UTF-8');
        $activeNav = 'messages';
        require __DIR__ . '/../views/module2/front/chat.php';
    }

    /**
     * Retourne la liste des conversations en JSON (pour mise à jour dynamique)
     */
    public function getConversationsList(): void
    {
        $user = $this->getCurrentUser();
        if ($user === null) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }

        $conversations = $this->messageModel->getConversationList((int) $user['id']);
        
        header('Content-Type: application/json');
        echo json_encode($conversations);
    }

    /**
     * Retourne le nombre de messages non lus
     */
    public function getUnreadCount(): void
    {
        $user = $this->getCurrentUser();
        if ($user === null) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }

        $count = $this->messageModel->getUnreadCount((int) $user['id']);
        
        header('Content-Type: application/json');
        echo json_encode(['unread_count' => $count]);
    }
}
