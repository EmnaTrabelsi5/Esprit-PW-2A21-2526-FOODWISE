<?php

$pageTitle  = $pageTitle  ?? 'FoodWise';
$activeNav  = $activeNav  ?? '';
$backoffice = $backoffice ?? false;
if (session_status() === PHP_SESSION_NONE) session_start();
$fwCurrentUser = null;
if (!empty($_SESSION['user_id']) && class_exists('UtilisateurModel') && class_exists('config')) {
  try {
    $fwCurrentUser = (new UtilisateurModel(config::getConnexion()))->findById((int)$_SESSION['user_id']);
  } catch (Throwable $e) {
    $fwCurrentUser = null;
  }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($pageTitle ?? 'LocalMarket') ?> — FoodWise</title>
  <link rel="stylesheet" href="<?= htmlspecialchars(assetUrl('foodwise.css'), ENT_QUOTES, 'UTF-8') ?>">
  <!-- CSS module LocalMarket -->
  <link rel="stylesheet" href="<?= htmlspecialchars(assetUrl('mealplanner.css'), ENT_QUOTES, 'UTF-8') ?>">
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
  position: fixed !important;
  top: 0; left: 0;
  width: var(--sidebar-w) !important;
  height: 100vh !important;
  background: linear-gradient(180deg, #2b3627 0%, #8FAF87 100%);
  color: #3e2723;
  display: flex !important;
  flex-direction: column !important;
  z-index: 100;
  box-shadow: 3px 0 20px rgba(0,0,0,0.25);
  overflow-y: auto;
  grid-column: unset !important;
  grid-row: unset !important;
}
.sidebar .nav-item,
.sidebar .sidebar-logo,
.sidebar .sidebar-logo img,
.sidebar svg {
  color: inherit;
}
.sidebar-logo {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 20px 18px 16px;
  border-bottom: 1px solid rgba(255,255,255,0.10);
  flex-shrink: 0;
  justify-content: center;
}
.sidebar-logo img {
  width: 100px;   
  height: auto;
  object-fit: contain;
}
/* ─── Main content ─── */
.main-content {
  margin-left: var(--sidebar-w) !important;
  padding-top: 64px;
  min-height: 100vh;
}
/* ─── Topbar ─── */
.topbar {
  position: fixed !important;
  top: 0;
  left: var(--sidebar-w) !important;
  right: 0;
  height: 64px;
  z-index: 99;
}
</style>

</head>

<body class="<?= $backoffice ? 'backoffice' : 'frontoffice' ?>">
 
<aside class="sidebar">
  <div class="sidebar-logo">
   <img src="<?= htmlspecialchars(assetUrl('img/logo.png'), ENT_QUOTES, 'UTF-8') ?>" alt="FoodWise Logo">
  </div>

  <nav class="sidebar-nav">
    <?php if (!$backoffice): ?>

      <a href="#" class="nav-item <?= $activeNav === 'recherche' ? 'active' : '' ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
         Recherche filtrée
      </a>
      <a href="?route=recettes" 
   class="nav-item <?= $activeNav === 'recettes' ? 'active' : '' ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
        Mes Recettes
      </a>

    
      <a href="?route=mealplanner/front/home/index" class="nav-item <?= $activeNav === 'planificateur' ? 'active' : '' ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
        Planificateur
      </a>
      <a href="?route=mealplanner/front/objectif/index" class="nav-item <?= $activeNav === 'objectifs' ? 'active' : '' ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="4"/><path d="M12 3v2M12 19v2M3 12h2M19 12h2"/></svg>
        Mes Objectifs
      </a>
      <a href="?route=mealplanner/front/digital_twin/index" class="nav-item <?= $activeNav === 'digital_twin' ? 'active' : '' ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 2a5 5 0 1 0 5 5 5 5 0 0 0-5-5zM12 14c-7 0-8 4-8 4v2h16v-2s-1-4-8-4z"/></svg>
        Jumeau Numérique
      </a>
      <a href="?route=mealplanner/front/magic_recipe/index" class="nav-item <?= $activeNav === 'magic_recipe' ? 'active' : '' ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
        Import IA (Magic)
      </a>
      <a href="?route=mealplanner/front/credit/index" class="nav-item <?= $activeNav === 'credits' ? 'active' : '' ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="10"/><path d="M12 6v12M6 12h12"/></svg>
        Mon Wallet
      </a>
      <a href="?route=offres" class="nav-item <?= $activeNav === 'marche' ? 'active' : '' ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
        Le Marché
      </a>
      <a href="?route=community" class="nav-item <?= $activeNav === 'community' ? 'active' : '' ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        Communauté
      </a>

      <!-- ── FoodLog ── -->
      <p style="padding:12px 18px 4px;font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:.1em;color:rgba(255,255,255,.5);margin:0;">FoodLog</p>
      <a href="?route=foodlog/journal" class="nav-item <?= $activeNav === 'foodlog_journal' ? 'active' : '' ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
        Journal alimentaire
      </a>
      <a href="?route=foodlog/suivi" class="nav-item <?= $activeNav === 'foodlog_suivi' ? 'active' : '' ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
        Suivi Santé
      </a>
      <a href="?route=foodlog/resume" class="nav-item <?= $activeNav === 'foodlog_resume' ? 'active' : '' ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M18 20V10M12 20V4M6 20v-6"/></svg>
        Résumé du jour
      </a>
      <a href="#" class="nav-item <?= $activeNav === 'nutrition' ? 'active' : '' ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="4"/><path d="M12 3v2M12 19v2M3 12h2M19 12h2"/></svg>
        Mes Objectifs
      </a>
    <?php endif; ?>
  </nav>
</aside>

 
<!-- ═══════════════════════════════════════════
     TOPBAR
═══════════════════════════════════════════ -->
<div class="main-content">
  <header class="topbar">
    <div class="topbar-search">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--texte-leger)" stroke-width="2">
        <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
      </svg>
      <input type="text" id="topbarSearch"
             placeholder="Rechercher des commerçants, offres, localisation..."
             autocomplete="off">
      <button onclick="doTopbarSearch()">Chercher</button>
    </div>
 
    <nav class="topbar-nav">
      <a href="?route=recettes" class="<?= $activeNav === 'recettes' ? 'active' : '' ?>">Mes Recettes</a>
      <a href="?route=mealplanner/front/home/index" class="<?= $activeNav === 'planificateur' ? 'active' : '' ?>">Planificateur</a>
      <a href="?route=mealplanner/front/objectif/index" class="<?= $activeNav === 'objectifs' ? 'active' : '' ?>">Mes Objectifs</a>
      <a href="?route=offres" class="<?= $activeNav === 'marche' ? 'active' : '' ?>">Le Marché</a>
      <a href="?route=community" class="<?= $activeNav === 'community' ? 'active' : '' ?>">Communauté</a>
    </nav>
 
    <a class="topbar-user" href="?route=module2/front/mon_profil" style="text-decoration:none;">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--texte-moyen)" stroke-width="2">
        <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/>
        <circle cx="12" cy="7" r="4"/>
      </svg>
      <span><?= htmlspecialchars(trim((string)($fwCurrentUser['prenom'] ?? '') . ' ' . (string)($fwCurrentUser['nom'] ?? '')) ?: 'Mon Compte') ?></span>
    </a>
  </header>
 
  <!-- Flash messages -->
  <?php if (!empty($_SESSION['flash_success'])): ?>
  <div class="flash flash-success" id="flash-msg">
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
      <polyline points="20 6 9 17 4 12"/>
    </svg>
    <?= $_SESSION['flash_success'] ?>
    <button class="flash-close" onclick="this.parentElement.remove()">×</button>
  </div>
  <?php unset($_SESSION['flash_success']); ?>
  <?php endif; ?>
 
  <?php if (!empty($_SESSION['flash_error'])): ?>
  <div class="flash flash-error" id="flash-msg">
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
      <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/>
      <line x1="12" y1="16" x2="12.01" y2="16"/>
    </svg>
    <?= $_SESSION['flash_error'] ?>
    <button class="flash-close" onclick="this.parentElement.remove()">×</button>
  </div>
  <?php unset($_SESSION['flash_error']); ?>
  <?php endif; ?>
 
  <div class="page-body">
