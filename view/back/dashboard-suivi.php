<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/auth.php';
requireAdmin();

// Inclure la configuration et les classes
$pdo = require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../controller/SuiviSanteController.php';

$controller = new SuiviSanteController($pdo);

// Récupérer les statistiques
$globalStats = $controller->getGlobalStats();
$statsByActivity = $controller->getStatsByActivityType();
$statsByIntensity = $controller->getStatsByIntensity();
$mostFrequent = $controller->getMostFrequentActivities(10);
?>

<?php include_once __DIR__ . '/../../view/template/header.php'; ?>

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
        <a href="dashboard-suivi.php" class="nav-item active"><span class="nav-ico" aria-hidden="true">🏃</span> Suivi santé</a>
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
          <input type="search" placeholder="Rechercher une activité, un utilisateur…" aria-label="Recherche admin" value="">
          <button type="button">OK</button>
        </form>
        <nav class="topbar-nav" aria-label="Raccourcis">
          <a href="liste-entries.php">Entrées</a>
          <a href="dashboard-admin.php">Dashboard</a>
        </nav>
        <div class="topbar-user">
          <span class="user-avatar-initials" aria-hidden="true">JM</span>
          <span>Admin</span>
        </div>
      </header>

      <main class="page-body">
        <h1 class="page-title">Suivi Santé</h1>
        <p class="page-subtitle">Rapport complet des activités physiques et de la performance santé.</p>

        <section class="card">
          <div class="card-header">
            <div>
              <h2 class="card-title">Vue d’ensemble</h2>
              <span class="card-meta">Statistiques clés et tendances</span>
            </div>
            <div class="card-actions">
              <a href="liste-entries.php" class="btn btn-outline btn-sm">Entrées utilisateurs</a>
              <a href="dashboard-admin.php" class="btn btn-secondary btn-sm">Dashboard</a>
              <a href="statistiques-globales.php" class="btn btn-primary btn-sm">Statistiques</a>
            </div>
          </div>

          <div class="filter-bar">
            <input type="text" placeholder="Recherche activité, intensité ou utilisateur..." aria-label="Recherche activité">
            <button type="button" class="btn btn-primary btn-sm">Rechercher</button>
            <button type="button" class="btn btn-outline btn-sm">Réinitialiser</button>
          </div>
        </section>

        <div class="summary-cards">
          <article class="summary-card">
            <h3>Total suivis</h3>
            <p class="big"><?= htmlspecialchars((string) $globalStats['total_followups'], ENT_QUOTES, 'UTF-8') ?></p>
            <p class="summary-card-hint mb-0">Suivis enregistrés</p>
          </article>
          <article class="summary-card">
            <h3>Utilisateurs</h3>
            <p class="big"><?= htmlspecialchars((string) $globalStats['total_users'], ENT_QUOTES, 'UTF-8') ?></p>
            <p class="summary-card-hint mb-0">Comptes actifs</p>
          </article>
          <article class="summary-card">
            <h3>Calories brûlées</h3>
            <p class="big"><?= htmlspecialchars((string) number_format((int)$globalStats['total_calories_burned'], 0), ENT_QUOTES, 'UTF-8') ?></p>
            <p class="summary-card-hint mb-0">Total plateforme</p>
          </article>
          <article class="summary-card">
            <h3>Durée activité</h3>
            <p class="big"><?= htmlspecialchars((string) number_format((int)$globalStats['total_duration'], 0), ENT_QUOTES, 'UTF-8') ?> min</p>
            <p class="summary-card-hint mb-0">Durée totale</p>
          </article>
        </div>

        <div class="admin-grid">
          <article class="card">
            <div class="card-header">
              <h2 class="card-title">Activités principales</h2>
              <span class="card-meta">Analyse par type d'activité</span>
            </div>
            <div class="table-wrap">
              <table class="fw-table">
                <thead>
                  <tr>
                    <th>Activité</th>
                    <th class="num">Sessions</th>
                    <th class="num">Durée</th>
                    <th class="num">Calories</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (empty($statsByActivity)): ?>
                    <tr><td colspan="4" class="text-muted">Aucune donnée</td></tr>
                  <?php else: ?>
                    <?php foreach ($statsByActivity as $stat): ?>
                      <tr>
                        <td><strong><?= htmlspecialchars($stat['type_activite'], ENT_QUOTES, 'UTF-8') ?></strong></td>
                        <td class="num"><?= number_format((int)$stat['count'], 0) ?></td>
                        <td class="num"><?= number_format((int)$stat['total_duration'], 0) ?> min</td>
                        <td class="num"><?= number_format((float)$stat['total_calories'], 1) ?></td>
                      </tr>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </article>

          <article class="card">
            <div class="card-header">
              <h2 class="card-title">Répartition par intensité</h2>
              <span class="card-meta">Comprendre l'effort</span>
            </div>
            <div class="table-wrap">
              <table class="fw-table">
                <thead>
                  <tr>
                    <th>Intensité</th>
                    <th class="num">Sessions</th>
                    <th class="num">Durée</th>
                    <th class="num">Calories</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (empty($statsByIntensity)): ?>
                    <tr><td colspan="4" class="text-muted">Aucune donnée</td></tr>
                  <?php else: ?>
                    <?php foreach ($statsByIntensity as $stat): ?>
                      <tr>
                        <td><span class="badge <?= $stat['intensite'] === 'élevé' ? 'bg-danger' : ($stat['intensite'] === 'moyen' ? 'bg-warning text-dark' : 'bg-info') ?>"><?= ucfirst(htmlspecialchars($stat['intensite'], ENT_QUOTES, 'UTF-8')) ?></span></td>
                        <td class="num"><?= number_format((int)$stat['count'], 0) ?></td>
                        <td class="num"><?= number_format((float)$stat['avg_duration'], 0) ?> min</td>
                        <td class="num"><?= number_format((float)$stat['avg_calories'], 1) ?></td>
                      </tr>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </article>
        </div>

        <article class="card">
          <div class="card-header">
            <h2 class="card-title">Top 10 activités fréquentes</h2>
            <span class="card-meta">Les activités les plus populaires</span>
          </div>
          <div class="table-wrap">
            <table class="fw-table">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Activité</th>
                  <th class="num">Sessions</th>
                  <th class="num">Calories</th>
                  <th class="num">Durée</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($mostFrequent)): ?>
                  <tr><td colspan="5" class="text-muted">Aucune donnée</td></tr>
                <?php else: ?>
                  <?php foreach ($mostFrequent as $index => $activity): ?>
                    <tr>
                      <td><span class="badge bg-secondary"><?= $index + 1 ?></span></td>
                      <td><strong><?= htmlspecialchars($activity['type_activite'], ENT_QUOTES, 'UTF-8') ?></strong></td>
                      <td class="num"><?= number_format((int)$activity['frequency'], 0) ?></td>
                      <td class="num"><?= number_format((float)$activity['avg_calories_burned'], 1) ?></td>
                      <td class="num"><?= number_format((float)$activity['avg_duration'], 0) ?> min</td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </article>
      </main>
    </div>
  </div>
</body>
</html>
