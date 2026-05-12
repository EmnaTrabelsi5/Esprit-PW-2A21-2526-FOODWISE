<?php
/**
 * Layout Front-office Module 2 — en-tête
 *
 * @var string $pageTitle
 * @var array<string,string>|null $routesModule2
 */

declare(strict_types=1);

$pageTitle = $pageTitle ?? 'FoodWise — Mon profil';
$cssUrl = assetUrl('module2-foodwise.css');
$backoffice = $backoffice ?? false;

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
  background: linear-gradient(180deg, #2b3627 0%, #8FAF87 100%);
  color: #3e2723;
  display: flex;
  flex-direction: column;
  z-index: 100;
  box-shadow: 3px 0 20px rgba(0,0,0,0.25);
  overflow-y: auto;
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
}
.sidebar-logo {
  justify-content: center;
}
.sidebar-logo img {
  width: 100px;   
  height: auto;
  object-fit: contain;
}
  </style>
</head>

<!-- modificationnnnn  -->
<body class="<?= $backoffice ? 'backoffice' : 'frontoffice' ?><?= !empty($hideSidebar) ? ' fw-no-sidebar' : '' ?>">

<!-- ========== SIDEBAR ========== -->
<?php if (empty($hideSidebar) && !empty($_SESSION['user_id'])): ?>
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

    
      <a href="#" class="nav-item <?= $activeNav === 'planificateur' ? 'active' : '' ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
        Planificateur
      </a>
      <a href="#" class="nav-item <?= $activeNav === 'objectifs' ? 'active' : '' ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="4"/><path d="M12 3v2M12 19v2M3 12h2M19 12h2"/></svg>
        Mes Objectifs
      </a>
      <a href="?route=offres" class="nav-item <?= $activeNav === 'marche' ? 'active' : '' ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
        Le Marché
      </a>
      <a href="?route=community" class="nav-item <?= $activeNav === 'community' ? 'active' : '' ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        Communauté
      </a>
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
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M18 20V10M12 20V4M6 20v-6"/></svg>
        Suivi Nutritionnel
      </a>
    <?php endif; ?>
  </nav>
</aside>
<?php endif; ?>
  <div class="fw-main-wrap">
    <?php if (empty($hideTopbar)) { ?>
    <header class="fw-topbar" role="banner">
      <h1 class="fw-topbar__title"><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></h1>
      <div class="fw-topbar__links" style="display:flex; gap:1rem; align-items:center;">
        <?php
        if (!isset($currentUser) && isset($utilisateur)) {
            $currentUser = $utilisateur;
        }
        $currentUser = $currentUser ?? null;
        $userName = $currentUser ? trim(($currentUser['prenom'] ?? '') . ' ' . ($currentUser['nom'] ?? '')) : 'Mon Compte';
        $userImage = $currentUser['photo_profil'] ?? null;
        ?>
        <a href="<?= htmlspecialchars($routesModule2['front_mon_profil'] ?? '#', ENT_QUOTES, 'UTF-8') ?>" style="display:inline-flex; align-items:center; gap:0.5rem; text-decoration:none; color:inherit;">
          <?php if ($userImage): ?>
            <img src="<?= htmlspecialchars(assetUrl('uploads/' . $userImage), ENT_QUOTES, 'UTF-8') ?>" alt="Avatar de <?= htmlspecialchars($userName, ENT_QUOTES, 'UTF-8') ?>" style="width: 28px; height: 28px; border-radius: 50%; object-fit:cover;">
          <?php else: ?>
            <span aria-hidden="true" style="font-size:1.1rem;">👤</span>
          <?php endif; ?>
          <span><?= htmlspecialchars($userName, ENT_QUOTES, 'UTF-8') ?></span>
        </a>
        <a href="#"><span aria-hidden="true">❓</span> Aide</a>
        <a href="<?= htmlspecialchars($routesModule2['front_logout'] ?? '#', ENT_QUOTES, 'UTF-8') ?>" style="color:var(--fw-alert)"><span aria-hidden="true">🚪</span> Déconnexion</a>
      </div>
    </header>
    <?php } ?>

