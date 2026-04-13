<?php
declare(strict_types=1);

require_once __DIR__ . '/../../controller/JournalController.php';
$pdo = require __DIR__ . '/../../config/config.php';
$controller = new JournalController($pdo);
$userId = 1;
$today = date('Y-m-d');
$calories = $controller->getDailyCalories($userId, $today);
$streak = $controller->getStreak($userId);
$entryCount = $controller->getEntryCount($userId, $today);
$macros = $controller->getMacroTotals($userId, $today);
$goal = 1800;
$remaining = max(0, $goal - $calories);
$proteinPercent = $goal > 0 ? min(100, ($macros['proteins'] * 4 / $goal) * 100) : 0;
$carbPercent = $goal > 0 ? min(100, ($macros['carbs'] * 4 / $goal) * 100) : 0;
$fatsPercent = $goal > 0 ? min(100, ($macros['fats'] * 9 / $goal) * 100) : 0;
$pageTitle = 'Résumé du jour — FoodWise';
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
        <a href="resume-jour.php" class="nav-item active"><span class="nav-ico" aria-hidden="true">📊</span> Résumé du jour</a>
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
          <a href="journal-alimentaire.php">Journal</a>
        </nav>
        <div class="topbar-user">
          <span class="user-avatar-initials" aria-hidden="true">MD</span>
          <span>Marie Dupont</span>
        </div>
      </header>

      <main class="page-body">
        <h1 class="page-title">Résumé du jour</h1>
        <p class="page-subtitle"><?= htmlspecialchars((new DateTimeImmutable($today))->format('d F Y'), ENT_QUOTES, 'UTF-8') ?> — données dynamiques</p>

        <div class="streak-banner" role="status">
          <span style="font-size:2rem" aria-hidden="true">🔥</span>
          <div>
            <strong>Série : <?= htmlspecialchars((string) $streak, ENT_QUOTES, 'UTF-8') ?> jours consécutifs</strong>
            <span>Progrès et calories du jour.</span>
          </div>
        </div>

        <div class="summary-cards">
          <article class="summary-card">
            <h3>Calories</h3>
            <p class="big"><?= htmlspecialchars((string) $calories, ENT_QUOTES, 'UTF-8') ?></p>
            <p class="summary-card-hint mb-0">Objectif <?= htmlspecialchars((string) $goal, ENT_QUOTES, 'UTF-8') ?> kcal</p>
          </article>
          <article class="summary-card">
            <h3>Repas</h3>
            <p class="big"><?= htmlspecialchars((string) $entryCount, ENT_QUOTES, 'UTF-8') ?></p>
            <p class="summary-card-hint mb-0">Entrées aujourd’hui</p>
          </article>
          <article class="summary-card">
            <h3>Reste</h3>
            <p class="big"><?= htmlspecialchars((string) $remaining, ENT_QUOTES, 'UTF-8') ?></p>
            <p class="summary-card-hint mb-0">Kcal restantes</p>
          </article>
        </div>

        <section class="card">
          <div class="card-header">
            <h2 class="card-title">Macronutriments</h2>
            <span class="card-meta">Totaux journaliers</span>
          </div>

          <div class="fw-macro-row">
            <div class="fw-macro-head">
              <span>Protéines</span>
              <span><strong><?= htmlspecialchars((string) $macros['proteins'], ENT_QUOTES, 'UTF-8') ?> g</strong></span>
            </div>
            <div class="fw-macro-track" aria-hidden="true">
              <div class="fw-macro-fill fw-macro-fill--p" style="width:<?= htmlspecialchars((string) $proteinPercent, ENT_QUOTES, 'UTF-8') ?>%"></div>
            </div>
          </div>

          <div class="fw-macro-row">
            <div class="fw-macro-head">
              <span>Glucides</span>
              <span><strong><?= htmlspecialchars((string) $macros['carbs'], ENT_QUOTES, 'UTF-8') ?> g</strong></span>
            </div>
            <div class="fw-macro-track" aria-hidden="true">
              <div class="fw-macro-fill fw-macro-fill--g" style="width:<?= htmlspecialchars((string) $carbPercent, ENT_QUOTES, 'UTF-8') ?>%"></div>
            </div>
          </div>

          <div class="fw-macro-row mb-0">
            <div class="fw-macro-head">
              <span>Lipides</span>
              <span><strong><?= htmlspecialchars((string) $macros['fats'], ENT_QUOTES, 'UTF-8') ?> g</strong></span>
            </div>
            <div class="fw-macro-track" aria-hidden="true">
              <div class="fw-macro-fill fw-macro-fill--l" style="width:<?= htmlspecialchars((string) $fatsPercent, ENT_QUOTES, 'UTF-8') ?>%"></div>
            </div>
          </div>
        </section>

        <section class="card">
          <div class="card-header">
            <h2 class="card-title">Synthèse rapide</h2>
          </div>
          <div class="table-wrap">
            <table class="fw-table">
              <thead>
                <tr>
                  <th>Indicateur</th>
                  <th class="num">Valeur</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td>Calories consommées</td>
                  <td class="num"><?= htmlspecialchars((string) $calories, ENT_QUOTES, 'UTF-8') ?></td>
                </tr>
                <tr>
                  <td>Repas aujourd’hui</td>
                  <td class="num"><?= htmlspecialchars((string) $entryCount, ENT_QUOTES, 'UTF-8') ?></td>
                </tr>
              </tbody>
            </table>
          </div>
          <p class="mt-1 mb-0">
            <a href="journal-alimentaire.php" class="btn btn-secondary">Voir le journal</a>
            <a href="ajouter-entree.php" class="btn btn-primary">Ajouter une entrée</a>
          </p>
        </section>
      </main>
    </div>
  </div>
</body>
</html>
