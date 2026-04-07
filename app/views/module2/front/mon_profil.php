<?php
/**
 * Front-office — Tableau de bord « Mon profil » (NutriProfile)
 *
 * Entités (jointure préparée pour le contrôleur) :
 * @var array<string, mixed> $utilisateur       ex. id, nom, prenom, email
 * @var array<string, mixed>|null $profilNutritionnel  jointure ProfilNutritionnel
 * @var float|int $scoreCorrespondanceObjectifsRecettes  0–100 pour la jauge métier
 */
declare(strict_types=1);

$utilisateur = $utilisateur ?? [
    'nom' => 'Nom Utilisateur',
    'prenom' => '',
];

$profilNutritionnel = $profilNutritionnel ?? [
    'donneesBase' => 'Données de base associées',
    'allergies' => [['libelle' => 'Arachides', 'actif' => true]],
    'regimes' => [['libelle' => 'Sans Gluten', 'actif' => true]],
    'intolerances' => [['libelle' => 'Lactose', 'niveau' => 'Moyenne']],
    'scoreCompletion' => 85,
];

$scoreCorrespondanceObjectifsRecettes = $scoreCorrespondanceObjectifsRecettes ?? 72;

$nomAffiche = trim(($utilisateur['prenom'] ?? '') . ' ' . ($utilisateur['nom'] ?? ''));
if ($nomAffiche === '') {
    $nomAffiche = 'Utilisateur';
}

$pageTitle = $pageTitle ?? 'Module 2 – NutriProfile – Profil nutritionnel';
$activeNav = 'mon_profil';

require dirname(__DIR__) . '/routes_defaults.php';

require __DIR__ . '/layouts/header.php';
?>

<form class="fw-search" role="search" action="<?= htmlspecialchars($routesModule2['front_mon_profil'] ?? '', ENT_QUOTES, 'UTF-8') ?>" method="get">
  <div class="fw-search__inner">
    <span aria-hidden="true">🔍</span>
    <label class="fw-skip-link" for="fw-client-search">Recherche</label>
    <input type="search" id="fw-client-search" name="q" placeholder="Rechercher des profils, allergies, objectifs..." autocomplete="off">
  </div>
</form>

