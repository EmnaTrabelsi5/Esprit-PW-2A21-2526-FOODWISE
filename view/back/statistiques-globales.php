<?php
declare(strict_types=1);

require_once __DIR__ . '/../../controller/JournalController.php';
$pdo = require __DIR__ . '/../../config/config.php';
$controller = new JournalController($pdo);
$summary = $controller->getGlobalSummary();
$pageTitle = 'Statistiques globales — FoodWise Admin';
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
        <a href="liste-entries.php" class="nav-item"><span class="nav-ico" aria-hidden="true">📑</span> Entrées utilisateurs</a>
        <a href="statistiques-globales.php" class="nav-item active"><span class="nav-ico" aria-hidden="true">📈</span> Statistiques</a>
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
        <h1 class="page-title">Statistiques globales</h1>
        <p class="page-subtitle">Agrégats produits en direct depuis la base.</p>

        <div class="summary-cards">
          <article class="summary-card">
            <h3>Entrées totales</h3>
            <p class="big"><?= htmlspecialchars((string) $summary['entries'], ENT_QUOTES, 'UTF-8') ?></p>
            <p class="summary-card-hint mb-0">Toutes dépenses enregistrées</p>
          </article>
          <article class="summary-card">
            <h3>Utilisateurs</h3>
            <p class="big"><?= htmlspecialchars((string) $summary['users'], ENT_QUOTES, 'UTF-8') ?></p>
            <p class="summary-card-hint mb-0">Comptes distincts</p>
          </article>
          <article class="summary-card">
            <h3>Calories aujourd’hui</h3>
            <p class="big"><?= htmlspecialchars((string) $summary['todayCalories'], ENT_QUOTES, 'UTF-8') ?></p>
            <p class="summary-card-hint mb-0">Total global</p>
          </article>
        </div>

        <div class="recipe-grid">
          <article class="card mb-0">
            <div class="card-header">
              <h2 class="card-title">Répartition macros</h2>
            </div>
            <div class="fw-macro-row">
              <div class="fw-macro-head">
                <span>Protéines</span>
                <span><strong>22 %</strong></span>
              </div>
              <div class="fw-macro-track"><div class="fw-macro-fill fw-macro-fill--p" style="width:22%"></div></div>
            </div>
            <div class="fw-macro-row">
              <div class="fw-macro-head">
                <span>Glucides</span>
                <span><strong>48 %</strong></span>
              </div>
              <div class="fw-macro-track"><div class="fw-macro-fill fw-macro-fill--g" style="width:48%"></div></div>
            </div>
            <div class="fw-macro-row mb-0">
              <div class="fw-macro-head">
                <span>Lipides</span>
                <span><strong>30 %</strong></span>
              </div>
              <div class="fw-macro-track"><div class="fw-macro-fill fw-macro-fill--l" style="width:30%"></div></div>
            </div>
          </article>

          <article class="card mb-0">
            <div class="card-header">
              <h2 class="card-title">Top aliments</h2>
            </div>
            <div class="table-wrap">
              <table class="fw-table">
                <thead>
                  <tr>
                    <th>Aliment</th>
                    <th class="num">Occurrences</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td>Pain complet</td>
                    <td class="num">42 100</td>
                  </tr>
                  <tr>
                    <td>Œufs</td>
                    <td class="num">38 920</td>
                  </tr>
                  <tr>
                    <td>Pomme</td>
                    <td class="num">35 401</td>
                  </tr>
                  <tr>
                    <td>Yaourt nature</td>
                    <td class="num">31 204</td>
                  </tr>
                </tbody>
              </table>
            </div>
            <p class="mt-1 mb-0">
              <a href="liste-entries.php" class="btn btn-secondary">Voir les entrées</a>
              <a href="dashboard-admin.php" class="btn btn-primary">Dashboard</a>
            </p>
          </article>
        </div>

        <section class="card">
          <div class="card-header">
            <h2 class="card-title">Zone graphique</h2>
            <span class="card-meta">Placeholder pour Chart.js</span>
          </div>
          <div class="placeholder-chart">
            Graphique « entrées par jour » — prêt à être branché.
          </div>
        </section>
      </main>
    </div>
  </div>
</body>
</html>
