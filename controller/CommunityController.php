<?php
// ============================================================
//  FoodWise · CommunityController.php
//  Point d'entrée unifié pour le module Communauté
//  Intégré dans FOODWISE sans modifier le CSS existant
// ============================================================

require_once __DIR__ . '/../model/NotificationModel.php';
require_once __DIR__ . '/../model/MentionModel.php';

// ── Fonctions utilitaires (niveau fichier pour éviter redéfinition) ──

if (!function_exists('table_has_column_comm')) {
    function table_has_column_comm(PDO $pdo, string $table, string $column): bool {
        try {
            $db = $pdo->query('SELECT DATABASE()')->fetchColumn();
            if (!$db) return false;
            $stmt = $pdo->prepare(
                "SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
                 WHERE TABLE_SCHEMA = :db AND TABLE_NAME = :t AND COLUMN_NAME = :c LIMIT 1"
            );
            $stmt->execute([':db' => $db, ':t' => $table, ':c' => $column]);
            return (bool)$stmt->fetchColumn();
        } catch (Throwable $e) { return false; }
    }
}

if (!function_exists('since_to_datetime_comm')) {
    function since_to_datetime_comm(string $since): ?string {
        $since = strtolower(trim($since));
        if ($since === '' || $since === 'all') return null;
        if ($since === 'day')   return (new DateTimeImmutable('-1 day'))->format('Y-m-d H:i:s');
        if ($since === 'week')  return (new DateTimeImmutable('-7 day'))->format('Y-m-d H:i:s');
        if ($since === 'month') return (new DateTimeImmutable('-1 month'))->format('Y-m-d H:i:s');
        if ($since === 'year')  return (new DateTimeImmutable('-1 year'))->format('Y-m-d H:i:s');
        return null;
    }
}

if (!function_exists('timeAgo_comm')) {
    function timeAgo_comm(string $datetime): string {
        $diff = time() - strtotime($datetime);
        if ($diff < 60)     return "À l'instant";
        if ($diff < 3600)   return 'Il y a ' . floor($diff / 60) . ' min';
        if ($diff < 86400)  return 'Il y a ' . floor($diff / 3600) . ' h';
        if ($diff < 604800) return 'Il y a ' . floor($diff / 86400) . ' j';
        return date('d/m/Y', strtotime($datetime));
    }
}

