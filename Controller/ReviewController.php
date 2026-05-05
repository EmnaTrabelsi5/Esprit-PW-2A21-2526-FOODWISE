<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

try {
    $pdo = new PDO("mysql:host=localhost;dbname=foodwise;charset=utf8", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die(json_encode(['success' => false, 'errors' => ['Erreur BDD: ' . $e->getMessage()]]));
}

$method = $_SERVER['REQUEST_METHOD'];

function table_has_column(PDO $pdo, string $table, string $column): bool {
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
    } catch (Throwable $e) {
        return false;
    }
}

function since_to_datetime(string $since): ?string {
    $since = strtolower(trim($since));
    if ($since === '' || $since === 'all') return null;
    if ($since === 'day') return (new DateTimeImmutable('-1 day'))->format('Y-m-d H:i:s');
    if ($since === 'week') return (new DateTimeImmutable('-7 day'))->format('Y-m-d H:i:s');
    if ($since === 'month') return (new DateTimeImmutable('-1 month'))->format('Y-m-d H:i:s');
    if ($since === 'year') return (new DateTimeImmutable('-1 year'))->format('Y-m-d H:i:s');
    return null;
}

try {
    switch($method) {
        case 'GET':
            // TENDANCES (Twitter-like) : top recettes par nombre d'avis
            if (isset($_GET['trends'])) {
                $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
                if ($limit < 1) $limit = 1;
                if ($limit > 50) $limit = 50;

                $stmt = $pdo->prepare("
                    SELECT
                        recipe_name,
                        COUNT(*) AS reviews_count
                    FROM reviews
                    WHERE recipe_name IS NOT NULL
                      AND TRIM(recipe_name) <> ''
                    GROUP BY recipe_name
                    ORDER BY reviews_count DESC, recipe_name ASC
                    LIMIT :lim
                ");
                $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
                $stmt->execute();
                $trends = $stmt->fetchAll(PDO::FETCH_ASSOC);
                echo json_encode(['success' => true, 'data' => $trends]);
                break;
            }

            // NOUVEAU : Récupérer le TOP 3 des avis likés
            if(isset($_GET['top'])) {
                $stmt = $pdo->query("
                    SELECT 
                        r.id,
                        r.recipe_name,
                        r.title,
                        r.content,
                        r.rating,
                        r.created_at,
                        COALESCE(COUNT(rl.id), 0) as likes_count,
                        (SELECT COUNT(*) FROM responses WHERE review_id = r.id) as responses_count
                    FROM reviews r
                    LEFT JOIN review_likes rl ON r.id = rl.review_id
                    GROUP BY r.id
                    ORDER BY likes_count DESC, responses_count DESC, r.created_at DESC
                    LIMIT 3
                ");
                $top = $stmt->fetchAll(PDO::FETCH_ASSOC);
                echo json_encode(['success' => true, 'data' => $top]);
                break;
            }
            
            // MODIFIÉ : Lire tous les avis (avec compteurs + tri + filtres)
            $sort = isset($_GET['sort']) ? strtolower((string)$_GET['sort']) : 'date';
            $orderBy = "r.created_at DESC";
            if ($sort === 'likes') {
                $orderBy = "likes_count DESC, r.created_at DESC";
            } elseif ($sort === 'responses') {
                $orderBy = "responses_count DESC, r.created_at DESC";
            }

            // Filtres (style Reddit)
            $q = isset($_GET['q']) ? trim((string)$_GET['q']) : '';
            $sinceKey = isset($_GET['since']) ? strtolower(trim((string)$_GET['since'])) : 'all';
            $sinceDt = since_to_datetime($sinceKey);

            $minRating = isset($_GET['min_rating']) ? (int)$_GET['min_rating'] : 0;
            if ($minRating < 0) $minRating = 0;
            if ($minRating > 5) $minRating = 5;

            $minResponses = isset($_GET['min_responses']) ? (int)$_GET['min_responses'] : 0;
            if ($minResponses < 0) $minResponses = 0;

            $minLikes = isset($_GET['min_likes']) ? (int)$_GET['min_likes'] : 0;
            if ($minLikes < 0) $minLikes = 0;

            $hasTitle = table_has_column($pdo, 'reviews', 'title');

            $sql = "
                SELECT 
                    r.*,
                    COALESCE(COUNT(rl.id), 0) as likes_count,
                    (SELECT COUNT(*) FROM responses WHERE review_id = r.id) as responses_count
                FROM reviews r
                LEFT JOIN review_likes rl ON r.id = rl.review_id
                WHERE 1=1
            ";

            $params = [];

            if ($sinceDt !== null) {
                $sql .= " AND r.created_at >= :since_dt";
                $params[':since_dt'] = $sinceDt;
            }

            if ($minRating > 0 && table_has_column($pdo, 'reviews', 'rating')) {
                $sql .= " AND r.rating >= :min_rating";
                $params[':min_rating'] = $minRating;
            }

            if ($minResponses > 0) {
                $sql .= " AND (SELECT COUNT(*) FROM responses WHERE review_id = r.id) >= :min_responses";
                $params[':min_responses'] = $minResponses;
            }

            if ($q !== '') {
                // Recherche texte (recette + contenu + titre si présent)
                if ($hasTitle) {
                    $sql .= " AND (
                        r.recipe_name LIKE :q1
                        OR r.content LIKE :q2
                        OR r.title LIKE :q3
                    )";
                    $like = '%' . $q . '%';
                    $params[':q1'] = $like;
                    $params[':q2'] = $like;
                    $params[':q3'] = $like;
                } else {
                    $sql .= " AND (
                        r.recipe_name LIKE :q1
                        OR r.content LIKE :q2
                    )";
                    $like = '%' . $q . '%';
                    $params[':q1'] = $like;
                    $params[':q2'] = $like;
                }
            }

            // Toujours grouper (nécessaire pour likes_count + filtres likes)
            $sql .= " GROUP BY r.id";

            if ($minLikes > 0) {
                // likes_count est un alias -> utiliser l'agrégat dans HAVING
                $sql .= " HAVING COALESCE(COUNT(rl.id), 0) >= :min_likes";
                $params[':min_likes'] = $minLikes;
            }

            $sql .= " ORDER BY $orderBy";

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'data' => $reviews]);
            break;
            
        case 'POST':
            $input = json_decode(file_get_contents('php://input'), true);
            $recipe_name = $input['recipe_name'];
            $title = isset($input['title']) ? $input['title'] : null;
            $content = trim($input['content']);
            $rating = $input['rating'];
            
            $errors = [];
            if(empty($recipe_name)) $errors[] = "Choisissez une recette";
            if(strlen($content) < 5) $errors[] = "L'avis doit faire au moins 5 caractères";
            if($rating < 1 || $rating > 5) $errors[] = "La note doit être entre 1 et 5";
            
            if(!empty($errors)) {
                echo json_encode(['success' => false, 'errors' => $errors]);
                break;
            }
            
            $stmt = $pdo->prepare("INSERT INTO reviews (recipe_name, title, content, rating, created_at) VALUES (?, ?, ?, ?, NOW())");
            $stmt->execute([$recipe_name, $title, $content, $rating]);
            echo json_encode(['success' => true, 'message' => 'Avis ajouté']);
            break;
            
        case 'PUT':
            $input = json_decode(file_get_contents('php://input'), true);
            $stmt = $pdo->prepare("UPDATE reviews SET title = ?, content = ?, rating = ? WHERE id = ?");
            $stmt->execute([$input['title'], $input['content'], $input['rating'], $input['id']]);
            echo json_encode(['success' => true, 'message' => 'Avis modifié']);
            break;
            
        case 'DELETE':
            $id = isset($_GET['id']) ? $_GET['id'] : null;
            $stmt = $pdo->prepare("DELETE FROM reviews WHERE id = ?");
            $stmt->execute([$id]);
            echo json_encode(['success' => true, 'message' => 'Avis supprimé']);
            break;
    }
} catch(Exception $e) {
    echo json_encode(['success' => false, 'errors' => [$e->getMessage()]]);
}
?>