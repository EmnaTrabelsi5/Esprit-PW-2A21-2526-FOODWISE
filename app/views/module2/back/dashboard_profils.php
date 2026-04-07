<?php
/**
 * Back-office — Gestion des profils nutritionnels (liste utilisateurs + métriques)
 *
 * Variables injectées par le contrôleur (jointure Utilisateur + ProfilNutritionnel) :
 * @var array $stats
 *   - float|string $scoreCorrespondanceGlobal  ex. 7.8
 *   - int $utilisateursProfilComplet
 *   - int $utilisateursTotal
 *   - int $totalRecettesMatchees
 * @var array<int, array<string, mixed>> $lignesProfils  lignes tableau
 *   Chaque ligne : id, nom (ou pseudo), email?, regime, scoreCorrespondance, profilComplet (bool), dateMaj?
 */
declare(strict_types=1);

$stats = $stats ?? [
    'scoreCorrespondanceGlobal' => 7.8,
    'utilisateursProfilComplet' => 1850,
    'utilisateursTotal' => 2450,
    'totalRecettesMatchees' => 6120,
];

$lignesProfils = $lignesProfils ?? [
    [
        'id' => 1,
        'nom' => 'Marie Dupont',
        'regime' => 'Méditerranéen',
        'scoreCorrespondance' => 8.2,
        'profilComplet' => true,
        'dateMaj' => '2026-01-15',
    ],
    [
        'id' => 2,
        'nom' => 'Jean Martin',
        'regime' => 'Perte de poids',
        'scoreCorrespondance' => 6.1,
        'profilComplet' => false,
        'dateMaj' => '2026-01-01',
    ],
];

$pageTitle = $pageTitle ?? 'Module 2 – NutriProfile – Profil nutritionnel';
$activeNav = 'suivi_nutritionnel';

require dirname(__DIR__) . '/routes_defaults.php';

require __DIR__ . '/layouts/header.php';
?>

<form class="fw-search" role="search" action="<?= htmlspecialchars($routesModule2['back_dashboard_profils'] ?? '', ENT_QUOTES, 'UTF-8') ?>" method="get">
  <div class="fw-search__inner">
    <span aria-hidden="true">🔍</span>
    <label class="fw-skip-link" for="fw-admin-search">Recherche</label>
    <input type="search" id="fw-admin-search" name="q" placeholder="Rechercher des utilisateurs, régimes, ou allergies..." autocomplete="off">
  </div>
</form>

<main id="fw-main-content" class="fw-content">
  <div class="fw-grid fw-grid--admin">
    <div class="fw-grid">
      <section class="fw-card" aria-labelledby="fw-bilan-title">
        <h2 id="fw-bilan-title" class="fw-card__head"><span aria-hidden="true">📅</span> Bilan Nutri-Score</h2>
        <div class="fw-card__body">
          <ul class="fw-stat-list">
            <li>Score de Correspondance Global: <strong><?= htmlspecialchars((string) $stats['scoreCorrespondanceGlobal'], ENT_QUOTES, 'UTF-8') ?> / 10</strong></li>
            <li>Utilisateurs avec Profil Complet: <strong><?= (int) $stats['utilisateursProfilComplet'] ?> / <?= (int) $stats['utilisateursTotal'] ?></strong></li>
            <li>Total Recettes Matchées: <strong><?= (int) $stats['totalRecettesMatchees'] ?></strong></li>
          </ul>
        </div>
      </section>

      <section class="fw-card" aria-labelledby="fw-alertes-title">
        <h2 id="fw-alertes-title" class="fw-card__head"><span aria-hidden="true">⚠</span> Alertes et incohérences</h2>
        <div class="fw-card__body">
          <p class="fw-alert-box fw-alert-box--orange" role="status">
            <span aria-hidden="true">⚠</span>
            <span>Alerte Diète : vérifiez les profils avec objectifs de poids non renseignés.</span>
          </p>
          <p class="fw-alert-box fw-alert-box--green" role="status">
            <span aria-hidden="true">✓</span>
            <span>Suggestion : invitez les utilisateurs à compléter les intolérances.</span>
          </p>
        </div>
      </section>
    </div>

    <section class="fw-card" aria-labelledby="fw-crud-admin-title">
      <h2 id="fw-crud-admin-title" class="fw-crud-title"><span aria-hidden="true">⚙</span> Configuration détaillée du profil nutritionnel (CRUD)</h2>
      <div class="fw-card__body" style="padding-top:0">
        <div class="fw-table-wrap">
          <table class="fw-table">
            <thead>
              <tr>
                <th scope="col">Utilisateur</th>
                <th scope="col">Type de régime</th>
                <th scope="col">Score correspondance</th>
                <th scope="col">Statut profil</th>
                <th scope="col">Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($lignesProfils as $ligne) :
                  $complet = !empty($ligne['profilComplet']);
                  $id = (int) ($ligne['id'] ?? 0);
                  $qs = rawurlencode((string) $id);
                  ?>
                <tr>
                  <td><?= htmlspecialchars((string) ($ligne['nom'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                  <td><?= htmlspecialchars((string) ($ligne['regime'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
                  <td><?= htmlspecialchars((string) ($ligne['scoreCorrespondance'] ?? '—'), ENT_QUOTES, 'UTF-8') ?> / 10</td>
                  <td>
                    <?php if ($complet) : ?>
                      <span class="fw-badge fw-badge--ok">Complet</span>
                    <?php else : ?>
                      <span class="fw-badge fw-badge--warn">Profil incomplet</span>
                    <?php endif; ?>
                  </td>
                  <td>
                    <a class="fw-btn" href="<?= htmlspecialchars($routesModule2['back_dashboard_profils'] ?? '', ENT_QUOTES, 'UTF-8') ?>&action=edit&id=<?= $id ?>">Éditer</a>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </section>

    <div class="fw-grid">
      <section class="fw-card" aria-labelledby="fw-notif-metier-title">
        <h2 id="fw-notif-metier-title" class="fw-card__head fw-card__head--olive"><span aria-hidden="true">📣</span> Notifications métier</h2>
        <div class="fw-card__body">
          <p class="fw-alert-box fw-alert-box--red" role="alert">
            <span aria-hidden="true">⚠</span>
            <span>Alerte incohérence : certains objectifs de poids ne correspondent pas aux apports enregistrés.</span>
          </p>
          <p class="fw-alert-box fw-alert-box--green" role="status">
            <span aria-hidden="true">✓</span>
            <span>Nouveau calcul métier : score de correspondance recalculé. <a href="#">Voir détails</a></span>
          </p>
        </div>
      </section>
    </div>
  </div>
</main>

<?php require __DIR__ . '/layouts/footer.php'; ?>
