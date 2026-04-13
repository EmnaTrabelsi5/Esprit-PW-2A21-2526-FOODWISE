<?php
declare(strict_types=1);

require_once __DIR__ . '/../../controller/JournalController.php';
$pdo = require __DIR__ . '/../../config/config.php';
$controller = new JournalController($pdo);
$summary = $controller->getGlobalSummary();
$latestEntries = array_slice($controller->listEntriesForAdmin(), 0, 5);
$pageTitle = 'Dashboard admin FoodLog — FoodWise';
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
        <a href="dashboard-admin.php" class="nav-item active"><span class="nav-ico" aria-hidden="true">▣</span> Dashboard</a>
        <a href="liste-entries.php" class="nav-item"><span class="nav-ico" aria-hidden="true">📑</span> Entrées utilisateurs</a>
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
          <input type="search" placeholder="Rechercher une entrée, un utilisateur…" aria-label="Recherche admin" value="">
          <button type="button">OK</button>
        </form>
        <nav class="topbar-nav" aria-label="Raccourcis">
          <a href="liste-entries.php">Entrées</a>
        </nav>
        <div class="topbar-user">
          <span class="user-avatar-initials" aria-hidden="true">JM</span>
          <span>Admin</span>
        </div>
      </header>

      <main class="page-body">
        <h1 class="page-title">Dashboard FoodLog</h1>
        <p class="page-subtitle">Vue d’ensemble complète avec données réelles depuis la base.</p>

        <div class="summary-cards">
          <article class="summary-card">
            <h3>Entrées totales</h3>
            <p class="big"><?= htmlspecialchars((string) $summary['entries'], ENT_QUOTES, 'UTF-8') ?></p>
            <p class="summary-card-hint mb-0">Toutes les entrées</p>
          </article>
          <article class="summary-card">
            <h3>Utilisateurs</h3>
            <p class="big"><?= htmlspecialchars((string) $summary['users'], ENT_QUOTES, 'UTF-8') ?></p>
            <p class="summary-card-hint mb-0">Comptes distincts</p>
          </article>
          <article class="summary-card">
            <h3>Calories aujourd’hui</h3>
            <p class="big"><?= htmlspecialchars((string) $summary['todayCalories'], ENT_QUOTES, 'UTF-8') ?></p>
            <p class="summary-card-hint mb-0">Total plateforme</p>
          </article>
        </div>

        <div class="recipe-grid">
          <article class="card mb-0">
            <div class="card-header">
              <h2 class="card-title">Actions rapides</h2>
            </div>
            <p class="text-muted mb-0">Accès direct aux pages de contenu.</p>
            <p class="mt-1 mb-0" style="display:flex;flex-wrap:wrap;gap:8px">
              <a href="liste-entries.php" class="btn btn-primary">Liste des entrées</a>
              <a href="statistiques-globales.php" class="btn btn-secondary">Statistiques</a>
            </p>
          </article>

          <article class="card mb-0">
            <div class="card-header">
              <h2 class="card-title">Dernières entrées</h2>
              <span class="card-meta">5 dernières mises à jour</span>
            </div>
            <div class="table-wrap">
              <table class="fw-table">
                <thead>
                  <tr>
                    <th>ID</th>
                    <th>Date</th>
                    <th>Repas</th>
                    <th class="num">kcal</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (empty($latestEntries)): ?>
                    <tr><td colspan="4" class="text-muted">Aucune entrée disponible.</td></tr>
                  <?php else: ?>
                    <?php foreach ($latestEntries as $entry): ?>
                      <tr>
                        <td><?= htmlspecialchars((string) $entry['id'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($entry['date'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><span class="badge badge-brun"><?= htmlspecialchars($entry['meal_type'], ENT_QUOTES, 'UTF-8') ?></span></td>
                        <td class="num"><?= htmlspecialchars((string) $entry['calories'], ENT_QUOTES, 'UTF-8') ?></td>
                      </tr>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </article>
        </div>
      </main>
    </div>
  </div>
</body>
</html>
