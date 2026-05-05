<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

try {
    $pdo = new PDO("mysql:host=localhost;dbname=foodwise;charset=utf8mb4", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Erreur BDD: ' . $e->getMessage()]);
    exit;
}

// Deux modes :
// - Global (BackOffice) : aucun user_id → stats globales de la plateforme
// - User (FrontOffice)  : user_id fourni → stats d'un utilisateur
$user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;

function tableHasColumn(PDO $pdo, string $table, string $column): bool {
    try {
        $db = $pdo->query('SELECT DATABASE()')->fetchColumn();
        if (!$db) return false;
        $stmt = $pdo->prepare(
            "SELECT 1
             FROM INFORMATION_SCHEMA.COLUMNS
             WHERE TABLE_SCHEMA = :db
               AND TABLE_NAME   = :t
               AND COLUMN_NAME  = :c
             LIMIT 1"
        );
        $stmt->execute([':db' => $db, ':t' => $table, ':c' => $column]);
        return (bool)$stmt->fetchColumn();
    } catch (\Throwable $e) {
        return false;
    }
}

// Stats par défaut
$stats = [
    'mode'            => $user_id > 0 ? 'user' : 'global',
    'total_reviews'   => 0,
    'avg_rating'      => 0,
    'total_responses' => 0,
    'active_users'    => 0,
    // Champs "user-only"
    'likes_received'  => 0,
    'popularity_top'  => null, // ex: 15 (=> "Top 15%")
];

try {
    // --------------------------------------------------------
    // Global stats (BackOffice)
    // --------------------------------------------------------
    if ($user_id <= 0) {
        // Total avis + moyenne note (si rating existe)
        if (tableHasColumn($pdo, 'reviews', 'rating')) {
            $row = $pdo->query("SELECT COUNT(*) AS c, AVG(rating) AS a FROM reviews")->fetch() ?: ['c' => 0, 'a' => null];
            $stats['total_reviews'] = (int)($row['c'] ?? 0);
            $stats['avg_rating']    = $row['a'] === null ? 0 : round((float)$row['a'], 1);
        } else {
            $stats['total_reviews'] = (int)($pdo->query("SELECT COUNT(*) FROM reviews")->fetchColumn());
            $stats['avg_rating'] = 0;
        }

        // Total réponses
        if (tableHasColumn($pdo, 'responses', 'id')) {
            $stats['total_responses'] = (int)($pdo->query("SELECT COUNT(*) FROM responses")->fetchColumn());
        }

        // Auteurs uniques (si user_id existe)
        if (tableHasColumn($pdo, 'reviews', 'user_id')) {
            $stats['active_users'] = (int)($pdo->query("SELECT COUNT(DISTINCT user_id) FROM reviews WHERE user_id IS NOT NULL")->fetchColumn());
        } else {
            $stats['active_users'] = 0;
        }

        echo json_encode(['success' => true, 'data' => $stats]);
        exit;
    }

    // --------------------------------------------------------
    // User stats (FrontOffice)
    // --------------------------------------------------------
    if (!tableHasColumn($pdo, 'reviews', 'user_id')) {
        echo json_encode(['success' => true, 'data' => $stats]);
        exit;
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) AS c, AVG(rating) AS a FROM reviews WHERE user_id = :uid");
    $stmt->execute([':uid' => $user_id]);
    $row = $stmt->fetch() ?: ['c' => 0, 'a' => null];
    $stats['total_reviews'] = (int)($row['c'] ?? 0);
    $stats['avg_rating']    = $row['a'] === null ? 0 : round((float)$row['a'], 1);

    if (tableHasColumn($pdo, 'responses', 'user_id')) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM responses WHERE user_id = :uid");
        $stmt->execute([':uid' => $user_id]);
        $stats['total_responses'] = (int)$stmt->fetchColumn();
    }

    $stmt = $pdo->prepare("
        SELECT COALESCE(COUNT(rl.id), 0) AS likes_received
        FROM reviews r
        LEFT JOIN review_likes rl ON rl.review_id = r.id
        WHERE r.user_id = :uid
    ");
    $stmt->execute([':uid' => $user_id]);
    $stats['likes_received'] = (int)$stmt->fetchColumn();

    $stmt = $pdo->query("
        SELECT r.user_id AS uid, COUNT(rl.id) AS likes_received
        FROM reviews r
        LEFT JOIN review_likes rl ON rl.review_id = r.id
        WHERE r.user_id IS NOT NULL
        GROUP BY r.user_id
    ");
    $all = $stmt->fetchAll();
    if ($all && count($all) >= 2) {
        $likesByUser = [];
        foreach ($all as $u) {
            $likesByUser[(int)$u['uid']] = (int)$u['likes_received'];
        }
        $myLikes = $likesByUser[$user_id] ?? 0;
        $values = array_values($likesByUser);
        sort($values);
        $n = count($values);

        $lessOrEqual = 0;
        foreach ($values as $v) {
            if ($v <= $myLikes) $lessOrEqual++;
        }
        $percentile = ($lessOrEqual / $n) * 100.0;
        $top = (int)max(1, min(100, round(100 - $percentile + 1)));
        $stats['popularity_top'] = $top;
    }

    echo json_encode(['success' => true, 'data' => $stats]);
} catch(Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>

