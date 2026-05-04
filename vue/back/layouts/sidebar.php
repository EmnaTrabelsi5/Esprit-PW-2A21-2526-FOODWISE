<?php
/** Barre latérale navigation admin — MealPlanner */
use Controller\Url;

$active = $sidebarActive ?? 'dashboard';
$brandLogoUrl = $brandLogoUrl ?? Url::asset('img/foodwise-logo.png');
?>
<aside class="sidebar" role="navigation" aria-label="Navigation principale">
  <div class="brand">
    <a class="brand__link" href="<?= htmlspecialchars(Url::to('back', 'home', 'index'), ENT_QUOTES, 'UTF-8') ?>">
      <img class="brand__logo" src="<?= htmlspecialchars($brandLogoUrl, ENT_QUOTES, 'UTF-8') ?>" alt="FoodWise">
    </a>
  </div>
  <ul class="sidebar__nav">
    <li>
      <a class="sidebar__link<?= $active === 'dashboard' ? ' sidebar__link--active' : '' ?>" href="<?= htmlspecialchars(Url::to('back', 'home', 'index'), ENT_QUOTES, 'UTF-8') ?>">
        <svg class="sidebar__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
        <span>Tableau de Bord</span>
      </a>
    </li>
    <li>
      <a class="sidebar__link<?= $active === 'plans' ? ' sidebar__link--active' : '' ?>" href="<?= htmlspecialchars(Url::to('back', 'plan_alimentaire', 'index'), ENT_QUOTES, 'UTF-8') ?>">
        <svg class="sidebar__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
        <span>Plans alimentaires</span>
      </a>
    </li>
    <li>
      <a class="sidebar__link<?= $active === 'objectif' ? ' sidebar__link--active' : '' ?>" href="<?= htmlspecialchars(Url::to('back', 'objectif', 'index'), ENT_QUOTES, 'UTF-8') ?>">
        <svg class="sidebar__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3"/></svg>
        <span>Objectifs nutritionnels</span>
      </a>
    </li>
    <li>
      <a class="sidebar__link<?= $active === 'recettes' ? ' sidebar__link--active' : '' ?>" href="<?= htmlspecialchars(Url::to('back', 'plan_recette', 'index'), ENT_QUOTES, 'UTF-8') ?>">
        <svg class="sidebar__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
        <span>Gestion Recettes</span>
      </a>
    </li>
    <li>
      <a class="sidebar__link" href="#">
        <svg class="sidebar__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M12 2C8 6 4 10 4 14a8 8 0 0 0 16 0c0-4-4-8-8-12z"/></svg>
        <span>Base Ingrédients</span>
      </a>
    </li>
    <li>
      <a class="sidebar__link" href="#">
        <svg class="sidebar__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/></svg>
        <span>Offres Partenaires</span>
      </a>
    </li>
    <li>
      <a class="sidebar__link" href="#">
        <svg class="sidebar__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
        <span>Suivi Nutritionnel</span>
      </a>
    </li>
    <li>
      <a class="sidebar__link" href="#">
        <svg class="sidebar__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
        <span>Utilisateurs</span>
      </a>
    </li>
    <li>
      <a class="sidebar__link" href="#">
        <svg class="sidebar__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="12" cy="12" r="3"/><path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2"/></svg>
        <span>Paramètres</span>
      </a>
    </li>
  </ul>
</aside>
