<?php
/** Barre latérale navigation admin — MealPlanner */
$active = $sidebarActive ?? 'dashboard';
$brandLogoUrl = $brandLogoUrl ?? '../../../public/assets/img/foodwise-logo.png';
?>
<aside class="sidebar" role="navigation" aria-label="Navigation principale">
  <div class="brand">
    <a class="brand__link" href="#" aria-label="FoodWise — Accueil">
      <img class="brand__logo" src="<?= htmlspecialchars($brandLogoUrl, ENT_QUOTES, 'UTF-8') ?>" alt="FoodWise" decoding="async">
    </a>
  </div>
  <ul class="sidebar__nav">
    <li>
      <a class="sidebar__link<?= $active === 'dashboard' ? ' sidebar__link--active' : '' ?>" href="#">
        <svg class="sidebar__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
        Tableau de Bord
      </a>
    </li>
    <li>
      <a class="sidebar__link<?= $active === 'recettes' ? ' sidebar__link--active' : '' ?>" href="#">
        <svg class="sidebar__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
        Gestion Recettes
      </a>
    </li>
    <li>
      <a class="sidebar__link<?= $active === 'ingredients' ? ' sidebar__link--active' : '' ?>" href="#">
        <svg class="sidebar__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2C8 6 4 10 4 14a8 8 0 0 0 16 0c0-4-4-8-8-12z"/></svg>
        Base Ingrédients
      </a>
    </li>
    <li>
      <a class="sidebar__link<?= $active === 'partenaires' ? ' sidebar__link--active' : '' ?>" href="#">
        <svg class="sidebar__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/></svg>
        Offres Partenaires
      </a>
    </li>
    <li>
      <a class="sidebar__link<?= $active === 'nutrition' ? ' sidebar__link--active' : '' ?>" href="#">
        <svg class="sidebar__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg>
        Suivi Nutritionnel
      </a>
    </li>
    <li>
      <a class="sidebar__link<?= $active === 'utilisateurs' ? ' sidebar__link--active' : '' ?>" href="#">
        <svg class="sidebar__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        Utilisateurs
      </a>
    </li>
    <li>
      <a class="sidebar__link<?= $active === 'parametres' ? ' sidebar__link--active' : '' ?>" href="#">
        <svg class="sidebar__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/></svg>
        Paramètres
      </a>
    </li>
  </ul>
</aside>
