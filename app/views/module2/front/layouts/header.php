<?php
/**
 * Layout Front-office Module 2 — en-tête
 *
 * @var string $pageTitle
 * @var array<string,string>|null $routesModule2
 */
declare(strict_types=1);

$pageTitle = $pageTitle ?? 'FoodWise — Mon profil';
$cssUrl = $cssUrl ?? 'app/views/module2/assets/css/module2-foodwise.css';

require dirname(__DIR__, 2) . '/routes_defaults.php';

$cssHref = $cssUrl;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?> — FoodWise</title>
  <link rel="stylesheet" href="<?= htmlspecialchars($cssHref, ENT_QUOTES, 'UTF-8') ?>">
</head>
<body class="fw-module2 fw-module2--front">
<a class="fw-skip-link" href="#fw-main-content">Aller au contenu principal</a>
<div class="fw-app">
  <?php require __DIR__ . '/sidebar.php'; ?>
  <div class="fw-main-wrap">
    <header class="fw-topbar" role="banner">
      <h1 class="fw-topbar__title"><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></h1>
      <div class="fw-topbar__links">
        <a href="<?= htmlspecialchars($routesModule2['front_mon_profil'] ?? '#', ENT_QUOTES, 'UTF-8') ?>"><span aria-hidden="true">👤</span> Mon Compte</a>
        <a href="#"><span aria-hidden="true">❓</span> Aide</a>
      </div>
    </header>
