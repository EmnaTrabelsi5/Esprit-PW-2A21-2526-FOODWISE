<?php
/**
 * Tableau de bord client — données dynamiques + liens CRUD
 *
 * @var list<array<string, mixed>> $plans
 * @var int $recetteCount
 * @var string $semaineLabel
 * @var class-string $url
 */
$pageTitle = 'MealPlanner — Plans alimentaires — FoodWise';
$sidebarActive = 'planning';

include __DIR__ . '/../../layouts/front/header.php';
?>

<div class="layout__main">
  <header class="page-header fade-in">
    <div class="page-header__row">
      <h1 class="page-title">
        Mon Planning
        <small>Mes Plans Alimentaires — FoodWise</small>
      </h1>
      <div style="display: flex; gap: 1rem;">
        <a class="btn btn--primary btn--sm" href="<?= htmlspecialchars($url::to('front', 'plan_alimentaire', 'create'), ENT_QUOTES, 'UTF-8') ?>">
          Nouveau plan
        </a>
        <a class="btn btn--outline btn--sm" href="<?= htmlspecialchars($url::to('front', 'home', 'index'), ENT_QUOTES, 'UTF-8') ?>">
          Accueil
        </a>
      </div>
    </div>
  </header>

  <div class="dashboard-grid fade-in">
    <div class="stats-container" style="grid-column: 1 / -1;">
      <div class="stat-card stat-card--primary">
        <svg class="stat-card__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
        <span class="stat-card__label">Mes Plans</span>
        <span class="stat-card__value"><?= count($plans) ?></span>
      </div>
      
      <div class="stat-card stat-card--secondary">
        <svg class="stat-card__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
        <span class="stat-card__label">Recettes</span>
        <span class="stat-card__value"><?= (int) $recetteCount ?></span>
      </div>

      <div class="stat-card stat-card--accent">
        <svg class="stat-card__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3"/></svg>
        <span class="stat-card__label">Objectifs</span>
        <span class="stat-card__value"><?= (int) $objectifCount ?></span>
      </div>
    </div>

    <section class="card" style="grid-column: 1 / -1;">
      <div class="card__header">Liste de mes plans</div>
      <div class="data-table-container">
        <table class="data-table">
          <thead>
            <tr>
              <th>ID</th>
              <th>Titre du Plan</th>
              <th>Calories</th>
              <th>Objectif</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($plans as $p) : ?>
            <tr>
              <td>#<?= (int) $p['id'] ?></td>
              <td style="font-weight: 600; color: var(--fw-brun-fonce);"><?= htmlspecialchars((string) $p['titre'], ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= (int) $p['calories_cible'] ?> kcal</td>
              <td style="font-size: 0.8rem; color: var(--text-muted);">
                <?php if (!empty($p['id_obj'])) : ?>
                  <a href="<?= htmlspecialchars($url::to('front', 'objectif', 'show', ['id' => (string) $p['id_obj']]), ENT_QUOTES, 'UTF-8') ?>" style="color: #a0522d; text-decoration: underline; font-weight: 600;">
                    <?= htmlspecialchars((string) ($p['objectif_type'] ?? 'maintien'), ENT_QUOTES, 'UTF-8') ?>
                  </a>
                <?php else : ?>
                  <?= htmlspecialchars((string) ($p['objectif_type'] ?? 'maintien'), ENT_QUOTES, 'UTF-8') ?>
                <?php endif; ?>
              </td>
              <td>
                <div style="display: flex; gap: 0.5rem;">
                  <a class="btn btn--voir btn--sm" href="<?= htmlspecialchars($url::to('front', 'plan_alimentaire', 'show', ['id' => (string) $p['id']]), ENT_QUOTES, 'UTF-8') ?>">Voir</a>
                  <a class="btn btn--modifier btn--sm" href="<?= htmlspecialchars($url::to('front', 'plan_alimentaire', 'edit', ['id' => (string) $p['id']]), ENT_QUOTES, 'UTF-8') ?>">Modifier</a>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($plans)) : ?>
              <tr>
                <td colspan="5" style="text-align: center; padding: 2rem; color: var(--text-muted);">Aucun plan trouvé. <a href="<?= htmlspecialchars($url::to('front', 'plan_alimentaire', 'create'), ENT_QUOTES, 'UTF-8') ?>">Créer mon premier plan</a></td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </section>
  </div>
</div>

<aside class="layout__notifications" aria-label="Notifications">
  <div class="notifications-panel">
    <div class="notifications-panel__header">Notifications</div>
    <div class="notifications-panel__body">
      <div class="notification-item notification-item--green">
        <span>CRUD disponible : plans et recettes liées (PDO + validation PHP/JS).</span>
      </div>
    </div>
  </div>
</aside>

<?php include __DIR__ . '/../../layouts/front/footer.php'; ?>


