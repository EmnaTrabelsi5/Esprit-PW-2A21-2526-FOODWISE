<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($pageTitle ?? 'LocalMarket') ?> — FoodWise</title>
  <!-- CSS commun FoodWise -->
  <link rel="stylesheet" href="/FOODWISE1/assets/foodwise.css">
  <!-- CSS module LocalMarket -->
  <link rel="stylesheet" href="/FOODWISE1/assets/localmarket.css">
</head>
<body>
 
<!-- ═══════════════════════════════════════════
     SIDEBAR
═══════════════════════════════════════════ -->
<aside class="sidebar">
  <div class="sidebar-logo">
    <img src="/FOODWISE1/assets/logo.png" alt="FoodWise">
    <span class="sidebar-logo-text">Food<span>Wise</span></span>
  </div>
 
  <nav class="sidebar-nav">
    <a href="../index.php"
       class="nav-item <?= ($activeModule ?? '') === 'dashboard'   ? 'active' : '' ?>">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/>
        <rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/>
      </svg>
      Tableau de Bord
    </a>
 
    <a href="../recipebook/recette.php"
       class="nav-item <?= ($activeModule ?? '') === 'recipe'      ? 'active' : '' ?>">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2"/>
        <rect x="9" y="3" width="6" height="4" rx="1"/>
      </svg>
      Gestion Recettes
    </a>
 
    <a href="../recipebook/ingredient.php"
       class="nav-item <?= ($activeModule ?? '') === 'ingredient'  ? 'active' : '' ?>">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M12 2a9 9 0 00-9 9c0 4.17 2.84 7.67 6.69 8.69L12 22l2.31-2.31C18.16 18.67 21 15.17 21 11a9 9 0 00-9-9z"/>
      </svg>
      Base Ingrédients
    </a>
 
    <a href="offre.php?action=index"
       class="nav-item <?= ($activeModule ?? '') === 'offre'       ? 'active' : '' ?>">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/>
        <path d="M1 1h4l2.68 13.39a2 2 0 001.95 1.61h9.72a2 2 0 001.95-1.61L23 6H6"/>
      </svg>
      Offres Partenaires
    </a>
 
    <a href="commercant.php?action=index"
       class="nav-item <?= ($activeModule ?? '') === 'commercant'  ? 'active' : '' ?>">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M3 9l1-5h16l1 5"/><path d="M3 9h18v11a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
        <path d="M9 22V12h6v10"/>
      </svg>
      Commerçants
    </a>
 
    <a href="../nutriprofile/utilisateur.php"
       class="nav-item <?= ($activeModule ?? '') === 'nutri'       ? 'active' : '' ?>">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
      </svg>
      Suivi Nutritionnel
    </a>
 
    <a href="../nutriprofile/utilisateur.php"
       class="nav-item <?= ($activeModule ?? '') === 'users'       ? 'active' : '' ?>">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/>
        <circle cx="12" cy="7" r="4"/>
      </svg>
      Utilisateurs
    </a>
 
    <a href="#"
       class="nav-item <?= ($activeModule ?? '') === 'settings'    ? 'active' : '' ?>">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <circle cx="12" cy="12" r="3"/>
        <path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-2 2 2 2 0 01-2-2v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83 0 2 2 0 010-2.83l.06-.06A1.65 1.65 0 004.68 15a1.65 1.65 0 00-1.51-1H3a2 2 0 01-2-2 2 2 0 012-2h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 010-2.83 2 2 0 012.83 0l.06.06A1.65 1.65 0 009 4.68a1.65 1.65 0 001-1.51V3a2 2 0 012-2 2 2 0 012 2v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 0 2 2 0 010 2.83l-.06.06A1.65 1.65 0 0019.4 9a1.65 1.65 0 001.51 1H21a2 2 0 012 2 2 2 0 01-2 2h-.09a1.65 1.65 0 00-1.51 1z"/>
      </svg>
      Paramètres
    </a>
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
      <a href="offre.php?action=index"
         class="<?= ($activeModule ?? '') === 'offre'      ? 'active' : '' ?>">Le Marché</a>
      <a href="commercant.php?action=index"
         class="<?= ($activeModule ?? '') === 'commercant' ? 'active' : '' ?>">Commerçants</a>
      <a href="offre.php?action=create">+ Publier</a>
    </nav>
 
    <div class="topbar-user">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--texte-moyen)" stroke-width="2">
        <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/>
        <circle cx="12" cy="7" r="4"/>
      </svg>
      <span>Mon Compte</span>
    </div>
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