<?php
/** @var string $area */
/** @var array<string, mixed> $objectif */
/** @var list<array<string, mixed>> $plans */
/** @var array{calories:int,proteines:float,glucides:float,lipides:float} $comparaison */
/** @var class-string $url */

$macroParts = ['P' => null, 'G' => null, 'L' => null];
if (preg_match('/P:(\d+)g\|G:(\d+)g\|L:(\d+)g/i', (string) $objectif['macros'], $m) === 1) {
    $macroParts = ['P' => (int) $m[1], 'G' => (int) $m[2], 'L' => (int) $m[3]];
}
?>
<header class="page-header fade-in">
  <div class="page-header__row">
    <h1 class="page-title">
      Objectif #<?= (int) $objectif['id_obj'] ?>
      <small>Détails & Analyses Nutritionnelles</small>
    </h1>
    <div style="display: flex; gap: 1rem;">
      <a class="btn btn--primary" href="<?= htmlspecialchars($url::to($area, 'objectif', 'edit', ['id' => (string) $objectif['id_obj']]), ENT_QUOTES, 'UTF-8') ?>">Modifier</a>
      <a class="btn btn--outline" href="<?= htmlspecialchars($url::to($area, 'objectif', 'index'), ENT_QUOTES, 'UTF-8') ?>">Retour</a>
    </div>
  </div>
</header>

<div class="dashboard-grid fade-in">
  <section class="card card--nutrition">
    <div class="card__header">
      <span>Objectif Calculé</span>
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 8v4l3 3"/></svg>
    </div>
    <div class="card__body">
      <div class="stats-container" style="margin-bottom: 2rem;">
        <div class="stat-box">
          <span class="stat-label">Cible Calorique</span>
          <span class="stat-value stat-value--primary"><?= (int) $objectif['calories_cible'] ?> <small style="font-size: 0.5em;">kcal</small></span>
        </div>
        <div class="stat-box" style="border-left: 1px solid rgba(78,44,14,0.1); padding-left: 2rem;">
          <span class="stat-label">Type</span>
          <span class="badge badge--ok" style="margin-top: 0.5rem;"><?= htmlspecialchars((string) $objectif['type'], ENT_QUOTES, 'UTF-8') ?></span>
        </div>
      </div>
      
      <div>
        <span class="stat-label">Répartition des Macros</span>
        <div style="display: flex; gap: 0.75rem; margin-top: 0.75rem;">
          <?php if ($macroParts['P'] !== null) : ?>
            <span class="badge" style="background: #e3f2fd; color: #1565c0; padding: 0.5rem 1rem;">Protéines: <?= (int) $macroParts['P'] ?>g</span>
            <span class="badge" style="background: #fff3e0; color: #ef6c00; padding: 0.5rem 1rem;">Glucides: <?= (int) $macroParts['G'] ?>g</span>
            <span class="badge" style="background: #fce4ec; color: #c2185b; padding: 0.5rem 1rem;">Lipides: <?= (int) $macroParts['L'] ?>g</span>
          <?php else : ?>
            <span style="color: var(--text-muted);"><?= htmlspecialchars((string) $objectif['macros'], ENT_QUOTES, 'UTF-8') ?></span>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </section>

  <section class="card card--meal-plan">
    <div class="card__header">
      <span>Consommation Réelle</span>
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21.21 15.89A10 10 0 1 1 8 2.83"/><path d="M22 12A10 10 0 0 0 12 2v10z"/></svg>
    </div>
    <div class="card__body">
      <div class="stats-container" style="margin-bottom: 1.5rem;">
        <div class="stat-box">
          <span class="stat-label">Total Calories</span>
          <span class="stat-value stat-value--dark"><?= (int) $comparaison['calories'] ?> <small style="font-size: 0.5em;">kcal</small></span>
        </div>
      </div>
      
      <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem;">
        <div class="stat-box">
          <span class="stat-label">P</span>
          <span style="font-weight: 700;"><?= (int) $comparaison['proteines'] ?>g</span>
        </div>
        <div class="stat-box">
          <span class="stat-label">G</span>
          <span style="font-weight: 700;"><?= (int) $comparaison['glucides'] ?>g</span>
        </div>
        <div class="stat-box">
          <span class="stat-label">L</span>
          <span style="font-weight: 700;"><?= (int) $comparaison['lipides'] ?>g</span>
        </div>
      </div>
    </div>
  </section>

  <section class="card card--meal-plan" style="grid-column: 1 / -1;">
    <div class="card__header">
      <span>Plans Alimentaires Liés</span>
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/></svg>
    </div>
    
    <?php if ($plans === []) : ?>
      <div class="card__body">
        <p style="color: var(--text-muted); text-align: center; padding: 1.5rem 0;">Aucun plan lié à cet objectif.</p>
      </div>
    <?php else : ?>
      <div class="data-table-container">
        <table class="data-table">
          <thead>
            <tr>
              <th>ID Plan</th>
              <th>Titre</th>
              <th>Période</th>
              <th>Statut</th>
              <th style="text-align: right;">Action</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($plans as $plan) : ?>
            <tr>
              <td style="color: var(--text-muted); font-size: 0.8rem;">#<?= (int) $plan['id'] ?></td>
              <td style="font-weight: 600; color: var(--fw-brun-fonce);"><?= htmlspecialchars((string) $plan['titre'], ENT_QUOTES, 'UTF-8') ?></td>
              <td style="font-size: 0.9rem;"><?= htmlspecialchars((string) $plan['date_debut'], ENT_QUOTES, 'UTF-8') ?> → <?= htmlspecialchars((string) $plan['date_fin'], ENT_QUOTES, 'UTF-8') ?></td>
              <td><span class="badge badge--ok"><?= htmlspecialchars((string) $plan['statut'], ENT_QUOTES, 'UTF-8') ?></span></td>
              <td style="text-align: right;">
                <a class="btn btn--outline btn--sm" href="<?= htmlspecialchars($url::to($area, 'plan_alimentaire', 'show', ['id' => (string) $plan['id']]), ENT_QUOTES, 'UTF-8') ?>">Voir</a>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </section>
</div>
