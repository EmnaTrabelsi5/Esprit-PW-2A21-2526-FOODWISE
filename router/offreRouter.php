
<?php
require_once __DIR__ . '/../Controller/OffreController.php';

$controller = new OffreController();
$controller->handleRequest();

$action = $_GET['action'] ?? 'list';

switch ($action) {

    case 'list':
        $controller->index();
        break;

    case 'show':
        $controller->show();
        break;

    default:
        echo "Action invalide";
}