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
        <form class="fw-form" method="post" action="<?= htmlspecialchars($routesModule2['front_profil_edit'] ?? '', ENT_QUOTES, 'UTF-8') ?>" enctype="multipart/form-data">
          <input type="hidden" name="_token" value="">
          <input type="hidden" name="_method" value="PUT">

          <div class="fw-form__group">
            <label for="fw-photo">Photo de profil (optionnel)</label>
            <input type="file" id="fw-photo" name="photo_profil" accept="image/jpeg,image/png,image/webp,image/gif">
            <small style="color:var(--fw-text-muted)">JPG, PNG, WebP ou GIF. Max 5MB. Laissez vide pour garder la photo actuelle.</small>
            <?php if (!empty($errors['photo_profil'])) : ?><small style="color:var(--fw-alert)"><?= htmlspecialchars((string) $errors['photo_profil'], ENT_QUOTES, 'UTF-8') ?></small><?php endif; ?>
            <div id="fw-photo-preview" style="margin-top: 1rem; display: none;">
              <img id="fw-photo-preview-img" src="" alt="Aperçu de la photo" style="max-width: 150px; max-height: 150px; border-radius: 8px; object-fit: cover;">
            </div>
          </div>

          <div class="fw-form__row fw-form__row--2">
            <div class="fw-form__group">
              <label for="fw-poids">Poids (kg)</label>
              <input type="text" id="fw-poids" name="poids_kg" value="<?= htmlspecialchars((string) ($profilNutritionnel['poids_kg'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
              <?php if (!empty($errors['poids_kg'])) : ?><small style="color:var(--fw-alert)"><?= htmlspecialchars((string) $errors['poids_kg'], ENT_QUOTES, 'UTF-8') ?></small><?php endif; ?>
            </div>
            <div class="fw-form__group">
              <label for="fw-taille">Taille (cm)</label>
              <input type="text" id="fw-taille" name="taille_cm" value="<?= htmlspecialchars((string) ($profilNutritionnel['taille_cm'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
              <?php if (!empty($errors['taille_cm'])) : ?><small style="color:var(--fw-alert)"><?= htmlspecialchars((string) $errors['taille_cm'], ENT_QUOTES, 'UTF-8') ?></small><?php endif; ?>
            </div>
          </div>
          <div class="fw-form__group">
            <label for="fw-objectif">Objectif</label>
            <select id="fw-objectif" name="objectif">
              <?php foreach ($objectifsOptions as $val => $label) : ?>
                <option value="<?= htmlspecialchars($val, ENT_QUOTES, 'UTF-8') ?>" <?= $sel === $val ? 'selected' : '' ?>><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></option>
              <?php endforeach; ?>
            </select>
            <?php if (!empty($errors['objectif'])) : ?><small style="color:var(--fw-alert)"><?= htmlspecialchars((string) $errors['objectif'], ENT_QUOTES, 'UTF-8') ?></small><?php endif; ?>
          </div>
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
            <a class="fw-btn fw-btn--ghost" href="<?= htmlspecialchars($routesModule2['front_mon_profil'] ?? '#', ENT_QUOTES, 'UTF-8') ?>">Annuler</a>
          </div>
        </form>
      </div>
    </section>
  </div>
</main>

<script>
// Prévisualisation de la photo avant l'upload
document.getElementById('fw-photo').addEventListener('change', function(e) {
  const file = e.target.files[0];
  const preview = document.getElementById('fw-photo-preview');
  const previewImg = document.getElementById('fw-photo-preview-img');

  if (file) {
    // Valider la taille (max 5MB)
    const maxSize = 5 * 1024 * 1024;
    if (file.size > maxSize) {
      alert('Le fichier dépasse 5MB. Veuillez sélectionner une image plus petite.');
      e.target.value = '';
      preview.style.display = 'none';
      return;
    }

    // Lire le fichier et afficher l'aperçu
    const reader = new FileReader();
    reader.onload = function(event) {
      previewImg.src = event.target.result;
      preview.style.display = 'block';
    };
    reader.readAsDataURL(file);
  } else {
    preview.style.display = 'none';
  }
});
</script>

<?php require __DIR__ . '/layouts/footer.php'; ?>
