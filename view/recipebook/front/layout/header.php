<?php
/**
 * FoodWise — Layout : Sidebar + Topbar
 * CSS embarqué — fonctionne sans routing, directement sous XAMPP
 * views/layout/header.php
 */
$pageTitle  = $pageTitle  ?? 'FoodWise';
$activeNav  = $activeNav  ?? '';
$backoffice = $backoffice ?? false;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($pageTitle) ?> — FoodWise</title>
  <link rel="stylesheet" href="/FOODWISE/assets/foodwise.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;700&family=Lato:wght@300;400;700&display=swap" rel="stylesheet">
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
<body class="<?= $backoffice ? 'backoffice' : 'frontoffice' ?>">

<!-- ========== SIDEBAR ========== -->
<aside class="sidebar">
  <div class="sidebar-logo">
   <img src="/FOODWISE/assets/img/logo.png" alt="FoodWise Logo">
  </div>

  <nav class="sidebar-nav">
    <?php if (!$backoffice): ?>

      <a href="#" class="nav-item <?= $activeNav === 'recherche' ? 'active' : '' ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
         Recherche filtrée
      </a>
      <a href="/FOODWISE/recettes" 
   class="nav-item <?= $activeNav === 'recettes' ? 'active' : '' ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
        Recettes
      </a>

    
      <a href="#" class="nav-item <?= $activeNav === 'planificateur' ? 'active' : '' ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
        Planificateur
      </a>
      <a href="#" class="nav-item <?= $activeNav === 'objectifs' ? 'active' : '' ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="4"/><path d="M12 3v2M12 19v2M3 12h2M19 12h2"/></svg>
        Mes Objectifs
      </a>
      <a href="/FOODWISE/offres" class="nav-item <?= $activeNav === 'marche' ? 'active' : '' ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
        Le Marché
      </a>
      <a href="#" class="nav-item <?= $activeNav === 'nutrition' ? 'active' : '' ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M18 20V10M12 20V4M6 20v-6"/></svg>
        Suivi Nutritionnel
      </a>
    <?php endif; ?>
  </nav>
</aside>

<!-- ========== TOPBAR ========== -->
<header class="topbar">
  <?php if (!$backoffice): ?>
  <?php
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $currentUser = null;
    if (!empty($_SESSION['user_id']) && class_exists('UtilisateurModel')) {
        $currentUser = (new UtilisateurModel(config::getConnexion()))->findById((int) $_SESSION['user_id']);
    }
    $userName = $currentUser ? trim(($currentUser['prenom'] ?? '') . ' ' . ($currentUser['nom'] ?? '')) : null;
    $userImage = $currentUser['photo_profil'] ?? null;
    $profileRoute = '?route=module2.front.mon_profil';
  ?>
  <div class="topbar-search">
    <input type="text" id="search-input"
           placeholder=""
           autocomplete="off">
    <button type="button" onclick="doSearch()">Rechercher</button>
  </div>
  <nav class="topbar-nav">
    <a href="/FOODWISE/recettes" 
   class="<?= $activeNav === 'recettes' ? 'active' : '' ?>">
   Mes Recettes
    </a>
    <a href="#" class="<?= $activeNav === 'planificateur' ? 'active' : '' ?>">Planificateur</a>
    <a href="#" class="<?= $activeNav === 'objectifs' ? 'active' : '' ?>">Mes Objectifs</a>
    <a href="/FOODWISE/offres" class="<?= $activeNav === 'marche' ? 'active' : '' ?>">Le Marché</a>
  </nav>
  <div class="topbar-user">
    <?php if ($currentUser): ?>
      <a href="<?= htmlspecialchars($profileRoute, ENT_QUOTES, 'UTF-8') ?>" style="display:inline-flex; align-items:center; gap:0.5rem; text-decoration:none; color:inherit;">
        <?php if ($userImage): ?>
          <img src="/FOODWISE/uploads/<?= htmlspecialchars($userImage, ENT_QUOTES, 'UTF-8') ?>" alt="Avatar de <?= htmlspecialchars($userName, ENT_QUOTES, 'UTF-8') ?>" style="width:32px; height:32px; border-radius:50%; object-fit:cover;">
        <?php else: ?>
          <span aria-hidden="true" style="font-size:1.1rem;">👤</span>
        <?php endif; ?>
        <span><?= htmlspecialchars($userName ?: 'Mon Compte', ENT_QUOTES, 'UTF-8') ?></span>
      </a>
    <?php else: ?>
      <a href="?route=module2.front.connexion" style="display:inline-flex; align-items:center; gap:0.5rem; text-decoration:none; color:inherit;">
        <span aria-hidden="true">👤</span>
        <span>Connexion</span>
      </a>
    <?php endif; ?>
    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="flex-shrink:0;display:block;"><path d="M6 9l6 6 6-6"/></svg>
  </div>
  <?php endif; ?>
</header>


<main class="main-content">
<div class="page-body">
<script>
function doSearch() {
  var q = document.getElementById('search-input').value.trim();
  if (q) window.location.href = '?q=' + encodeURIComponent(q);
}
document.getElementById('search-input').addEventListener('keydown', function(e){
  if (e.key === 'Enter') doSearch();
});
</script>
