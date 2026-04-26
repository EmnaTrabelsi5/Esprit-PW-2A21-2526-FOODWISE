<?php
/** @var string $area */
/** @var ?array<string, mixed> $objectif */
/** @var list<string> $errors */
/** @var array<string, string> $old */
/** @var string $csrf */
/** @var class-string $url */

$isEdit = $objectif !== null;
$v = static function (string $key) use ($old, $objectif): string {
    if (array_key_exists($key, $old)) {
        return (string) $old[$key];
    }
    if ($objectif !== null && array_key_exists($key, $objectif) && $objectif[$key] !== null) {
        return (string) $objectif[$key];
    }
    return '';
};
?>
<header class="page-header fade-in">
  <div class="page-header__row">
    <h1 class="page-title">
      <?= $isEdit ? 'Modifier l\'objectif' : 'Nouvel objectif' ?>
      <small>Calcul de vos besoins nutritionnels</small>
    </h1>
    <a class="btn btn--outline" href="<?= htmlspecialchars($url::to($area, 'objectif', 'index'), ENT_QUOTES, 'UTF-8') ?>">Annuler</a>
  </div>
</header>

<?php if ($errors !== []) : ?>
  <div class="alert alert--error fade-in" role="alert">
    <ul class="form-errors">
      <?php foreach ($errors as $err) : ?>
        <li><?= htmlspecialchars($err, ENT_QUOTES, 'UTF-8') ?></li>
      <?php endforeach; ?>
    </ul>
  </div>
<?php endif; ?>

<section class="card card--meal-plan fade-in">
  <div class="card__header">
    <span>Calculateur BMR / TDEE</span>
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg>
  </div>
  <div class="card__body">
    <form class="form-stack" method="post" action="<?= htmlspecialchars($url::to($area, 'objectif', $isEdit ? 'update' : 'store'), ENT_QUOTES, 'UTF-8') ?>" data-crud="objectif">
      <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
      <?php if ($isEdit) : ?>
        <input type="hidden" name="id" value="<?= (int) $objectif['id_obj'] ?>">
      <?php endif; ?>

      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem;">
        <div>
          <label class="form-label" for="type">Type d'objectif</label>
          <?php $vt = $v('type') ?: 'maintien'; ?>
          <select class="form-input" id="type" name="type" required>
            <option value="maintien"<?= $vt === 'maintien' ? ' selected' : '' ?>>Maintien</option>
            <option value="perte"<?= $vt === 'perte' ? ' selected' : '' ?>>Perte de poids</option>
            <option value="prise"<?= $vt === 'prise' ? ' selected' : '' ?>>Prise de masse</option>
          </select>
        </div>
        <div>
          <label class="form-label" for="sexe">Sexe</label>
          <?php $vs = $v('sexe') ?: 'homme'; ?>
          <select class="form-input" id="sexe" name="sexe" required>
            <option value="homme"<?= $vs === 'homme' ? ' selected' : '' ?>>Homme</option>
            <option value="femme"<?= $vs === 'femme' ? ' selected' : '' ?>>Femme</option>
          </select>
        </div>
      </div>

      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 1.5rem;">
        <div>
          <label class="form-label" for="age">Age</label>
          <input class="form-input" type="number" id="age" name="age" min="15" max="100" step="1" required value="<?= htmlspecialchars($v('age'), ENT_QUOTES, 'UTF-8') ?>" placeholder="25">
        </div>
        <div>
          <label class="form-label" for="poids_kg">Poids (kg)</label>
          <input class="form-input" type="number" id="poids_kg" name="poids_kg" min="20" step="1" required value="<?= htmlspecialchars($v('poids_kg'), ENT_QUOTES, 'UTF-8') ?>" placeholder="70">
        </div>
        <div>
          <label class="form-label" for="taille_cm">Taille (cm)</label>
          <input class="form-input" type="number" id="taille_cm" name="taille_cm" min="100" step="1" required value="<?= htmlspecialchars($v('taille_cm'), ENT_QUOTES, 'UTF-8') ?>" placeholder="175">
        </div>
      </div>

      <div>
        <label class="form-label" for="niveau_activite">Niveau d'activité</label>
        <?php $va = $v('niveau_activite') ?: 'modere'; ?>
        <select class="form-input" id="niveau_activite" name="niveau_activite" required>
          <option value="sedentaire"<?= $va === 'sedentaire' ? ' selected' : '' ?>>Sédentaire (Bureau, peu de sport)</option>
          <option value="leger"<?= $va === 'leger' ? ' selected' : '' ?>>Léger (1-2 fois par semaine)</option>
          <option value="modere"<?= $va === 'modere' ? ' selected' : '' ?>>Modéré (3-5 fois par semaine)</option>
          <option value="intense"<?= $va === 'intense' ? ' selected' : '' ?>>Intense (6-7 fois par semaine)</option>
          <option value="extreme"<?= $va === 'extreme' ? ' selected' : '' ?>>Extrême (Sportif pro / travail physique)</option>
        </select>
      </div>

      <div class="notification-item notification-item--brown">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 18px;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
        <span>Les calories cibles et macros sont calculées automatiquement au backend via les formules BMR/TDEE.</span>
      </div>

      <div class="form-actions">
        <button type="submit" class="btn btn--primary"><?= $isEdit ? 'Recalculer et enregistrer' : 'Calculer et créer l\'objectif' ?></button>
        <a class="btn btn--outline" href="<?= htmlspecialchars($url::to($area, 'objectif', 'index'), ENT_QUOTES, 'UTF-8') ?>">Annuler</a>
      </div>
    </form>
  </div>
</section>
