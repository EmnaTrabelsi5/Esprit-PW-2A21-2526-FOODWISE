<?php
/** @var string $area */
/** @var array<string, mixed> $recette */
/** @var class-string $url */
$labelsCreneau = ['dejeuner' => 'Déjeuner', 'diner' => 'Dîner', 'collation' => 'Collation'];
$labelsJour = [
    'lundi' => 'Lundi', 'mardi' => 'Mardi', 'mercredi' => 'Mercredi', 'jeudi' => 'Jeudi',
    'vendredi' => 'Vendredi', 'samedi' => 'Samedi', 'dimanche' => 'Dimanche',
];
?>
<header class="page-header fade-in">
  <div class="page-header__row">
    <h1 class="page-title">
      <?= htmlspecialchars((string) $recette['nom_recette'], ENT_QUOTES, 'UTF-8') ?>
      <small>Détails de la Programmation</small>
    </h1>
    <div style="display: flex; gap: 1rem;">
      <a class="btn btn--primary" href="<?= htmlspecialchars($url::to($area, 'plan_recette', 'edit', ['id' => (string) $recette['id']]), ENT_QUOTES, 'UTF-8') ?>">Modifier</a>
      <a class="btn btn--outline" href="<?= htmlspecialchars($url::to($area, 'plan_recette', 'index', ['plan_id' => (string) $recette['plan_alimentaire_id']]), ENT_QUOTES, 'UTF-8') ?>">Retour</a>
    </div>
  </div>
</header>

<div class="dashboard-grid fade-in">
  <section class="card card--meal-plan">
    <div class="card__header">
      <span>Planning & Recette</span>
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
    </div>
    <div class="card__body">
      <div style="display: grid; gap: 1.5rem;">
        <div style="display: flex; gap: 3rem; flex-wrap: wrap;">
          <div class="stat-box">
            <span class="stat-label">Jour</span>
            <span class="badge badge--ok"><?= htmlspecialchars($labelsJour[$recette['jour_semaine']] ?? (string) $recette['jour_semaine'], ENT_QUOTES, 'UTF-8') ?></span>
          </div>
          <div class="stat-box">
            <span class="stat-label">Créneau</span>
            <span class="badge badge--pending"><?= htmlspecialchars($labelsCreneau[$recette['creneau']] ?? (string) $recette['creneau'], ENT_QUOTES, 'UTF-8') ?></span>
          </div>
          <div class="stat-box">
            <span class="stat-label">Durée estimée</span>
            <span style="font-weight: 600; color: var(--primary);"><?= $recette['duree_minutes'] !== null ? (int) $recette['duree_minutes'] . ' min' : '—' ?></span>
          </div>
        </div>
        
        <div style="border-top: 1px solid rgba(78,44,14,0.05); padding-top: 1.5rem;">
          <span class="stat-label">Notes & Instructions</span>
          <p style="margin-top: 0.5rem; color: var(--text-muted); line-height: 1.6;">
            <?= $recette['notes'] !== null && $recette['notes'] !== '' ? nl2br(htmlspecialchars((string) $recette['notes'], ENT_QUOTES, 'UTF-8')) : '— Aucune note —' ?>
          </p>
        </div>
      </div>
    </div>
  </section>

  <section class="card card--nutrition">
    <div class="card__header">
      <span>Plan Associé</span>
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
    </div>
    <div class="card__body">
      <div class="notification-item notification-item--brown">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 18px;"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
        <span>Ligne programmée dans : <strong><?= htmlspecialchars((string) $recette['plan_titre'], ENT_QUOTES, 'UTF-8') ?></strong></span>
      </div>
      <div style="margin-top: 1.5rem;">
        <a class="btn btn--outline" href="<?= htmlspecialchars($url::to($area, 'plan_alimentaire', 'show', ['id' => (string) $recette['plan_alimentaire_id']]), ENT_QUOTES, 'UTF-8') ?>">Voir le plan complet</a>
      </div>
    </div>
  </section>
</div>
