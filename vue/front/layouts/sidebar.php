<?php
/** Barre latérale client — Mon Planning, Mes Recettes, Mon Score Santé */
use Controller\Url;

$active = $sidebarActive ?? 'planning';
$brandLogoUrl = $brandLogoUrl ?? Url::asset('img/foodwise-logo.png');
?>
<aside class="sidebar" role="navigation" aria-label="Navigation client">
  <div class="brand">
    <a class="brand__link" href="<?= htmlspecialchars(Url::to('front', 'home', 'index'), ENT_QUOTES, 'UTF-8') ?>">
      <img class="brand__logo" src="<?= htmlspecialchars($brandLogoUrl, ENT_QUOTES, 'UTF-8') ?>" alt="FoodWise">
    </a>
  </div>
  <ul class="sidebar__nav">
    <li>
      <a class="sidebar__link<?= $active === 'planning' ? ' sidebar__link--active' : '' ?>" href="<?= htmlspecialchars(Url::to('front', 'home', 'index'), ENT_QUOTES, 'UTF-8') ?>">
        <svg class="sidebar__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
        <span>Mon Planning</span>
      </a>
    </li>
    <li>
      <a class="sidebar__link<?= $active === 'plan_alimentaire' ? ' sidebar__link--active' : '' ?>" href="<?= htmlspecialchars(Url::to('front', 'plan_alimentaire', 'index'), ENT_QUOTES, 'UTF-8') ?>">
        <svg class="sidebar__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
        <span>Plans alimentaires</span>
      </a>
    </li>
    <li>
      <a class="sidebar__link<?= $active === 'objectif' ? ' sidebar__link--active' : '' ?>" href="<?= htmlspecialchars(Url::to('front', 'objectif', 'index'), ENT_QUOTES, 'UTF-8') ?>">
        <svg class="sidebar__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3"/></svg>
        <span>Mon Objectif</span>
      </a>
    </li>
    <li>
      <a class="sidebar__link<?= $active === 'recettes' ? ' sidebar__link--active' : '' ?>" href="<?= htmlspecialchars(Url::to('front', 'plan_recette', 'index'), ENT_QUOTES, 'UTF-8') ?>">
        <svg class="sidebar__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
        <span>Mes Recettes</span>
      </a>
    </li>
    <li>
      <a class="sidebar__link<?= $active === 'score' ? ' sidebar__link--active' : '' ?>" href="#">
        <svg class="sidebar__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
        <span>Mon Score Santé</span>
      </a>
    </li>
  </ul>
</aside>
