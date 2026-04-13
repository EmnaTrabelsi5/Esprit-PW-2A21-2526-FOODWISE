<?php
class Review {
    private $id;
    private $user_id;
    private $recipe_id;
    private $content;
    private $rating;
    private $created_at;
    private $updated_at;
    private $recipe_name;
    
    // Getters
    public function getId() { return $this->id; }
    public function getUserId() { return $this->user_id; }
    public function getRecipeId() { return $this->recipe_id; }
    public function getContent() { return $this->content; }
    public function getRating() { return $this->rating; }
    public function getCreatedAt() { return $this->created_at; }
    public function getUpdatedAt() { return $this->updated_at; }
    public function getRecipeName() { return $this->recipe_name; }
    
    // Setters
    public function setId($id) { $this->id = $id; return $this; }
    public function setUserId($user_id) { $this->user_id = $user_id; return $this; }
    public function setRecipeId($recipe_id) { $this->recipe_id = $recipe_id; return $this; }
    public function setContent($content) { $this->content = trim($content); return $this; }
    public function setRating($rating) { $this->rating = (int)$rating; return $this; }
    public function setCreatedAt($created_at) { $this->created_at = $created_at; return $this; }
    public function setUpdatedAt($updated_at) { $this->updated_at = $updated_at; return $this; }
    public function setRecipeName($recipe_name) { $this->recipe_name = $recipe_name; return $this; }
    
    // Validation
    public function validate() {
        $errors = [];
        
        if(empty($this->recipe_id)) {
            $errors[] = "Veuillez sélectionner une recette";
        }
        
        if(strlen($this->content) < 5) {
            $errors[] = "L'avis doit contenir au moins 5 caractères";
        }
        
        if(strlen($this->content) > 1000) {
            $errors[] = "L'avis ne peut pas dépasser 1000 caractères";
        }
        
        if($this->rating < 1 || $this->rating > 5) {
            $errors[] = "La note doit être comprise entre 1 et 5";
        }
        
        return $errors;
    }
    
    // Convertir en tableau
    public function toArray() {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'recipe_id' => $this->recipe_id,
            'content' => $this->content,
            'rating' => $this->rating,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'recipe_name' => $this->recipe_name
        ];
    }
}
?>