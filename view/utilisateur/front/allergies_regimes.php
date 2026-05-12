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

$profilNutritionnel['allergies'] = $profilNutritionnel['allergies'] ?? '';
$profilNutritionnel['regimes'] = $profilNutritionnel['regimes'] ?? '';
$profilNutritionnel['intolerances'] = $profilNutritionnel['intolerances'] ?? '';

$currentUser = $utilisateur;

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
          <?php if (!empty($errors['global'])) : ?><small style="color:var(--fw-alert)"><?= htmlspecialchars((string) $errors['global'], ENT_QUOTES, 'UTF-8') ?></small><?php endif; ?>
          <div class="fw-form__group">
            <label for="fw-allergies">Allergies (séparées par des virgules)</label>
            <input type="text" id="fw-allergies" name="allergies" value="<?= htmlspecialchars((string) ($profilNutritionnel['allergies'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
          </div>
          <div class="fw-form__group">
            <label for="fw-regimes">Régimes (séparés par des virgules)</label>
            <input type="text" id="fw-regimes" name="regimes" value="<?= htmlspecialchars((string) ($profilNutritionnel['regimes'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
          </div>
          <div class="fw-form__group">
            <label for="fw-intolerances">Intolérances (séparées par des virgules)</label>
            <input type="text" id="fw-intolerances" name="intolerances" value="<?= htmlspecialchars((string) ($profilNutritionnel['intolerances'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
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

