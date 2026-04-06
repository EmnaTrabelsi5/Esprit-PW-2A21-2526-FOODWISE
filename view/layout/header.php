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
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;700&family=Lato:wght@300;400;700&display=swap" rel="stylesheet">
<style>
:root {
  --brun-fonce:   #4E2C0E;
  --brun-moyen:   #7B3F1A;
  --brun-chaud:   #A0522D;
  --brun-clair:   #C68B5A;
  --brun-pale:    #E8C9A0;
  --vert-fonce:   #3A5C1E;
  --vert-moyen:   #5C7A3E;
  --vert-pale:    #C8DEB0;
  --creme:        #FDF6EC;
  --creme-fonce:  #F0E2C8;
  --blanc:        #FFFFFF;
  --texte-sombre: #2A1A0A;
  --texte-moyen:  #5C3D20;
  --texte-leger:  #9B7355;
  --alerte-rouge: #C0392B;
  --alerte-orange:#E67E22;
  --alerte-vert:  #27AE60;
  --sidebar-w:    220px;
  --radius:       10px;
  --shadow:       0 2px 12px rgba(78,44,14,0.10);
  --shadow-hover: 0 6px 24px rgba(78,44,14,0.18);
  --transition:   0.2s ease;
}
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

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
.sidebar-nav { padding: 12px 0; flex: 1; }
.nav-item {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px 18px;
  color: rgba(253,246,236,0.72);
  text-decoration: none;
  font-size: 14px;
  font-weight: 400;
  border-left: 3px solid transparent;
  transition: var(--transition);
  white-space: nowrap;
}
.nav-item svg {
  width: 17px;
  height: 17px;
  flex-shrink: 0;
  display: block; /* empêche le SVG de grossir sans viewBox ni width fixe */
}
.nav-item:hover { background: rgba(255,255,255,0.08); color: #FDF6EC; border-left-color: #E8C9A0; }
.nav-item.active { background: rgba(255,255,255,0.13); color: #FDF6EC; border-left-color: #E8C9A0; font-weight: 700; }

/* ─── Topbar ─── */
.topbar {
  position: fixed;
  top: 0; left: var(--sidebar-w); right: 0;
  height: 60px;
  background: var(--blanc);
  border-bottom: 1px solid var(--creme-fonce);
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 0 24px;
  z-index: 90;
  box-shadow: 0 2px 8px rgba(78,44,14,0.07);
}
.topbar-search {
  flex: 1;
  max-width: 400px;
  display: flex;
  align-items: center;
  background: var(--creme);
  border: 1.5px solid var(--brun-pale);
  border-radius: 30px;
  padding: 0 6px 0 14px;
  gap: 6px;
}
.topbar-search input {
  flex: 1; border: none; background: transparent;
  padding: 8px 0; font-family: 'Lato', Arial, sans-serif;
  font-size: 13px; color: var(--texte-sombre); outline: none; min-width: 0;
}
.topbar-search button {
  background: var(--brun-chaud); border: none; border-radius: 20px;
  padding: 5px 12px; color: var(--creme); font-size: 12px;
  cursor: pointer; transition: var(--transition); white-space: nowrap;
  font-family: 'Lato', Arial, sans-serif;
}
.topbar-search button:hover { background: var(--brun-moyen); }
.topbar-nav { display: flex; gap: 4px; }
.topbar-nav a {
  padding: 6px 13px; border-radius: 20px; font-size: 13px;
  color: var(--texte-moyen); text-decoration: none; transition: var(--transition); white-space: nowrap;
}
.topbar-nav a:hover, .topbar-nav a.active { background: var(--brun-pale); color: var(--brun-fonce); }
.topbar-user {
  display: flex; align-items: center; gap: 7px; cursor: pointer;
  padding: 4px 10px; border-radius: 25px; border: 1.5px solid var(--brun-pale);
  transition: var(--transition); margin-left: auto; flex-shrink: 0;
}
.topbar-user:hover { background: var(--creme-fonce); }
.topbar-user img { width: 28px; height: 28px; border-radius: 50%; object-fit: cover; display: block; }
.topbar-user span { font-size: 13px; font-weight: 700; color: var(--texte-moyen); }

/* ─── Layout ─── */
.main-content {
  margin-left: var(--sidebar-w);
  padding-top: 60px;
  min-height: 100vh;
  background: var(--creme);
}
.page-body { padding: 26px 30px; }

/* ─── Typographie ─── */
.page-title {
  font-family: 'Playfair Display', Georgia, serif;
  font-size: 25px; font-weight: 700;
  color: var(--brun-fonce); margin-bottom: 4px;
}
.page-subtitle { font-size: 13px; color: var(--texte-leger); margin-bottom: 22px; }

/* ─── Cards ─── */
.card {
  background: var(--blanc); border-radius: var(--radius);
  box-shadow: var(--shadow); padding: 22px; margin-bottom: 20px;
}
.card-header {
  display: flex; align-items: center; justify-content: space-between;
  margin-bottom: 16px; padding-bottom: 12px;
  border-bottom: 1px solid var(--creme-fonce);
}
.card-title {
  font-family: 'Playfair Display', Georgia, serif;
  font-size: 17px; font-weight: 700; color: var(--brun-fonce);
}

/* ─── Grille recettes ─── */
.recipe-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(230px, 1fr));
  gap: 20px;
}
.recipe-card {
  background: var(--blanc); border-radius: var(--radius);
  overflow: hidden; box-shadow: var(--shadow);
  transition: var(--transition); text-decoration: none; color: inherit;
  display: flex; flex-direction: column;
}
.recipe-card:hover { transform: translateY(-3px); box-shadow: var(--shadow-hover); }
.recipe-card-img { width: 100%; height: 155px; object-fit: cover; background: var(--creme-fonce); display: block; }
.recipe-card-img-placeholder {
  width: 100%; height: 155px; background: var(--creme-fonce);
  display: flex; align-items: center; justify-content: center; font-size: 36px;
}
.recipe-card-body { padding: 13px 15px 15px; flex: 1; display: flex; flex-direction: column; }
.recipe-card-title {
  font-family: 'Playfair Display', Georgia, serif;
  font-size: 15px; font-weight: 700; color: var(--brun-fonce); margin-bottom: 5px;
}
.recipe-card-meta { display: flex; gap: 6px; flex-wrap: wrap; margin-top: auto; padding-top: 9px; }