<main id="fw-main-content" class="fw-content">
  <div class="fw-grid fw-grid--3">
    <div class="fw-grid">
      <section class="fw-card" aria-labelledby="fw-identite-title">
        <h2 id="fw-identite-title" class="fw-card__head"><span aria-hidden="true">👤</span> Identité de l'utilisateur</h2>
        <div class="fw-card__body">
          <dl>
            <dt>Nom</dt>
            <dd><?= htmlspecialchars($nomAffiche, ENT_QUOTES, 'UTF-8') ?></dd>
            <dt>Score de complétion</dt>
            <dd><?= (int) ($profilNutritionnel['scoreCompletion'] ?? 0) ?> %</dd>
          </dl>
        </div>
      </section>

      <section class="fw-card" aria-labelledby="fw-profil-nut-title">
        <h2 id="fw-profil-nut-title" class="fw-card__head"><span aria-hidden="true">⇄</span> Profil nutritionnel (jointure)</h2>
        <div class="fw-card__body">
          <p style="margin:0"><?= htmlspecialchars((string) ($profilNutritionnel['donneesBase'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></p>
        </div>
      </section>

      <section class="fw-card" aria-labelledby="fw-restr-title">
        <h2 id="fw-restr-title" class="fw-card__head"><span aria-hidden="true">🚫</span> Restrictions et régimes</h2>
        <div class="fw-card__body">
          <dl>
            <dt>Allergies</dt>
            <dd>
              <?php foreach ($profilNutritionnel['allergies'] ?? [] as $a) : ?>
                <?= htmlspecialchars((string) ($a['libelle'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                <?php if (!empty($a['actif'])) : ?><span class="fw-badge fw-badge--muted">Active</span><?php endif; ?>
                <br>
              <?php endforeach; ?>
            </dd>
            <dt>Régimes</dt>
            <dd>
              <?php foreach ($profilNutritionnel['regimes'] ?? [] as $g) : ?>
                <?= htmlspecialchars((string) ($g['libelle'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                <?php if (!empty($g['actif'])) : ?><span class="fw-badge fw-badge--muted">Activé</span><?php endif; ?>
                <br>
              <?php endforeach; ?>
            </dd>
            <dt>Intolérances</dt>
            <dd>
              <?php foreach ($profilNutritionnel['intolerances'] ?? [] as $i) : ?>
                <?= htmlspecialchars((string) ($i['libelle'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                (<?= htmlspecialchars((string) ($i['niveau'] ?? '—'), ENT_QUOTES, 'UTF-8') ?>)
                <br>
              <?php endforeach; ?>
            </dd>
          </dl>
        </div>
      </section>
    </div>

    <section class="fw-card" aria-labelledby="fw-crud-title">
      <h2 id="fw-crud-title" class="fw-crud-title"><span aria-hidden="true">▦</span> Configuration du profil — fonctionnalités CRUD</h2>
      <div class="fw-card__body" style="padding-top:0">
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
                <td>Inscription / connexion</td>
                <td>Compte créé</td>
                <td><a class="fw-btn" href="<?= htmlspecialchars($routesModule2['front_connexion'] ?? '#', ENT_QUOTES, 'UTF-8') ?>">Gérer</a></td>
              </tr>
              <tr>
                <td>Créer / modifier son profil</td>
                <td>Données de base</td>
                <td><a class="fw-btn" href="<?= htmlspecialchars($routesModule2['front_profil_edit'] ?? '#', ENT_QUOTES, 'UTF-8') ?>">Éditer</a></td>
              </tr>
              <tr>
                <td>Définir allergies &amp; régimes</td>
                <td>2 activés</td>
                <td><a class="fw-btn" href="<?= htmlspecialchars($routesModule2['front_allergies'] ?? '#', ENT_QUOTES, 'UTF-8') ?>">Modifier</a></td>
              </tr>
              <tr>
                <td>Consulter son profil</td>
                <td>Vue active</td>
                <td><a class="fw-btn" href="<?= htmlspecialchars($routesModule2['front_mon_profil'] ?? '#', ENT_QUOTES, 'UTF-8') ?>">Actualiser</a></td>
              </tr>
            </tbody>
          </table>
        </div>

        <h3 class="fw-section-title" id="fw-score-metier-title"><span aria-hidden="true">📈</span> Score de correspondance (objectifs ↔ recettes)</h3>
        <?php
        $pct = max(0, min(100, (float) $scoreCorrespondanceObjectifsRecettes));
        ?>
        <div class="fw-gauge" role="img" aria-labelledby="fw-score-metier-title" aria-valuenow="<?= (int) round($pct) ?>" aria-valuemin="0" aria-valuemax="100">
          <div class="fw-gauge__label">
            <span>Correspondance</span>
            <span><?= htmlspecialchars(number_format($pct, 0, ',', ' '), ENT_QUOTES, 'UTF-8') ?> %</span>
          </div>
          <div class="fw-gauge__track">
            <div class="fw-gauge__fill" style="width: <?= $pct ?>%"></div>
          </div>
        </div>
      </div>
    </section>

    <div class="fw-grid">
      <section class="fw-card" aria-labelledby="fw-notif-title">
        <h2 id="fw-notif-title" class="fw-card__head fw-card__head--olive"><span aria-hidden="true">🔔</span> Notifications et insights</h2>
        <div class="fw-card__body">
          <p class="fw-alert-box fw-alert-box--orange" role="status">
            <span aria-hidden="true">⚠</span>
            <span>Alerte données : profil incomplet, complétez vos régimes pour un meilleur score de correspondance.</span>
          </p>
          <p class="fw-alert-box fw-alert-box--green" role="status">
            <span aria-hidden="true">✓</span>
            <span>Nouveau score métier disponible : correspondance pour 5 recettes récentes. <a href="#">Voir détails</a></span>
          </p>
        </div>
      </section>
    </div>
  </div>
</main>

<?php require __DIR__ . '/layouts/footer.php'; ?>
