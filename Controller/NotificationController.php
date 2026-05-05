<?php
// ============================================================
//  FoodWise · NotificationController.php
//  API REST — Notifications
//
//  GET  ?user_id=1              → liste + compteur non lus
//  GET  ?user_id=1&count=1      → compteur seul (polling léger)
//  POST action=read             → marquer une notif comme lue
//  POST action=read_all         → tout marquer comme lu
// ============================================================

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

// -- Connexion PDO (centralisée ici — à remplacer par require config/database.php)
try {
    $pdo = new PDO("mysql:host=localhost;dbname=foodwise;charset=utf8mb4", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE,            PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Erreur BDD: ' . $e->getMessage()]);
    exit;
}

require_once __DIR__ . '/../Model/NotificationModel.php';
$model = new NotificationModel($pdo);

$method = $_SERVER['REQUEST_METHOD'];

try {

    // ──────────────────────────────────────────────────────────
    //  GET : récupérer les notifications / compter les non lues
    // ──────────────────────────────────────────────────────────
    if ($method === 'GET') {

        $user_id = isset($_GET['user_id']) ? (int) $_GET['user_id'] : 0;
        if ($user_id <= 0) {
            echo json_encode(['success' => false, 'error' => 'user_id requis']);
            exit;
        }

        // Mode léger : juste le compteur (utilisé par le polling)
        if (isset($_GET['count'])) {
            $unread = $model->countUnread($user_id);
            echo json_encode(['success' => true, 'unread' => $unread]);
            exit;
        }

        // Mode complet : liste + compteur
        $notifications = $model->getByUser($user_id, 20);
        $unread        = $model->countUnread($user_id);

        // Formatage des dates en lisible français
        foreach ($notifications as &$n) {
            $n['time_ago'] = timeAgo($n['created_at']);
            $n['icon']     = $n['type'] === 'new_response' ? '💬' : ($n['type'] === 'reaction' ? '👍' : '🔔');
        }

        echo json_encode([
            'success'       => true,
            'unread'        => $unread,
            'notifications' => $notifications,
        ]);
        exit;
    }

    // ──────────────────────────────────────────────────────────
    //  POST : marquer comme lu
    // ──────────────────────────────────────────────────────────
    if ($method === 'POST') {

        $input   = json_decode(file_get_contents('php://input'), true) ?? [];
        $action  = $input['action']  ?? '';
        $user_id = isset($input['user_id']) ? (int) $input['user_id'] : 0;

        if ($user_id <= 0) {
            echo json_encode(['success' => false, 'error' => 'user_id requis']);
            exit;
        }

        if ($action === 'read_all') {
            $model->markAllAsRead($user_id);
            echo json_encode(['success' => true, 'message' => 'Toutes les notifications lues']);
            exit;
        }

        if ($action === 'read') {
            $id = isset($input['id']) ? (int) $input['id'] : 0;
            if ($id <= 0) {
                echo json_encode(['success' => false, 'error' => 'id requis']);
                exit;
            }
            $model->markAsRead($id, $user_id);
            echo json_encode(['success' => true]);
            exit;
        }

        echo json_encode(['success' => false, 'error' => 'Action inconnue']);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

// ──────────────────────────────────────────────────────────────
//  Utilitaire : "il y a X minutes / heures / jours"
// ──────────────────────────────────────────────────────────────
function timeAgo(string $datetime): string {
    $diff = time() - strtotime($datetime);
    if ($diff < 60)       return 'À l\'instant';
    if ($diff < 3600)     return 'Il y a ' . floor($diff / 60) . ' min';
    if ($diff < 86400)    return 'Il y a ' . floor($diff / 3600) . ' h';
    if ($diff < 604800)   return 'Il y a ' . floor($diff / 86400) . ' j';
    return date('d/m/Y', strtotime($datetime));
}
?>