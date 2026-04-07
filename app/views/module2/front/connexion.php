<?php
/**
 * Front-office — Connexion
 *
 * @var array<string, mixed>|null $errors
 * @var array<string, mixed>|null $old
 */
declare(strict_types=1);

$errors = $errors ?? [];
$old = $old ?? [];

$pageTitle = $pageTitle ?? 'Connexion';
$activeNav = '';

require dirname(__DIR__) . '/routes_defaults.php';

require __DIR__ . '/layouts/header.php';
?>

<main id="fw-main-content" class="fw-content">
  <div class="fw-auth-panel">
    <section class="fw-card" aria-labelledby="fw-connexion-title">
      <h2 id="fw-connexion-title" class="fw-card__head"><span aria-hidden="true">🔐</span> Connexion</h2>
      <div class="fw-card__body">
        <form class="fw-form" method="post" action="<?= htmlspecialchars($routesModule2['front_connexion'] ?? '', ENT_QUOTES, 'UTF-8') ?>" novalidate>
          <input type="hidden" name="_token" value="">
          <div class="fw-form__group">
            <label for="fw-co-email">Courriel</label>
            <input type="email" id="fw-co-email" name="email" value="<?= htmlspecialchars((string) ($old['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required autocomplete="username">
            <?php if (!empty($errors['email'])) : ?><small style="color:var(--fw-alert)"><?= htmlspecialchars((string) $errors['email'], ENT_QUOTES, 'UTF-8') ?></small><?php endif; ?>
          </div>
          <div class="fw-form__group">
            <label for="fw-co-pass">Mot de passe</label>
            <input type="password" id="fw-co-pass" name="password" required autocomplete="current-password">
            <?php if (!empty($errors['password'])) : ?><small style="color:var(--fw-alert)"><?= htmlspecialchars((string) $errors['password'], ENT_QUOTES, 'UTF-8') ?></small><?php endif; ?>
          </div>
          <button type="submit" class="fw-btn" style="width:100%;padding:0.65rem">Se connecter</button>
        </form>
        <p class="fw-auth-links">Pas encore de compte ? <a href="<?= htmlspecialchars($routesModule2['front_inscription'] ?? '#', ENT_QUOTES, 'UTF-8') ?>">Inscription</a></p>
        <p class="fw-auth-links"><a href="#">Mot de passe oublié</a></p>
      </div>
    </section>
  </div>
</main>

<?php require __DIR__ . '/layouts/footer.php'; ?>
