<?php
require_once 'config/config.php';
require_once 'controller/RecetteController.php';
require_once 'controller/IngredientController.php';
if (session_status() === PHP_SESSION_NONE) session_start();

$controller = new RecetteController();

$route = $_GET['route'] ?? $_GET['url'] ?? 'recettes';
$params = explode('/', trim($route, '/'));

switch ($params[0]) {

    // ================= FRONT =================
    case 'recettes':

        if (!isset($params[1]) || $params[1] === '') {
            $controller->index();

        } elseif ($params[1] === 'ajouter') {
            $controller->create();

        } elseif (is_numeric($params[1])) {
            $id = (int)$params[1];

            if (isset($params[2]) && $params[2] === 'modifier') {
                $controller->edit($id);

            } elseif (isset($params[2]) && $params[2] === 'supprimer') {
                $controller->delete($id);

            } else {
                $controller->show($id);
            }
        } else {
            http_response_code(404);
            echo "404 — Page introuvable";
        }
        break;

    // ================= BACK =================
    case 'admin':

        if (isset($params[1])) {
            if ($params[1] === 'recettes') {
                if (!isset($params[2]) || $params[2] === '') {
                    $controller->adminIndex();

                } elseif ($params[2] === 'ajouter') {
                    $controller->create(true);

                } elseif (is_numeric($params[2])) {
                    $id = (int)$params[2];

                    if (isset($params[3]) && $params[3] === 'modifier') {
                        $controller->edit($id, true);

                    } elseif (isset($params[3]) && $params[3] === 'supprimer') {
                        $controller->delete($id);

                    } else {
                        $controller->showAdmin($id, true);
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
        $controller->adminIndex();
        break;

    case 'admin_ingredients':
        $ingredientController = new IngredientController();
        $ingredientController->adminIndex();
        break;

    default:
        http_response_code(404);
        echo "404 — Page introuvable";
}