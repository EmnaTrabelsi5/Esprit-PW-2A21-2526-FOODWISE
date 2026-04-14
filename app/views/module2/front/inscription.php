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
        <form class="fw-form" method="post" action="<?= htmlspecialchars($routesModule2['front_inscription'] ?? '', ENT_QUOTES, 'UTF-8') ?>" novalidate>
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
          </div>
          <button type="submit" class="fw-btn" style="width:100%;padding:0.65rem">S'inscrire</button>
        </form>
        <p class="fw-auth-links">Déjà inscrit ? <a href="<?= htmlspecialchars($routesModule2['front_connexion'] ?? '#', ENT_QUOTES, 'UTF-8') ?>">Connexion</a></p>
      </div>
    </section>
  </div>
</main>

<?php require __DIR__ . '/layouts/footer.php'; ?>
