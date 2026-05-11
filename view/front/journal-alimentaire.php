<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/auth.php';
requireClient();

require_once __DIR__ . '/../../controller/JournalController.php';
$pdo = require __DIR__ . '/../../config/config.php';
$controller = new JournalController($pdo);
$userId = 1;
$today = date('Y-m-d');

$sort = $_GET['sort'] ?? 'date_desc';
$sortDirection = $sort === 'date_asc' ? 'ASC' : 'DESC';
$entries = $controller->listEntriesForUser($userId, $sortDirection);
$totalCalories = $controller->getDailyCalories($userId, $today);
$streak = $controller->getStreak($userId);

// Macros du jour pour le coaching IA
$macros = $controller->getMacroTotals($userId, $today);
$history7Days = $controller->getLast7DaysSummary($userId);

$alertMessage = '';
$alertClass = 'success';
if (isset($_GET['created'])) {
    $alertMessage = $_GET['created'] === '1' ? 'Entree ajoutee avec succes.' : 'Impossible d\'ajouter l\'entree.';
    $alertClass = $_GET['created'] === '1' ? 'success' : 'error';
} elseif (isset($_GET['updated'])) {
    $alertMessage = $_GET['updated'] === '1' ? 'Entree modifiee avec succes.' : 'Impossible de modifier l\'entree.';
    $alertClass = $_GET['updated'] === '1' ? 'success' : 'error';
} elseif (isset($_GET['deleted'])) {
    $alertMessage = $_GET['deleted'] === '1' ? 'Entree supprimee avec succes.' : 'Impossible de supprimer l\'entree.';
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
        <a href="ajouter-entree.php" class="nav-item"><span class="nav-ico" aria-hidden="true">➕</span> Ajouter une entree</a>
        <a href="resume.php" class="nav-item"><span class="nav-ico" aria-hidden="true">📊</span> Resume du jour</a>
        <p class="fw-sidebar-section">Sante</p>
        <a href="suivi-sante-unifie.php" class="nav-item"><span class="nav-ico" aria-hidden="true">🏃</span> Suivi Sante</a>
        <a href="ajouter-suivi.php" class="nav-item"><span class="nav-ico" aria-hidden="true">➕</span> Ajouter activite</a>
        <p class="fw-sidebar-section">Compte</p>
        <a href="#" class="nav-item"><span class="nav-ico" aria-hidden="true">👤</span> Profil</a>
        <a href="#" class="nav-item"><span class="nav-ico" aria-hidden="true">⎋</span> Deconnexion</a>
      </nav>
    </aside>

    <div class="main-content">
      <header class="topbar">
        <label for="fw-nav-toggle" class="topbar-menu-btn" aria-label="Ouvrir le menu">☰</label>
        <form class="topbar-search" action="#" method="get" role="search">
          <input type="search" name="q" placeholder="Rechercher un aliment..." aria-label="Recherche" value="">
          <button type="button">OK</button>
        </form>
        <nav class="topbar-nav" aria-label="Raccourcis">
          <a href="resume.php">Resume</a>
          <a href="suivi-sante-unifie.php">Sante</a>
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
        <p class="page-subtitle">Suivi des repas et collations — total calories aujourd'hui :</p>

        <div class="summary-cards">
          <article class="summary-card">
            <h3>Calories</h3>
            <p class="big"><?= htmlspecialchars((string) $totalCalories, ENT_QUOTES, 'UTF-8') ?></p>
            <p class="summary-card-hint mb-0">Aujourd'hui</p>
          </article>
          <article class="summary-card">
            <h3>Proteines</h3>
            <p class="big"><?= round($macros['proteins'], 0) ?>g</p>
            <p class="summary-card-hint mb-0">Objectif 75g</p>
          </article>
          <article class="summary-card">
            <h3>Streak</h3>
            <p class="big"><?= htmlspecialchars((string) $streak, ENT_QUOTES, 'UTF-8') ?> j.</p>
            <p class="summary-card-hint mb-0">Jours consecutifs</p>
          </article>
          <article class="summary-card">
            <h3>Entrees</h3>
            <p class="big"><?= htmlspecialchars((string) count($entries), ENT_QUOTES, 'UTF-8') ?></p>
            <p class="summary-card-hint mb-0">Enregistrements</p>
          </article>
        </div>

        <div class="flex-between" style="margin-bottom: 18px; gap: 12px; align-items: center;">
          <div class="sort-controls">
            <span class="text-muted">Trier par date :</span>
            <a href="journal-alimentaire.php?sort=<?= $sortDirection === 'ASC' ? 'date_desc' : 'date_asc' ?>" class="btn btn-secondary btn-sm">
              <?= $sortDirection === 'ASC' ? 'Date decroissante' : 'Date croissante' ?>
            </a>
          </div>
        </div>

        <div class="flex-between" style="margin-bottom: 22px;">
          <p class="text-muted mb-0">Liste des entrees enregistrees pour aujourd'hui et les jours recents.</p>
          <div class="flex-between mb-0" style="gap:8px">
            <a href="ajouter-entree.php" class="btn btn-primary">+ Ajouter une entree</a>
            <a href="resume-jour.php" class="btn btn-secondary">Voir le resume</a>
          </div>
        </div>

        <!-- ===== WIDGET COACHING IA ===== -->
        <section class="mb-4" aria-label="Coaching IA">
          <div class="card" id="journal-coaching-widget" style="border-left: 4px solid #6d4c1b; padding: 20px;">
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 14px;">
              <span style="font-size: 1.4em;" aria-hidden="true">🤖</span>
              <h2 style="margin: 0; font-size: 1.05em; color: #6d4c1b;">Coaching IA — Bilan du jour</h2>
            </div>
            <div id="journal-coach-summary" style="color: #555; font-size: 0.95em; line-height: 1.6; margin-bottom: 10px;">
              <em>Chargement de l'analyse...</em>
            </div>
            <div id="journal-coach-messages"></div>
          </div>
        </section>

        <section class="card mb-0">
          <div class="table-wrap">
            <table class="fw-table">
              <thead>
                <tr>
                  <th>Date</th>
                  <th>Repas</th>
                  <th>Aliment</th>
                  <th class="num">Quantite</th>
                  <th class="num">kcal</th>
                  <th></th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($entries)): ?>
                  <tr>
                    <td colspan="6" class="text-muted">Aucune entree enregistree pour le moment.</td>
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

