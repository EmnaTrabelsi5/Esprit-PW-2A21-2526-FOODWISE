<?php
declare(strict_types=1);

$pdo = require __DIR__ . '/../config/config.php';
require_once __DIR__ . '/JournalController.php';

$controller = new JournalController($pdo);
$id = (int) ($_GET['id'] ?? 0);
$source = $_GET['source'] ?? 'back';
$redirect = $source === 'front' ? '../view/front/journal-alimentaire.php' : '../view/back/liste-entries.php';

if ($id <= 0) {
    header('Location: ' . $redirect . '?deleted=0');
    exit;
}

$result = $controller->deleteEntry($id);
$query = $result ? 'deleted=1' : 'deleted=0';
header('Location: ' . $redirect . '?' . $query);
exit;
