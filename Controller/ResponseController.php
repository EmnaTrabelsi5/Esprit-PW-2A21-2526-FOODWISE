<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

// Connexion BDD
try {
    $pdo = new PDO("mysql:host=localhost;dbname=foodwise;charset=utf8", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die(json_encode(['success' => false, 'errors' => ['Erreur BDD: ' . $e->getMessage()]]));
}

$method = $_SERVER['REQUEST_METHOD'];

try {
    switch($method) {
        case 'GET':
            if(isset($_GET['review_id'])) {
                $stmt = $pdo->prepare("SELECT * FROM responses WHERE review_id = ? ORDER BY created_at ASC");
                $stmt->execute([$_GET['review_id']]);
            } else {
                $stmt = $pdo->query("SELECT * FROM responses ORDER BY created_at DESC");
            }
            $responses = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'data' => $responses]);
            break;
            
        case 'POST':
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
            $stmt = $pdo->prepare("DELETE FROM responses WHERE id = ?");
            $stmt->execute([$id]);
            echo json_encode(['success' => true, 'message' => 'Réponse supprimée']);
            break;
    }
} catch(Exception $e) {
    echo json_encode(['success' => false, 'errors' => [$e->getMessage()]]);
}
?>