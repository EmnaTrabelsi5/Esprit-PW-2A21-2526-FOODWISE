<?php
declare(strict_types=1);

require __DIR__ . '/app/bootstrap.php';

$route = $_GET['route'] ?? DEFAULT_ROUTE;
$frontController = new FrontController($pdo);
$backController = new BackController($pdo);
$messageController = new MessageController($pdo);

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
    case 'module2.front.password_reset':
        $frontController->passwordReset();
        break;
    case 'module2.front.verify_reset_code':
        $frontController->verifyResetCode();
        break;
    case 'module2.front.logout':
        $frontController->logout();
        break;
    case 'module2.front.allergies_regimes':
        $frontController->allergiesRegimes();
        break;
    case 'module2.front.users_list':
        $messageController->usersList();
        break;
    case 'module2.front.chat':
        $messageController->chat();
        break;
    case 'module2.front.get_unread_count':
        $messageController->getUnreadCount();
        break;
    case 'module2.front.public_view':
        $frontController->viewPublicProfile();
        break;
    case 'module2.api.toggle_visibility':
        $frontController->toggleVisibility();
        break;
    case 'module2.back.login':
        $backController->connexion();
        break;
    case 'module2.back.logout':
        $backController->logoutAdmin();
        break;
    case 'module2.back.dashboard.profils':
        $backController->dashboardProfils();
        break;
    case 'module2.back.profil.form':
        $backController->profilForm();
        break;
    case 'module2.back.modification.history':
        $backController->modificationHistory();
        break;
    case 'module2.back.suspend.user':
        $backController->suspendUser();
        break;
    case 'module2.back.lift.suspension':
        $backController->liftSuspension();
        break;
    case 'module2.back.ban.user':
        $backController->banUser();
        break;
    case 'module2.back.unban.user':
        $backController->unbanUser();
        break;
    default:
        header('HTTP/1.0 404 Not Found');
        echo 'Page non trouvée';
        break;
}
