<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../JournalController.php';
require_once __DIR__ . '/../SuiviSanteController.php';

$pdo    = config::getConnexion();
$id     = (int) ($_GET['id'] ?? 0);
$type   = $_GET['source'] ?? 'journal'; // 'journal', 'suivi', 'back'
$result = false;

if (empty($_SESSION['user_id'])) {
    header('Location: ?route=module2/front/connexion');
    exit;
}

if ($type === 'suivi') {
    $controller = new SuiviSanteController($pdo);
    $origin     = $_GET['origin'] ?? 'front';
    $redirect   = $origin === 'back'
        ? '?route=foodlog/admin/suivi'
        : '?route=foodlog/suivi';
    $result = $id > 0 ? $controller->deleteSuivi($id) : false;
} else {
    $controller = new JournalController($pdo);
    $redirect   = $type === 'back'
        ? '?route=foodlog/admin/entries'
        : '?route=foodlog/journal';
    $result = $id > 0 ? $controller->deleteEntry($id) : false;
}

$query = $result ? 'deleted=1' : 'deleted=0';
header('Location: ' . $redirect . '&' . $query);
exit;

