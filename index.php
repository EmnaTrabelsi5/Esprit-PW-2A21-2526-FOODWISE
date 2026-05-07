<?php
require_once 'config/config.php';
require_once 'config/helpers.php';
require_once 'config/MailerService.php';
require_once 'model/UtilisateurModel.php';
require_once 'model/ProfilNutritionnelModel.php';
require_once 'model/MessageModel.php';
require_once 'model/AllergenMappings.php';
require_once 'controller/RecetteController.php';
require_once 'controller/IngredientController.php';
require_once 'controller/OffreController.php';
require_once 'controller/CommandeController.php';
require_once 'controller/FrontController.php';
require_once 'controller/BackController.php';
require_once 'controller/MessageController.php';
if (session_status() === PHP_SESSION_NONE) session_start();

$pdo = config::getConnexion();

$RecetteController = new RecetteController();
$FrontController = new FrontController($pdo);
$BackController = new BackController($pdo);
$MessageController = new MessageController($pdo);

$route = $_GET['route'] ?? $_GET['url'] ?? 'recettes';
$normalizedRoute = trim($route, '/');

// Autoriser l'ancien format module2.front.xxx en le convertissant en module2/front/xxx
if (preg_match('/^module2\./', $normalizedRoute)) {
    $normalizedRoute = preg_replace('/^module2\./', 'module2/', $normalizedRoute);
    $normalizedRoute = str_replace('.', '/', $normalizedRoute);
}

$params = explode('/', $normalizedRoute);

$publicRoutesUser = [
    'module2/front/connexion',
    'module2/front/inscription',
    'module2/front/password_reset',
    'module2/front/verify_reset_code',
];
$publicRoutesAdmin = [
    'module2/back/login',
];

if (str_starts_with($normalizedRoute, 'module2/back/')) {
    if (!in_array($normalizedRoute, $publicRoutesAdmin, true) && empty($_SESSION['admin_id'])) {
        redirect('?route=module2/back/login');
    }
} else {
    if (empty($_SESSION['user_id']) && !in_array($normalizedRoute, $publicRoutesUser, true)) {
        redirect('?route=module2/front/connexion');
    }
}

switch ($params[0]) {

    // ================= FRONT =================
    case 'recettes':

        if (!isset($params[1]) || $params[1] === '') {
            $RecetteController->index();

        } elseif ($params[1] === 'ajouter') {
            $RecetteController->create();
        
        } elseif (is_numeric($params[1]) && isset($params[2]) && $params[2] === 'courses') {
            $RecetteController->courses((int)$params[1]);
        
        } elseif (is_numeric($params[1]) && isset($params[2]) && $params[2] === 'favori') {
            $RecetteController->toggleFavori((int)$params[1]);

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

    case 'favoris':
    $RecetteController->mesFavoris();
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

    case 'module2':
        $subroute = implode('.', array_slice($params, 1));
        switch ($subroute) {
            case 'front.mon_profil':
                $FrontController->monProfil();
                break;
            case 'front.profil.edit':
                $FrontController->profilEdit();
                break;
            case 'front.connexion':
                $FrontController->connexion();
                break;
            case 'front.inscription':
                $FrontController->inscription();
                break;
            case 'front.password_reset':
                $FrontController->passwordReset();
                break;
            case 'front.verify_reset_code':
                $FrontController->verifyResetCode();
                break;
            case 'front.logout':
                $FrontController->logout();
                break;
            case 'front.allergies_regimes':
                $FrontController->allergiesRegimes();
                break;
            case 'front.users_list':
                $MessageController->usersList();
                break;
            case 'front.chat':
                $MessageController->chat();
                break;
            case 'front.get_unread_count':
                $MessageController->getUnreadCount();
                break;
            case 'front.public_view':
                $FrontController->viewPublicProfile();
                break;
            case 'api.toggle_visibility':
                $FrontController->toggleVisibility();
                break;
            case 'back.login':
                $BackController->connexion();
                break;
            case 'back.logout':
                $BackController->logoutAdmin();
                break;
            case 'back.dashboard.profils':
                $BackController->dashboardProfils();
                break;
            case 'back.profil.form':
                $BackController->profilForm();
                break;
            case 'back.modification.history':
                $BackController->modificationHistory();
                break;
            case 'back.suspend.user':
                $BackController->suspendUser();
                break;
            case 'back.lift.suspension':
                $BackController->liftSuspension();
                break;
            case 'back.ban.user':
                $BackController->banUser();
                break;
            case 'back.unban.user':
                $BackController->unbanUser();
                break;
            default:
                http_response_code(404);
                echo "404 — Page introuvable";
        }
        break;

    default:
        http_response_code(404);
        echo "404 — Page introuvable";
}