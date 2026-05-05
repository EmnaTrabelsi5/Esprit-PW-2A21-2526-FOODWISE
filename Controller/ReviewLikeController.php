<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

try {
    $pdo = new PDO("mysql:host=localhost;dbname=foodwise;charset=utf8", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die(json_encode(['success' => false, 'errors' => ['Erreur BDD: ' . $e->getMessage()]]));
}

$method = $_SERVER['REQUEST_METHOD'];

try {
    switch($method) {
        case 'POST':
            $input = json_decode(file_get_contents('php://input'), true);
            $review_id = $input['review_id'];
            $user_id = $input['user_id'];
            
            // Vérifier si l'utilisateur a déjà liké
            $stmt = $pdo->prepare("SELECT id FROM review_likes WHERE review_id = ? AND user_id = ?");
            $stmt->execute([$review_id, $user_id]);
            
            if($stmt->rowCount() > 0) {
                // Supprimer le like
                $stmt = $pdo->prepare("DELETE FROM review_likes WHERE review_id = ? AND user_id = ?");
                $stmt->execute([$review_id, $user_id]);
                echo json_encode(['success' => true, 'action' => 'removed']);
            } else {
                // Ajouter le like
                $stmt = $pdo->prepare("INSERT INTO review_likes (review_id, user_id) VALUES (?, ?)");
                $stmt->execute([$review_id, $user_id]);
                echo json_encode(['success' => true, 'action' => 'added']);
            }
            break;
            
        case 'GET':
            if(isset($_GET['review_id'])) {
                $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM review_likes WHERE review_id = ?");
                $stmt->execute([$_GET['review_id']]);
                $count = $stmt->fetch(PDO::FETCH_ASSOC);
                echo json_encode(['success' => true, 'likes' => $count['count']]);
            }
            break;
    }
} catch(Exception $e) {
    echo json_encode(['success' => false, 'errors' => [$e->getMessage()]]);
}
?>