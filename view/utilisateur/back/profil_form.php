<?php
declare(strict_types=1);

$errors = $errors ?? [];
$old = $old ?? [];
$isNew = $isNew ?? true;

$pageTitle = $pageTitle ?? ($isNew ? 'Créer un profil nutritionnel' : 'Modifier un profil nutritionnel');
$activeNav = 'suivi_nutritionnel';

require dirname(__DIR__) . '/routes_defaults.php';
require __DIR__ . '/layouts/header.php';
?>

<main id="fw-main-content" class="fw-content">
  <div class="fw-auth-panel" style="max-width:720px;margin-top:1rem">
    <section class="fw-card" aria-labelledby="fw-admin-profil-title">
      <h2 id="fw-admin-profil-title" class="fw-card__head"><span aria-hidden="true">🧾</span> <?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></h2>
      <div class="fw-card__body">
        <form class="fw-form" method="post" action="<?= htmlspecialchars($routesModule2['back_profil_form'] ?? '', ENT_QUOTES, 'UTF-8') ?><?= empty($old['id']) ? '' : '&id=' . urlencode((string) $old['id']) ?>" enctype="multipart/form-data">
          <input type="hidden" name="id" value="<?= htmlspecialchars((string) ($old['id'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">

          <div class="fw-form__row fw-form__row--2">
            <div class="fw-form__group">
              <label for="fw-admin-nom">Nom</label>
              <input type="text" id="fw-admin-nom" name="nom" value="<?= oldValue($old, 'nom') ?>">
              <?php if (!empty($errors['nom'])) : ?><small style="color:var(--fw-alert)"><?= htmlspecialchars((string) $errors['nom'], ENT_QUOTES, 'UTF-8') ?></small><?php endif; ?>
            </div>
            <div class="fw-form__group">
              <label for="fw-admin-prenom">Prénom</label>
              <input type="text" id="fw-admin-prenom" name="prenom" value="<?= oldValue($old, 'prenom') ?>">
            </div>
          </div>

          <div class="fw-form__group">
            <label for="fw-admin-email">Courriel</label>
            <input type="text" id="fw-admin-email" name="email" value="<?= oldValue($old, 'email') ?>">
            <?php if (!empty($errors['email'])) : ?><small style="color:var(--fw-alert)"><?= htmlspecialchars((string) $errors['email'], ENT_QUOTES, 'UTF-8') ?></small><?php endif; ?>
          </div>

          <?php if ($isNew) : ?>
            <div class="fw-form__group">
              <label for="fw-admin-password">Mot de passe</label>
              <input type="password" id="fw-admin-password" name="password" value="">
            </div>
          <?php endif; ?>

          <div class="fw-form__group">
            <label for="fw-admin-photo">Photo de profil (optionnel)</label>
            <input type="file" id="fw-admin-photo" name="photo_profil" accept="image/jpeg,image/png,image/webp,image/gif">
            <small style="color:var(--fw-text-muted)">JPG, PNG, WebP ou GIF. Max 5MB.</small>
            <?php if (!empty($errors['photo_profil'])) : ?><small style="color:var(--fw-alert)"><?= htmlspecialchars((string) $errors['photo_profil'], ENT_QUOTES, 'UTF-8') ?></small><?php endif; ?>
          </div>

          <div class="fw-form__row fw-form__row--2">
            <div class="fw-form__group">
              <label for="fw-admin-poids">Poids (kg)</label>
              <input type="text" id="fw-admin-poids" name="poids_kg" value="<?= oldValue($old, 'poids_kg') ?>">
              <?php if (!empty($errors['poids_kg'])) : ?><small style="color:var(--fw-alert)"><?= htmlspecialchars((string) $errors['poids_kg'], ENT_QUOTES, 'UTF-8') ?></small><?php endif; ?>
            </div>
            <div class="fw-form__group">
              <label for="fw-admin-taille">Taille (cm)</label>
              <input type="text" id="fw-admin-taille" name="taille_cm" value="<?= oldValue($old, 'taille_cm') ?>">
              <?php if (!empty($errors['taille_cm'])) : ?><small style="color:var(--fw-alert)"><?= htmlspecialchars((string) $errors['taille_cm'], ENT_QUOTES, 'UTF-8') ?></small><?php endif; ?>
            </div>
          </div>

          <?php 
            $poids = isset($old['poids_kg']) ? (float) $old['poids_kg'] : 0;
            $taille = isset($old['taille_cm']) ? (int) $old['taille_cm'] : 0;
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
            <label for="fw-admin-objectif">Objectif</label>
            <select id="fw-admin-objectif" name="objectif">
              <?php foreach (['perte' => 'Perte de poids', 'maintien' => 'Maintien', 'prise' => 'Prise de masse', 'performance' => 'Performance sportive'] as $value => $label) : ?>
                <option value="<?= htmlspecialchars($value, ENT_QUOTES, 'UTF-8') ?>" <?= ($old['objectif'] ?? '') === $value ? 'selected' : '' ?>><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></option>
              <?php endforeach; ?>
            </select>
            <?php if (!empty($errors['objectif'])) : ?><small style="color:var(--fw-alert)"><?= htmlspecialchars((string) $errors['objectif'], ENT_QUOTES, 'UTF-8') ?></small><?php endif; ?>
          </div>

          <div class="fw-form__group">
            <label for="fw-admin-allergies">Allergies (séparées par des virgules)</label>
            <input type="text" id="fw-admin-allergies" name="allergies" value="<?= oldValue($old, 'allergies') ?>">
          </div>

          <div class="fw-form__group">
            <label for="fw-admin-regimes">Régimes (séparés par des virgules)</label>
            <input type="text" id="fw-admin-regimes" name="regimes" value="<?= oldValue($old, 'regimes') ?>">
          </div>

          <div class="fw-form__group">
            <label for="fw-admin-intolerances">Intolérances (séparées par des virgules)</label>
            <input type="text" id="fw-admin-intolerances" name="intolerances" value="<?= oldValue($old, 'intolerances') ?>">
          </div>

          <div style="display:flex;gap:0.75rem;flex-wrap:wrap;margin-top:1rem">
            <button type="submit" class="fw-btn"><?= $isNew ? 'Créer' : 'Mettre à jour' ?></button>
            <a class="fw-btn fw-btn--ghost" href="<?= htmlspecialchars($routesModule2['back_dashboard_profils'] ?? '#', ENT_QUOTES, 'UTF-8') ?>">Retour</a>
          </div>
        </form>
      </div>
    </section>
  </div>
</main>

<?php require __DIR__ . '/layouts/footer.php'; ?>
