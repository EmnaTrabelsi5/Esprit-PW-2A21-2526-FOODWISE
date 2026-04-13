<?php
declare(strict_types=1);

require_once __DIR__ . '/../../controller/JournalController.php';
$pdo = require __DIR__ . '/../../config/config.php';
$controller = new JournalController($pdo);
$entries = $controller->listEntriesForAdmin();

$alertMessage = '';
$alertClass = 'success';
if (isset($_GET['deleted'])) {
    $alertMessage = $_GET['deleted'] === '1' ? 'Entrée supprimée avec succès.' : 'Impossible de supprimer l’entrée.';
    $alertClass = $_GET['deleted'] === '1' ? 'success' : 'error';
}

$pageTitle = 'Entrées utilisateurs — FoodWise Admin';
require __DIR__ . '/../template/header.php';
?>
<body class="theme-admin">
  <input type="checkbox" id="fw-nav-toggle" class="fw-nav-toggle" hidden>

  <div class="fw-layout">
    <label for="fw-nav-toggle" class="sidebar-backdrop" aria-label="Fermer le menu"></label>

    <aside class="sidebar" aria-label="Navigation administration">
      <div class="sidebar-logo">
        <img src="../../public/images/foodwise-logo.png" alt="FoodWise">
      </div>
      <nav class="sidebar-nav">
        <p class="fw-sidebar-section">FoodLog</p>
        <a href="dashboard-admin.php" class="nav-item"><span class="nav-ico" aria-hidden="true">▣</span> Dashboard</a>
        <a href="liste-entries.php" class="nav-item active"><span class="nav-ico" aria-hidden="true">📑</span> Entrées utilisateurs</a>
        <a href="statistiques-globales.php" class="nav-item"><span class="nav-ico" aria-hidden="true">📈</span> Statistiques</a>
        <p class="fw-sidebar-section">Admin</p>
        <a href="#" class="nav-item"><span class="nav-ico" aria-hidden="true">👥</span> Utilisateurs</a>
        <a href="#" class="nav-item"><span class="nav-ico" aria-hidden="true">⚙</span> Paramètres</a>
      </nav>
    </aside>

    <div class="main-content">
      <header class="topbar">
        <label for="fw-nav-toggle" class="topbar-menu-btn" aria-label="Ouvrir le menu">☰</label>
        <form class="topbar-search" action="#" method="get" role="search">
          <input type="search" placeholder="Rechercher…" aria-label="Recherche" value="">
          <button type="button">OK</button>
        </form>
        <nav class="topbar-nav" aria-label="Raccourcis">
          <a href="dashboard-admin.php">Dashboard</a>
        </nav>
        <div class="topbar-user">
          <span class="user-avatar-initials" aria-hidden="true">JM</span>
          <span>Admin</span>
        </div>
      </header>

      <main class="page-body">
        <?php if ($alertMessage): ?>
          <div class="notif-panel <?= htmlspecialchars($alertClass, ENT_QUOTES, 'UTF-8') ?>" style="margin-bottom:18px;">
            <?= htmlspecialchars($alertMessage, ENT_QUOTES, 'UTF-8') ?>
          </div>
        <?php endif; ?>

        <h1 class="page-title">Liste des entrées</h1>
        <p class="page-subtitle">Liste dynamique des entrées avec suppression et détails.</p>

        <section class="card">
          <div class="card-header">
            <h2 class="card-title">Toutes les entrées</h2>
            <span class="card-meta">Mise à jour en temps réel depuis la base de données.</span>
          </div>

          <div class="filter-bar" style="margin-top:0;margin-bottom:18px">
            <input type="text" placeholder="Utilisateur (ID)" aria-label="Utilisateur">
            <input type="date" aria-label="Date">
            <button type="button" class="btn btn-primary btn-sm">Filtrer</button>
            <button type="button" class="btn btn-outline btn-sm">Réinitialiser</button>
          </div>

          <div class="table-wrap">
            <table class="fw-table">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Utilisateur</th>
                  <th>Date</th>
                  <th>Repas</th>
                  <th>Aliment</th>
                  <th class="num">kcal</th>
                  <th></th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($entries)): ?>
                  <tr>
                    <td colspan="7" class="text-muted">Aucune entrée enregistrée.</td>
                  </tr>
                <?php else: ?>
                  <?php foreach ($entries as $entry): ?>
                    <tr>
                      <td>#<?= htmlspecialchars((string) $entry['id'], ENT_QUOTES, 'UTF-8') ?></td>
                      <td><?= htmlspecialchars((string) $entry['user_id'], ENT_QUOTES, 'UTF-8') ?></td>
                      <td><?= htmlspecialchars($entry['date'], ENT_QUOTES, 'UTF-8') ?></td>
                      <td><span class="badge badge-brun"><?= htmlspecialchars($entry['meal_type'], ENT_QUOTES, 'UTF-8') ?></span></td>
                      <td><?= htmlspecialchars($entry['food'], ENT_QUOTES, 'UTF-8') ?></td>
                      <td class="num"><?= htmlspecialchars((string) $entry['calories'], ENT_QUOTES, 'UTF-8') ?></td>
                      <td class="actions-cell">
                        <a href="../../view/front/modifier-entree.php?id=<?= htmlspecialchars((string) $entry['id'], ENT_QUOTES, 'UTF-8') ?>" class="btn btn-sm btn-secondary">Modifier</a>
                        <a href="../../controller/supprimer.php?id=<?= htmlspecialchars((string) $entry['id'], ENT_QUOTES, 'UTF-8') ?>&source=back" class="btn btn-sm btn-danger confirm-delete">Suppr.</a>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>

          <p class="text-muted mt-1 mb-0">Pagination à intégrer côté PHP / SQL si nécessaire.</p>
        </section>
      </main>
    </div>
  </div>
</body>
</html>
