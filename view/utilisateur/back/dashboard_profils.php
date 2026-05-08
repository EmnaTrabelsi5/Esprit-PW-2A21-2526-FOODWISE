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

$pageTitle = $pageTitle ?? 'FoodWise – Administration – Gestion des profils';
$activeNav = 'suivi_nutritionnel';

require dirname(__DIR__) . '/routes_defaults.php';

require __DIR__ . '/layouts/header.php';
?>

<form class="fw-search" role="search" method="get">
  <input type="hidden" name="route" value="module2.back.dashboard.profils">
  <div class="fw-search__inner">
    <span aria-hidden="true">🔍</span>
    <label class="fw-skip-link" for="fw-admin-search">Recherche</label>
    <input type="search" id="fw-admin-search" name="q" value="<?= htmlspecialchars($searchTerm ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="Rechercher des utilisateurs, régimes, allergies, objectifs..." autocomplete="off">
    <button type="submit" class="fw-btn">Chercher</button>
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

    <section class="fw-card" aria-labelledby="fw-crud-admin-title" style="grid-column: 2 / -1;">
      <h2 id="fw-crud-admin-title" class="fw-crud-title"><span aria-hidden="true">⚙</span> Configuration détaillée du profil nutritionnel (CRUD)</h2>
      <div class="fw-card__body" style="padding-top:0">
        <div style="margin-bottom:1rem">
          <a class="fw-btn" href="<?= htmlspecialchars($routesModule2['back_profil_form'] ?? '', ENT_QUOTES, 'UTF-8') ?>">Créer un nouveau profil</a>
        </div>
        <div class="fw-table-wrap">
          <table class="fw-table fw-table--compact">
            <thead>
              <tr>
                <th scope="col">Photo</th>
                <th scope="col">Utilisateur</th>
                <th scope="col">Courriel</th>
                <th scope="col">Objectif</th>
                <th scope="col">Statut</th>
                <th scope="col">Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($lignesProfils as $ligne) :
                  $id = (int) ($ligne['utilisateur_id'] ?? 0);
                  $score = (int) ($ligne['score_completion'] ?? 0);
                  $complet = $score === 100;
                  ?>
                <tr>
                  <td>
                    <?php 
                      $photoProfil = $ligne['photo_profil'] ?? null;
                      $nomAffiche = trim(($ligne['prenom'] ?? '') . ' ' . ($ligne['nom'] ?? ''));
                      if ($photoProfil && file_exists(__DIR__ . '/../../' . $photoProfil)): ?>
                        <img src="<?= htmlspecialchars($photoProfil, ENT_QUOTES, 'UTF-8') ?>" alt="Photo de <?= htmlspecialchars($nomAffiche, ENT_QUOTES, 'UTF-8') ?>" style="width: 32px; height: 32px; border-radius: 50%; object-fit: cover;">
                      <?php else: ?>
                        <img src="<?= generateAvatarSVG($nomAffiche) ?>" alt="Avatar de <?= htmlspecialchars($nomAffiche, ENT_QUOTES, 'UTF-8') ?>" style="width: 32px; height: 32px; border-radius: 50%;">
                      <?php endif; ?>
                  </td>
                  <td><?= htmlspecialchars((string) ($ligne['prenom'] ?? '') . ' ' . ($ligne['nom'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                  <td><?= htmlspecialchars((string) ($ligne['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                  <td><?= htmlspecialchars((string) ($ligne['objectif'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
                  <td>
                    <?php
                      $status = $ligne['status'] ?? 'active';
                      $suspendedUntil = $ligne['suspended_until'] ?? null;
                      
                      if ($status === 'active') {
                        echo '<span style="color: green; font-weight: bold;">✓ Actif</span>';
                      } elseif ($status === 'suspended') {
                        $daysLeft = null;
                        if ($suspendedUntil) {
                          $daysLeft = ceil((strtotime($suspendedUntil) - time()) / (24 * 60 * 60));
                        }
                        echo '<span style="color: orange; font-weight: bold;">⏸ Suspendu' . ($daysLeft !== null && $daysLeft > 0 ? ' (' . $daysLeft . 'j)' : '') . '</span>';
                      } elseif ($status === 'banned') {
                        echo '<span style="color: red; font-weight: bold;">🚫 Banni</span>';
                      }
                    ?>
                  </td>
                  <td>
                    <div style="display: flex; flex-direction: column; gap: 0.3rem;">
                      <a class="fw-btn" href="<?= htmlspecialchars($routesModule2['back_profil_form'] ?? '', ENT_QUOTES, 'UTF-8') ?>&id=<?= $id ?>">Edit</a>
                      
                      <?php if ($status === 'active'): ?>
                        <form method="post" style="display: flex; gap: 0.2rem;" onsubmit="return confirm('Suspendre cet utilisateur ?');">
                          <input type="hidden" name="user_id" value="<?= $id ?>">
                          <input type="number" name="days" value="7" min="1" max="365" style="width: 50px; padding: 4px 6px; border: 1px solid #ccc; border-radius: 3px; font-size: 11px;">
                          <button type="submit" formaction="<?= htmlspecialchars('index.php?route=module2.back.suspend.user', ENT_QUOTES, 'UTF-8') ?>" class="fw-btn fw-btn--ghost">Suspendre</button>
                        </form>
                        <form method="post" onsubmit="return confirm('Bannir cet utilisateur ?');">
                          <input type="hidden" name="user_id" value="<?= $id ?>">
                          <input type="hidden" name="reason" value="">
                          <button type="submit" formaction="<?= htmlspecialchars('index.php?route=module2.back.ban.user', ENT_QUOTES, 'UTF-8') ?>" class="fw-btn fw-btn--ghost" style="color: red;">Bannir</button>
                        </form>
                      <?php elseif ($status === 'suspended'): ?>
                        <form method="post">
                          <input type="hidden" name="user_id" value="<?= $id ?>">
                          <button type="submit" formaction="<?= htmlspecialchars('index.php?route=module2.back.lift.suspension', ENT_QUOTES, 'UTF-8') ?>" class="fw-btn fw-btn--ghost" style="color: green;">Lever suspen.</button>
                        </form>
                      <?php endif; ?>
                      
                      <?php if ($status === 'banned'): ?>
                        <form method="post">
                          <input type="hidden" name="user_id" value="<?= $id ?>">
                          <button type="submit" formaction="<?= htmlspecialchars('index.php?route=module2.back.unban.user', ENT_QUOTES, 'UTF-8') ?>" class="fw-btn fw-btn--ghost" style="color: green;">Débannir</button>
                        </form>
                      <?php endif; ?>
                      
                      <a class="fw-btn fw-btn--ghost" href="<?= htmlspecialchars($routesModule2['back_dashboard_profils'] ?? '', ENT_QUOTES, 'UTF-8') ?>&action=delete&id=<?= $id ?>">Supprimer</a>
                      <a class="fw-btn fw-btn--ghost" href="<?= htmlspecialchars('index.php?route=module2.back.modification.history&user_id=' . $id, ENT_QUOTES, 'UTF-8') ?>">Historique</a>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </section>


  </div>
</main>

<?php require __DIR__ . '/layouts/footer.php'; ?>
