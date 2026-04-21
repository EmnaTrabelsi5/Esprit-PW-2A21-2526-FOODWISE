<?php
/**
 * Back-office — Connexion administrateur
 *
 * @var array<string, mixed>|null $errors
 * @var array<string, mixed>|null $old
 */
declare(strict_types=1);

$errors = $errors ?? [];
$old = $old ?? [];

$pageTitle = $pageTitle ?? 'Connexion Admin';
$activeNav = '';

require dirname(__DIR__, 2) . '/routes_defaults.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Connexion Admin — FoodWise</title>
  <link rel="stylesheet" href="<?= htmlspecialchars($routesModule2['static_css'] ?? 'app/views/module2/assets/css/module2-foodwise.css', ENT_QUOTES, 'UTF-8') ?>">
  <style>
    body {
      display: flex;
      align-items: center;
      justify-content: center;
      min-height: 100vh;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
    .login-container {
      background: white;
      padding: 2rem;
      border-radius: 8px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.2);
      width: 100%;
      max-width: 400px;
    }
    .login-container h1 {
      text-align: center;
      margin-bottom: 1.5rem;
      color: #333;
    }
  </style>
</head>
<body>
  <div class="login-container">
    <h1>🔐 Connexion Admin</h1>
    
    <form method="post" action="?route=module2.back.login" novalidate>
      <div style="margin-bottom: 1rem;">
        <label for="email" style="display: block; margin-bottom: 0.5rem; font-weight: bold;">Email</label>
        <input type="email" id="email" name="email" value="<?= htmlspecialchars((string) ($old['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 4px; font-size: 1rem;">
        <?php if (!empty($errors['email'])) : ?>
          <small style="color: #e74c3c; display: block; margin-top: 0.25rem;"><?= htmlspecialchars((string) $errors['email'], ENT_QUOTES, 'UTF-8') ?></small>
        <?php endif; ?>
      </div>

      <div style="margin-bottom: 1.5rem;">
        <label for="password" style="display: block; margin-bottom: 0.5rem; font-weight: bold;">Mot de passe</label>
        <input type="password" id="password" name="password" style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 4px; font-size: 1rem;">
        <?php if (!empty($errors['password'])) : ?>
          <small style="color: #e74c3c; display: block; margin-top: 0.25rem;"><?= htmlspecialchars((string) $errors['password'], ENT_QUOTES, 'UTF-8') ?></small>
        <?php endif; ?>
      </div>

      <button type="submit" style="width: 100%; padding: 0.75rem; background: #667eea; color: white; border: none; border-radius: 4px; font-weight: bold; cursor: pointer; font-size: 1rem;">Se connecter</button>
    </form>

    <?php if (!empty($errors['global'])) : ?>
      <div style="margin-top: 1rem; padding: 1rem; background: #ffe5e5; border-left: 4px solid #e74c3c; border-radius: 4px; color: #c0392b;">
        <?= htmlspecialchars((string) $errors['global'], ENT_QUOTES, 'UTF-8') ?>
      </div>
    <?php endif; ?>
  </div>
</body>
</html>
