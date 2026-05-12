<?php
/** @var string $area */
/** @var list<array<string, mixed>> $objectifs */
/** @var ?string $flashSuccess */
/** @var ?string $flashError */
/** @var string $csrf */
/** @var class-string $url */

$parseMacros = static function (string $raw): array {
    $result = ['P' => null, 'G' => null, 'L' => null];
    if (preg_match('/P:(\d+)g\|G:(\d+)g\|L:(\d+)g/i', $raw, $m) === 1) {
        $result['P'] = (int) $m[1];
        $result['G'] = (int) $m[2];
        $result['L'] = (int) $m[3];
    }
    return $result;
};
?>
<header class="page-header fade-in">
  <div class="page-header__row">
    <h1 class="page-title">
      Objectifs Nutritionnels
      <small><?= $area === 'back' ? 'Configuration globale' : 'Mes paramètres personnels' ?></small>
    </h1>
    <div style="display: flex; gap: 1rem;">
      <a class="btn btn--primary" href="<?= htmlspecialchars($url::to($area, 'objectif', 'create'), ENT_QUOTES, 'UTF-8') ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 18px;"><path d="M12 5v14M5 12h14"/></svg>
        Nouvel objectif
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
    <span>Liste des Objectifs</span>
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3"/></svg>
  </div>
  
  <?php if ($objectifs === []) : ?>
    <div class="card__body">
      <p style="color: var(--text-muted); text-align: center; padding: 2rem 0;">Aucun objectif enregistré.</p>
    </div>
  <?php else : ?>
    <div class="data-table-container">
      <table class="data-table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Type</th>
            <th>Calories</th>
            <th>Macronutriments</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($objectifs as $o) : ?>
          <?php
          $type = (string) $o['type'];
          $typeClass = 'badge--ok';
          if ($type === 'perte') $typeClass = 'badge--pending';
          $macro = $parseMacros((string) $o['macros']);
          $objectifId = (int) $o['id_obj'];
          ?>
          <tr>
            <td>#<?= $objectifId ?></td>
            <td><span class="badge <?= $typeClass ?>"><?= htmlspecialchars($type, ENT_QUOTES, 'UTF-8') ?></span></td>
            <td style="font-weight: 600;"><?= (int) $o['calories_cible'] ?> kcal</td>
            <td>
              <div style="display: flex; gap: 0.5rem;">
                <?php if ($macro['P'] !== null) : ?>
                  <span class="badge" style="background: #e3f2fd; color: #1565c0;">P <?= (int) $macro['P'] ?>g</span>
                  <span class="badge" style="background: #fff3e0; color: #ef6c00;">G <?= (int) $macro['G'] ?>g</span>
                  <span class="badge" style="background: #fce4ec; color: #c2185b;">L <?= (int) $macro['L'] ?>g</span>
                <?php else : ?>
                  <span style="color: var(--text-muted); font-style: italic;"><?= htmlspecialchars((string) $o['macros'], ENT_QUOTES, 'UTF-8') ?></span>
                <?php endif; ?>
              </div>
            </td>
            <td>
              <div style="display: flex; gap: 0.5rem; justify-content: flex-end;">
                <a class="btn btn--voir btn--sm" href="<?= htmlspecialchars($url::to($area, 'objectif', 'show', ['id' => (string) $objectifId]), ENT_QUOTES, 'UTF-8') ?>">Voir</a>
                <a class="btn btn--modifier btn--sm" href="<?= htmlspecialchars($url::to($area, 'objectif', 'edit', ['id' => (string) $objectifId]), ENT_QUOTES, 'UTF-8') ?>">Modifier</a>
                
                <form class="inline-form" method="post" action="<?= htmlspecialchars($url::to($area, 'objectif', 'destroy'), ENT_QUOTES, 'UTF-8') ?>" onsubmit="return window.confirm('Supprimer cet objectif ?');">
                  <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
                  <input type="hidden" name="id" value="<?= $objectifId ?>">
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




