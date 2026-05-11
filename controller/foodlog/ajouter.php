<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../JournalController.php';

$pdo = config::getConnexion();
$controller = new JournalController($pdo);

$source = $_POST['source'] ?? 'front';
$redirect = $source === 'back'
    ? '/FOODWISE/?route=foodlog/admin/entries'
    : '/FOODWISE/?route=foodlog/journal';
$backToForm = '/FOODWISE/?route=foodlog/ajouter-entree';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . $redirect);
    exit;
}

if (empty($_SESSION['user_id'])) {
    header('Location: /FOODWISE/?route=module2/front/connexion');
    exit;
}

$data = [
    'user_id'   => (int) $_SESSION['user_id'],
    'date'      => trim((string) ($_POST['date'] ?? '')),
    'time'      => trim((string) ($_POST['time'] ?? '')),
    'meal_type' => trim((string) ($_POST['meal_type'] ?? '')),
    'food'      => trim((string) ($_POST['food'] ?? '')),
    'quantity'  => trim((string) ($_POST['quantity'] ?? '')),
    'calories'  => (int) ($_POST['calories'] ?? 0),
    'proteins'  => (float) ($_POST['proteins'] ?? 0),
    'carbs'     => (float) ($_POST['carbs'] ?? 0),
    'fats'      => (float) ($_POST['fats'] ?? 0),
    'note'      => trim((string) ($_POST['note'] ?? '')),
];

$errors = $controller->validateEntryData($data);
$result = false;
if (empty($errors)) {
    $result = $controller->createEntry($data);
}

if (!empty($errors) || !$result) {
    $_SESSION['form_errors'] = $errors ?: ["Impossible de créer l'entrée."];
    $_SESSION['form_old']    = $data;
    header('Location: ' . $backToForm);
    exit;
}

header('Location: ' . $redirect . '&created=1');
exit;
