<?php
/** @var string $area */
/** @var array<string, mixed> $plan */
/** @var ?string $flashError */
/** @var class-string $url */
?>
<header class="page-header fade-in">
  <div class="page-header__row">
    <h1 class="page-title">
      <?= htmlspecialchars((string) $plan['titre'], ENT_QUOTES, 'UTF-8') ?>
      <small>Détails du Plan Alimentaire</small>
    </h1>
    <div style="display: flex; gap: 1rem;">
      <a class="btn btn--primary" href="<?= htmlspecialchars($url::to($area, 'plan_alimentaire', 'edit', ['id' => (string) $plan['id']]), ENT_QUOTES, 'UTF-8') ?>">Modifier</a>
      <a class="btn btn--outline" href="<?= htmlspecialchars($url::to($area, 'plan_alimentaire', 'index'), ENT_QUOTES, 'UTF-8') ?>">Retour</a>
    </div>
  </div>
</header>

<?php if (!empty($flashError)) : ?>
  <div class="notification-item notification-item--brown fade-in" role="alert">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 18px;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
    <?= htmlspecialchars($flashError, ENT_QUOTES, 'UTF-8') ?>
  </div>
<?php endif; ?>

<div class="dashboard-grid fade-in">
  <section class="card card--meal-plan">
    <div class="card__header">
      <span>Informations Générales</span>
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
    </div>
    <div class="card__body">
      <div style="display: grid; gap: 1.5rem;">
        <div>
          <span class="stat-label">Description</span>
          <p style="margin-top: 0.5rem; color: var(--text-muted); line-height: 1.5;">
            <?= $plan['description'] !== null && $plan['description'] !== '' ? nl2br(htmlspecialchars((string) $plan['description'], ENT_QUOTES, 'UTF-8')) : '— Aucune description —' ?>
          </p>
        </div>
        
        <div style="display: flex; gap: 3rem; flex-wrap: wrap;">
          <div class="stat-box">
            <span class="stat-label">Période</span>
            <span style="font-weight: 600;"><?= htmlspecialchars((string) $plan['date_debut'], ENT_QUOTES, 'UTF-8') ?> → <?= htmlspecialchars((string) $plan['date_fin'], ENT_QUOTES, 'UTF-8') ?></span>
          </div>
          <div class="stat-box">
            <span class="stat-label">Statut</span>
            <span class="badge badge--ok"><?= htmlspecialchars((string) $plan['statut'], ENT_QUOTES, 'UTF-8') ?></span>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="card card--nutrition">
    <div class="card__header">
      <span>Besoins & Objectifs</span>
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
    </div>
    <div class="card__body">
      <div class="stats-container" style="margin-bottom: 2rem;">
        <div class="stat-box">
          <span class="stat-label">Cible Calorique</span>
          <span class="stat-value stat-value--primary"><?= $plan['calories_cible'] !== null ? (int) $plan['calories_cible'] : '—' ?> <small style="font-size: 0.5em;">kcal</small></span>
        </div>
      </div>
      
      <div>
        <span class="stat-label">Objectif lié</span>
        <div style="margin-top: 0.5rem;">
          <?php if (!empty($plan['id_obj'])) : ?>
            <a class="btn btn--outline" href="<?= htmlspecialchars($url::to($area, 'objectif', 'show', ['id' => (string) $plan['id_obj']]), ENT_QUOTES, 'UTF-8') ?>">
              #<?= (int) $plan['id_obj'] ?><?= !empty($plan['objectif_type']) ? ' — ' . htmlspecialchars((string) $plan['objectif_type'], ENT_QUOTES, 'UTF-8') : '' ?>
            </a>
          <?php else : ?>
            <span style="color: var(--text-muted); font-style: italic;">Aucun objectif lié</span>
          <?php endif; ?>
        </div>
      </div>
    </div>
    <div class="card__body" style="background: #f8f1e7; border-top: 1px solid rgba(78,44,14,0.05); display: flex; gap: 1rem;">
      <a class="btn btn--primary" href="<?= htmlspecialchars($url::to($area, 'plan_recette', 'index', ['plan_id' => (string) $plan['id']]), ENT_QUOTES, 'UTF-8') ?>">Voir les recettes</a>
      <a class="btn btn--outline" href="<?= htmlspecialchars($url::to($area, 'plan_recette', 'create', ['plan_id' => (string) $plan['id']]), ENT_QUOTES, 'UTF-8') ?>">Ajouter une ligne</a>
    </div>
  </section>
</div>




