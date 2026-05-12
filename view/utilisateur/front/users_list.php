<?php
/**
 * Front-office — Annuaire des utilisateurs
 *
 * @var array<string, mixed> $user
 * @var array<int, array<string, mixed>> $users
 */
declare(strict_types=1);

$errors = $errors ?? [];
$old = $old ?? [];

$pageTitle = $pageTitle ?? 'Annuaire des utilisateurs';
$activeNav = 'messages';
$hideSidebar = false;
$hideTopbar = false;

$currentUser = $utilisateur;

require dirname(__DIR__) . '/routes_defaults.php';
require __DIR__ . '/layouts/header.php';
?>

<main id="fw-main-content" class="fw-content">
  <div class="fw-container" style="max-width: 1000px;">
    <section class="fw-card">
      <h2 class="fw-card__head"><span aria-hidden="true">👥</span> Annuaire des utilisateurs</h2>
      <div class="fw-card__body">
        <p style="margin-bottom: 2rem; color: var(--fw-text-muted);">
          Sélectionnez un utilisateur pour démarrer une conversation.
        </p>

        <?php if (empty($users)) : ?>
          <p style="text-align: center; padding: 2rem; color: var(--fw-text-muted);">
            Aucun autre utilisateur disponible pour le moment.
          </p>
        <?php else : ?>
          <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1.5rem;">
            <?php foreach ($users as $u) : ?>
              <div style="background: linear-gradient(135deg, #f2e9d4 0%, #e8dcc8 100%); border-radius: 12px; padding: 1.5rem; transition: transform 0.2s, box-shadow 0.2s; cursor: pointer;"
                   onmouseover="this.style.transform='translateY(-4px)'; this.style.boxShadow='0 8px 16px rgba(110, 58, 36, 0.1)'"
                   onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'">
                
                <!-- Photo de profil -->
                <div style="text-align: center; margin-bottom: 1rem;">
                  <?php if (!empty($u['photo_profil'])) : ?>
                    <img src="<?= htmlspecialchars($u['photo_profil'], ENT_QUOTES, 'UTF-8') ?>" 
                         alt="Photo de <?= htmlspecialchars($u['prenom'] . ' ' . $u['nom'], ENT_QUOTES, 'UTF-8') ?>"
                         style="width: 80px; height: 80px; border-radius: 50%; object-fit: cover; border: 3px solid #6E3A24;">
                  <?php else : ?>
                    <div style="width: 80px; height: 80px; border-radius: 50%; background: #6E3A24; color: white; display: flex; align-items: center; justify-content: center; font-weight: bold; margin: 0 auto;">
                      <?= htmlspecialchars(substr($u['prenom'], 0, 1) . substr($u['nom'], 0, 1), ENT_QUOTES, 'UTF-8') ?>
                    </div>
                  <?php endif; ?>
                </div>

                <!-- Informations utilisateur -->
                <h3 style="margin: 1rem 0 0.5rem 0; text-align: center; color: #6E3A24; font-size: 1.1rem;">
                  <?= htmlspecialchars($u['prenom'] . ' ' . $u['nom'], ENT_QUOTES, 'UTF-8') ?>
                </h3>
                <p style="text-align: center; color: #666; font-size: 0.9rem; margin-bottom: 1.5rem;">
                  <?= htmlspecialchars($u['email'], ENT_QUOTES, 'UTF-8') ?>
                </p>

                <!-- Boutons d'action -->
                <div style="display: flex; gap: 0.5rem;">
                  <a href="<?= htmlspecialchars($routesModule2['front_public_view'] ?? '', ENT_QUOTES, 'UTF-8') ?>&id=<?= (int) $u['id'] ?>"
                     style="flex: 1; text-align: center; background: #6E3A24; color: white; padding: 0.75rem 1rem; border-radius: 6px; text-decoration: none; font-weight: 500; transition: background 0.2s; font-size: 0.9rem;">
                    👁️ Voir Profil
                  </a>
                  <a href="<?= htmlspecialchars($routesModule2['front_chat'] ?? '', ENT_QUOTES, 'UTF-8') ?>&recipient_id=<?= (int) $u['id'] ?>"
                     style="flex: 1; text-align: center; background: #8B5A3C; color: white; padding: 0.75rem 1rem; border-radius: 6px; text-decoration: none; font-weight: 500; transition: background 0.2s; font-size: 0.9rem;">
                    ✉️ Contacter
                  </a>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </section>
  </div>
</main>

<?php require __DIR__ . '/layouts/footer.php'; ?>

