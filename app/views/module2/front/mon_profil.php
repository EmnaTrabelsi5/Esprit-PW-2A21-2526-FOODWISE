<?php
/**
 * Front-office — Tableau de bord « Mon profil » (NutriProfile)
 *
 * @var array<string, mixed> $utilisateur       ex. id, nom, prenom, email
 * @var array<string, mixed> $profilNutritionnel  poids_kg, taille_cm, objectif, allergies[], regimes[], intolerances[], scoreCompletion
 */
declare(strict_types=1);

$utilisateur = $utilisateur ?? ['nom' => 'Utilisateur', 'prenom' => ''];
$profilNutritionnel = $profilNutritionnel ?? [
    'poids_kg' => null,
    'taille_cm' => null,
    'objectif' => null,
    'allergies' => [],
    'regimes' => [],
    'intolerances' => [],
    'scoreCompletion' => 0,
];
$photoProfil = $photoProfil ?? null;

$nomAffiche = trim(($utilisateur['prenom'] ?? '') . ' ' . ($utilisateur['nom'] ?? ''));
if ($nomAffiche === '') {
    $nomAffiche = 'Utilisateur';
}

// Vérifier si le profil est complet
$scoreCompletion = (int) ($profilNutritionnel['scoreCompletion'] ?? 0);
$isProfileComplete = $scoreCompletion === 100;

$pageTitle = $pageTitle ?? 'Mon profil';
$activeNav = 'mon_profil';

require dirname(__DIR__) . '/routes_defaults.php';

require __DIR__ . '/layouts/header.php';
?>