<!-- Coaching IA Widget Script -->
<script>
document.addEventListener('DOMContentLoaded', function () {
  if (typeof window.nutritionCoach === 'undefined') return;

  const userData = {
    calories:      <?= (int)$totalCalories ?>,
    proteins:      <?= round($macros['proteins'], 1) ?>,
    carbs:         <?= round($macros['carbs'], 1) ?>,
    fats:          <?= round($macros['fats'], 1) ?>,
    sugar:         0,
    water:         0,
    caloriesBurned: 0,
    goal:          'fitness',
    history: <?= json_encode(array_map(fn($d) => [
      'calories' => (int)$d['calories'],
      'proteins' => (float)$d['proteins'],
      'carbs'    => (float)$d['carbs'],
      'fats'     => (float)$d['fats'],
      'sugar'    => 0,
      'water'    => 0,
      'caloriesBurned' => 0,
    ], $history7Days), JSON_THROW_ON_ERROR) ?>
  };

  // Résumé
  const summary = window.nutritionCoach.generateCoachSummary(userData);
  const summaryEl = document.getElementById('journal-coach-summary');
  if (summaryEl) summaryEl.textContent = summary;

  // Messages (top 3)
  const messages = window.nutritionCoach.generateCoachMessages(userData).slice(0, 3);
  const msgEl = document.getElementById('journal-coach-messages');
  if (!msgEl || messages.length === 0) return;

  const colorMap = { warning: '#e67e22', advice: '#3498db', motivation: '#27ae60' };
  const iconMap  = { warning: '⚠️', advice: '💡', motivation: '🎉' };

  msgEl.innerHTML = messages.map(m => {
    const color = colorMap[m.type] || '#6d4c1b';
    const icon  = iconMap[m.type]  || 'ℹ️';
    return `<div style="display:flex;align-items:flex-start;gap:10px;padding:10px 0;border-top:1px solid #f0ece6;">
      <span style="font-size:1.1em;flex-shrink:0;" aria-hidden="true">${icon}</span>
      <p style="margin:0;font-size:0.9em;color:#444;line-height:1.5;">${m.message}</p>
    </div>`;
  }).join('');
});
</script>
</html>
