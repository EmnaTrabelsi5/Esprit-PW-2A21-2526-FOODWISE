<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/auth.php';
requireClient();

require_once __DIR__ . '/../../controller/SuiviSanteController.php';
$pdo = require __DIR__ . '/../../config/config.php';
$controller = new SuiviSanteController($pdo);
$userId = 1;
$today = date('Y-m-d');

$sort = $_GET['sort'] ?? 'date_desc';
$sortDirection = $sort === 'date_asc' ? 'ASC' : 'DESC';
$suivis = $controller->getSuivisByUser($userId, $sortDirection);
$report = $controller->getDailyHealthReport($userId, $today, null);

$alertMessage = '';
$alertClass = 'success';
if (isset($_GET['created'])) {
    $alertMessage = $_GET['created'] === '1' ? 'Activité ajoutée avec succès.' : 'Impossible d\'ajouter l\'activité.';
    $alertClass = $_GET['created'] === '1' ? 'success' : 'error';
} elseif (isset($_GET['updated'])) {
    $alertMessage = $_GET['updated'] === '1' ? 'Activité modifiée avec succès.' : 'Impossible de modifier l\'activité.';
    $alertClass = $_GET['updated'] === '1' ? 'success' : 'error';
} elseif (isset($_GET['deleted'])) {
    $alertMessage = $_GET['deleted'] === '1' ? 'Activité supprimée avec succès.' : 'Impossible de supprimer l\'activité.';
    $alertClass = $_GET['deleted'] === '1' ? 'success' : 'error';
}

