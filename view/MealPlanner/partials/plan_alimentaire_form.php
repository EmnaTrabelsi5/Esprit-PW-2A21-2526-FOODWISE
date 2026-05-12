<?php
/** @var string $area */
/** @var ?array<string, mixed> $plan */
/** @var list<array<string, mixed>> $objectifs */
/** @var list<string> $errors */
/** @var array<string, string> $old */
/** @var string $csrf */
/** @var class-string $url */

$isEdit = $plan !== null;
$v = static function (string $key) use ($old, $plan): string {
    if (array_key_exists($key, $old)) {
        return (string) $old[$key];
    }
    if ($plan !== null && array_key_exists($key, $plan) && $plan[$key] !== null) {
        return (string) $plan[$key];
    }
    return '';
};
?>
<header class="page-header fade-in">
  <div class="page-header__row">
    <h1 class="page-title">
      <?= $isEdit ? 'Modifier le plan' : 'Nouveau plan' ?>
      <small><?= $isEdit ? 'Édition des détails' : 'Création d\'un nouveau programme' ?></small>
    </h1>
    <a class="btn btn--outline" href="<?= htmlspecialchars($url::to($area, 'plan_alimentaire', 'index'), ENT_QUOTES, 'UTF-8') ?>">Annuler</a>
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
    <span>Détails du Plan</span>
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
  </div>
  <div class="card__body">
    <form class="form-stack" method="post" action="<?= htmlspecialchars($url::to($area, 'plan_alimentaire', $isEdit ? 'update' : 'store'), ENT_QUOTES, 'UTF-8') ?>" data-crud="plan_alimentaire">
      <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
      <?php if ($isEdit) : ?>
        <input type="hidden" name="id" value="<?= (int) $plan['id'] ?>">
      <?php endif; ?>

      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem;">
        <div>
          <label class="form-label" for="titre">Titre du plan</label>
          <input class="form-input" id="titre" name="titre" value="<?= htmlspecialchars($v('titre'), ENT_QUOTES, 'UTF-8') ?>" placeholder="Ex: Ma semaine détox">
        </div>
        <div>
          <label class="form-label" for="id_obj">Objectif lié</label>
          <?php $selectedObj = $v('id_obj'); ?>
          <select class="form-input" id="id_obj" name="id_obj">
            <option value="">— Aucun objectif —</option>
            <?php foreach ($objectifs as $obj) : ?>
              <option value="<?= (int) $obj['id_obj'] ?>"<?= $selectedObj === (string) $obj['id_obj'] ? ' selected' : '' ?>>
                #<?= (int) $obj['id_obj'] ?> — <?= htmlspecialchars((string) $obj['type'], ENT_QUOTES, 'UTF-8') ?> (<?= (int) $obj['calories_cible'] ?> kcal)
              </option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <div>
        <label class="form-label" for="description">Description (optionnel)</label>
        <textarea class="form-input form-textarea" id="description" name="description" rows="3" placeholder="Quelques détails sur ce plan..."><?= htmlspecialchars($v('description'), ENT_QUOTES, 'UTF-8') ?></textarea>
      </div>

      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem;">
        <div>
          <label class="form-label" for="date_debut">Date de début</label>
          <input class="form-input" type="date" id="date_debut" name="date_debut" required value="<?= htmlspecialchars($v('date_debut'), ENT_QUOTES, 'UTF-8') ?>">
        </div>
        <div>
          <label class="form-label" for="date_fin">Date de fin</label>
          <input class="form-input" type="date" id="date_fin" name="date_fin" required value="<?= htmlspecialchars($v('date_fin'), ENT_QUOTES, 'UTF-8') ?>">
        </div>
        <div>
          <label class="form-label" for="calories_cible">Calories (kcal)</label>
          <input class="form-input" id="calories_cible" name="calories_cible" type="number" min="800" max="8000" step="1" value="<?= htmlspecialchars($v('calories_cible'), ENT_QUOTES, 'UTF-8') ?>" placeholder="2000">
        </div>
        <div>
          <label class="form-label" for="statut">Statut</label>
          <select class="form-input" id="statut" name="statut">
            <?php
            $st = $v('statut') ?: 'brouillon';
            foreach (['brouillon' => 'Brouillon', 'actif' => 'Actif', 'archive' => 'Archivé'] as $val => $label) :
            ?>
              <option value="<?= htmlspecialchars($val, ENT_QUOTES, 'UTF-8') ?>"<?= $st === $val ? ' selected' : '' ?>><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <div class="form-actions">
        <button type="submit" class="btn btn--primary"><?= $isEdit ? 'Enregistrer les modifications' : 'Créer le plan' ?></button>
        <a class="btn btn--outline" href="<?= htmlspecialchars($url::to($area, 'plan_alimentaire', 'index'), ENT_QUOTES, 'UTF-8') ?>">Annuler</a>
      </div>
    </form>
  </div>
</section>




