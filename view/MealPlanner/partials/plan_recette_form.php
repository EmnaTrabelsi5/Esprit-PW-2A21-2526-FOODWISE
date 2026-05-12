<?php
/** @var string $area */
/** @var ?array<string, mixed> $recette */
/** @var ?array<string, mixed> $plan */
/** @var list<array<string, mixed>> $plans */
/** @var list<string> $errors */
/** @var array<string, string> $old */
/** @var string $csrf */
/** @var class-string $url */

$isEdit = $recette !== null;
$v = static function (string $key) use ($old, $recette): string {
    if (array_key_exists($key, $old)) {
        return (string) $old[$key];
    }
    if ($recette !== null && array_key_exists($key, $recette) && $recette[$key] !== null) {
        return (string) $recette[$key];
    }
    return '';
};

$jours = [
    'lundi' => 'Lundi', 'mardi' => 'Mardi', 'mercredi' => 'Mercredi', 'jeudi' => 'Jeudi',
    'vendredi' => 'Vendredi', 'samedi' => 'Samedi', 'dimanche' => 'Dimanche',
];
$creneaux = ['dejeuner' => 'Déjeuner', 'diner' => 'Dîner', 'collation' => 'Collation'];

$selectedPlanId = $v('plan_alimentaire_id');
if ($selectedPlanId === '' && $plan !== null) {
    $selectedPlanId = (string) $plan['id'];
}
?>
<header class="page-header fade-in">
  <div class="page-header__row">
    <h1 class="page-title">
      <?= $isEdit ? 'Modifier la ligne' : 'Nouvelle ligne' ?>
      <small><?= $isEdit ? 'Mise à jour d\'une recette' : 'Ajout d\'une recette au planning' ?></small>
    </h1>
    <a class="btn btn--outline" href="<?= htmlspecialchars($url::to($area, 'plan_recette', 'index', $selectedPlanId !== '' ? ['plan_id' => $selectedPlanId] : []), ENT_QUOTES, 'UTF-8') ?>">Annuler</a>
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
    <span>Détails de la Programmation</span>
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
  </div>
  <div class="card__body">
    <form class="form-stack" method="post" action="<?= htmlspecialchars($url::to($area, 'plan_recette', $isEdit ? 'update' : 'store'), ENT_QUOTES, 'UTF-8') ?>" data-crud="plan_recette">
      <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
      <?php if ($isEdit) : ?>
        <input type="hidden" name="id" value="<?= (int) $recette['id'] ?>">
      <?php endif; ?>

      <?php if (!$isEdit) : ?>
        <div>
          <label class="form-label" for="plan_alimentaire_id">Plan alimentaire</label>
          <select class="form-input" id="plan_alimentaire_id" name="plan_alimentaire_id">
            <option value="">— Choisir un plan —</option>
            <?php foreach ($plans as $p) : ?>
              <option value="<?= (int) $p['id'] ?>"<?= $selectedPlanId === (string) $p['id'] ? ' selected' : '' ?>><?= htmlspecialchars((string) $p['titre'], ENT_QUOTES, 'UTF-8') ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      <?php else : ?>
        <div class="notification-item notification-item--brown">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 18px;"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
          <span>Modification pour le plan : <strong><?= htmlspecialchars((string) ($plan['titre'] ?? $recette['plan_titre'] ?? ''), ENT_QUOTES, 'UTF-8') ?></strong></span>
        </div>
      <?php endif; ?>

      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem;">
        <div>
          <label class="form-label" for="jour_semaine">Jour de la semaine</label>
          <select class="form-input" id="jour_semaine" name="jour_semaine">
            <?php
            $vj = $v('jour_semaine') ?: 'lundi';
            foreach ($jours as $val => $label) :
            ?>
              <option value="<?= htmlspecialchars($val, ENT_QUOTES, 'UTF-8') ?>"<?= $vj === $val ? ' selected' : '' ?>><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label class="form-label" for="creneau">Créneau horaire</label>
          <select class="form-input" id="creneau" name="creneau">
            <?php
            $vc = $v('creneau') ?: 'dejeuner';
            foreach ($creneaux as $val => $label) :
            ?>
              <option value="<?= htmlspecialchars($val, ENT_QUOTES, 'UTF-8') ?>"<?= $vc === $val ? ' selected' : '' ?>><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem;">
        <div>
          <label class="form-label" for="nom_recette">Nom de la recette</label>
          <input class="form-input" id="nom_recette" name="nom_recette" value="<?= htmlspecialchars($v('nom_recette'), ENT_QUOTES, 'UTF-8') ?>" placeholder="Ex: Salade César">
        </div>
        <div>
          <label class="form-label" for="duree_minutes">Durée (min)</label>
          <input class="form-input" id="duree_minutes" name="duree_minutes" value="<?= htmlspecialchars($v('duree_minutes'), ENT_QUOTES, 'UTF-8') ?>" placeholder="20">
        </div>
      </div>

      <div>
        <label class="form-label" for="notes">Notes & Instructions</label>
        <textarea class="form-input form-textarea" id="notes" name="notes" rows="3" placeholder="Notes optionnelles..."><?= htmlspecialchars($v('notes'), ENT_QUOTES, 'UTF-8') ?></textarea>
      </div>

      <div class="form-actions">
        <button type="submit" class="btn btn--primary"><?= $isEdit ? 'Mettre à jour la ligne' : 'Ajouter au plan' ?></button>
        <a class="btn btn--outline" href="<?= htmlspecialchars($url::to($area, 'plan_recette', 'index', $selectedPlanId !== '' ? ['plan_id' => $selectedPlanId] : []), ENT_QUOTES, 'UTF-8') ?>">Annuler</a>
      </div>
    </form>
  </div>
</section>
        <button type="submit" class="btn btn--primary"><?= $isEdit ? 'Enregistrer' : 'Créer' ?></button>
      </div>
    </form>
  </div>
</section>




