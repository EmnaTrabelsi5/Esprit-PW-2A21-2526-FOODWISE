<?php
/**
 * Front-office — Création / modification du profil nutritionnel (poids, taille, objectifs)
 *
 * Jointure prévue : $profilNutritionnel lié à $utilisateur
 * @var array<string, mixed> $utilisateur
 * @var array<string, mixed>|null $profilNutritionnel
 * @var array<string, mixed>|null $errors
 */
declare(strict_types=1);

$utilisateur = $utilisateur ?? [];
$profilNutritionnel = $profilNutritionnel ?? [
    'poids_kg' => 70,
    'taille_cm' => 175,
    'objectif' => 'maintien',
];
$errors = $errors ?? [];

$pageTitle = $pageTitle ?? 'Modifier mon profil';
$activeNav = 'mon_profil';

require dirname(__DIR__) . '/routes_defaults.php';

require __DIR__ . '/layouts/header.php';

$objectifsOptions = [
    'perte' => 'Perte de poids',
    'maintien' => 'Maintien',
    'prise' => 'Prise de masse',
    'performance' => 'Performance sportive',
];
$sel = (string) ($profilNutritionnel['objectif'] ?? 'maintien');
?>

<main id="fw-main-content" class="fw-content">
  <div class="fw-auth-panel" style="max-width:560px;margin-top:1rem">
    <section class="fw-card" aria-labelledby="fw-profil-edit-title">
      <h2 id="fw-profil-edit-title" class="fw-card__head"><span aria-hidden="true">✎</span> Profil nutritionnel</h2>
      <div class="fw-card__body">
        <p style="margin-top:0;font-size:0.9rem">Utilisateur #<?= (int) ($utilisateur['id'] ?? 0) ?> — données liées à <strong>ProfilNutritionnel</strong></p>
        <form class="fw-form" method="post" action="<?= htmlspecialchars($routesModule2['front_profil_edit'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
          <input type="hidden" name="_token" value="">
          <input type="hidden" name="_method" value="PUT">
          <div class="fw-form__row fw-form__row--2">
            <div class="fw-form__group">
              <label for="fw-poids">Poids (kg)</label>
              <input type="number" id="fw-poids" name="poids_kg" min="20" max="300" step="0.1" value="<?= htmlspecialchars((string) ($profilNutritionnel['poids_kg'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required>
              <?php if (!empty($errors['poids_kg'])) : ?><small style="color:var(--fw-alert)"><?= htmlspecialchars((string) $errors['poids_kg'], ENT_QUOTES, 'UTF-8') ?></small><?php endif; ?>
            </div>
            <div class="fw-form__group">
              <label for="fw-taille">Taille (cm)</label>
              <input type="number" id="fw-taille" name="taille_cm" min="100" max="250" value="<?= htmlspecialchars((string) ($profilNutritionnel['taille_cm'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required>
              <?php if (!empty($errors['taille_cm'])) : ?><small style="color:var(--fw-alert)"><?= htmlspecialchars((string) $errors['taille_cm'], ENT_QUOTES, 'UTF-8') ?></small><?php endif; ?>
            </div>
          </div>
          <div class="fw-form__group">
            <label for="fw-objectif">Objectif</label>
            <select id="fw-objectif" name="objectif" required>
              <?php foreach ($objectifsOptions as $val => $label) : ?>
                <option value="<?= htmlspecialchars($val, ENT_QUOTES, 'UTF-8') ?>" <?= $sel === $val ? 'selected' : '' ?>><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></option>
              <?php endforeach; ?>
            </select>
            <?php if (!empty($errors['objectif'])) : ?><small style="color:var(--fw-alert)"><?= htmlspecialchars((string) $errors['objectif'], ENT_QUOTES, 'UTF-8') ?></small><?php endif; ?>
          </div>
          <div style="display:flex;gap:0.75rem;flex-wrap:wrap;margin-top:1rem">
            <button type="submit" class="fw-btn">Enregistrer</button>
            <a class="fw-btn fw-btn--ghost" href="<?= htmlspecialchars($routesModule2['front_mon_profil'] ?? '#', ENT_QUOTES, 'UTF-8') ?>">Annuler</a>
          </div>
        </form>
      </div>
    </section>
  </div>
</main>

<?php require __DIR__ . '/layouts/footer.php'; ?>
