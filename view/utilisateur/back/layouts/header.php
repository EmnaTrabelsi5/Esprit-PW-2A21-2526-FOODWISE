<?php
/**
 * Layout Back-office Module 2 — en-tête + début zone principale
 *
 * Variables optionnelles (contrôleur) :
 * @var string $pageTitle Titre sous le H1 de la barre du haut
 * @var array<string,string> $routesModule2 Surcharge des URLs (voir routes_defaults.php)
 */
declare(strict_types=1);

$pageTitle = $pageTitle ?? 'Module 2 – NutriProfile – Administration';
/** @var string|null $cssUrl URL vers la CSS (surcharge contrôleur). Relatif au dossier de la page (front/ ou back/). */
$cssUrl = '/FOODWISE/assets/module2-foodwise.css';

require dirname(__DIR__, 2) . '/routes_defaults.php';

$cssHref = $cssUrl;
$backoffice = $backoffice ?? true;

?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?> — FoodWise</title>
  <link rel="stylesheet" href="<?= htmlspecialchars($cssHref, ENT_QUOTES, 'UTF-8') ?>">
<style>
    body {
  font-family: 'Lato', Arial, sans-serif;
  background-color: var(--creme);
  color: var(--texte-sombre);
  font-size: 15px;
  line-height: 1.6;
  min-height: 100vh;
}

/* ─── Sidebar ─── */
.sidebar {
  position: fixed;
  top: 0; left: 0;
  width: var(--sidebar-w);
  height: 100vh;
  background: linear-gradient(180deg, #3A1A06 0%, #6B2E10 100%);
  display: flex;
  flex-direction: column;
  z-index: 100;
  box-shadow: 3px 0 20px rgba(0,0,0,0.25);
  overflow-y: auto;
}
.sidebar-logo {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 20px 18px 16px;
  border-bottom: 1px solid rgba(255,255,255,0.10);
  flex-shrink: 0;
}
.sidebar-logo {
  justify-content: center;
}
.sidebar-logo img {
  width: 100px;   
  height: auto;
  object-fit: contain;
}
.fw-main-wrap {
  margin-left: var(--sidebar-w, 260px);
}
  </style>

</head>
<body class="backoffice">


<a class="fw-skip-link" href="#fw-main-content">Aller au contenu principal</a>
<div class="fw-app">
 <!-- ========== SIDEBAR ========== -->
<aside class="sidebar">
  <div class="sidebar-logo">
   <img src="/FOODWISE/assets/img/logo.png" alt="FoodWise Logo">
  </div>

  <nav class="sidebar-nav">
    <?php if ($backoffice): ?>
      <a href="#" class="nav-item <?= $activeNav === 'dashboard' ? 'active' : '' ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
        Tableau de bord
      </a>
      <a href="/FOODWISE/?route=admin_recettes" class="nav-item <?= $activeNav === 'gestion_recettes' ? 'active' : '' ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
        Gestion Recettes
      </a>
      <a href="/FOODWISE/?route=admin_ingredients" class="nav-item <?= $activeNav === 'gestion_ingredients' ? 'active' : '' ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="7" r="4"/><path d="M6 21v-2a4 4 0 014-4h4a4 4 0 014 4v2"/></svg>
        Base Ingrédients
      </a>
      <a href="/FOODWISE/?route=admin/offres" class="nav-item <?= $activeNav === 'partenaires' ? 'active' : '' ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2M9 7a4 4 0 100 8 4 4 0 000-8zM23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg>
        Offres Partenaires
      </a>
      <a href="/FOODWISE/?route=community/admin" class="nav-item <?= $activeNav === 'community' ? 'active' : '' ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
        Avis Communauté
      </a>
      <a href="/FOODWISE/?route=module2.back.dashboard.profils" class="nav-item <?= $activeNav === 'utilisateurs' ? 'active' : '' ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
        Utilisateurs
      </a>

      <!-- ── FoodLog Admin ── -->
      <p style="padding:12px 18px 4px;font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:.1em;color:rgba(255,255,255,.4);margin:0;">FoodLog</p>
      <a href="/FOODWISE/?route=foodlog/admin/dashboard" class="nav-item <?= $activeNav === 'foodlog_admin' ? 'active' : '' ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M18 20V10M12 20V4M6 20v-6"/></svg>
        Dashboard FoodLog
      </a>
      <a href="/FOODWISE/?route=foodlog/admin/entries" class="nav-item <?= $activeNav === 'foodlog_entries' ? 'active' : '' ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
        Entrées utilisateurs
      </a>
      <a href="/FOODWISE/?route=foodlog/admin/suivi" class="nav-item <?= $activeNav === 'foodlog_suivi_admin' ? 'active' : '' ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
        Suivi Santé
      </a>

      <a href="#" class="nav-item <?= $activeNav === 'parametres' ? 'active' : '' ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-4 0v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83-2.83l.06-.06A1.65 1.65 0 004.68 15a1.65 1.65 0 00-1.51-1H3a2 2 0 010-4h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 012.83-2.83l.06.06A1.65 1.65 0 009 4.68a1.65 1.65 0 001-1.51V3a2 2 0 014 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 2.83l-.06.06A1.65 1.65 0 0019.4 9a1.65 1.65 0 001.51 1H21a2 2 0 010 4h-.09a1.65 1.65 0 00-1.51 1z"/></svg>
        Paramètres
      </a>
    <?php endif; ?>
  </nav>
</aside>
  <div class="fw-main-wrap">
    <header class="fw-topbar" role="banner">
      <h1 class="fw-topbar__title"><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></h1>
      <div class="fw-topbar__links">
        <a href="<?= htmlspecialchars($routesModule2['front_mon_profil'] ?? '#', ENT_QUOTES, 'UTF-8') ?>"><span aria-hidden="true">👤</span> Mon Compte</a>
        <a href="<?= htmlspecialchars($routesModule2['back_logout'] ?? '#', ENT_QUOTES, 'UTF-8') ?>" style="color:var(--fw-alert)"><span aria-hidden="true">🚪</span> Déconnexion</a>
      </div>
    </header>