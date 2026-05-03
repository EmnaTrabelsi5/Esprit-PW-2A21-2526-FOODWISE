<?php
require_once 'config/config.php';
require_once 'controller/RecetteController.php';
require_once 'controller/IngredientController.php';
require_once 'controller/OffreController.php';
require_once 'controller/CommandeController.php';
if (session_status() === PHP_SESSION_NONE) session_start();

$RecetteController = new RecetteController();

$route = $_GET['route'] ?? $_GET['url'] ?? 'recettes';
$params = explode('/', trim($route, '/'));

switch ($params[0]) {

    // ================= FRONT =================
    case 'recettes':

        if (!isset($params[1]) || $params[1] === '') {
            $RecetteController->index();

        } elseif ($params[1] === 'ajouter') {
            $RecetteController->create();
        
        } elseif (is_numeric($params[1]) && isset($params[2]) && $params[2] === 'courses') {
            $RecetteController->courses((int)$params[1]);

        } elseif (is_numeric($params[1])) {
            $id = (int)$params[1];

            if (isset($params[2]) && $params[2] === 'modifier') {
                $RecetteController->edit($id);

            } elseif (isset($params[2]) && $params[2] === 'supprimer') {
                $RecetteController->delete($id);

            } else {
                $RecetteController->show($id);
            }
        } else {
            http_response_code(404);
            echo "404 — Page introuvable";
        }
        break;

    case 'offres':
        $offreController = new OffreController();
        $action = $params[1] ?? 'index';
        $_GET['action'] = $action;
        if (isset($params[2])) $_GET['id'] = $params[2];
        $offreController->handleRequest();
        break;

    case 'commandes':
        $commandeController = new CommandeController();
        $action = $params[1] ?? 'index';
        $_GET['action'] = $action;
        if (isset($params[2])) $_GET['id'] = $params[2];
        $commandeController->handleRequest();
        break;

    // ================= BACK =================
    case 'admin':

        if (isset($params[1])) {
            if ($params[1] === 'recettes') {
                if (!isset($params[2]) || $params[2] === '') {
                    $RecetteController->adminIndex();

                } elseif ($params[2] === 'ajouter') {
                    $RecetteController->create(true);

                } elseif (is_numeric($params[2])) {
                    $id = (int)$params[2];

                    if (isset($params[3]) && $params[3] === 'modifier') {
                        $RecetteController->edit($id, true);

                    } elseif (isset($params[3]) && $params[3] === 'supprimer') {
                        $RecetteController->delete($id);

                    } else {
                        $RecetteController->showAdmin($id, true);
                    }
                } else {
                    http_response_code(404);
                    echo "404 — Page introuvable";
                }
            } elseif ($params[1] === 'ingredients') {
                $ingredientController = new IngredientController();
                if (!isset($params[2]) || $params[2] === '') {
                    $ingredientController->adminIndex();

                } elseif ($params[2] === 'ajouter') {
                    $ingredientController->create();

                } elseif (is_numeric($params[2])) {
                    $id = (int)$params[2];

                    if (isset($params[3]) && $params[3] === 'modifier') {
                        $ingredientController->edit($id);

                    } elseif (isset($params[3]) && $params[3] === 'supprimer') {
                        $ingredientController->delete($id);

                    } else {
                        http_response_code(404);
                        echo "404 — Page introuvable";
                    }
                } else {
                    http_response_code(404);
                    echo "404 — Page introuvable";
                }
            } elseif ($params[1] === 'offres') {
                $offreController = new OffreController();
                $action = $params[2] ?? 'indexAdmin';
                $_GET['action'] = $action;
                if (isset($params[3])) $_GET['id'] = $params[3];
                $offreController->handleAdminRequest();
            } elseif ($params[1] === 'commandes') {
                $commandeController = new CommandeController();
                $action = $params[2] ?? 'indexAdmin';
                $_GET['action'] = $action;
                if (isset($params[3])) $_GET['id'] = $params[3];
                $commandeController->handleAdminRequest();
            } else {
                http_response_code(404);
                echo "404 — Page introuvable";
            }
        } else {
            http_response_code(404);
            echo "404 — Page introuvable";
        }
        break;

    case 'admin_recettes':
        $RecetteController->adminIndex();
        break;

    case 'admin_ingredients':
        $ingredientController = new IngredientController();
        $ingredientController->adminIndex();
        break;

    default:
        http_response_code(404);
        echo "404 — Page introuvable";
}