<?php
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Review.php';

class ReviewModel extends Database {
    
    // Ajouter un avis
    public function create(Review $review) {
        $sql = "INSERT INTO reviews (user_id, recipe_id, content, rating, created_at) 
                VALUES (:user_id, :recipe_id, :content, :rating, NOW())";
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':user_id' => $review->getUserId(),
            ':recipe_id' => $review->getRecipeId(),
            ':content' => $review->getContent(),
            ':rating' => $review->getRating()
        ]);
    }
    
    // Récupérer tous les avis d'un utilisateur
    public function getByUser($user_id) {
        $sql = "SELECT r.*, rc.name as recipe_name 
                FROM reviews r
                JOIN recipes rc ON r.recipe_id = rc.id
                WHERE r.user_id = :user_id 
                ORDER BY r.created_at DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':user_id' => $user_id]);
        
        $reviews = [];
        while($row = $stmt->fetch()) {
            $review = new Review();
            $review->setId($row['id'])
                   ->setUserId($row['user_id'])
                   ->setRecipeId($row['recipe_id'])
                   ->setContent($row['content'])
                   ->setRating($row['rating'])
                   ->setCreatedAt($row['created_at'])
                   ->setUpdatedAt($row['updated_at'])
                   ->setRecipeName($row['recipe_name']);
            $reviews[] = $review;
        }
        
        return $reviews;
    }
    
    // Vérifier si l'utilisateur a déjà un avis pour cette recette
    public function userHasReview($user_id, $recipe_id) {
        $sql = "SELECT id FROM reviews WHERE user_id = :user_id AND recipe_id = :recipe_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':user_id' => $user_id, ':recipe_id' => $recipe_id]);
        return $stmt->rowCount() > 0;
    }
    
    // Modifier un avis
    public function update($id, $user_id, $content, $rating) {
        $sql = "UPDATE reviews 
                SET content = :content, rating = :rating, updated_at = NOW() 
                WHERE id = :id AND user_id = :user_id";
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':id' => $id,
            ':user_id' => $user_id,
            ':content' => $content,
            ':rating' => $rating
        ]);
    }
    
    // Supprimer un avis
    public function delete($id, $user_id) {
        $sql = "DELETE FROM reviews WHERE id = :id AND user_id = :user_id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([':id' => $id, ':user_id' => $user_id]);
    }
    
    // Calculer la note moyenne d'une recette
    public function getAverageRating($recipe_id) {
        $sql = "SELECT AVG(rating) as average FROM reviews WHERE recipe_id = :recipe_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':recipe_id' => $recipe_id]);
        $result = $stmt->fetch();
        return round($result['average'], 1);
    }
}
?>