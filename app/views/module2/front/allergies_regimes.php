<?php
/**
 * Front-office — Allergies & régimes (cases à cocher / listes)
 *
 * @var array<string, mixed>|null $profilNutritionnel  clés: allergy_ids[], regime_ids[] ou tableaux d'objets
 * @var array<int, array{id:int,libelle:string}>|null $allergiesCatalogue  liste référentielle (contrôleur)
 * @var array<int, array{id:int,libelle:string}>|null $regimesCatalogue
 * @var array<string, mixed>|null $errors
 */
declare(strict_types=1);

$profilNutritionnel = $profilNutritionnel ?? [];
$errors = $errors ?? [];

$allergiesCatalogue = $allergiesCatalogue ?? [
    ['id' => 1, 'libelle' => 'Arachides'],
    ['id' => 2, 'libelle' => 'Lait'],
    ['id' => 3, 'libelle' => 'Gluten'],
    ['id' => 4, 'libelle' => 'Fruits à coque'],
];
$regimesCatalogue = $regimesCatalogue ?? [
    ['id' => 1, 'libelle' => 'Végétarien'],
    ['id' => 2, 'libelle' => 'Sans gluten'],
    ['id' => 3, 'libelle' => 'Méditerranéen'],
    ['id' => 4, 'libelle' => 'Halal'],
];

$selectedAllergies = $profilNutritionnel['allergy_ids'] ?? [1];
$selectedRegimes = $profilNutritionnel['regime_ids'] ?? [2];
if (!is_array($selectedAllergies)) {
    $selectedAllergies = [];
}
if (!is_array($selectedRegimes)) {
    $selectedRegimes = [];
}

$pageTitle = $pageTitle ?? 'Allergies et régimes';
$activeNav = 'mon_profil';

require dirname(__DIR__) . '/routes_defaults.php';

require __DIR__ . '/layouts/header.php';
?>

<main id="fw-main-content" class="fw-content">
  <div class="fw-auth-panel" style="max-width:560px;margin-top:1rem">
    <section class="fw-card" aria-labelledby="fw-allergies-title">
      <h2 id="fw-allergies-title" class="fw-card__head"><span aria-hidden="true">🚫</span> Allergies &amp; régimes</h2>
      <div class="fw-card__body">
        <form class="fw-form" method="post" action="<?= htmlspecialchars($routesModule2['front_allergies'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
          <input type="hidden" name="_token" value="">
          <fieldset class="fw-form__group" style="border:none;padding:0;margin:0">
            <legend class="fw-form__group" style="margin-bottom:0.5rem">Allergies</legend>
            <div class="fw-check-grid">
              <?php foreach ($allergiesCatalogue as $item) :
                  $id = (int) ($item['id'] ?? 0);
                  $checked = in_array($id, array_map('intval', $selectedAllergies), true);
                  ?>
                <label class="fw-check">
                  <input type="checkbox" name="allergy_ids[]" value="<?= $id ?>" <?= $checked ? 'checked' : '' ?>>
                  <?= htmlspecialchars((string) ($item['libelle'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                </label>
              <?php endforeach; ?>
            </div>
            <?php if (!empty($errors['allergy_ids'])) : ?><small style="color:var(--fw-alert)"><?= htmlspecialchars((string) $errors['allergy_ids'], ENT_QUOTES, 'UTF-8') ?></small><?php endif; ?>
          </fieldset>
          <fieldset class="fw-form__group" style="border:none;padding:0;margin:1rem 0 0">
            <legend class="fw-form__group" style="margin-bottom:0.5rem">Régimes</legend>
            <div class="fw-check-grid">
              <?php foreach ($regimesCatalogue as $item) :
                  $id = (int) ($item['id'] ?? 0);
                  $checked = in_array($id, array_map('intval', $selectedRegimes), true);
                  ?>
                <label class="fw-check">
                  <input type="checkbox" name="regime_ids[]" value="<?= $id ?>" <?= $checked ? 'checked' : '' ?>>
                  <?= htmlspecialchars((string) ($item['libelle'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                </label>
              <?php endforeach; ?>
            </div>
            <?php if (!empty($errors['regime_ids'])) : ?><small style="color:var(--fw-alert)"><?= htmlspecialchars((string) $errors['regime_ids'], ENT_QUOTES, 'UTF-8') ?></small><?php endif; ?>
          </fieldset>
          <div class="fw-form__group">
            <label for="fw-intolerance">Intolérance principale (optionnel)</label>
            <select id="fw-intolerance" name="intolerance_principale">
              <option value="">—</option>
              <option value="lactose" <?= (($profilNutritionnel['intolerance_principale'] ?? '') === 'lactose') ? 'selected' : '' ?>>Lactose</option>
              <option value="fructose" <?= (($profilNutritionnel['intolerance_principale'] ?? '') === 'fructose') ? 'selected' : '' ?>>Fructose</option>
            </select>
          </div>
          <div style="display:flex;gap:0.75rem;flex-wrap:wrap;margin-top:1rem">
            <button type="submit" class="fw-btn">Enregistrer</button>
            <a class="fw-btn fw-btn--ghost" href="<?= htmlspecialchars($routesModule2['front_mon_profil'] ?? '#', ENT_QUOTES, 'UTF-8') ?>">Retour au profil</a>
          </div>
        </form>
      </div>
    </section>
  </div>
</main>

<?php require __DIR__ . '/layouts/footer.php'; ?>