/* ─── Badges ─── */
.badge { display: inline-block; padding: 2px 8px; border-radius: 20px; font-size: 11px; font-weight: 700; }
.badge-vert   { background: var(--vert-pale);   color: var(--vert-fonce); }
.badge-brun   { background: var(--creme-fonce);  color: var(--brun-moyen); }
.badge-orange { background: #FDEBD0; color: #784212; }
.badge-rouge  { background: #FADBD8; color: #922B21; }
.badge-info   { background: #D6EAF8; color: #1A5276; }

/* ─── Boutons ─── */
.btn {
  display: inline-flex; align-items: center; gap: 6px;
  padding: 8px 18px; border-radius: 25px;
  font-family: 'Lato', Arial, sans-serif; font-size: 14px; font-weight: 700;
  cursor: pointer; border: none; transition: var(--transition);
  text-decoration: none; line-height: 1.4;
}
.btn-primary   { background: var(--brun-chaud); color: var(--creme); }
.btn-primary:hover  { background: var(--brun-moyen); }
.btn-secondary { background: var(--vert-moyen); color: var(--creme); }
.btn-secondary:hover { background: var(--vert-fonce); }
.btn-outline   { background: transparent; color: var(--brun-chaud); border: 2px solid var(--brun-chaud); }
.btn-outline:hover { background: var(--brun-pale); }
.btn-danger    { background: var(--alerte-rouge); color: #fff; }
.btn-danger:hover { background: #922B21; }
.btn-sm { padding: 5px 13px; font-size: 12px; }

/* ─── Formulaires ─── */
.form-group { margin-bottom: 16px; }
.form-label {
  display: block; font-size: 12px; font-weight: 700;
  color: var(--texte-moyen); margin-bottom: 5px;
  text-transform: uppercase; letter-spacing: 0.4px;
}
.form-control {
  width: 100%; padding: 9px 13px;
  border: 1.5px solid var(--brun-pale); border-radius: var(--radius);
  font-family: 'Lato', Arial, sans-serif; font-size: 14px;
  background: var(--creme); color: var(--texte-sombre);
  transition: var(--transition); outline: none;
}
.form-control:focus {
  border-color: var(--brun-chaud); background: var(--blanc);
  box-shadow: 0 0 0 3px rgba(160,82,45,0.10);
}
textarea.form-control { resize: vertical; min-height: 85px; }
select.form-control { cursor: pointer; }
.form-check { display: flex; align-items: center; gap: 9px; margin-bottom: 8px; cursor: pointer; }
.form-check input[type="checkbox"] { width: 16px; height: 16px; accent-color: var(--brun-chaud); cursor: pointer; flex-shrink: 0; }
.form-check span { font-size: 14px; color: var(--texte-sombre); }

/* ─── Table ─── */
.fw-table { width: 100%; border-collapse: collapse; }
.fw-table th {
  background: var(--creme-fonce); color: var(--brun-moyen);
  font-size: 11px; font-weight: 700; text-transform: uppercase;
  letter-spacing: 0.5px; padding: 9px 15px; text-align: left;
}
.fw-table td { padding: 11px 15px; border-bottom: 1px solid var(--creme-fonce); font-size: 13px; vertical-align: middle; }
.fw-table tr:last-child td { border-bottom: none; }
.fw-table tr:hover td { background: #FBF4E8; }

/* ─── Filtre bar ─── */
.filter-bar {
  display: flex; flex-wrap: wrap; gap: 9px; align-items: center;
  background: var(--blanc); border-radius: var(--radius);
  padding: 12px 18px; box-shadow: var(--shadow); margin-bottom: 20px;
}
.filter-bar select,
.filter-bar input[type="text"],
.filter-bar input[type="number"] {
  border: 1.5px solid var(--brun-pale); border-radius: 20px;
  padding: 6px 13px; font-size: 13px;
  font-family: 'Lato', Arial, sans-serif;
  background: var(--creme); color: var(--texte-sombre); outline: none; cursor: pointer;
}

/* ─── Substitut alerte ─── */
.substitut-alert {
  background: #FEF9E7; border: 1.5px solid #F0C040;
  border-radius: var(--radius); padding: 11px 15px;
  display: flex; align-items: flex-start; gap: 9px;
  font-size: 13px; color: #7D6608; margin-top: 8px;
}

/* ─── Pagination ─── */
.pagination { display: flex; gap: 5px; align-items: center; justify-content: center; padding-top: 18px; }
.pagination a, .pagination span {
  display: inline-flex; align-items: center; justify-content: center;
  width: 32px; height: 32px; border-radius: 50%;
  font-size: 13px; font-weight: 700; text-decoration: none;
  transition: var(--transition); color: var(--brun-moyen);
}
.pagination a:hover { background: var(--creme-fonce); }
.pagination .current { background: var(--brun-chaud); color: var(--creme); }

/* ─── Footer ─── */
.site-footer {
  text-align: center; padding: 18px 30px;
  font-size: 12px; color: var(--texte-leger);
  border-top: 1px solid var(--creme-fonce); margin-top: 36px;
  background: var(--creme);
}
.site-footer a { color: var(--brun-clair); text-decoration: none; }
</style>
</head>
<body>

<!-- ========== SIDEBAR ========== -->
<aside class="sidebar">
  <div class="sidebar-logo">
   <img src="/FOODWISE/assets/img/logo.png" alt="FoodWise Logo">
  </div>

  <nav class="sidebar-nav">
    <?php if (!$backoffice): ?>
      <a href="#" class="nav-item <?= $activeNav === 'recettes' ? 'active' : '' ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
        Mes Recettes
      </a>
      <a href="#" class="nav-item <?= $activeNav === 'recherche' ? 'active' : '' ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
        Recherche filtrée
      </a>
      <a href="#" class="nav-item <?= $activeNav === 'ingredients' ? 'active' : '' ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 2a4 4 0 014 4c0 2.5-2 5-4 7-2-2-4-4.5-4-7a4 4 0 014-4z"/><path d="M12 13v9M8 17h8"/></svg>
        Ingrédients
      </a>
      <a href="#" class="nav-item <?= $activeNav === 'planificateur' ? 'active' : '' ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
        Planificateur
      </a>
      <a href="#" class="nav-item <?= $activeNav === 'objectifs' ? 'active' : '' ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="4"/><path d="M12 3v2M12 19v2M3 12h2M19 12h2"/></svg>
        Mes Objectifs
      </a>
      <a href="#" class="nav-item <?= $activeNav === 'marche' ? 'active' : '' ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
        Le Marché
      </a>
      <a href="#" class="nav-item <?= $activeNav === 'nutrition' ? 'active' : '' ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M18 20V10M12 20V4M6 20v-6"/></svg>
        Suivi Nutritionnel
      </a>
    <?php else: ?>
      <a href="#" class="nav-item <?= $activeNav === 'dashboard' ? 'active' : '' ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
        Tableau de bord
      </a>
      <a href="#" class="nav-item <?= $activeNav === 'gestion_recettes' ? 'active' : '' ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
        Gestion Recettes
      </a>
      <a href="#" class="nav-item <?= $activeNav === 'gestion_ingredients' ? 'active' : '' ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="7" r="4"/><path d="M6 21v-2a4 4 0 014-4h4a4 4 0 014 4v2"/></svg>
        Base Ingrédients
      </a>
      <a href="#" class="nav-item <?= $activeNav === 'substituts' ? 'active' : '' ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M7 16V4m0 0L3 8m4-4l4 4M17 8v12m0 0l4-4m-4 4l-4-4"/></svg>
        Substituts
      </a>
      <a href="#" class="nav-item <?= $activeNav === 'partenaires' ? 'active' : '' ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2M9 7a4 4 0 100 8 4 4 0 000-8zM23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg>
        Offres Partenaires
      </a>
      <a href="#" class="nav-item <?= $activeNav === 'suivi' ? 'active' : '' ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M18 20V10M12 20V4M6 20v-6"/></svg>
        Suivi Nutritionnel
      </a>
      <a href="#" class="nav-item <?= $activeNav === 'utilisateurs' ? 'active' : '' ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
        Utilisateurs
      </a>
      <a href="#" class="nav-item <?= $activeNav === 'parametres' ? 'active' : '' ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-4 0v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83-2.83l.06-.06A1.65 1.65 0 004.68 15a1.65 1.65 0 00-1.51-1H3a2 2 0 010-4h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 012.83-2.83l.06.06A1.65 1.65 0 009 4.68a1.65 1.65 0 001-1.51V3a2 2 0 014 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 2.83l-.06.06A1.65 1.65 0 0019.4 9a1.65 1.65 0 001.51 1H21a2 2 0 010 4h-.09a1.65 1.65 0 00-1.51 1z"/></svg>
        Paramètres
      </a>
    <?php endif; ?>
  </nav>
</aside>

<!-- ========== TOPBAR ========== -->
<header class="topbar">
  <div class="topbar-search">
    <input type="text" id="search-input"
           placeholder="Rechercher des Recettes, Ingrédients..."
           autocomplete="off">
    <button type="button" onclick="doSearch()">Rechercher</button>
  </div>
  <nav class="topbar-nav">
    <a href="#" class="<?= $activeNav === 'recettes' ? 'active' : '' ?>">Mes Recettes</a>
    <a href="#" class="<?= $activeNav === 'planificateur' ? 'active' : '' ?>">Planificateur</a>
    <a href="#" class="<?= $activeNav === 'objectifs' ? 'active' : '' ?>">Mes Objectifs</a>
    <a href="#" class="<?= $activeNav === 'marche' ? 'active' : '' ?>">Le Marché</a>
  </nav>
  <div class="topbar-user">
    <img src="https://ui-avatars.com/api/?name=Lucas+Organic&background=A0522D&color=FDF6EC&bold=true&size=64" alt="Avatar">
    <span>Lucas_Organic</span>
    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="flex-shrink:0;display:block;"><path d="M6 9l6 6 6-6"/></svg>
  </div>
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
