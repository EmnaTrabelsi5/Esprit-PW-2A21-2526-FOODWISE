<?php
/** @var string $area */
/** @var list<array<string, mixed>> $recettes */
/** @var ?array<string, mixed> $filteredPlan */
/** @var ?int $planFilterId */
/** @var ?string $flashSuccess */
/** @var ?string $flashError */
/** @var string $csrf */
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
      Recettes des Plans
      <small><?= $area === 'back' ? 'Administration des lignes' : 'Mes recettes programmées' ?></small>
    </h1>
    <div style="display: flex; gap: 1rem;">
      <?php $createQ = $planFilterId !== null ? ['plan_id' => (string) $planFilterId] : []; ?>
      <a class="btn btn--primary" href="<?= htmlspecialchars($url::to($area, 'plan_recette', 'create', $createQ), ENT_QUOTES, 'UTF-8') ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 18px;"><path d="M12 5v14M5 12h14"/></svg>
        Nouvelle ligne
      </a>
      <a class="btn btn--outline" href="<?= htmlspecialchars($url::to($area, 'plan_alimentaire', 'index'), ENT_QUOTES, 'UTF-8') ?>">Plans</a>
      <a class="btn btn--outline" href="<?= htmlspecialchars($url::to($area, 'home', 'index'), ENT_QUOTES, 'UTF-8') ?>">Accueil</a>
    </div>
  </div>
</header>

<?php if ($filteredPlan !== null) : ?>
  <div class="notification-item notification-item--brown fade-in" style="margin-bottom: 1.5rem;">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 18px;"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
    <span>Filtré sur le plan : <strong><?= htmlspecialchars((string) $filteredPlan['titre'], ENT_QUOTES, 'UTF-8') ?></strong></span>
    <a href="<?= htmlspecialchars($url::to($area, 'plan_recette', 'index'), ENT_QUOTES, 'UTF-8') ?>" style="margin-left: auto; font-weight: 700; text-decoration: underline;">Voir tout</a>
  </div>
<?php endif; ?>

<?php if (!empty($flashSuccess)) : ?>
  <div class="notification-item notification-item--green fade-in" role="status">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 18px;"><path d="M20 6L9 17l-5-5"/></svg>
    <?= htmlspecialchars($flashSuccess, ENT_QUOTES, 'UTF-8') ?>
  </div>
<?php endif; ?>

<?php if (!empty($flashError)) : ?>
  <div class="notification-item notification-item--brown fade-in" role="alert">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 18px;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
    <?= htmlspecialchars($flashError, ENT_QUOTES, 'UTF-8') ?>
  </div>
<?php endif; ?>

<section class="card card--meal-plan fade-in">
  <div class="card__header">
    <span>Lignes de Recettes</span>
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
  </div>
  
  <?php if ($recettes === []) : ?>
    <div class="card__body">
      <p style="color: var(--text-muted); text-align: center; padding: 2rem 0;">Aucune recette liée à ce plan.</p>
    </div>
  <?php else : ?>
    <div class="data-table-container">
      <table class="data-table">
        <thead>
          <tr>
            <th>Plan</th>
            <th>Jour</th>
            <th>Créneau</th>
            <th>Recette</th>
            <th>Durée</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($recettes as $r) : ?>
          <tr>
            <td style="font-size: 0.85rem; color: var(--text-muted);"><?= htmlspecialchars((string) ($r['plan_titre'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
            <td><span class="badge badge--ok"><?= htmlspecialchars($labelsJour[$r['jour_semaine']] ?? (string) $r['jour_semaine'], ENT_QUOTES, 'UTF-8') ?></span></td>
            <td><span class="badge badge--pending"><?= htmlspecialchars($labelsCreneau[$r['creneau']] ?? (string) $r['creneau'], ENT_QUOTES, 'UTF-8') ?></span></td>
            <td style="font-weight: 600; color: var(--fw-brun-fonce);"><?= htmlspecialchars((string) $r['nom_recette'], ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= $r['duree_minutes'] !== null ? (int) $r['duree_minutes'] . ' min' : '—' ?></td>
            <td>
              <div style="display: flex; gap: 0.5rem; justify-content: flex-end;">
                <a class="btn btn--voir btn--sm" href="<?= htmlspecialchars($url::to($area, 'plan_recette', 'show', ['id' => (string) $r['id']]), ENT_QUOTES, 'UTF-8') ?>">Voir</a>
                <a class="btn btn--modifier btn--sm" href="<?= htmlspecialchars($url::to($area, 'plan_recette', 'edit', ['id' => (string) $r['id']]), ENT_QUOTES, 'UTF-8') ?>">Modifier</a>
                
                <form class="inline-form" method="post" action="<?= htmlspecialchars($url::to($area, 'plan_recette', 'destroy'), ENT_QUOTES, 'UTF-8') ?>" onsubmit="return window.confirm('Supprimer cette ligne ?');">
                  <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
                  <input type="hidden" name="id" value="<?= (int) $r['id'] ?>">
                  <button type="submit" class="btn btn--supprimer btn--sm">Supprimer</button>
                </form>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</section>




