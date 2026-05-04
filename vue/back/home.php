<?php
/**
 * Tableau de bord administration
 *
 * @var int $planCount
 * @var int $recetteCount
 * @var class-string $url
 */
$pageTitle = 'MealPlanner – Administration — FoodWise';
$sidebarActive = 'dashboard';

require __DIR__ . '/layouts/header.php';
require __DIR__ . '/layouts/sidebar.php';
?>

<div class="layout__main">
  <header class="page-header fade-in">
    <div class="page-header__row">
      <h1 class="page-title" style="font-size: 1.75rem; font-weight: 700;">MealPlanner — Administration</h1>
      <div class="search-bar" style="margin-bottom: 0; max-width: 400px;">
        <svg class="search-bar__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
        <input class="search-bar__input" type="search" placeholder="Rechercher...">
      </div>
    </div>
  </header>

  <div class="dashboard-grid fade-in">
    <div class="stats-container" style="grid-column: 1 / -1;">
      <div class="stat-card stat-card--primary">
        <svg class="stat-card__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
        <span class="stat-card__label">Plans Alimentaires</span>
        <span class="stat-card__value"><?= (int) $planCount ?></span>
      </div>
      
      <div class="stat-card stat-card--secondary">
        <svg class="stat-card__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
        <span class="stat-card__label">Recettes Programmées</span>
        <span class="stat-card__value"><?= (int) $recetteCount ?></span>
      </div>

      <div class="stat-card stat-card--accent">
        <svg class="stat-card__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3"/></svg>
        <span class="stat-card__label">Objectifs Actifs</span>
        <span class="stat-card__value"><?= (int) $objectifCount ?></span>
      </div>
    </div>

    <section class="card" style="grid-column: 1 / -1;">
      <div class="card__header">Rappel pédagogique</div>
      <div class="card__body">
        <p style="color: var(--text-muted); font-size: 0.9rem; line-height: 1.6;">
          Architecture MVC, accès SQL exclusivement via PDO, validation des formulaires en PHP (contrôleur) et contrôle supplémentaire en JavaScript (alertes), sans attributs HTML <code>required</code> / <code>pattern</code> sur les champs métier.
        </p>
        <div style="margin-top: 1.5rem; display: flex; gap: 1rem;">
          <a class="btn btn--primary" href="<?= htmlspecialchars($url::to('back', 'plan_alimentaire', 'index'), ENT_QUOTES, 'UTF-8') ?>">Gérer les plans</a>
          <a class="btn btn--modifier btn--sm" href="<?= htmlspecialchars($url::to('back', 'plan_recette', 'index'), ENT_QUOTES, 'UTF-8') ?>">Gérer les recettes</a>
        </div>
      </div>
    </section>
  </div>
</div>

<aside class="layout__notifications fade-in" aria-label="Notifications">
  <h2 class="notifications-title">Système</h2>
  <div class="notifications-list">
    <div class="notification-item notification-item--green">
      <span>Base de données synchronisée.</span>
    </div>
    <div class="notification-item notification-item--brown">
      <span>Interface d'administration active.</span>
    </div>
  </div>
</aside>

<?php require __DIR__ . '/layouts/footer.php'; ?>
