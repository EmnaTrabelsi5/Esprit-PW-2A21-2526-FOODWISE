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
$action = isset($_GET['action']) ? $_GET['action'] : '';

try {
    switch($method) {
        case 'GET':
            // Récupérer les réactions pour une réponse
            if($action === 'reactions' && isset($_GET['response_id'])) {
                $stmt = $pdo->prepare("
                    SELECT 
                        SUM(CASE WHEN type = 'like' THEN 1 ELSE 0 END) as likes,
                        SUM(CASE WHEN type = 'dislike' THEN 1 ELSE 0 END) as dislikes
                    FROM response_reactions 
                    WHERE response_id = ?
                ");
                $stmt->execute([$_GET['response_id']]);
                $counts = $stmt->fetch(PDO::FETCH_ASSOC);
                
                // Vérifier la réaction de l'utilisateur actuel
                $user_id = isset($_GET['user_id']) ? $_GET['user_id'] : 1;
                $stmt2 = $pdo->prepare("SELECT type FROM response_reactions WHERE response_id = ? AND user_id = ?");
                $stmt2->execute([$_GET['response_id'], $user_id]);
                $userReaction = $stmt2->fetch(PDO::FETCH_ASSOC);
                
                echo json_encode([
                    'success' => true, 
                    'data' => [
                        'likes' => $counts['likes'] ?? 0,
                        'dislikes' => $counts['dislikes'] ?? 0,
                        'user_reaction' => $userReaction ? $userReaction['type'] : null
                    ]
                ]);
                break;
            }
            
            // Récupérer les réponses d'un avis
            if(isset($_GET['review_id'])) {
                $stmt = $pdo->prepare("SELECT * FROM responses WHERE review_id = ? ORDER BY created_at ASC");
                $stmt->execute([$_GET['review_id']]);
                $responses = $stmt->fetchAll(PDO::FETCH_ASSOC);
                echo json_encode(['success' => true, 'data' => $responses]);
                break;
            }
            
            // Récupérer toutes les réponses
            $stmt = $pdo->query("SELECT * FROM responses ORDER BY created_at DESC");
            $responses = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'data' => $responses]);
            break;
            
        case 'POST':
            // Gestion des réactions (like/dislike)
            if($action === 'reaction') {
                $input = json_decode(file_get_contents('php://input'), true);
                
                if(!isset($input['response_id']) || !isset($input['user_id']) || !isset($input['type'])) {
                    echo json_encode(['success' => false, 'errors' => ['Paramètres manquants']]);
                    break;
                }
                
                // Vérifier si l'utilisateur a déjà réagi
                $stmt = $pdo->prepare("SELECT id, type FROM response_reactions WHERE response_id = ? AND user_id = ?");
                $stmt->execute([$input['response_id'], $input['user_id']]);
                $existing = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if($existing) {
                    if($existing['type'] === $input['type']) {
                        // Supprimer la réaction (toggle off)
                        $stmt = $pdo->prepare("DELETE FROM response_reactions WHERE id = ?");
                        $stmt->execute([$existing['id']]);
                        echo json_encode(['success' => true, 'action' => 'removed']);
                    } else {
                        // Changer le type de réaction
                        $stmt = $pdo->prepare("UPDATE response_reactions SET type = ? WHERE id = ?");
                        $stmt->execute([$input['type'], $existing['id']]);
                        echo json_encode(['success' => true, 'action' => 'changed']);
                    }
                } else {
                    // Ajouter une nouvelle réaction
                    $stmt = $pdo->prepare("INSERT INTO response_reactions (response_id, user_id, type) VALUES (?, ?, ?)");
                    $stmt->execute([$input['response_id'], $input['user_id'], $input['type']]);
                    echo json_encode(['success' => true, 'action' => 'added']);
                }
                break;
            }
            
            // Ajouter une réponse
            $input = json_decode(file_get_contents('php://input'), true);
            $stmt = $pdo->prepare("INSERT INTO responses (review_id, user_id, content, created_at) VALUES (?, ?, ?, NOW())");
            $stmt->execute([$input['review_id'], $input['user_id'], $input['content']]);
            echo json_encode(['success' => true, 'message' => 'Réponse ajoutée']);
            break;
            
        case 'PUT':
            $input = json_decode(file_get_contents('php://input'), true);
            $stmt = $pdo->prepare("UPDATE responses SET content = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$input['content'], $input['id']]);
            echo json_encode(['success' => true, 'message' => 'Réponse modifiée']);
            break;
            
        case 'DELETE':
            $id = isset($_GET['id']) ? $_GET['id'] : null;
            // Supprimer d'abord les réactions associées
            $stmt = $pdo->prepare("DELETE FROM response_reactions WHERE response_id = ?");
            $stmt->execute([$id]);
            // Puis supprimer la réponse
            $stmt = $pdo->prepare("DELETE FROM responses WHERE id = ?");
            $stmt->execute([$id]);
            echo json_encode(['success' => true, 'message' => 'Réponse supprimée']);
            break;
    }
} catch(Exception $e) {
    echo json_encode(['success' => false, 'errors' => [$e->getMessage()]]);
}
?>