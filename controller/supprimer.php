<?php
declare(strict_types=1);

$pdo = require __DIR__ . '/../config/config.php';
require_once __DIR__ . '/JournalController.php';
require_once __DIR__ . '/SuiviSanteController.php';

$id = (int) ($_GET['id'] ?? 0);
$type = $_GET['source'] ?? 'journal'; // 'journal', 'suivi', ou 'back'
$result = false;

// Déterminer le contrôleur et la redirection
if ($type === 'suivi') {
    $controller = new SuiviSanteController($pdo);
    $redirect = $_GET['origin'] === 'back' ? '../view/back/manage-suivi.php' : '../view/front/suivi-sante-unifie.php';
    $result = $id > 0 ? $controller->deleteSuivi($id) : false;
} else {
    // Par défaut: journal alimentaire
    $controller = new JournalController($pdo);
    $redirect = $type === 'back' ? '../view/back/liste-entries.php' : '../view/front/journal-alimentaire.php';
    $result = $id > 0 ? $controller->deleteEntry($id) : false;
}

$query = $result ? 'deleted=1' : 'deleted=0';
header('Location: ' . $redirect . '?' . $query);
exit;
