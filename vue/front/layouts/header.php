<?php
/**
 * En-tête HTML — Front-office MealPlanner (client)
 * Variables optionnelles : $pageTitle, $assetCss, $bodyClass
 */
use Controller\Url;

$pageTitle = $pageTitle ?? 'FoodWise — MealPlanner';
$assetCss = $assetCss ?? Url::asset('css/mealplanner.css');
$bodyClass = $bodyClass ?? '';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></title>
  <link rel="stylesheet" href="<?= htmlspecialchars($assetCss, ENT_QUOTES, 'UTF-8') ?>">
</head>
<body<?= $bodyClass !== '' ? ' class="' . htmlspecialchars($bodyClass, ENT_QUOTES, 'UTF-8') . '"' : '' ?>>
<div class="layout layout--client">
