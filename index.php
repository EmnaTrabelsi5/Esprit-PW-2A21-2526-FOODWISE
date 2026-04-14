<?php
declare(strict_types=1);

require __DIR__ . '/app/bootstrap.php';

$route = $_GET['route'] ?? DEFAULT_ROUTE;
$frontController = new FrontController($pdo);
$backController = new BackController($pdo);

switch ($route) {
    case 'module2.front.mon_profil':
        $frontController->monProfil();
        break;
    case 'module2.front.profil.edit':
        $frontController->profilEdit();
        break;
    case 'module2.front.connexion':
        $frontController->connexion();
        break;
    case 'module2.front.inscription':
        $frontController->inscription();
        break;
    case 'module2.front.allergies_regimes':
        $frontController->allergiesRegimes();
        break;
    case 'module2.back.dashboard.profils':
        $backController->dashboardProfils();
        break;
    case 'module2.back.profil.form':
        $backController->profilForm();
        break;
    default:
        header('HTTP/1.0 404 Not Found');
        echo 'Page non trouvée';
        break;
}
