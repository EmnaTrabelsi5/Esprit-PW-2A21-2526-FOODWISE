<?php
require_once 'config/config.php';
require_once 'controller/RecetteController.php';

$controller = new RecetteController();

$url    = $_GET['url'] ?? 'recettes';
$params = explode('/', trim($url, '/'));

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

        if (isset($params[1]) && $params[1] === 'recettes') {

            if (!isset($params[2]) || $params[2] === '') {
                $controller->adminIndex();

            } elseif ($params[2] === 'ajouter') {
                $controller->create(true);

            } elseif (is_numeric($params[2])) {
                $id = (int)$params[2];

                if (isset($params[3]) && $params[3] === 'supprimer') {
                    $controller->delete($id);

                } else {
                    $controller->show($id);
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

    default:
        http_response_code(404);
        echo "404 — Page introuvable";
}