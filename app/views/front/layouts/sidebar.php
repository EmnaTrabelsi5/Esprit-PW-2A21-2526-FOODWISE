<?php
/** Barre latérale client — Mon Planning, Mes Recettes, Mon Score Santé */
$active = $sidebarActive ?? 'planning';
$brandLogoUrl = $brandLogoUrl ?? '../../../public/assets/img/foodwise-logo.png';
?>
<aside class="sidebar" role="navigation" aria-label="Navigation client">
  <div class="brand">
    <a class="brand__link" href="#" aria-label="FoodWise — Accueil">
      <img class="brand__logo" src="<?= htmlspecialchars($brandLogoUrl, ENT_QUOTES, 'UTF-8') ?>" alt="FoodWise" decoding="async">
    </a>
  </div>
  <ul class="sidebar__nav">
    <li>
      <a class="sidebar__link<?= $active === 'planning' ? ' sidebar__link--active' : '' ?>" href="#">
        <svg class="sidebar__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
        Mon Planning
      </a>
    </li>
    <li>
      <a class="sidebar__link<?= $active === 'recettes' ? ' sidebar__link--active' : '' ?>" href="#">
        <svg class="sidebar__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
        Mes Recettes
      </a>
    </li>
    <li>
      <a class="sidebar__link<?= $active === 'score' ? ' sidebar__link--active' : '' ?>" href="#">
        <svg class="sidebar__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
        Mon Score Santé
      </a>
    </li>
  </ul>
</aside>
