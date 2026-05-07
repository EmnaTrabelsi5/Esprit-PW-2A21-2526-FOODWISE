<?php
/**
 * Front-office — Vue publique du profil d'un utilisateur
 *
 * @var array<string, mixed> $user
 * @var array<string, mixed> $viewedUser
 * @var array<string, mixed>|null $publicProfile
 */
declare(strict_types=1);

$viewedUser = $viewedUser ?? [];
$publicProfile = $publicProfile ?? [];
$user = $user ?? [];

$pageTitle = $pageTitle ?? 'Profil';
$activeNav = 'messages';

require dirname(__DIR__) . '/routes_defaults.php';
require __DIR__ . '/layouts/header.php';

// Fonction helper pour afficher les champs selon la confidentialité
function displayField($value, $isPublic) {
    if (!$isPublic) {
        return '<span style="color: #999; font-style: italic;">🔒 Privé</span>';
    }
    return !empty($value) ? htmlspecialchars($value, ENT_QUOTES, 'UTF-8') : '<span style="color: #999;">-</span>';
}

$objectifsOptions = [
    'perte' => 'Perte de poids',
    'maintien' => 'Maintien',
    'prise' => 'Prise de masse',
    'performance' => 'Performance sportive',
];
?>

<main id="fw-main-content" class="fw-content">
  <div class="fw-container" style="max-width: 900px;">
    <section class="fw-card">
      <h2 class="fw-card__head" style="display: flex; align-items: center; gap: 1rem;">
        <?php if (!empty($viewedUser['photo_profil'])) : ?>
          <img src="<?= htmlspecialchars($viewedUser['photo_profil'], ENT_QUOTES, 'UTF-8') ?>" 
               alt="Photo de <?= htmlspecialchars($viewedUser['prenom'] . ' ' . $viewedUser['nom'], ENT_QUOTES, 'UTF-8') ?>"
               style="width: 60px; height: 60px; border-radius: 50%; object-fit: cover; border: 2px solid #6E3A24;">
        <?php else : ?>
          <div style="width: 60px; height: 60px; border-radius: 50%; background: #6E3A24; color: white; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 1.5rem;">
            <?= htmlspecialchars(substr($viewedUser['prenom'], 0, 1) . substr($viewedUser['nom'], 0, 1), ENT_QUOTES, 'UTF-8') ?>
          </div>
        <?php endif; ?>
        <div>
          <p style="margin: 0; font-size: 1.5rem; color: #6E3A24;">
            <?= htmlspecialchars($viewedUser['prenom'] . ' ' . $viewedUser['nom'], ENT_QUOTES, 'UTF-8') ?>
          </p>
          <p style="margin: 0.25rem 0 0 0; font-size: 0.9rem; color: #999;">
            <?= htmlspecialchars($viewedUser['email'], ENT_QUOTES, 'UTF-8') ?>
          </p>
        </div>
      </h2>

      <div class="fw-card__body">
        <?php if ($publicProfile === null) : ?>
          <p style="text-align: center; color: #999; padding: 2rem;">
            Le profil nutritionnel de cet utilisateur n'est pas disponible.
          </p>
        <?php else : ?>
          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-top: 1.5rem;">
            <!-- Colonne gauche : Infos physiques -->
            <div>
              <h3 style="color: #6E3A24; margin-bottom: 1rem; border-bottom: 2px solid #F2E9D4; padding-bottom: 0.5rem;">
                📏 Infos physiques
              </h3>
              <div style="display: grid; gap: 1rem;">
                <div style="padding: 1rem; background: #f9f7f4; border-radius: 8px;">
                  <p style="margin: 0 0 0.5rem 0; color: #666; font-size: 0.9rem;">Poids</p>
                  <p style="margin: 0; font-size: 1.2rem; font-weight: bold; color: #6E3A24;">
                    <?php 
                    if ($publicProfile['show_weight'] ?? 0) {
                        echo htmlspecialchars((string) $publicProfile['poids_kg'], ENT_QUOTES, 'UTF-8') . ' kg';
                    } else {
                        echo '<span style="color: #999; font-style: italic;">🔒 Privé</span>';
                    }
                    ?>
                  </p>
                </div>
                <div style="padding: 1rem; background: #f9f7f4; border-radius: 8px;">
                  <p style="margin: 0 0 0.5rem 0; color: #666; font-size: 0.9rem;">Taille</p>
                  <p style="margin: 0; font-size: 1.2rem; font-weight: bold; color: #6E3A24;">
                    <?php 
                    if ($publicProfile['show_height'] ?? 0) {
                        echo htmlspecialchars((string) $publicProfile['taille_cm'], ENT_QUOTES, 'UTF-8') . ' cm';
                    } else {
                        echo '<span style="color: #999; font-style: italic;">🔒 Privé</span>';
                    }
                    ?>
                  </p>
                </div>
              </div>
            </div>

            <!-- Colonne droite : Infos nutritionnelles -->
            <div>
              <h3 style="color: #6E3A24; margin-bottom: 1rem; border-bottom: 2px solid #F2E9D4; padding-bottom: 0.5rem;">
                🎯 Infos nutritionnelles
              </h3>
              <div style="display: grid; gap: 1rem;">
                <div style="padding: 1rem; background: #f9f7f4; border-radius: 8px;">
                  <p style="margin: 0 0 0.5rem 0; color: #666; font-size: 0.9rem;">Objectif</p>
                  <p style="margin: 0; font-size: 1.1rem; color: #6E3A24;">
                    <?php 
                    if ($publicProfile['show_goal'] ?? 0) {
                        $goal = htmlspecialchars($publicProfile['objectif'], ENT_QUOTES, 'UTF-8');
                        echo $objectifsOptions[$goal] ?? $goal;
                    } else {
                        echo '<span style="color: #999; font-style: italic;">🔒 Privé</span>';
                    }
                    ?>
                  </p>
                </div>
              </div>
            </div>
          </div>

          <!-- Allergies et régimes -->
          <div style="margin-top: 2rem; padding-top: 2rem; border-top: 1px solid #ddd;">
            <h3 style="color: #6E3A24; margin-bottom: 1rem;">🍽️ Régimes alimentaires</h3>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
              <div style="padding: 1rem; background: #f9f7f4; border-radius: 8px;">
                <p style="margin: 0 0 0.5rem 0; color: #666; font-size: 0.9rem; font-weight: bold;">Allergies</p>
                <p style="margin: 0; color: #6E3A24;">
                  <?php 
                  if ($publicProfile['show_allergies'] ?? 0) {
                      if (!empty($publicProfile['allergies'])) {
                          echo htmlspecialchars($publicProfile['allergies'], ENT_QUOTES, 'UTF-8');
                      } else {
                          echo '<span style="color: #999;">Aucune allergie déclarée</span>';
                      }
                  } else {
                      echo '<span style="color: #999; font-style: italic;">🔒 Privé</span>';
                  }
                  ?>
                </p>
              </div>
              <div style="padding: 1rem; background: #f9f7f4; border-radius: 8px;">
                <p style="margin: 0 0 0.5rem 0; color: #666; font-size: 0.9rem; font-weight: bold;">Régimes</p>
                <p style="margin: 0; color: #6E3A24;">
                  <?php 
                  if ($publicProfile['show_diet'] ?? 0) {
                      if (!empty($publicProfile['regimes'])) {
                          echo htmlspecialchars($publicProfile['regimes'], ENT_QUOTES, 'UTF-8');
                      } else {
                          echo '<span style="color: #999;">Aucun régime</span>';
                      }
                  } else {
                      echo '<span style="color: #999; font-style: italic;">🔒 Privé</span>';
                  }
                  ?>
                </p>
              </div>
            </div>

            <div style="padding: 1rem; background: #f9f7f4; border-radius: 8px; margin-top: 1.5rem;">
              <p style="margin: 0 0 0.5rem 0; color: #666; font-size: 0.9rem; font-weight: bold;">Intolérances</p>
              <p style="margin: 0; color: #6E3A24;">
                <?php 
                if (!empty($publicProfile['intolerances'])) {
                    echo htmlspecialchars($publicProfile['intolerances'], ENT_QUOTES, 'UTF-8');
                } else {
                    echo '<span style="color: #999;">Aucune intolérance déclarée</span>';
                }
                ?>
              </p>
            </div>
          </div>

          <!-- Actions -->
          <div style="display: flex; gap: 1rem; margin-top: 2rem; padding-top: 1rem; border-top: 1px solid #ddd;">
            <a href="<?= htmlspecialchars($routesModule2['front_chat'] ?? '', ENT_QUOTES, 'UTF-8') ?>&recipient_id=<?= (int) $viewedUser['id'] ?>"
               class="fw-btn" style="background: #6E3A24; color: white; text-decoration: none; flex: 1; text-align: center;">
              ✉️ Envoyer un message
            </a>
            <a href="<?= htmlspecialchars($routesModule2['front_users_list'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
               class="fw-btn fw-btn--ghost">
              ← Retour
            </a>
          </div>
        <?php endif; ?>
      </div>
    </section>
  </div>
</main>

<?php require __DIR__ . '/layouts/footer.php'; ?>
