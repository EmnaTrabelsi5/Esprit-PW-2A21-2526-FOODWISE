<?php
declare(strict_types=1);

session_start();
$pdo = require __DIR__ . '/../config/config.php';
require_once __DIR__ . '/JournalController.php';

$controller = new JournalController($pdo);
$source = $_POST['source'] ?? 'front';
$redirect = $source === 'back' ? '../view/back/liste-entries.php' : '../view/front/journal-alimentaire.php';
$backToForm = '../view/front/modifier-entree.php?id=' . ((int) ($_POST['id'] ?? 0));

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . $redirect);
    exit;
}

$id = (int) ($_POST['id'] ?? 0);
if ($id <= 0) {
    header('Location: ' . $redirect . '?updated=0');
    exit;
}

$data = [
    'date' => trim((string) ($_POST['date'] ?? '')),
    'time' => trim((string) ($_POST['time'] ?? '')),
    'meal_type' => trim((string) ($_POST['meal_type'] ?? '')),
    'food' => trim((string) ($_POST['food'] ?? '')),
    'quantity' => trim((string) ($_POST['quantity'] ?? '')),
    'calories' => (int) ($_POST['calories'] ?? 0),
    'proteins' => (float) ($_POST['proteins'] ?? 0),
    'carbs' => (float) ($_POST['carbs'] ?? 0),
    'fats' => (float) ($_POST['fats'] ?? 0),
    'note' => trim((string) ($_POST['note'] ?? '')),
];

$errors = $controller->validateEntryData($data);
$result = false;
if (empty($errors)) {
    $result = $controller->updateEntry($id, $data);
}

if (!empty($errors) || !$result) {
    $_SESSION['form_errors'] = $errors ?: ['Impossible de mettre à jour l’entrée.'];
    $_SESSION['form_old'] = $data;
    header('Location: ' . $backToForm);
    exit;
}

$query = 'updated=1';
header('Location: ' . $redirect . '?' . $query);
exit;
