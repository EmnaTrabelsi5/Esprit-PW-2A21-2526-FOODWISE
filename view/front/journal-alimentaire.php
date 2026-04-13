<?php
declare(strict_types=1);

require_once __DIR__ . '/../../controller/JournalController.php';
$pdo = require __DIR__ . '/../../config/config.php';
$controller = new JournalController($pdo);
$userId = 1;
$today = date('Y-m-d');
$entries = $controller->listEntriesForUser($userId);
$totalCalories = $controller->getDailyCalories($userId, $today);
$streak = $controller->getStreak($userId);

$alertMessage = '';
$alertClass = 'success';
if (isset($_GET['created'])) {
    $alertMessage = $_GET['created'] === '1' ? 'Entrée ajoutée avec succès.' : 'Impossible d’ajouter l’entrée.';
    $alertClass = $_GET['created'] === '1' ? 'success' : 'error';
} elseif (isset($_GET['updated'])) {
    $alertMessage = $_GET['updated'] === '1' ? 'Entrée modifiée avec succès.' : 'Impossible de modifier l’entrée.';
    $alertClass = $_GET['updated'] === '1' ? 'success' : 'error';
} elseif (isset($_GET['deleted'])) {
    $alertMessage = $_GET['deleted'] === '1' ? 'Entrée supprimée avec succès.' : 'Impossible de supprimer l’entrée.';
    $alertClass = $_GET['deleted'] === '1' ? 'success' : 'error';
}

$pageTitle = 'Journal alimentaire — FoodWise';
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
        <a href="journal-alimentaire.php" class="nav-item active"><span class="nav-ico" aria-hidden="true">📋</span> Journal alimentaire</a>
        <a href="ajouter-entree.php" class="nav-item"><span class="nav-ico" aria-hidden="true">➕</span> Ajouter une entrée</a>
        <a href="resume-jour.php" class="nav-item"><span class="nav-ico" aria-hidden="true">📊</span> Résumé du jour</a>
        <p class="fw-sidebar-section">Compte</p>
        <a href="#" class="nav-item"><span class="nav-ico" aria-hidden="true">👤</span> Profil</a>
        <a href="#" class="nav-item"><span class="nav-ico" aria-hidden="true">⎋</span> Déconnexion</a>
      </nav>
    </aside>

    <div class="main-content">
      <header class="topbar">
        <label for="fw-nav-toggle" class="topbar-menu-btn" aria-label="Ouvrir le menu">☰</label>
        <form class="topbar-search" action="#" method="get" role="search">
          <input type="search" name="q" placeholder="Rechercher un aliment…" aria-label="Recherche" value="">
          <button type="button">OK</button>
        </form>
        <nav class="topbar-nav" aria-label="Raccourcis">
          <a href="resume-jour.php">Résumé</a>
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

        <h1 class="page-title">Journal alimentaire</h1>
        <p class="page-subtitle">Suivi des repas et collations — total calories aujourd’hui :</p>

        <div class="summary-cards">
          <article class="summary-card">
            <h3>Calories</h3>
            <p class="big"><?= htmlspecialchars((string) $totalCalories, ENT_QUOTES, 'UTF-8') ?></p>
            <p class="summary-card-hint mb-0">Aujourd’hui</p>
          </article>
          <article class="summary-card">
            <h3>Streak</h3>
            <p class="big"><?= htmlspecialchars((string) $streak, ENT_QUOTES, 'UTF-8') ?> j.</p>
            <p class="summary-card-hint mb-0">Jours consécutifs</p>
          </article>
          <article class="summary-card">
            <h3>Entrées</h3>
            <p class="big"><?= htmlspecialchars((string) count($entries), ENT_QUOTES, 'UTF-8') ?></p>
            <p class="summary-card-hint mb-0">Enregistrements</p>
          </article>
        </div>

        <div class="flex-between" style="margin-bottom: 22px;">
          <p class="text-muted mb-0">Liste des entrées enregistrées pour aujourd’hui et les jours récents.</p>
          <div class="flex-between mb-0" style="gap:8px">
            <a href="ajouter-entree.php" class="btn btn-primary">+ Ajouter une entrée</a>
            <a href="resume-jour.php" class="btn btn-secondary">Voir le résumé</a>
          </div>
        </div>

        <section class="card mb-0">
          <div class="table-wrap">
            <table class="fw-table">
              <thead>
                <tr>
                  <th>Date</th>
                  <th>Repas</th>
                  <th>Aliment</th>
                  <th class="num">Quantité</th>
                  <th class="num">kcal</th>
                  <th></th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($entries)): ?>
                  <tr>
                    <td colspan="6" class="text-muted">Aucune entrée enregistrée pour le moment.</td>
                  </tr>
                <?php else: ?>
                  <?php foreach ($entries as $entry): ?>
                    <tr>
                      <td><?= htmlspecialchars($entry['date'], ENT_QUOTES, 'UTF-8') ?></td>
                      <td><span class="badge badge-brun"><?= htmlspecialchars($entry['meal_type'], ENT_QUOTES, 'UTF-8') ?></span></td>
                      <td><?= htmlspecialchars($entry['food'], ENT_QUOTES, 'UTF-8') ?></td>
                      <td class="num"><?= htmlspecialchars($entry['quantity'], ENT_QUOTES, 'UTF-8') ?></td>
                      <td class="num"><?= htmlspecialchars((string) $entry['calories'], ENT_QUOTES, 'UTF-8') ?></td>
                      <td class="actions-cell">
                        <a href="modifier-entree.php?id=<?= htmlspecialchars((string) $entry['id'], ENT_QUOTES, 'UTF-8') ?>" class="btn btn-sm btn-secondary">Modifier</a>
                        <a href="../../controller/supprimer.php?id=<?= htmlspecialchars((string) $entry['id'], ENT_QUOTES, 'UTF-8') ?>&source=front" class="btn btn-sm btn-danger confirm-delete">Suppr.</a>
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
