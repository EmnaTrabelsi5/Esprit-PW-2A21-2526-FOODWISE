<?php
/**
 * Front-office — Chat privé
 *
 * @var array<string, mixed> $user
 * @var array<string, mixed> $recipient
 * @var array<int, array<string, mixed>> $conversation
 * @var array<string, mixed> $errors
 */
declare(strict_types=1);

$errors = $errors ?? [];
$old = $old ?? [];

$pageTitle = $pageTitle ?? 'Conversation';
$activeNav = 'messages';
$hideSidebar = false;
$hideTopbar = false;

$currentUser = $utilisateur;

require dirname(__DIR__) . '/routes_defaults.php';
require __DIR__ . '/layouts/header.php';
?>

<main id="fw-main-content" class="fw-content">
  <div class="fw-container" style="max-width: 900px;">
    <section class="fw-card">
      <!-- En-tête de la conversation -->
      <div class="fw-card__head" style="display: flex; justify-content: space-between; align-items: center;">
        <div style="display: flex; align-items: center; gap: 1rem;">
          <?php if (!empty($recipient['photo_profil'])) : ?>
            <img src="<?= htmlspecialchars($recipient['photo_profil'], ENT_QUOTES, 'UTF-8') ?>" 
                 alt="Photo de <?= htmlspecialchars($recipient['prenom'] . ' ' . $recipient['nom'], ENT_QUOTES, 'UTF-8') ?>"
                 style="width: 50px; height: 50px; border-radius: 50%; object-fit: cover; border: 2px solid #6E3A24;">
          <?php else : ?>
            <div style="width: 50px; height: 50px; border-radius: 50%; background: #6E3A24; color: white; display: flex; align-items: center; justify-content: center; font-weight: bold;">
              <?= htmlspecialchars(substr($recipient['prenom'], 0, 1) . substr($recipient['nom'], 0, 1), ENT_QUOTES, 'UTF-8') ?>
            </div>
          <?php endif; ?>
          <div>
            <h2 style="margin: 0; color: #6E3A24;">
              <?= htmlspecialchars($recipient['prenom'] . ' ' . $recipient['nom'], ENT_QUOTES, 'UTF-8') ?>
            </h2>
            <p style="margin: 0; font-size: 0.9rem; color: #999;">
              <?= htmlspecialchars($recipient['email'], ENT_QUOTES, 'UTF-8') ?>
            </p>
          </div>
        </div>
        <a href="<?= htmlspecialchars($routesModule2['front_users_list'] ?? '', ENT_QUOTES, 'UTF-8') ?>" 
           style="color: #6E3A24; text-decoration: none; font-size: 1.5rem;">←</a>
      </div>

      <div class="fw-card__body" style="padding: 0; display: flex; flex-direction: column; height: 500px;">
        
        <!-- Zone de messages -->
        <div style="flex: 1; overflow-y: auto; padding: 1.5rem; background: #fafafa; border-bottom: 1px solid #ddd;">
          <?php if (empty($conversation)) : ?>
            <p style="text-align: center; color: #999; margin-top: 2rem;">
              Aucun message pour le moment. Soyez le premier à envoyer un message !
            </p>
          <?php else : ?>
            <div style="display: flex; flex-direction: column; gap: 1rem;">
              <?php foreach ($conversation as $msg) : ?>
                <div style="display: flex; <?= $msg['sender_id'] === (int) $user['id'] ? 'justify-content: flex-end;' : 'justify-content: flex-start;' ?>">
                  <div style="max-width: 70%; background: <?= $msg['sender_id'] === (int) $user['id'] ? '#6E3A24; color: white;' : '#f2e9d4; color: #333;' ?> padding: 0.75rem 1rem; border-radius: 12px; <?= $msg['sender_id'] === (int) $user['id'] ? 'border-bottom-right-radius: 4px;' : 'border-bottom-left-radius: 4px;' ?>">
                    <p style="margin: 0; word-wrap: break-word;">
                      <?= htmlspecialchars($msg['content'], ENT_QUOTES, 'UTF-8') ?>
                    </p>
                    <p style="margin: 0.5rem 0 0 0; font-size: 0.75rem; opacity: 0.7;">
                      <?= date('H:i', strtotime($msg['created_at'])) ?>
                    </p>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>

        <!-- Zone d'envoi de message -->
        <div style="padding: 1.5rem; background: white;">
          <?php if (!empty($errors['global'])) : ?>
            <p style="color: var(--fw-alert); margin-bottom: 1rem;">
              <?= htmlspecialchars($errors['global'], ENT_QUOTES, 'UTF-8') ?>
            </p>
          <?php endif; ?>

          <form method="post" novalidate>
            <div style="display: flex; gap: 0.75rem;">
              <textarea name="content" 
                        placeholder="Écrivez votre message..."
                        style="flex: 1; padding: 0.75rem; border: 2px solid #ddd; border-radius: 6px; font-family: inherit; resize: none; min-height: 44px; border-color: <?= !empty($errors['content']) ? 'var(--fw-alert)' : '#ddd' ?>;"
                        rows="1"></textarea>
              <button type="submit" 
                      style="background: #6E3A24; color: white; border: none; padding: 0.75rem 1.5rem; border-radius: 6px; cursor: pointer; font-weight: 500; transition: background 0.2s; white-space: nowrap;">
                Envoyer
              </button>
            </div>
            <?php if (!empty($errors['content'])) : ?>
              <small style="color: var(--fw-alert); display: block; margin-top: 0.5rem;">
                <?= htmlspecialchars($errors['content'], ENT_QUOTES, 'UTF-8') ?>
              </small>
            <?php endif; ?>
          </form>
        </div>
      </div>
    </section>
  </div>
</main>

<script>
  // Scroller vers le bas de la conversation au chargement
  document.addEventListener('DOMContentLoaded', function() {
    const chatBox = document.querySelector('[style*="overflow-y: auto"]');
    if (chatBox) {
      chatBox.scrollTop = chatBox.scrollHeight;
    }
  });
</script>

<?php require __DIR__ . '/layouts/footer.php'; ?>