<main id="fw-main-content" class="fw-content">
  <div style="display: grid; grid-template-columns: 0.6fr 1.8fr 0.6fr; gap: 1rem;">
    
    <!-- COLONNE GAUCHE: Identité + Profil nutritionnel + Restrictions -->
    <div>
      <section class="fw-card" aria-labelledby="fw-identite-title" style="padding: 0;">
        <h2 id="fw-identite-title" class="fw-card__head" style="padding: 0.5rem 0.75rem; font-size: 0.85rem;"><span aria-hidden="true">👤</span> Identité</h2>
        <div class="fw-card__body" style="padding: 0.5rem 0.75rem;">
          <dl style="margin: 0;">
            <dt style="font-size: 0.75rem;">Nom</dt>
            <dd style="font-size: 0.75rem; margin: 0.15rem 0;"><?= htmlspecialchars($nomAffiche, ENT_QUOTES, 'UTF-8') ?></dd>
            <dt style="font-size: 0.75rem;">Email</dt>
            <dd style="font-size: 0.75rem; margin: 0.15rem 0;"><?= htmlspecialchars($utilisateur['email'] ?? '', ENT_QUOTES, 'UTF-8') ?></dd>
            <dt style="font-size: 0.75rem;">Score</dt>
            <dd style="font-size: 0.75rem; margin: 0.15rem 0;">
              <div style="background: #e0e0e0; border-radius: 4px; height: 12px; overflow: hidden; margin: 0.25rem 0;">
                <div style="background: linear-gradient(90deg, #667eea, #764ba2); height: 100%; width: <?= $scoreCompletion ?>%; transition: width 0.3s ease;"></div>
              </div>
              <span style="font-size: 0.7rem; color: #666;"><?= $scoreCompletion ?> %</span>
            </dd>
          </dl>
        </div>
      </section>

      <section class="fw-card" aria-labelledby="fw-profil-nut-title" style="padding: 0; margin-top: 0.75rem;">
        <h2 id="fw-profil-nut-title" class="fw-card__head" style="padding: 0.5rem 0.75rem; font-size: 0.85rem;"><span aria-hidden="true">⇄</span> Profil</h2>
        <div class="fw-card__body" style="padding: 0.5rem 0.75rem;">
          <dl style="margin: 0;">
            <dt style="font-size: 0.75rem;">Poids</dt>
            <dd style="font-size: 0.75rem; margin: 0.15rem 0;"><?= $profilNutritionnel['poids_kg'] ? htmlspecialchars((string) $profilNutritionnel['poids_kg'], ENT_QUOTES, 'UTF-8') . ' kg' : '—' ?></dd>
            <dt style="font-size: 0.75rem;">Taille</dt>
            <dd style="font-size: 0.75rem; margin: 0.15rem 0;"><?= $profilNutritionnel['taille_cm'] ? htmlspecialchars((string) $profilNutritionnel['taille_cm'], ENT_QUOTES, 'UTF-8') . ' cm' : '—' ?></dd>
            <dt style="font-size: 0.75rem;">Objectif</dt>
            <dd style="font-size: 0.75rem; margin: 0.15rem 0;">
              <?php
              $objectifsLabels = [
                  'perte' => 'Perte',
                  'maintien' => 'Maintien',
                  'prise' => 'Prise',
                  'performance' => 'Performance',
              ];
              $objectifLabel = $objectifsLabels[$profilNutritionnel['objectif']] ?? '—';
              echo htmlspecialchars($objectifLabel, ENT_QUOTES, 'UTF-8');
              ?>
            </dd>
          </dl>
        </div>
      </section>

      <section class="fw-card" aria-labelledby="fw-restr-title" style="padding: 0; margin-top: 0.75rem;">
        <h2 id="fw-restr-title" class="fw-card__head" style="padding: 0.5rem 0.75rem; font-size: 0.85rem;"><span aria-hidden="true">🚫</span> Régimes</h2>
        <div class="fw-card__body" style="padding: 0.5rem 0.75rem;">
          <dl style="margin: 0;">
            <dt style="font-size: 0.75rem;">Allergies</dt>
            <dd style="font-size: 0.75rem; margin: 0.15rem 0;">
              <?php if (!empty($profilNutritionnel['allergies'])) : ?>
                <?php foreach (array_slice($profilNutritionnel['allergies'], 0, 2) as $allergie) : ?>
                  <?= htmlspecialchars(trim($allergie), ENT_QUOTES, 'UTF-8') ?><br>
                <?php endforeach; ?>
              <?php else : ?>
                —
              <?php endif; ?>
            </dd>
            <dt style="font-size: 0.75rem;">Régimes</dt>
            <dd style="font-size: 0.75rem; margin: 0.15rem 0;">
              <?php if (!empty($profilNutritionnel['regimes'])) : ?>
                <?php foreach (array_slice($profilNutritionnel['regimes'], 0, 2) as $regime) : ?>
                  <?= htmlspecialchars(trim($regime), ENT_QUOTES, 'UTF-8') ?><br>
                <?php endforeach; ?>
              <?php else : ?>
                —
              <?php endif; ?>
            </dd>
          </dl>
        </div>
      </section>
    </div>

    <!-- COLONNE CENTRE: Gérer votre profil (BEAUCOUP PLUS GRAND) -->
    <div>
      <section class="fw-card" aria-labelledby="fw-crud-title" style="height: 100%;">
        <h2 id="fw-crud-title" class="fw-crud-title"><span aria-hidden="true">▦</span> Gérer votre profil</h2>
        <div class="fw-card__body" style="padding-top: 0.5rem;">
          <div class="fw-table-wrap">
            <table class="fw-table">
              <thead>
                <tr>
                  <th scope="col">Paramètre</th>
                  <th scope="col">Valeur actuelle</th>
                  <th scope="col">Action</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td>Créer / modifier son profil &amp; photo</td>
                  <td>Données de base</td>
                  <td><a class="fw-btn" href="<?= htmlspecialchars($routesModule2['front_profil_edit'] ?? '#', ENT_QUOTES, 'UTF-8') ?>">Éditer</a></td>
                </tr>
                <tr>
                  <td>Définir allergies &amp; régimes</td>
                  <td><?= !empty($profilNutritionnel['allergies']) || !empty($profilNutritionnel['regimes']) ? 'Configuré' : 'Non configuré' ?></td>
                  <td><a class="fw-btn" href="<?= htmlspecialchars($routesModule2['front_allergies'] ?? '#', ENT_QUOTES, 'UTF-8') ?>">Modifier</a></td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </section>
    </div>

    <!-- COLONNE DROITE: Photo + Notifications -->
    <div>
      <!-- Photo de profil -->
      <?php if (!empty($photoProfil)) : ?>
      <section class="fw-card" aria-labelledby="fw-photo-title" style="padding: 0;">
        <h2 id="fw-photo-title" class="fw-card__head" style="padding: 0.5rem 0.75rem; font-size: 0.85rem;"><span aria-hidden="true">📸</span> Photo</h2>
        <div class="fw-card__body" style="text-align: center; padding: 0.5rem 0.75rem;">
          <img src="<?= htmlspecialchars($photoProfil, ENT_QUOTES, 'UTF-8') ?>" alt="Photo de profil de <?= htmlspecialchars($nomAffiche, ENT_QUOTES, 'UTF-8') ?>" style="width: 100%; max-width: 100px; height: auto; border-radius: 8px; object-fit: cover; display: block; margin: 0 auto;">
          <a class="fw-btn" style="margin-top: 0.4rem; font-size: 0.7rem; padding: 0.3rem 0.5rem;" href="<?= htmlspecialchars($routesModule2['front_profil_edit'] ?? '#', ENT_QUOTES, 'UTF-8') ?>">Modifier</a>
        </div>
      </section>
      <?php else : ?>
      <section class="fw-card" aria-labelledby="fw-photo-title" style="padding: 0;">
        <h2 id="fw-photo-title" class="fw-card__head" style="padding: 0.5rem 0.75rem; font-size: 0.85rem;"><span aria-hidden="true">📸</span> Photo</h2>
        <div class="fw-card__body" style="text-align: center; padding: 0.5rem 0.75rem;">
          <img src="<?= generateAvatarSVG($nomAffiche) ?>" alt="Avatar de <?= htmlspecialchars($nomAffiche, ENT_QUOTES, 'UTF-8') ?>" style="width: 100%; max-width: 100px; height: auto; border-radius: 8px; object-fit: cover; display: block; margin: 0 auto;">
          <a class="fw-btn" style="margin-top: 0.4rem; font-size: 0.7rem; padding: 0.3rem 0.5rem;" href="<?= htmlspecialchars($routesModule2['front_profil_edit'] ?? '#', ENT_QUOTES, 'UTF-8') ?>">Ajouter</a>
        </div>
      </section>
      <?php endif; ?>

      <!-- Notifications -->
      <?php if (!$isProfileComplete) : ?>
      <section class="fw-card" aria-labelledby="fw-notif-title" style="padding: 0; margin-top: 0.75rem;">
        <h2 id="fw-notif-title" class="fw-card__head fw-card__head--olive" style="padding: 0.5rem 0.75rem; font-size: 0.85rem;"><span aria-hidden="true">🔔</span> Alertes</h2>
        <div class="fw-card__body" style="padding: 0.5rem 0.75rem;">
          <p class="fw-alert-box fw-alert-box--orange" role="status" style="font-size: 0.7rem; margin: 0; padding: 0.4rem;">
            <span aria-hidden="true">⚠</span>
            <span>Profil incomplet (<?= $scoreCompletion ?>%)</span>
          </p>
        </div>
      </section>
      <?php endif; ?>
    </div>

  </div>
</main>

<?php require __DIR__ . '/layouts/footer.php'; ?>
