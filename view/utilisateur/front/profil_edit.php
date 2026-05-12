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

$currentUser = $utilisateur;

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
              <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                <label for="fw-poids">Poids (kg)</label>
                <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer; font-size: 0.9rem; font-weight: normal; margin: 0;">
                  <input type="checkbox" name="show_weight" value="1" <?= (!empty($profilNutritionnel['show_weight']) ? 'checked' : '') ?> style="cursor: pointer;">
                  <span id="weight-visibility" style="font-size: 1rem;">👁️</span>
                  <span style="font-size: 0.85rem; color: var(--fw-text-muted);" id="weight-label">Public</span>
                </label>
              </div>
              <input type="text" id="fw-poids" name="poids_kg" value="<?= htmlspecialchars((string) ($profilNutritionnel['poids_kg'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
              <?php if (!empty($errors['poids_kg'])) : ?><small style="color:var(--fw-alert)"><?= htmlspecialchars((string) $errors['poids_kg'], ENT_QUOTES, 'UTF-8') ?></small><?php endif; ?>
            </div>
            <div class="fw-form__group">
              <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                <label for="fw-taille">Taille (cm)</label>
                <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer; font-size: 0.9rem; font-weight: normal; margin: 0;">
                  <input type="checkbox" name="show_height" value="1" <?= (!empty($profilNutritionnel['show_height']) ? 'checked' : '') ?> style="cursor: pointer;">
                  <span id="height-visibility" style="font-size: 1rem;">👁️</span>
                  <span style="font-size: 0.85rem; color: var(--fw-text-muted);" id="height-label">Public</span>
                </label>
              </div>
              <input type="text" id="fw-taille" name="taille_cm" value="<?= htmlspecialchars((string) ($profilNutritionnel['taille_cm'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
              <?php if (!empty($errors['taille_cm'])) : ?><small style="color:var(--fw-alert)"><?= htmlspecialchars((string) $errors['taille_cm'], ENT_QUOTES, 'UTF-8') ?></small><?php endif; ?>
            </div>
          </div>

          <?php 
            $poids = isset($profilNutritionnel['poids_kg']) ? (float) $profilNutritionnel['poids_kg'] : 0;
            $taille = isset($profilNutritionnel['taille_cm']) ? (int) $profilNutritionnel['taille_cm'] : 0;
            if ($poids > 0 && $taille > 0) {
              $imc = calculateIMC($poids, $taille);
              $interpretation = interpretIMC($imc);
              $bgColor = match($interpretation['couleur']) {
                'success' => '#d4edda',
                'warning' => '#fff3cd',
                'alert' => '#f8d7da',
                default => '#e7f3ff'
              };
              $textColor = match($interpretation['couleur']) {
                'success' => '#155724',
                'warning' => '#856404',
                'alert' => '#721c24',
                default => '#004085'
              };
          ?>
          <div style="background-color:<?= $bgColor ?>;border:1px solid <?= $textColor ?>;border-radius:6px;padding:1rem;margin-top:1rem">
            <strong style="color:<?= $textColor ?>">Indice de Masse Corporelle (IMC)</strong>
            <p style="margin:0.5rem 0;color:<?= $textColor ?>">
              <strong><?= number_format($imc, 2, '.', '') ?></strong> — <?= htmlspecialchars($interpretation['categorie'], ENT_QUOTES, 'UTF-8') ?>
            </p>
            <small style="color:<?= $textColor ?>"><?= htmlspecialchars($interpretation['description'], ENT_QUOTES, 'UTF-8') ?></small>
          </div>
          <?php } ?>

          <div class="fw-form__group">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
              <label for="fw-objectif">Objectif</label>
              <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer; font-size: 0.9rem; font-weight: normal; margin: 0;">
                <input type="checkbox" name="show_goal" value="1" <?= (!empty($profilNutritionnel['show_goal']) ? 'checked' : '') ?> style="cursor: pointer;">
                <span id="goal-visibility" style="font-size: 1rem;">👁️</span>
                <span style="font-size: 0.85rem; color: var(--fw-text-muted);" id="goal-label">Public</span>
              </label>
            </div>
            <select id="fw-objectif" name="objectif">
              <?php foreach ($objectifsOptions as $val => $label) : ?>
                <option value="<?= htmlspecialchars($val, ENT_QUOTES, 'UTF-8') ?>" <?= $sel === $val ? 'selected' : '' ?>><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></option>
              <?php endforeach; ?>
            </select>
            <?php if (!empty($errors['objectif'])) : ?><small style="color:var(--fw-alert)"><?= htmlspecialchars((string) $errors['objectif'], ENT_QUOTES, 'UTF-8') ?></small><?php endif; ?>
          </div>
          <div class="fw-form__group">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
              <label for="fw-allergies">Allergies (séparées par des virgules)</label>
              <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer; font-size: 0.9rem; font-weight: normal; margin: 0;">
                <input type="checkbox" name="show_allergies" value="1" <?= (!empty($profilNutritionnel['show_allergies']) ? 'checked' : '') ?> style="cursor: pointer;">
                <span id="allergies-visibility" style="font-size: 1rem;">👁️</span>
                <span style="font-size: 0.85rem; color: var(--fw-text-muted);" id="allergies-label">Public</span>
              </label>
            </div>
            <input type="text" id="fw-allergies" name="allergies" value="<?= htmlspecialchars((string) ($profilNutritionnel['allergies'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
          </div>
          <div class="fw-form__group">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
              <label for="fw-regimes">Régimes (séparés par des virgules)</label>
              <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer; font-size: 0.9rem; font-weight: normal; margin: 0;">
                <input type="checkbox" name="show_diet" value="1" <?= (!empty($profilNutritionnel['show_diet']) ? 'checked' : '') ?> style="cursor: pointer;">
                <span id="diet-visibility" style="font-size: 1rem;">👁️</span>
                <span style="font-size: 0.85rem; color: var(--fw-text-muted);" id="diet-label">Public</span>
              </label>
            </div>
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

// Gestion de la visibilité des champs
function updateVisibilityDisplay() {
  const fields = [
    { checkbox: 'show_weight', icon: 'weight-visibility', label: 'weight-label' },
    { checkbox: 'show_height', icon: 'height-visibility', label: 'height-label' },
    { checkbox: 'show_goal', icon: 'goal-visibility', label: 'goal-label' },
    { checkbox: 'show_allergies', icon: 'allergies-visibility', label: 'allergies-label' },
    { checkbox: 'show_diet', icon: 'diet-visibility', label: 'diet-label' },
  ];

  fields.forEach(field => {
    const checkbox = document.querySelector(`input[name="${field.checkbox}"]`);
    const icon = document.getElementById(field.icon);
    const label = document.getElementById(field.label);
    
    if (checkbox && icon && label) {
      if (checkbox.checked) {
        icon.textContent = '👁️';
        label.textContent = 'Public';
        label.style.color = 'var(--fw-text-muted)';
      } else {
        icon.textContent = '🔒';
        label.textContent = 'Privé';
        label.style.color = '#d32f2f';
      }
    }
  });
}

// Initialiser l'affichage au chargement
document.addEventListener('DOMContentLoaded', updateVisibilityDisplay);

// Mettre à jour l'affichage quand les checkboxes changent
document.querySelectorAll('input[name^="show_"]').forEach(checkbox => {
  checkbox.addEventListener('change', updateVisibilityDisplay);
});
</script>

<?php require __DIR__ . '/layouts/footer.php'; ?>

