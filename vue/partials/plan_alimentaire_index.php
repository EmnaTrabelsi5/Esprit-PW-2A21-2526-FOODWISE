<?php
/** @var string $area front|back */
/** @var list<array<string, mixed>> $plans */
/** @var ?string $flashSuccess */
/** @var ?string $flashError */
/** @var class-string $url */
?>
<header class="page-header fade-in">
  <div class="page-header__row">
    <h1 class="page-title">
      Plans Alimentaires
      <small><?= $area === 'back' ? 'Administration' : 'Mes Plans' ?></small>
    </h1>
    <div style="display: flex; gap: 1rem;">
      <a class="btn btn--primary" href="<?= htmlspecialchars($url::to($area, 'plan_alimentaire', 'create'), ENT_QUOTES, 'UTF-8') ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 18px;"><path d="M12 5v14M5 12h14"/></svg>
        Nouveau plan
      </a>
      <a class="btn btn--outline" href="<?= htmlspecialchars($url::to($area, 'home', 'index'), ENT_QUOTES, 'UTF-8') ?>">Retour</a>
    </div>
  </div>
</header>

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
    <span>Liste des Plans</span>
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/></svg>
  </div>
  
  <?php if ($plans === []) : ?>
    <div class="card__body">
      <p style="color: var(--text-muted); text-align: center; padding: 2rem 0;">Aucun enregistrement trouvé.</p>
    </div>
  <?php else : ?>
    <div class="data-table-container">
      <table class="data-table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Titre</th>
            <th>Période</th>
            <th>Objectif</th>
            <th>Calories</th>
            <th>Statut</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($plans as $p) : ?>
          <tr>
            <td>#<?= (int) $p['id'] ?></td>
            <td style="font-weight: 600; color: var(--fw-brun-fonce);"><?= htmlspecialchars((string) $p['titre'], ENT_QUOTES, 'UTF-8') ?></td>
            <td style="color: var(--text-muted);"><?= htmlspecialchars((string) $p['date_debut'], ENT_QUOTES, 'UTF-8') ?> → <?= htmlspecialchars((string) $p['date_fin'], ENT_QUOTES, 'UTF-8') ?></td>
            <td>
              <?php if (!empty($p['id_obj'])) : ?>
                <?php
                $type = (string) ($p['objectif_type'] ?? '');
                $typeClass = 'badge--ok';
                if ($type === 'perte') $typeClass = 'badge--pending';
                ?>
                <a href="<?= htmlspecialchars($url::to($area, 'objectif', 'show', ['id' => (string) $p['id_obj']]), ENT_QUOTES, 'UTF-8') ?>">
                  <span class="badge <?= $typeClass ?>">#<?= (int) $p['id_obj'] ?> <?= htmlspecialchars($type, ENT_QUOTES, 'UTF-8') ?></span>
                </a>
              <?php else : ?>
                <span style="color: var(--fw-brun-pale);">—</span>
              <?php endif; ?>
            </td>
            <td style="font-weight: 500;"><?= $p['calories_cible'] !== null ? (int) $p['calories_cible'] . ' kcal' : '—' ?></td>
            <td><span class="badge badge--ok"><?= htmlspecialchars((string) $p['statut'], ENT_QUOTES, 'UTF-8') ?></span></td>
            <td>
              <div style="display: flex; gap: 0.5rem; justify-content: flex-end;">
                <a class="btn btn--voir btn--sm" href="<?= htmlspecialchars($url::to($area, 'plan_alimentaire', 'show', ['id' => (string) $p['id']]), ENT_QUOTES, 'UTF-8') ?>">Voir</a>
                <a class="btn btn--modifier btn--sm" href="<?= htmlspecialchars($url::to($area, 'plan_alimentaire', 'edit', ['id' => (string) $p['id']]), ENT_QUOTES, 'UTF-8') ?>">Modifier</a>
                
                <form class="inline-form" method="post" action="<?= htmlspecialchars($url::to($area, 'plan_alimentaire', 'destroy'), ENT_QUOTES, 'UTF-8') ?>" onsubmit="return window.confirm('Supprimer ce plan ?');">
                  <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
                  <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
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
