<?php
/**
 * Front-office — Vérification du code de réinitialisation
 *
 * @var array<string, mixed>|null $errors
 * @var array<string, mixed>|null $old
 * @var string|null $successMessage
 */
declare(strict_types=1);

$errors = $errors ?? [];
$old = $old ?? [];
$successMessage = $successMessage ?? null;

$pageTitle = $pageTitle ?? 'Vérifier le code';
$activeNav = '';
$hideSidebar = true;
$hideTopbar = true;

require dirname(__DIR__) . '/routes_defaults.php';

require __DIR__ . '/layouts/header.php';
?>

<main id="fw-main-content" class="fw-content">
  <div class="fw-auth-panel">
    <section class="fw-card" aria-labelledby="fw-verify-title">
      <h2 id="fw-verify-title" class="fw-card__head"><span aria-hidden="true">🔐</span> Vérifier votre code</h2>
      <div class="fw-card__body">
        <?php if ($successMessage) : ?>
          <p class="fw-alert-box fw-alert-box--green" role="status">
            <span aria-hidden="true">✓</span>
            <span><?= htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8') ?></span>
          </p>
        <?php endif; ?>

        <form class="fw-form" method="post" action="<?= htmlspecialchars($routesModule2['front_verify_reset_code'] ?? '', ENT_QUOTES, 'UTF-8') ?>" novalidate>
          <input type="hidden" name="_token" value="">
          
          <p style="font-size:0.9rem;margin-bottom:1rem;color:var(--fw-text-muted);">
            Saisissez le code que vous avez reçu par email, puis votre nouveau mot de passe.
          </p>

          <div class="fw-form__group">
            <label for="fw-verify-email">Courriel</label>
            <input type="text" id="fw-verify-email" name="email" value="<?= htmlspecialchars((string) ($old['email'] ?? $_GET['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" autocomplete="email">
            <?php if (!empty($errors['email'])) : ?><small style="color:var(--fw-alert)"><?= htmlspecialchars((string) $errors['email'], ENT_QUOTES, 'UTF-8') ?></small><?php endif; ?>
          </div>

          <div class="fw-form__group">
            <label for="fw-verify-code">Code de réinitialisation</label>
            <input type="text" id="fw-verify-code" name="code" placeholder="123456" maxlength="10" value="<?= htmlspecialchars((string) ($old['code'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
            <?php if (!empty($errors['code'])) : ?><small style="color:var(--fw-alert)"><?= htmlspecialchars((string) $errors['code'], ENT_QUOTES, 'UTF-8') ?></small><?php endif; ?>
          </div>

          <div class="fw-form__group">
            <label for="fw-verify-password">Nouveau mot de passe</label>
            <input type="password" id="fw-verify-password" name="password" autocomplete="new-password">
            <?php if (!empty($errors['password'])) : ?><small style="color:var(--fw-alert)"><?= htmlspecialchars((string) $errors['password'], ENT_QUOTES, 'UTF-8') ?></small><?php endif; ?>
          </div>

          <div class="fw-form__group">
            <label for="fw-verify-password-confirm">Confirmer le mot de passe</label>
            <input type="password" id="fw-verify-password-confirm" name="password_confirm" autocomplete="new-password">
            <?php if (!empty($errors['password_confirm'])) : ?><small style="color:var(--fw-alert)"><?= htmlspecialchars((string) $errors['password_confirm'], ENT_QUOTES, 'UTF-8') ?></small><?php endif; ?>
          </div>

          <button type="submit" class="fw-btn" style="width:100%;padding:0.65rem">Réinitialiser le mot de passe</button>
        </form>

        <p class="fw-auth-links">
          <a href="<?= htmlspecialchars($routesModule2['front_password_reset'] ?? '#', ENT_QUOTES, 'UTF-8') ?>">Revenir à la réinitialisation</a>
        </p>
      </div>
    </section>
  </div>
</main>

<?php require __DIR__ . '/layouts/footer.php'; ?>