class CommunityController {

    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    private function getCurrentUserContext(): array {
        $userId = (int)($_SESSION['user_id'] ?? 0);
        if ($userId <= 0) {
            return ['id' => 0, 'display_name' => 'Utilisateur', 'username' => 'utilisateur'];
        }

        $stmt = $this->pdo->prepare("SELECT id, nom, prenom FROM utilisateurs WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $userId]);
        $u = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

        $prenom = trim((string)($u['prenom'] ?? ''));
        $nom = trim((string)($u['nom'] ?? ''));
        $display = trim($prenom . ' ' . $nom);
        if ($display === '') $display = 'Utilisateur';

        $username = strtolower(trim($prenom . '_' . $nom));
        $username = preg_replace('/\s+/', '_', $username);
        $username = preg_replace('/[^a-z0-9_\-]/i', '', (string)$username);
        if ($username === '') $username = 'utilisateur' . $userId;

        return ['id' => $userId, 'display_name' => $display, 'username' => $username];
    }

    // ── Front : page principale communauté ──────────────────
    public function indexFront(): void {
        $pageTitle = 'Communauté';
        $activeNav = 'community';
        require_once __DIR__ . '/../view/community/front/index.php';
    }

    // ── Back : page admin avis & réponses ───────────────────
    public function indexBack(): void {
        $pageTitle = 'Admin — Avis & Réponses';
        $activeNav = 'community';
        $backoffice = true;
        require_once __DIR__ . '/../view/community/back/manageReviews.php';
    }

    // ─────────────────────────────────────────────────────────
    //  API REST — Reviews
    // ─────────────────────────────────────────────────────────
    public function apiReviews(): void {
        header('Content-Type: application/json');
        $pdo = $this->pdo;
        $method = $_SERVER['REQUEST_METHOD'];

        try {
            switch($method) {
                case 'GET':
                    if (isset($_GET['recipes'])) {
                        $stmt = $pdo->query("
                            SELECT id_recette, nom
                            FROM recette
                            WHERE nom IS NOT NULL AND TRIM(nom) <> ''
                            ORDER BY nom ASC
                        ");
                        echo json_encode(['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
                        break;
                    }

                    if (isset($_GET['trends'])) {
                        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
                        $limit = max(1, min(50, $limit));
                        $stmt = $pdo->prepare("
                            SELECT recipe_name, COUNT(*) AS reviews_count
                            FROM reviews
                            WHERE recipe_name IS NOT NULL AND TRIM(recipe_name) <> ''
                            GROUP BY recipe_name
                            ORDER BY reviews_count DESC, recipe_name ASC
                            LIMIT :lim
                        ");
                        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
                        $stmt->execute();
                        echo json_encode(['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
                        break;
                    }

                    if (isset($_GET['top'])) {
                        $stmt = $pdo->query("
                            SELECT r.id, r.recipe_name, r.title, r.content, r.rating, r.created_at,
                                   CONCAT(COALESCE(u.prenom, ''), ' ', COALESCE(u.nom, '')) AS username,
                                   COALESCE(COUNT(rl.id), 0) as likes_count,
                                   (SELECT COUNT(*) FROM responses WHERE review_id = r.id) as responses_count
                            FROM reviews r
                            LEFT JOIN utilisateurs u ON u.id = r.user_id
                            LEFT JOIN review_likes rl ON r.id = rl.review_id
                            GROUP BY r.id
                            ORDER BY likes_count DESC, responses_count DESC, r.created_at DESC
                            LIMIT 3
                        ");
                        echo json_encode(['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
                        break;
                    }

                    $sort = isset($_GET['sort']) ? strtolower((string)$_GET['sort']) : 'date';
                    $orderBy = "r.created_at DESC";
                    if ($sort === 'likes')     $orderBy = "likes_count DESC, r.created_at DESC";
                    elseif ($sort === 'responses') $orderBy = "responses_count DESC, r.created_at DESC";

                    $q           = isset($_GET['q'])            ? trim((string)$_GET['q']) : '';
                    $sinceKey    = isset($_GET['since'])         ? strtolower(trim((string)$_GET['since'])) : 'all';
                    $sinceDt     = since_to_datetime_comm($sinceKey);
                    $minRating   = max(0, min(5, (int)($_GET['min_rating']   ?? 0)));
                    $minResponses= max(0, (int)($_GET['min_responses'] ?? 0));
                    $minLikes    = max(0, (int)($_GET['min_likes']    ?? 0));

                    $hasTitle = table_has_column_comm($pdo, 'reviews', 'title');
                    $sql = "SELECT r.*, CONCAT(COALESCE(u.prenom, ''), ' ', COALESCE(u.nom, '')) AS username,
                                   COALESCE(COUNT(rl.id), 0) as likes_count,
                                   (SELECT COUNT(*) FROM responses WHERE review_id = r.id) as responses_count
                            FROM reviews r
                            LEFT JOIN review_likes rl ON r.id = rl.review_id
                            LEFT JOIN utilisateurs u ON u.id = r.user_id WHERE 1=1";
                    $params = [];

                    if ($sinceDt) { $sql .= " AND r.created_at >= :since_dt"; $params[':since_dt'] = $sinceDt; }
                    if ($minRating > 0 && table_has_column_comm($pdo, 'reviews', 'rating')) {
                        $sql .= " AND r.rating >= :min_rating"; $params[':min_rating'] = $minRating;
                    }
                    if ($minResponses > 0) {
                        $sql .= " AND (SELECT COUNT(*) FROM responses WHERE review_id = r.id) >= :min_responses";
                        $params[':min_responses'] = $minResponses;
                    }
                    if ($q !== '') {
                        if ($hasTitle) {
                            $sql .= " AND (r.recipe_name LIKE :q1 OR r.content LIKE :q2 OR r.title LIKE :q3)";
                            $like = '%'.$q.'%';
                            $params[':q1'] = $like; $params[':q2'] = $like; $params[':q3'] = $like;
                        } else {
                            $sql .= " AND (r.recipe_name LIKE :q1 OR r.content LIKE :q2)";
                            $like = '%'.$q.'%'; $params[':q1'] = $like; $params[':q2'] = $like;
                        }
                    }
                    $sql .= " GROUP BY r.id";
                    if ($minLikes > 0) { $sql .= " HAVING COALESCE(COUNT(rl.id), 0) >= :min_likes"; $params[':min_likes'] = $minLikes; }
                    $sql .= " ORDER BY $orderBy";

                    $stmt = $pdo->prepare($sql);
                    $stmt->execute($params);
                    echo json_encode(['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
                    break;

                case 'POST':
                    $input = json_decode(file_get_contents('php://input'), true);
                    $actor = $this->getCurrentUserContext();
                    $errors = [];
                    if (empty($input['recipe_name'])) $errors[] = "Choisissez une recette";
                    if (strlen($input['content'] ?? '') < 5) $errors[] = "L'avis doit faire au moins 5 caractères";
                    if (($input['rating'] ?? 0) < 1 || ($input['rating'] ?? 0) > 5) $errors[] = "La note doit être entre 1 et 5";
                    if (!empty($errors)) { echo json_encode(['success' => false, 'errors' => $errors]); break; }
                    $hasUserIdCol = table_has_column_comm($pdo, 'reviews', 'user_id');
                    $hasUsernameCol = table_has_column_comm($pdo, 'reviews', 'username');
                    if ($hasUserIdCol && $hasUsernameCol) {
                        $stmt = $pdo->prepare("INSERT INTO reviews (user_id, username, recipe_name, title, content, rating, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
                        $stmt->execute([$actor['id'], $actor['username'], $input['recipe_name'], $input['title'] ?? null, $input['content'], $input['rating']]);
                    } elseif ($hasUserIdCol) {
                        $stmt = $pdo->prepare("INSERT INTO reviews (user_id, recipe_name, title, content, rating, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
                        $stmt->execute([$actor['id'], $input['recipe_name'], $input['title'] ?? null, $input['content'], $input['rating']]);
                    } else {
                        $stmt = $pdo->prepare("INSERT INTO reviews (recipe_name, title, content, rating, created_at) VALUES (?, ?, ?, ?, NOW())");
                        $stmt->execute([$input['recipe_name'], $input['title'] ?? null, $input['content'], $input['rating']]);
                    }
                    echo json_encode(['success' => true, 'message' => 'Avis ajouté']);
                    break;

                case 'PUT':
                    $input = json_decode(file_get_contents('php://input'), true);
                    $stmt = $pdo->prepare("UPDATE reviews SET title = ?, content = ?, rating = ? WHERE id = ?");
                    $stmt->execute([$input['title'], $input['content'], $input['rating'], $input['id']]);
                    echo json_encode(['success' => true, 'message' => 'Avis modifié']);
                    break;

                case 'DELETE':
                    $stmt = $pdo->prepare("DELETE FROM reviews WHERE id = ?");
                    $stmt->execute([$_GET['id'] ?? null]);
                    echo json_encode(['success' => true, 'message' => 'Avis supprimé']);
                    break;
            }
        } catch(Exception $e) {
            echo json_encode(['success' => false, 'errors' => [$e->getMessage()]]);
        }
    }

    // ─────────────────────────────────────────────────────────
    //  API REST — Responses
    // ─────────────────────────────────────────────────────────
    public function apiResponses(): void {
        header('Content-Type: application/json');
        $pdo = $this->pdo;
        $notifModel  = new NotificationModel($pdo);
        $mentionModel = new MentionModel($pdo);
        $method = $_SERVER['REQUEST_METHOD'];
        $action = $_GET['action'] ?? '';
        $actor = $this->getCurrentUserContext();

        if ($method === 'GET' && $action === 'users') {
            echo json_encode(['success' => true, 'data' => $mentionModel->getKnownAuthors()]);
            return;
        }

        try {
            switch($method) {
                case 'GET':
                    if ($action === 'reactions' && isset($_GET['response_id'])) {
                        $stmt = $pdo->prepare("SELECT SUM(CASE WHEN type='like' THEN 1 ELSE 0 END) as likes, SUM(CASE WHEN type='dislike' THEN 1 ELSE 0 END) as dislikes FROM response_reactions WHERE response_id = ?");
                        $stmt->execute([$_GET['response_id']]);
                        $counts = $stmt->fetch(PDO::FETCH_ASSOC);
                        $user_id = isset($_GET['user_id']) ? $_GET['user_id'] : ($_SESSION['user_id'] ?? 1);
                        $stmt2 = $pdo->prepare("SELECT type FROM response_reactions WHERE response_id = ? AND user_id = ?");
                        $stmt2->execute([$_GET['response_id'], $user_id]);
                        $userReaction = $stmt2->fetch(PDO::FETCH_ASSOC);
                        echo json_encode(['success' => true, 'data' => ['likes' => intval($counts['likes'] ?? 0), 'dislikes' => intval($counts['dislikes'] ?? 0), 'user_reaction' => $userReaction ? $userReaction['type'] : null]]);
                        break;
                    }
                    if (isset($_GET['review_id'])) {
                        $stmt = $pdo->prepare("
                            SELECT r.*, CONCAT(COALESCE(u.prenom, ''), ' ', COALESCE(u.nom, '')) AS username
                            FROM responses r
                            LEFT JOIN utilisateurs u ON u.id = r.user_id
                            WHERE r.review_id = ?
                            ORDER BY r.created_at ASC
                        ");
                        $stmt->execute([$_GET['review_id']]);
                        echo json_encode(['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
                        break;
                    }
                    if (isset($_GET['all'])) {
                        $stmt = $pdo->query("
                            SELECT r.*, CONCAT(COALESCE(u.prenom, ''), ' ', COALESCE(u.nom, '')) AS username
                            FROM responses r
                            LEFT JOIN utilisateurs u ON u.id = r.user_id
                            ORDER BY r.created_at DESC
                        ");
                        echo json_encode(['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
                        break;
                    }
                    echo json_encode(['success' => false, 'errors' => ['Paramètre manquant']]);
                    break;

                case 'POST':
                    if ($action === 'reaction') {
                        $input = json_decode(file_get_contents('php://input'), true);
                        if (!isset($input['response_id'], $input['type'])) {
                            echo json_encode(['success' => false, 'errors' => ['Paramètres manquants']]); break;
                        }
                        $input['user_id'] = $actor['id'];
                        $stmt = $pdo->prepare("SELECT id, type FROM response_reactions WHERE response_id = ? AND user_id = ?");
                        $stmt->execute([$input['response_id'], $input['user_id']]);
                        $existing = $stmt->fetch(PDO::FETCH_ASSOC);
                        if ($existing) {
                            if ($existing['type'] === $input['type']) {
                                $pdo->prepare("DELETE FROM response_reactions WHERE id = ?")->execute([$existing['id']]);
                                echo json_encode(['success' => true, 'action' => 'removed']);
                            } else {
                                $pdo->prepare("UPDATE response_reactions SET type = ? WHERE id = ?")->execute([$input['type'], $existing['id']]);
                                echo json_encode(['success' => true, 'action' => 'changed']);
                            }
                        } else {
                            $pdo->prepare("INSERT INTO response_reactions (response_id, user_id, type) VALUES (?, ?, ?)")->execute([$input['response_id'], $input['user_id'], $input['type']]);
                            $stmtOwner = $pdo->prepare("SELECT user_id FROM responses WHERE id = ?");
                            $stmtOwner->execute([$input['response_id']]);
                            $responseOwnerId = (int)$stmtOwner->fetchColumn();
                            if ($responseOwnerId > 0) {
                                $notifModel->createReaction($responseOwnerId, (int)$input['response_id'], (int)$actor['id'], (string)$actor['display_name'], (string)$input['type']);
                            }
                            echo json_encode(['success' => true, 'action' => 'added']);
                        }
                        break;
                    }
                    $input = json_decode(file_get_contents('php://input'), true);
                    $stmt = $pdo->prepare("INSERT INTO responses (review_id, user_id, content, parent_id, created_at) VALUES (?, ?, ?, ?, NOW())");
                    $stmt->execute([$input['review_id'], $actor['id'], $input['content'], $input['parent_id'] ?? null]);
                    $new_id = (int)$pdo->lastInsertId();
                    $mentioned = MentionModel::extractMentions($input['content']);
                    if (!empty($mentioned)) {
                        $mentionModel->saveMentions($new_id, $mentioned);
                        $actorName = (string)$actor['display_name'];
                        foreach ($mentioned as $uname) {
                            $mentionModel->notifyMention($uname, $new_id, (int)$actor['id'], $actorName);
                        }
                    }
                    $stmtOwner = $pdo->prepare("SELECT user_id FROM reviews WHERE id = ?");
                    $stmtOwner->execute([$input['review_id']]);
                    $ownerId = $stmtOwner->fetchColumn();
                    if ($ownerId) $notifModel->createNewResponse((int)$ownerId, (int)$input['review_id'], (int)$actor['id'], (string)$actor['display_name']);
                    echo json_encode(['success' => true, 'message' => 'Réponse ajoutée', 'id' => $new_id]);
                    break;

                case 'PUT':
                    $input = json_decode(file_get_contents('php://input'), true);
                    $pdo->prepare("UPDATE responses SET content = ?, updated_at = NOW() WHERE id = ?")->execute([$input['content'], $input['id']]);
                    echo json_encode(['success' => true, 'message' => 'Réponse modifiée']);
                    break;

                case 'DELETE':
                    if (!isset($_GET['id'])) { echo json_encode(['success' => false, 'errors' => ['ID manquant']]); break; }
                    $pdo->prepare("DELETE FROM responses WHERE id = ?")->execute([$_GET['id']]);
                    echo json_encode(['success' => true, 'message' => 'Réponse supprimée']);
                    break;
            }
        } catch(Exception $e) {
            echo json_encode(['success' => false, 'errors' => [$e->getMessage()]]);
        }
    }

    // ─────────────────────────────────────────────────────────
    //  API REST — Notifications
    // ─────────────────────────────────────────────────────────
    public function apiNotifications(): void {
        header('Content-Type: application/json');
        $pdo = $this->pdo;
        $model = new NotificationModel($pdo);
        $method = $_SERVER['REQUEST_METHOD'];

        try {
            if ($method === 'GET') {
                $user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : ($_SESSION['user_id'] ?? 0);
                if ($user_id <= 0) { echo json_encode(['success' => false, 'error' => 'user_id requis']); return; }
                if (isset($_GET['count'])) {
                    echo json_encode(['success' => true, 'unread' => $model->countUnread($user_id)]); return;
                }
                $notifications = $model->getByUser($user_id, 20);
                $unread = $model->countUnread($user_id);
                foreach ($notifications as &$n) {
                    $n['time_ago'] = timeAgo_comm($n['created_at']);
                    $n['icon'] = $n['type'] === 'new_response' ? '💬' : ($n['type'] === 'reaction' ? '👍' : '🔔');
                }
                echo json_encode(['success' => true, 'unread' => $unread, 'notifications' => $notifications]);
                return;
            }
            if ($method === 'POST') {
                $input = json_decode(file_get_contents('php://input'), true) ?? [];
                $action = $input['action'] ?? '';
                $user_id = isset($input['user_id']) ? (int)$input['user_id'] : ($_SESSION['user_id'] ?? 0);
                if ($user_id <= 0) { echo json_encode(['success' => false, 'error' => 'user_id requis']); return; }
                if ($action === 'read_all') { $model->markAllAsRead($user_id); echo json_encode(['success' => true]); return; }
                if ($action === 'read') {
                    $id = isset($input['id']) ? (int)$input['id'] : 0;
                    if ($id <= 0) { echo json_encode(['success' => false, 'error' => 'id requis']); return; }
                    $model->markAsRead($id, $user_id);
                    echo json_encode(['success' => true]); return;
                }
                echo json_encode(['success' => false, 'error' => 'Action inconnue']);
            }
        } catch(Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    // ─────────────────────────────────────────────────────────
    //  API REST — Review Likes
    // ─────────────────────────────────────────────────────────
    public function apiReviewLikes(): void {
        header('Content-Type: application/json');
        $pdo = $this->pdo;
        $method = $_SERVER['REQUEST_METHOD'];
        try {
            switch($method) {
                case 'POST':
                    $input = json_decode(file_get_contents('php://input'), true);
                    $review_id = $input['review_id'];
                    $user_id   = $input['user_id'] ?? ($_SESSION['user_id'] ?? 1);
                    $stmt = $pdo->prepare("SELECT id FROM review_likes WHERE review_id = ? AND user_id = ?");
                    $stmt->execute([$review_id, $user_id]);
                    if ($stmt->rowCount() > 0) {
                        $pdo->prepare("DELETE FROM review_likes WHERE review_id = ? AND user_id = ?")->execute([$review_id, $user_id]);
                        echo json_encode(['success' => true, 'action' => 'removed']);
                    } else {
                        $pdo->prepare("INSERT INTO review_likes (review_id, user_id) VALUES (?, ?)")->execute([$review_id, $user_id]);
                        echo json_encode(['success' => true, 'action' => 'added']);
                    }
                    break;
                case 'GET':
                    if (isset($_GET['review_id'])) {
                        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM review_likes WHERE review_id = ?");
                        $stmt->execute([$_GET['review_id']]);
                        echo json_encode(['success' => true, 'likes' => $stmt->fetch(PDO::FETCH_ASSOC)['count']]);
                    }
                    break;
            }
        } catch(Exception $e) {
            echo json_encode(['success' => false, 'errors' => [$e->getMessage()]]);
        }
    }

    // ─────────────────────────────────────────────────────────
    //  API REST — Stats
    // ─────────────────────────────────────────────────────────
    public function apiStats(): void {
        header('Content-Type: application/json');
        $pdo = $this->pdo;
        $user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;

        $stats = ['mode' => $user_id > 0 ? 'user' : 'global', 'total_reviews' => 0, 'avg_rating' => 0, 'total_responses' => 0, 'active_users' => 0, 'likes_received' => 0, 'popularity_top' => null];
        try {
            if ($user_id <= 0) {
                // Global stats
                $row = $pdo->query("SELECT COUNT(*) AS c, AVG(rating) AS a FROM reviews")->fetch(PDO::FETCH_ASSOC);
                $stats['total_reviews'] = (int)($row['c'] ?? 0);
                $stats['avg_rating'] = isset($row['a']) && $row['a'] !== null ? round((float)$row['a'], 1) : 0;

                $stats['total_responses'] = (int)$pdo->query("SELECT COUNT(*) FROM responses")->fetchColumn();
                $stats['active_users'] = (int)$pdo->query("SELECT COUNT(DISTINCT user_id) FROM reviews WHERE user_id IS NOT NULL")->fetchColumn();
            } else {
                // Stats pour un utilisateur spécifique (si besoin plus tard)
                $stmt = $pdo->prepare("SELECT COUNT(*) AS c, AVG(rating) AS a FROM reviews WHERE user_id = ?");
                $stmt->execute([$user_id]);
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                $stats['total_reviews'] = (int)($row['c'] ?? 0);
                $stats['avg_rating'] = isset($row['a']) && $row['a'] !== null ? round((float)$row['a'], 1) : 0;

                $stmt = $pdo->prepare("SELECT COUNT(*) FROM responses WHERE user_id = ?");
                $stmt->execute([$user_id]);
                $stats['total_responses'] = (int)$stmt->fetchColumn();
            }
            echo json_encode(['success' => true, 'data' => $stats]);
        } catch(Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }
}

