<?php
require_once __DIR__ . '/../config/database.php';

class Database {
    protected $pdo;
    
    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
    }
    
    public function getConnection() {
        return $this->pdo;
    }
}
?>