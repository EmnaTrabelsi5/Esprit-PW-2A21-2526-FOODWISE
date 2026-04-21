<?php
/**
 * Front-office — Inscription
 *
 * @var array<string, mixed>|null $errors  erreurs formulaire (clé champ => message)
 * @var array<string, mixed>|null $old     valeurs soumises précédemment
 */
declare(strict_types=1);

$errors = $errors ?? [];
$old = $old ?? [];

$pageTitle = $pageTitle ?? 'Inscription';
$activeNav = '';

require dirname(__DIR__) . '/routes_defaults.php';

require __DIR__ . '/layouts/header.php';
?>

<main id="fw-main-content" class="fw-content">
  <div class="fw-auth-panel">
    <section class="fw-card" aria-labelledby="fw-inscription-title">
      <h2 id="fw-inscription-title" class="fw-card__head"><span aria-hidden="true">✉</span> Créer un compte FoodWise</h2>
      <div class="fw-card__body">
        <form class="fw-form" method="post" action="<?= htmlspecialchars($routesModule2['front_inscription'] ?? '', ENT_QUOTES, 'UTF-8') ?>" enctype="multipart/form-data" novalidate>
          <input type="hidden" name="_token" value="">
          <div class="fw-form__group">
            <label for="fw-ins-nom">Nom</label>
            <input type="text" id="fw-ins-nom" name="nom" value="<?= htmlspecialchars((string) ($old['nom'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" autocomplete="family-name">
            <?php if (!empty($errors['nom'])) : ?><small style="color:var(--fw-alert)"><?= htmlspecialchars((string) $errors['nom'], ENT_QUOTES, 'UTF-8') ?></small><?php endif; ?>
          </div>
          <div class="fw-form__group">
            <label for="fw-ins-prenom">Prénom</label>
            <input type="text" id="fw-ins-prenom" name="prenom" value="<?= htmlspecialchars((string) ($old['prenom'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" autocomplete="given-name">
          </div>
          <div class="fw-form__group">
            <label for="fw-ins-email">Courriel</label>
            <input type="text" id="fw-ins-email" name="email" value="<?= htmlspecialchars((string) ($old['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" autocomplete="email">
            <?php if (!empty($errors['email'])) : ?><small style="color:var(--fw-alert)"><?= htmlspecialchars((string) $errors['email'], ENT_QUOTES, 'UTF-8') ?></small><?php endif; ?>
          </div>
          <div class="fw-form__group">
            <label for="fw-ins-pass">Mot de passe</label>
            <input type="password" id="fw-ins-pass" name="password" autocomplete="new-password">
            <?php if (!empty($errors['password'])) : ?><small style="color:var(--fw-alert)"><?= htmlspecialchars((string) $errors['password'], ENT_QUOTES, 'UTF-8') ?></small><?php endif; ?>
          </div>
          <div class="fw-form__group">
            <label for="fw-ins-pass2">Confirmer le mot de passe</label>
            <input type="password" id="fw-ins-pass2" name="password_confirm" autocomplete="new-password">
            <?php if (!empty($errors['password_confirm'])) : ?><small style="color:var(--fw-alert)"><?= htmlspecialchars((string) $errors['password_confirm'], ENT_QUOTES, 'UTF-8') ?></small><?php endif; ?>
          </div>

          <div class="fw-form__group">
            <label for="fw-ins-photo">Photo de profil (optionnel)</label>
            <input type="file" id="fw-ins-photo" name="photo_profil" accept="image/jpeg,image/png,image/webp,image/gif">
            <small style="color:var(--fw-text-muted)">JPG, PNG, WebP ou GIF. Max 5MB.</small>
            <?php if (!empty($errors['photo_profil'])) : ?><small style="color:var(--fw-alert)"><?= htmlspecialchars((string) $errors['photo_profil'], ENT_QUOTES, 'UTF-8') ?></small><?php endif; ?>
            <div id="fw-photo-preview" style="margin-top: 1rem; display: none;">
              <img id="fw-photo-preview-img" src="" alt="Aperçu de la photo" style="max-width: 150px; max-height: 150px; border-radius: 8px; object-fit: cover;">
            </div>
          </div>

          <hr style="margin:1.5rem 0;border:none;border-top:1px solid var(--fw-border)">
          <p style="font-size:0.9rem;color:var(--fw-text-muted);margin:0 0 1rem 0">Données du profil nutritionnel</p>

          <div class="fw-form__row fw-form__row--2">
            <div class="fw-form__group">
              <label for="fw-ins-poids">Poids (kg)</label>
              <input type="text" id="fw-ins-poids" name="poids_kg" value="<?= htmlspecialchars((string) ($old['poids_kg'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
              <?php if (!empty($errors['poids_kg'])) : ?><small style="color:var(--fw-alert)"><?= htmlspecialchars((string) $errors['poids_kg'], ENT_QUOTES, 'UTF-8') ?></small><?php endif; ?>
            </div>
            <div class="fw-form__group">
              <label for="fw-ins-taille">Taille (cm)</label>
              <input type="text" id="fw-ins-taille" name="taille_cm" value="<?= htmlspecialchars((string) ($old['taille_cm'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
              <?php if (!empty($errors['taille_cm'])) : ?><small style="color:var(--fw-alert)"><?= htmlspecialchars((string) $errors['taille_cm'], ENT_QUOTES, 'UTF-8') ?></small><?php endif; ?>
            </div>
          </div>

          <div class="fw-form__group">
            <label for="fw-ins-objectif">Objectif</label>
            <select id="fw-ins-objectif" name="objectif">
              <option value="">-- Sélectionner un objectif --</option>
              <option value="perte" <?= ($old['objectif'] ?? '') === 'perte' ? 'selected' : '' ?>>Perte de poids</option>
              <option value="maintien" <?= ($old['objectif'] ?? '') === 'maintien' ? 'selected' : '' ?>>Maintien</option>
              <option value="prise" <?= ($old['objectif'] ?? '') === 'prise' ? 'selected' : '' ?>>Prise de masse</option>
              <option value="performance" <?= ($old['objectif'] ?? '') === 'performance' ? 'selected' : '' ?>>Performance sportive</option>
            </select>
            <?php if (!empty($errors['objectif'])) : ?><small style="color:var(--fw-alert)"><?= htmlspecialchars((string) $errors['objectif'], ENT_QUOTES, 'UTF-8') ?></small><?php endif; ?>
          </div>

          <div class="fw-form__group">
            <label for="fw-ins-allergies">Allergies (séparées par des virgules)</label>
            <input type="text" id="fw-ins-allergies" name="allergies" value="<?= htmlspecialchars((string) ($old['allergies'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" placeholder="ex: Arachides, Noix">
          </div>

          <div class="fw-form__group">
            <label for="fw-ins-regimes">Régimes (séparés par des virgules)</label>
            <input type="text" id="fw-ins-regimes" name="regimes" value="<?= htmlspecialchars((string) ($old['regimes'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" placeholder="ex: Sans Gluten, Végétarien">
          </div>

          <div class="fw-form__group">
            <label for="fw-ins-intolerances">Intolérances (séparées par des virgules)</label>
            <input type="text" id="fw-ins-intolerances" name="intolerances" value="<?= htmlspecialchars((string) ($old['intolerances'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" placeholder="ex: Lactose, Gluten">
          </div>

          <button type="submit" class="fw-btn" style="width:100%;padding:0.65rem">S'inscrire</button>
        </form>
        <p class="fw-auth-links">Déjà inscrit ? <a href="<?= htmlspecialchars($routesModule2['front_connexion'] ?? '#', ENT_QUOTES, 'UTF-8') ?>">Connexion</a></p>
      </div>
    </section>
  </div>
</main>

<script>
// Prévisualisation de la photo avant l'upload
document.getElementById('fw-ins-photo').addEventListener('change', function(e) {
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
