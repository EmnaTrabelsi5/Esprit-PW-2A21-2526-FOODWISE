<?php
/**
 * Vue principale — Back-office Module 4 MealPlanner
 * Administration des plans alimentaires
 */
$pageTitle = 'Module 4 – MealPlanner – Administration des Plans alimentaires — FoodWise';
$sidebarActive = 'dashboard';

require __DIR__ . '/layouts/header.php';
require __DIR__ . '/layouts/sidebar.php';
?>

<div class="layout__main">
  <header class="page-header">
    <div class="page-header__row">
      <h1 class="page-title">Module 4 – MealPlanner – Administration des Plans alimentaires</h1>
      <div class="search-bar" role="search">
        <svg class="search-bar__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
        <input class="search-bar__input" type="search" name="q" placeholder="Rechercher des Utilisateurs, Recettes, ou ID de Plan..." aria-label="Recherche">
      </div>
    </div>
  </header>

  <div class="dashboard-grid dashboard-grid--admin">
    <section class="card card--stats" aria-labelledby="stats-heading">
      <div class="card__header" id="stats-heading">
        <svg class="card__header-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
        Statistiques d'Utilisation Hebdomadaire
      </div>
      <div class="card__body">
        <div class="stats-row">
          <div class="stat-item">
            <svg class="stat-item__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
            <div>
              <span class="stat-item__label">Total Plans Actifs</span>
              <span class="stat-item__value">2&nbsp;450</span>
            </div>
          </div>
          <div class="stat-item">
            <svg class="stat-item__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
            <div>
              <span class="stat-item__label">Utilisateurs avec Plan Complet</span>
              <span class="stat-item__value">1&nbsp;120</span>
            </div>
          </div>
          <div class="stat-item">
            <svg class="stat-item__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
            <div>
              <span class="stat-item__label">Score de Santé Moyen</span>
              <span class="stat-item__value">8,2/10</span>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="card card--allergen" aria-labelledby="allergen-heading">
      <div class="card__header" id="allergen-heading">
        <svg class="card__header-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
        Alertes Allergènes Générales
      </div>
      <div class="card__body card__body--muted">
        <p>Aucune alerte globale pour le moment. Les conflits détectés sur les plans utilisateurs apparaissent dans le panneau Notifications.</p>
      </div>
    </section>

    <section class="card card--validation" aria-labelledby="validation-heading">
      <div class="card__header" id="validation-heading">
        <svg class="card__header-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><rect x="8" y="2" width="8" height="4" rx="1" ry="1"/></svg>
        Validation des Plans Alimentaires Partenaires
      </div>
      <div class="data-table-wrap">
        <table class="data-table">
          <thead>
            <tr>
              <th>Utilisateur</th>
              <th>ID Plan</th>
              <th>Date de Début</th>
              <th>Recettes Utilisées</th>
              <th>Statut</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>HassanM</td>
              <td>#PL102</td>
              <td>12 Avril</td>
              <td>Poulet Granolla…</td>
              <td><span class="badge badge--pending">En attente</span></td>
              <td><button type="button" class="btn btn--primary">Editer/Consulter</button></td>
            </tr>
            <tr>
              <td>MarieD</td>
              <td>#PL089</td>
              <td>10 Avril</td>
              <td>Bowl quinoa…</td>
              <td><span class="badge badge--pending">En attente</span></td>
              <td><button type="button" class="btn btn--primary">Editer/Consulter</button></td>
            </tr>
            <tr>
              <td>LucasP</td>
              <td>#PL076</td>
              <td>8 Avril</td>
              <td>Papillote mer…</td>
              <td><span class="badge badge--ok">Validé</span></td>
              <td><button type="button" class="btn btn--primary">Editer/Consulter</button></td>
            </tr>
          </tbody>
        </table>
      </div>
    </section>

    <section class="card card--planning-global" aria-labelledby="planning-heading">
      <div class="card__header" id="planning-heading">
        <svg class="card__header-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
        Vue Globale des Plannings
      </div>
      <div class="data-table-wrap">
        <table class="data-table">
          <thead>
            <tr>
              <th>Utilisateur</th>
              <th>ID Plan</th>
              <th>Recettes Utilisées</th>
              <th>Statut</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>HassanM</td>
              <td>#PL102</td>
              <td>7 recettes</td>
              <td><span class="badge badge--pending">En cours</span></td>
              <td><button type="button" class="btn btn--primary">Editer/Consulter</button></td>
            </tr>
            <tr>
              <td>SoniaK</td>
              <td>#PL095</td>
              <td>10 recettes</td>
              <td><span class="badge badge--ok">Complet</span></td>
              <td><button type="button" class="btn btn--primary">Editer/Consulter</button></td>
            </tr>
          </tbody>
        </table>
      </div>
    </section>
  </div>
</div>

<aside class="layout__notifications" aria-label="Notifications">
  <h2 class="notifications-title">Notifications</h2>
  <div class="notifications-list">
    <div class="notification-item notification-item--green">
      <svg class="notification-item__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
      <span><strong>Plans Suggérés :</strong> 15 nouveaux plans en attente de revue.</span>
    </div>
    <div class="notification-item notification-item--brown">
      <svg class="notification-item__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
      <span><strong>Alerte Allergène :</strong> Un conflit dans un plan de HassanM — vérifier les recettes assignées.</span>
    </div>
    <div class="notification-item notification-item--brown">
      <svg class="notification-item__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
      <span><strong>Nouveaux Plans Suggérés :</strong> Action administrateur requise sur la file « Plans Suggérés ».</span>
    </div>
  </div>
</aside>

<?php require __DIR__ . '/layouts/footer.php'; ?>
