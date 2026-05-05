<?php
declare(strict_types=1);

/**
 * Point d'entrée principal de l'application FoodWise
 * Redirige vers le dashboard approprié selon le rôle utilisateur
 */

session_start();

// Vérifier s'il y a une session utilisateur valide
if (empty($_SESSION['user_role']) || empty($_SESSION['user_id'])) {
    // Pas de session - rediriger vers la page de test d'accès pour permettre de choisir le rôle
    header('Location: ./test-acces.php');
    exit;
}

// Utilisateur authentifié - rediriger selon le rôle
if ($_SESSION['user_role'] === 'admin') {
    header('Location: ./view/back/dashboard-admin.php');
    exit;
} else {
    // Rôle client ou autre
    header('Location: ./view/front/journal-alimentaire.php');
    exit;
}
