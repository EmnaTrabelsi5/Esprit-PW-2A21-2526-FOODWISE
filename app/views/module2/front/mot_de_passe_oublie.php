<?php
/**
 * Front-office — Récupération de mot de passe
 *
 * @var array<string, mixed>|null $errors
 * @var array<string, mixed>|null $old
 * @var string|null $successMessage
 */
declare(strict_types=1);

$errors = $errors ?? [];
$old = $old ?? [];
$successMessage = $successMessage ?? null;

$pageTitle = $pageTitle ?? 'Réinitialiser mon mot de passe';
$activeNav = '';

require dirname(__DIR__) . '/routes_defaults.php';

require __DIR__ . '/layouts/header.php';
?>

<main id="fw-main-content" class="fw-content">
  <div class="fw-auth-panel">
    <section class="fw-card" aria-labelledby="fw-reset-title">
      <h2 id="fw-reset-title" class="fw-card__head"><span aria-hidden="true">🔑</span> Récupération de mot de passe</h2>
      <div class="fw-card__body">
        <?php if ($successMessage) : ?>
          <p class="fw-alert-box fw-alert-box--green" role="status">
            <span aria-hidden="true">✓</span>
            <span><?= htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8') ?></span>
          </p>
        <?php endif; ?>

        <form class="fw-form" method="post" action="<?= htmlspecialchars($routesModule2['front_password_reset'] ?? '', ENT_QUOTES, 'UTF-8') ?>" novalidate>
          <input type="hidden" name="_token" value="">
          
          <p style="font-size:0.9rem;margin-bottom:1rem;color:var(--fw-text-muted);">
            Saisissez votre adresse courriel pour recevoir les instructions de réinitialisation au mot de passe.
          </p>

          <div class="fw-form__group">
            <label for="fw-reset-email">Courriel</label>
            <input type="text" id="fw-reset-email" name="email" value="<?= htmlspecialchars((string) ($old['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" autocomplete="email">
            <?php if (!empty($errors['email'])) : ?><small style="color:var(--fw-alert)"><?= htmlspecialchars((string) $errors['email'], ENT_QUOTES, 'UTF-8') ?></small><?php endif; ?>
          </div>

          <button type="submit" class="fw-btn" style="width:100%;padding:0.65rem">Réinitialiser le mot de passe</button>
        </form>

        <p class="fw-auth-links"><a href="<?= htmlspecialchars($routesModule2['front_connexion'] ?? '#', ENT_QUOTES, 'UTF-8') ?>">Retour à la connexion</a></p>
      </div>
    </section>
  </div>
</main>

<?php require __DIR__ . '/layouts/footer.php'; ?>
