<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

// Connexion à la base de données
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
            // Lire tous les avis
            $stmt = $pdo->query("SELECT * FROM reviews ORDER BY created_at DESC");
            $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'data' => $reviews]);
            break;
            
        case 'POST':
            // Ajouter un avis (avec titre)
            $input = json_decode(file_get_contents('php://input'), true);
            
            $recipe_name = $input['recipe_name'];
            $title = isset($input['title']) ? $input['title'] : null;
            $content = trim($input['content']);
            $rating = $input['rating'];
            
            // Validation
            $errors = [];
            if(empty($recipe_name)) $errors[] = "Choisissez une recette";
            if(strlen($content) < 5) $errors[] = "L'avis doit faire au moins 5 caractères";
            if($rating < 1 || $rating > 5) $errors[] = "La note doit être entre 1 et 5";
            
            if(!empty($errors)) {
                echo json_encode(['success' => false, 'errors' => $errors]);
                break;
            }
            
            // Insérer dans la base (avec titre)
            $stmt = $pdo->prepare("INSERT INTO reviews (recipe_name, title, content, rating, created_at) VALUES (?, ?, ?, ?, NOW())");
            $stmt->execute([$recipe_name, $title, $content, $rating]);
            
            echo json_encode(['success' => true, 'message' => 'Avis ajouté avec succès']);
            break;
            
        case 'PUT':
            // Modifier un avis (avec titre)
            $input = json_decode(file_get_contents('php://input'), true);
            
            $stmt = $pdo->prepare("UPDATE reviews SET title = ?, content = ?, rating = ? WHERE id = ?");
            $stmt->execute([$input['title'], $input['content'], $input['rating'], $input['id']]);
            
            echo json_encode(['success' => true, 'message' => 'Avis modifié']);
            break;
            
        case 'DELETE':
            // Supprimer un avis
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