$pageTitle = 'Suivi Santé — FoodWise';
require __DIR__ . '/../template/header.php';
?>
<body class="theme-front">
  <input type="checkbox" id="fw-nav-toggle" class="fw-nav-toggle" hidden>

  <div class="fw-layout">
    <label for="fw-nav-toggle" class="sidebar-backdrop" aria-label="Fermer le menu"></label>

    <aside class="sidebar" aria-label="Navigation principale">
      <div class="sidebar-logo">
        <img src="../../public/images/foodwise-logo.png" alt="FoodWise">
      </div>
      <nav class="sidebar-nav">
        <p class="fw-sidebar-section">FoodLog</p>
        <a href="journal-alimentaire.php" class="nav-item"><span class="nav-ico" aria-hidden="true">📋</span> Journal alimentaire</a>
        <a href="ajouter-entree.php" class="nav-item"><span class="nav-ico" aria-hidden="true">➕</span> Ajouter une entrée</a>
        <a href="resume.php" class="nav-item"><span class="nav-ico" aria-hidden="true">📊</span> Résumé du jour</a>
        <p class="fw-sidebar-section">Santé</p>
        <a href="suivi-sante-unifie.php" class="nav-item active"><span class="nav-ico" aria-hidden="true">🏃</span> Suivi Santé</a>
        <a href="ajouter-suivi.php" class="nav-item"><span class="nav-ico" aria-hidden="true">➕</span> Ajouter activité</a>
        <p class="fw-sidebar-section">Compte</p>
        <a href="#" class="nav-item"><span class="nav-ico" aria-hidden="true">👤</span> Profil</a>
        <a href="#" class="nav-item"><span class="nav-ico" aria-hidden="true">⎋</span> Déconnexion</a>
      </nav>
    </aside>

    <div class="main-content">
      <header class="topbar">
        <label for="fw-nav-toggle" class="topbar-menu-btn" aria-label="Ouvrir le menu">☰</label>
        <form class="topbar-search" action="#" method="get" role="search">
          <input type="search" name="q" placeholder="Rechercher une activité…" aria-label="Recherche" value="">
          <button type="button">OK</button>
        </form>
        <nav class="topbar-nav" aria-label="Raccourcis">
          <a href="resume.php">Résumé</a>
          <a href="suivi-sante-unifie.php">Santé</a>
        </nav>
        <div class="topbar-user">
          <span class="user-avatar-initials" aria-hidden="true">MD</span>
          <span>Marie Dupont</span>
        </div>
      </header>

      <main class="page-body">
        <?php if ($alertMessage): ?>
          <div class="notif-panel <?= htmlspecialchars($alertClass, ENT_QUOTES, 'UTF-8') ?>" style="margin-bottom:18px;">
            <?= htmlspecialchars($alertMessage, ENT_QUOTES, 'UTF-8') ?>
          </div>
        <?php endif; ?>

        <h1 class="page-title">Suivi Santé</h1>
        <p class="page-subtitle">Suivi des activités et de la santé — bilan du jour :</p>

        <div class="summary-cards">
          <article class="summary-card">
            <h3>Calories Brûlées</h3>
            <p class="big"><?= htmlspecialchars((string)($report['total_calories_burned'] ?? 0), ENT_QUOTES, 'UTF-8') ?></p>
            <p class="summary-card-hint mb-0"><?= ($report['suivis_count'] ?? 0) ?> activité<?= ($report['suivis_count'] ?? 0) > 1 ? 's' : '' ?></p>
          </article>
          <article class="summary-card">
            <h3>Eau Consommée</h3>
            <p class="big"><?= htmlspecialchars(number_format(($report['total_water'] ?? 0) / 1000, 2, ',', ' '), ENT_QUOTES, 'UTF-8') ?>L</p>
            <p class="summary-card-hint mb-0"><?= (($report['total_water'] ?? 0) >= 2000) ? '✓ Objectif atteint' : number_format((($report['total_water'] ?? 0) / 2000) * 100, 0) . '% objectif' ?></p>
          </article>
          <article class="summary-card">
            <h3>Bilan Calorique</h3>
            <p class="big"><?= ($report['balance'] ?? 0) >= 0 ? '+' : '' ?><?= htmlspecialchars((string)($report['balance'] ?? 0), ENT_QUOTES, 'UTF-8') ?></p>
            <p class="summary-card-hint mb-0"><?= ($report['balance'] ?? 0) > 0 ? 'Surplus' : 'Déficit' ?></p>
          </article>
        </div>

        <div class="flex-between" style="margin-bottom: 18px; gap: 12px; align-items: center;">
          <div class="sort-controls">
            <span class="text-muted">Trier par date :</span>
            <a href="suivi-sante-unifie.php?sort=<?= $sortDirection === 'ASC' ? 'date_desc' : 'date_asc' ?>" class="btn btn-secondary btn-sm">
              <?= $sortDirection === 'ASC' ? 'Date décroissante' : 'Date croissante' ?>
            </a>
          </div>
        </div>

        <div class="flex-between" style="margin-bottom: 22px;">
          <p class="text-muted mb-0">Liste des activités enregistrées pour aujourd'hui et les jours récents.</p>
          <div class="flex-between mb-0" style="gap:8px">
            <a href="ajouter-suivi.php" class="btn btn-primary">+ Ajouter une activité</a>
          </div>
        </div>

        <section class="card mb-0">
          <div class="table-wrap">
            <table class="fw-table">
              <thead>
                <tr>
                  <th>Date</th>
                  <th>Type d'activité</th>
                  <th class="num">Durée (min)</th>
                  <th>Intensité</th>
                  <th class="num">Calories brûlées</th>
                  <th></th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($suivis)): ?>
                  <tr>
                    <td colspan="5" class="text-muted">Aucune activité enregistrée pour le moment.</td>
                  </tr>
                <?php else: ?>
                  <?php foreach ($suivis as $suivi): ?>
                    <tr>
                      <td><?= htmlspecialchars($suivi['date'], ENT_QUOTES, 'UTF-8') ?></td>
                      <td><?= htmlspecialchars($suivi['type_activite'], ENT_QUOTES, 'UTF-8') ?></td>
                      <td class="num"><?= htmlspecialchars((string)$suivi['duree'], ENT_QUOTES, 'UTF-8') ?></td>
                      <td><span class="badge badge-brun"><?= htmlspecialchars($suivi['intensite'], ENT_QUOTES, 'UTF-8') ?></span></td>
                      <td class="num"><?= htmlspecialchars((string)$suivi['calories_brulees'], ENT_QUOTES, 'UTF-8') ?></td>
                      <td class="actions-cell">
                        <a href="modifier-suivi.php?id=<?= htmlspecialchars((string)$suivi['id'], ENT_QUOTES, 'UTF-8') ?>" class="btn btn-sm btn-secondary">Modifier</a>
                        <a href="../../controller/supprimer.php?id=<?= htmlspecialchars((string)$suivi['id'], ENT_QUOTES, 'UTF-8') ?>&source=suivi&origin=front" class="btn btn-sm btn-danger confirm-delete">Suppr.</a>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </section>
      </main>
    </div>
  </div>
</body>
</html>
