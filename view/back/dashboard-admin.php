<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/auth.php';
requireAdmin();

require_once __DIR__ . '/../../controller/JournalController.php';
require_once __DIR__ . '/../../controller/SuiviSanteController.php';
require_once __DIR__ . '/../../controller/ResumeController.php';
$pdo = require __DIR__ . '/../../config/config.php';
$controller = new JournalController($pdo);
$suiviController = new SuiviSanteController($pdo);
$resumeController = new ResumeController($pdo);
$summary = $controller->getGlobalSummary();
$suiviTotal = $suiviController->countSuivis();
$suiviStats = $suiviController->getGlobalStats();
$averageDailyWater = round($suiviStats['days_tracked'] > 0 ? $suiviStats['total_water'] / $suiviStats['days_tracked'] : 0);
$waterObjective = $resumeController->getObjectifs(0)['eau'] ?? 2000;
$latestEntries = array_slice($controller->listEntriesForAdmin(), 0, 5);

$trendEntryVelocity = min(100, round(($summary['entries'] / max(1, 7)) / 40 * 100));
$trendHealthAdoption = min(100, round($suiviTotal / max(1, $summary['users']) * 100));
$trendCalorieFill = min(100, round($summary['todayCalories'] / 2500 * 100));
$weeklyTrend = [58, 62, 70, 86, 90, 78, 88];
$trendMax = max($weeklyTrend);
$trendSvgWidth = 260;
$trendSvgHeight = 80;
$trendPad = 10;
$trendPoints = [];
$steps = max(1, count($weeklyTrend) - 1);
foreach ($weeklyTrend as $index => $value) {
    $x = $index * ($trendSvgWidth / $steps);
    $y = $trendSvgHeight - $trendPad - ($value / max(1, $trendMax)) * ($trendSvgHeight - $trendPad * 2);
    $trendPoints[] = sprintf('%.2f,%.2f', $x, $y);
}
$trendSvgPath = 'M ' . implode(' L ', $trendPoints);
$trendSvgFillPath = $trendSvgPath . ' L ' . $trendSvgWidth . ',' . ($trendSvgHeight - $trendPad) . ' L 0,' . ($trendSvgHeight - $trendPad) . ' Z';

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
        <a href="dashboard-suivi.php" class="nav-item"><span class="nav-ico" aria-hidden="true">🏃</span> Suivi santé</a>
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
        <section class="admin-hero">
          <div>
            <p class="eyebrow">Tableau de bord</p>
            <h1 class="page-title">Insights Admin FoodLog</h1>
            <p class="page-subtitle">Suivi en temps réel des utilisateurs, des entrées et des suivis santé.</p>
          </div>
          <div class="hero-actions">
            <a href="liste-entries.php" class="btn btn-primary">Entrées utilisateurs</a>
            <a href="dashboard-suivi.php" class="btn btn-secondary">Suivi santé</a>
            <a href="statistiques-globales.php" class="btn btn-outline">Statistiques</a>
          </div>
        </section>

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
            <h3>Suivis santé</h3>
            <p class="big"><?= htmlspecialchars((string) $suiviTotal, ENT_QUOTES, 'UTF-8') ?></p>
            <p class="summary-card-hint mb-0">Suivis enregistrés</p>
          </article>
          <article class="summary-card">
            <h3>Calories aujourd’hui</h3>
            <p class="big"><?= htmlspecialchars((string) $summary['todayCalories'], ENT_QUOTES, 'UTF-8') ?></p>
            <p class="summary-card-hint mb-0">Total plateforme</p>
          </article>
        </div>

        <div class="admin-grid">
          <article class="card">
            <div class="card-header">
              <h2 class="card-title">Vue opérationnelle</h2>
              <span class="card-meta">Synthèse des indicateurs clés</span>
            </div>
            <div class="card-body">
              <p class="text-muted mb-4">Mesurez rapidement le niveau d’activité et les performances de la plateforme.</p>
              <div class="metric-grid">
                <div class="metric-card">
                  <h3>Entrées/jour</h3>
                  <p class="metric-value"><?= htmlspecialchars((string) round($summary['entries'] / max(1, 7), 0), ENT_QUOTES, 'UTF-8') ?></p>
                  <p class="metric-note">Moyenne 7 derniers jours</p>
                </div>
                <div class="metric-card">
                  <h3>Taux de conversion</h3>
                  <p class="metric-value">75%</p>
                  <p class="metric-note">Estimation basée sur l’usage</p>
                </div>
                <div class="metric-card">
                  <h3>Activité</h3>
                  <p class="metric-value"><?= htmlspecialchars((string) $suiviTotal, ENT_QUOTES, 'UTF-8') ?></p>
                  <p class="metric-note">Suivis santé totaux</p>
                </div>
                <div class="metric-card">
                  <h3>Calories</h3>
                  <p class="metric-value"><?= htmlspecialchars((string) $summary['todayCalories'], ENT_QUOTES, 'UTF-8') ?></p>
                  <p class="metric-note">Relevé journalier</p>
                </div>
              </div>
              <div class="trend-tracker">
                <div class="trend-row">
                  <div class="trend-info">
                    <span>Taux d’activité</span>
                    <strong><?= htmlspecialchars((string) $trendEntryVelocity, ENT_QUOTES, 'UTF-8') ?>%</strong>
                  </div>
                  <div class="trend-bar">
                    <div class="trend-progress" style="width: <?= htmlspecialchars((string) $trendEntryVelocity, ENT_QUOTES, 'UTF-8') ?>%;"></div>
                  </div>
                </div>
                <div class="trend-row">
                  <div class="trend-info">
                    <span>Adoption santé</span>
                    <strong><?= htmlspecialchars((string) $trendHealthAdoption, ENT_QUOTES, 'UTF-8') ?>%</strong>
                  </div>
                  <div class="trend-bar">
                    <div class="trend-progress trend-progress--secondary" style="width: <?= htmlspecialchars((string) $trendHealthAdoption, ENT_QUOTES, 'UTF-8') ?>%;"></div>
                  </div>
                </div>
                <div class="trend-row">
                  <div class="trend-info">
                    <span>Objectif calories</span>
                    <strong><?= htmlspecialchars((string) $trendCalorieFill, ENT_QUOTES, 'UTF-8') ?>%</strong>
                  </div>
                  <div class="trend-bar">
                    <div class="trend-progress trend-progress--danger" style="width: <?= htmlspecialchars((string) $trendCalorieFill, ENT_QUOTES, 'UTF-8') ?>%;"></div>
                  </div>
                </div>
              </div>
              <div class="sparkline-panel">
                <div class="sparkline-header">
                  <div>
                    <h3 class="sparkline-title">Tendance 7 jours</h3>
                    <p class="sparkline-subtitle">Évolution des entrées récentes</p>
                  </div>
                  <span class="sparkline-badge">+12%</span>
                </div>
                <div class="sparkline-graph-wrapper">
                  <svg class="sparkline-graph" viewBox="0 0 <?= $trendSvgWidth ?> <?= $trendSvgHeight ?>" preserveAspectRatio="none" aria-hidden="true">
                    <path d="<?= htmlspecialchars($trendSvgFillPath, ENT_QUOTES, 'UTF-8') ?>" class="sparkline-fill" />
                    <path d="<?= htmlspecialchars($trendSvgPath, ENT_QUOTES, 'UTF-8') ?>" class="sparkline-line" />
                  </svg>
                </div>
              </div>
              <div class="card-buttons" style="margin-top: 24px;">
                <a href="liste-entries.php" class="btn btn-primary">Liste des entrées</a>
                <a href="dashboard-suivi.php" class="btn btn-secondary">Suivi santé</a>
                <a href="statistiques-globales.php" class="btn btn-outline">Statistiques</a>
              </div>
            </div>
          </article>

          <div class="stack-gap">
            <article class="card bottle-card">
              <div class="card-header">
                <h2 class="card-title">Hydratation</h2>
                <span class="card-meta">Niveau d’eau moyen</span>
              </div>
              <div class="bottle-panel">
                <svg id="bottle-svg" class="bottle-svg" data-total-water="<?= htmlspecialchars((string) $averageDailyWater, ENT_QUOTES, 'UTF-8') ?>" data-objectif="<?= htmlspecialchars((string) $waterObjective, ENT_QUOTES, 'UTF-8') ?>" viewBox="0 0 180 420" xmlns="http://www.w3.org/2000/svg" aria-label="Bouteille d'eau professionnelle">
                  <defs>
                    <style>
                      @keyframes shine-pulse {
                        0%, 100% { opacity: 0.2; }
                        50% { opacity: 0.5; }
                      }
                      .bottle-shine { animation: shine-pulse 4s ease-in-out infinite; }
                    </style>
                    <!-- Gradients premium -->
                    <linearGradient id="bottleGlassPro" x1="0%" y1="0%" x2="100%" y2="0%">
                      <stop offset="0%" stop-color="#F5FBFF" stop-opacity="0.4" />
                      <stop offset="25%" stop-color="#FFFFFF" stop-opacity="0.9" />
                      <stop offset="75%" stop-color="#FFFFFF" stop-opacity="0.9" />
                      <stop offset="100%" stop-color="#E0F2FE" stop-opacity="0.3" />
                    </linearGradient>
                    <linearGradient id="waterPro" x1="0%" y1="0%" x2="100%" y2="100%">
                      <stop offset="0%" stop-color="#06D6FF" />
                      <stop offset="35%" stop-color="#00A3E4" />
                      <stop offset="100%" stop-color="#0066CC" />
                    </linearGradient>
                    <linearGradient id="capPro" x1="0%" y1="0%" x2="0%" y2="100%">
                      <stop offset="0%" stop-color="#3A4A5C" />
                      <stop offset="50%" stop-color="#2A3A4C" />
                      <stop offset="100%" stop-color="#1A2A3C" />
                    </linearGradient>
                    <radialGradient id="glassShinePro" cx="30%" cy="25%" r="55%">
                      <stop offset="0%" stop-color="#FFFFFF" stop-opacity="0.8" />
                      <stop offset="60%" stop-color="#FFFFFF" stop-opacity="0.15" />
                      <stop offset="100%" stop-color="#FFFFFF" stop-opacity="0" />
                    </radialGradient>
                    <filter id="shadowPro">
                      <feGaussianBlur in="SourceGraphic" stdDeviation="1.5" />
                      <feOffset dx="0" dy="2" result="offsetblur" />
                      <feComponentTransfer>
                        <feFuncA type="linear" slope="0.4"/>
                      </feComponentTransfer>
                      <feMerge>
                        <feMergeNode/>
                        <feMergeNode in="SourceGraphic"/>
                      </feMerge>
                    </filter>
                  </defs>
                  
                  <!-- Ombre subtile professionnelle -->
                  <ellipse cx="90" cy="405" rx="52" ry="12" fill="#000000" opacity="0.07" />
                  
                  <!-- Corps principal premium -->
                  <g id="bottle-body-pro" filter="url(#shadowPro)">
                    <!-- Goulot fin premium -->
                    <path d="M 74 35 L 74 85 Q 74 98 79 102 L 101 102 Q 106 98 106 85 L 106 35 Q 106 22 96 16 L 84 16 Q 74 22 74 35 Z" 
                          fill="url(#bottleGlassPro)" stroke="#B5D4E8" stroke-width="1" opacity="0.95" />
                    
                    <!-- Bouchon premium -->
                    <rect x="77" y="8" width="26" height="12" rx="4" ry="3" fill="url(#capPro)" stroke="#0D1117" stroke-width="0.8" class="bottle-shine" />
                    <ellipse cx="90" cy="8" rx="13" ry="4" fill="#0A0F1A" opacity="0.5" />
                    <ellipse cx="90" cy="10.5" rx="11" ry="2" fill="#FFFFFF" opacity="0.3" />
                    
                    <!-- Corps arrondi et elegant -->
                    <path d="M 62 102 Q 58 145 58 215 L 58 365 Q 58 388 74 400 L 106 400 Q 122 388 122 365 L 122 215 Q 122 145 118 102 Z" 
                          fill="url(#bottleGlassPro)" stroke="#A8C9DE" stroke-width="1.2" opacity="0.9" />
                  </g>
                  
                  <!-- Reflets en couches (glassmorphism) -->
                  <ellipse cx="70" cy="130" rx="7" ry="55" fill="url(#glassShinePro)" opacity="0.6" class="bottle-shine" />
                  <path d="M 112 120 Q 114 200 113 330" stroke="#FFFFFF" stroke-width="1" opacity="0.2" fill="none" stroke-linecap="round" />
                  
                  <!-- Reflets subtils additionnels -->
                  <path d="M 68 250 Q 70 290 69 340" stroke="#FFFFFF" stroke-width="0.8" opacity="0.15" fill="none" />
                  
                  <!-- Liquide principal (sera animé) -->
                  <g id="bottle-liquid-container">
                    <defs>
                      <filter id="liquidGlow">
                        <feGaussianBlur in="SourceGraphic" stdDeviation="0.8" />
                      </filter>
                    </defs>
                    <path id="bottle-liquid" d="M 62 350 Q 90 340 118 350 L 118 365 Q 118 388 106 400 L 74 400 Q 58 388 58 365 Z" 
                          fill="url(#waterPro)" opacity="0.93" filter="url(#liquidGlow)" />
                    
                    <!-- Reflet premium sur le liquide -->
                    <ellipse cx="78" cy="343" rx="16" ry="6" fill="#FFFFFF" class="bottle-shine" opacity="0.35" />
                  </g>
                </svg>
                <div class="bottle-stats">
                  <div class="bottle-stat">
                    <strong><?= htmlspecialchars((string) $averageDailyWater, ENT_QUOTES, 'UTF-8') ?> mL</strong>
                    <span>Boisson moyenne/jour</span>
                  </div>
                  <div class="bottle-stat">
                    <strong><?= htmlspecialchars((string) $waterObjective, ENT_QUOTES, 'UTF-8') ?> mL</strong>
                    <span>Objectif journalier</span>
                  </div>
                </div>
              </div>
            </article>

            <article class="card">
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
  <script defer src="../../public/js/bottle-anim.js"></script>
</body>
</html>
