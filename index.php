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
require_once 'controller/CommunityController.php';
// MealPlannerBridgeController supprimé car intégré directement
require_once 'model/NotificationModel.php';
require_once 'model/MentionModel.php';
// Module FoodLog (projet_M5 intégré)
require_once 'model/EntreeJournal.php';
require_once 'model/SuiviSante.php';
require_once 'controller/JournalController.php';
require_once 'controller/SuiviSanteController.php';
require_once 'controller/ResumeController.php';

// Autoloader pour le module MealPlanner (ex-module4)
spl_autoload_register(function (string $class): void {
    $parts = explode('\\', $class);
    $namespace = $parts[0] ?? '';
    $relativeClass = implode('/', array_slice($parts, 1));
    
    $baseDir = '';
    switch ($namespace) {
        case 'Controller':
            $baseDir = __DIR__ . '/controller/MealPlanner/';
            break;
        case 'Model':
            $baseDir = __DIR__ . '/model/MealPlanner/';
            break;
        case 'Config':
            if ($relativeClass === 'Config' || $relativeClass === 'Database') {
                $file = __DIR__ . '/config/MealPlannerConfig.php';
                if (is_file($file)) {
                    require_once $file;
                }
                return;
            }
            $baseDir = __DIR__ . '/config/';
            break;
    }

    if ($baseDir !== '') {
        $file = $baseDir . $relativeClass . '.php';
        if (is_file($file)) {
            require_once $file;
        }
    }
});

if (session_status() === PHP_SESSION_NONE) session_start();

$pdo = config::getConnexion();

$RecetteController = new RecetteController();
$FrontController = new FrontController($pdo);
$BackController = new BackController($pdo);
$MessageController = new MessageController($pdo);
$CommunityController = new CommunityController($pdo);

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

// Routes FoodLog (module intégré depuis projet_M5)
$foodlogFrontRoutes = [
    'foodlog/journal',
    'foodlog/ajouter-entree',
    'foodlog/modifier-entree',
    'foodlog/suivi',
    'foodlog/ajouter-suivi',
    'foodlog/modifier-suivi',
    'foodlog/resume',
];
$foodlogAdminRoutes = [
    'foodlog/admin/dashboard',
    'foodlog/admin/entries',
    'foodlog/admin/suivi',
];

if (str_starts_with($normalizedRoute, 'module2/back/')) {
    if (!in_array($normalizedRoute, $publicRoutesAdmin, true) && empty($_SESSION['admin_id'])) {
        redirect('?route=module2/back/login');
    }
} elseif (str_starts_with($normalizedRoute, 'admin/')) {
    if (empty($_SESSION['admin_id'])) {
        redirect('?route=module2/back/login');
    }
} elseif (str_starts_with($normalizedRoute, 'foodlog/admin/')) {
    if (empty($_SESSION['admin_id'])) {
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

    // ================= MODULE FOODLOG (projet_M5 intégré) =================
    case 'foodlog':
        $foodlogSub = $params[1] ?? '';
        switch ($foodlogSub) {
            // ── Front ──
            case 'journal':
                include 'view/foodlog/front/journal.php';
                break;
            case 'ajouter-entree':
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    require_once 'controller/foodlog/ajouter.php';
                } else {
                    include 'view/foodlog/front/ajouter-entree.php';
                }
                break;
            case 'modifier-entree':
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    require_once 'controller/foodlog/modifier.php';
                } else {
                    include 'view/foodlog/front/modifier-entree.php';
                }
                break;
            case 'suivi':
                include 'view/foodlog/front/suivi.php';
                break;
            case 'ajouter-suivi':
                include 'view/foodlog/front/ajouter-suivi.php';
                break;
            case 'modifier-suivi':
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    // Le formulaire modifier-suivi gère lui-même le POST via include
                    include 'view/foodlog/front/modifier-suivi.php';
                } else {
                    include 'view/foodlog/front/modifier-suivi.php';
                }
                break;
            case 'resume':
                include 'view/foodlog/front/resume.php';
                break;
            case 'supprimer':
                require_once 'controller/foodlog/supprimer.php';
                break;            // ── Back Admin ──
            case 'admin':
                $adminSub = $params[2] ?? 'dashboard';
                switch ($adminSub) {
                    case 'dashboard':
                        include 'view/foodlog/back/dashboard.php';
                        break;
                    case 'entries':
                        include 'view/foodlog/back/entries.php';
                        break;
                    case 'suivi':
                        include 'view/foodlog/back/suivi.php';
                        break;
                    default:
                        http_response_code(404);
                        echo "404 — Page introuvable";
                }
                break;
            default:
                // Redirection par défaut vers le journal
                redirect('?route=foodlog/journal');
        }
        break;

    // ================= MODULE COMMUNAUTÉ =================
    case 'community':
        $subRoute = $params[1] ?? 'index';
        if ($subRoute === 'api') {
            $apiType = $params[2] ?? '';
            switch ($apiType) {
                case 'reviews':       $CommunityController->apiReviews();       break;
                case 'responses':     $CommunityController->apiResponses();     break;
                case 'notifications': $CommunityController->apiNotifications(); break;
                case 'likes':         $CommunityController->apiReviewLikes();   break;
                case 'stats':         $CommunityController->apiStats();         break;
                default:
                    http_response_code(404);
                    echo json_encode(['success' => false, 'error' => 'API endpoint inconnu']);
            }
        } elseif ($subRoute === 'admin') {
            if (empty($_SESSION['admin_id'])) { redirect('?route=module2/back/login'); }
            $CommunityController->indexBack();
        } else {
            $CommunityController->indexFront();
        }
        break;

    // ================= MODULE 4 (MealPlanner) =================
    case 'mealplanner':
        $_GET['area'] = $params[1] ?? 'front';
        $_GET['resource'] = $params[2] ?? 'home';
        $_GET['action'] = $params[3] ?? 'index';

        $router = new \Controller\Router();
        $router->dispatch();
        break;

    default:
        http_response_code(404);
        echo "404 — Page introuvable";
}
