<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/auth.php';
requireClient();

$errors = $_SESSION['form_errors'] ?? []; 
$oldInput = $_SESSION['form_old'] ?? [];
unset($_SESSION['form_errors'], $_SESSION['form_old']);

require_once __DIR__ . '/../../controller/JournalController.php';
$pdo = require __DIR__ . '/../../config/config.php';
$controller = new JournalController($pdo);
$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: journal-alimentaire.php');
    exit;
}

$entry = $controller->getEntry($id);
if ($entry === null) {
    header('Location: journal-alimentaire.php');
    exit;
}

$pageTitle = 'Modifier une entrée — FoodWise';
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
          <a href="journal-alimentaire.php">Journal</a>
        </nav>
        <div class="topbar-user">
          <span class="user-avatar-initials" aria-hidden="true">MD</span>
          <span>Marie Dupont</span>
        </div>
      </header>

      <main class="page-body">
        <h1 class="page-title">Modifier une entrée</h1>
        <p class="page-subtitle">Édition de l’entrée enregistrée.</p>

        <section class="card">
          <div class="card-header">
            <h2 class="card-title">Édition — <?= htmlspecialchars($entry['meal_type'], ENT_QUOTES, 'UTF-8') ?></h2>
            <span class="card-meta"><?= htmlspecialchars($entry['date'], ENT_QUOTES, 'UTF-8') ?>, <?= htmlspecialchars($entry['time'] ?: '00:00', ENT_QUOTES, 'UTF-8') ?></span>
          </div>

          <form id="entry-form" action="../../controller/modifier.php" method="post">
            <input type="hidden" name="id" value="<?= htmlspecialchars((string) $entry['id'], ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="source" value="front">
            <div id="form-errors" class="notif-panel<?= empty($errors) ? '' : ' error' ?>" style="display: <?= empty($errors) ? 'none' : 'block' ?>;margin-bottom:18px;">
              <?php if (!empty($errors)): ?>
                <ul>
                  <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></li>
                  <?php endforeach; ?>
                </ul>
              <?php endif; ?>
            </div>

            <div class="form-row">
              <div class="form-group mb-0">
                <label class="form-label" for="date">Date</label>
                <input type="date" id="date" name="date" class="form-control" value="<?= htmlspecialchars($oldInput['date'] ?? $entry['date'], ENT_QUOTES, 'UTF-8') ?>" required>
              </div>
              <div class="form-group mb-0">
                <label class="form-label" for="time">Heure</label>
                <input type="time" id="time" name="time" class="form-control" value="<?= htmlspecialchars($oldInput['time'] ?? ($entry['time'] ?: ''), ENT_QUOTES, 'UTF-8') ?>">
              </div>
              <div class="form-group mb-0">
                <label class="form-label" for="meal_type">Type de repas</label>
                <select id="meal_type" name="meal_type" class="form-control" required>
                  <?php foreach (['Petit-déjeuner', 'Déjeuner', 'Collation', 'Dîner'] as $mealType): ?>
                    <option value="<?= htmlspecialchars($mealType, ENT_QUOTES, 'UTF-8') ?>" <?= $mealType === ($oldInput['meal_type'] ?? $entry['meal_type']) ? 'selected' : '' ?>><?= htmlspecialchars($mealType, ENT_QUOTES, 'UTF-8') ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>

            <div class="form-group">
              <label class="form-label" for="food">Aliment / plat</label>
              <input type="text" id="food" name="food" class="form-control" value="<?= htmlspecialchars($oldInput['food'] ?? $entry['food'], ENT_QUOTES, 'UTF-8') ?>" required>
            </div>

            <div class="form-row">
              <div class="form-group mb-0">
                <label class="form-label" for="quantity">Quantité</label>
                <input type="text" id="quantity" name="quantity" class="form-control" value="<?= htmlspecialchars($oldInput['quantity'] ?? $entry['quantity'], ENT_QUOTES, 'UTF-8') ?>" required pattern="^[0-9]+(?:[.,][0-9]+)?\s*[a-zA-Z%°]*$" title="Ex. 250 g ou 1.5 portion">
              </div>
              <div class="form-group mb-0">
                <label class="form-label" for="calories">Calories (kcal)</label>
                <input type="number" id="calories" name="calories" class="form-control" value="<?= htmlspecialchars($oldInput['calories'] ?? (string) $entry['calories'], ENT_QUOTES, 'UTF-8') ?>" min="0" step="1" required>
              </div>
            </div>

            <div class="form-row">
              <div class="form-group mb-0">
                <label class="form-label" for="proteins">Protéines (g)</label>
                <input type="number" id="proteins" name="proteins" class="form-control" step="0.1" value="<?= htmlspecialchars($oldInput['proteins'] ?? (string) $entry['proteins'], ENT_QUOTES, 'UTF-8') ?>" min="0" required>
              </div>
              <div class="form-group mb-0">
                <label class="form-label" for="carbs">Glucides (g)</label>
                <input type="number" id="carbs" name="carbs" class="form-control" step="0.1" value="<?= htmlspecialchars($oldInput['carbs'] ?? (string) $entry['carbs'], ENT_QUOTES, 'UTF-8') ?>" min="0" required>
              </div>
              <div class="form-group mb-0">
                <label class="form-label" for="fats">Lipides (g)</label>
                <input type="number" id="fats" name="fats" class="form-control" step="0.1" value="<?= htmlspecialchars($oldInput['fats'] ?? (string) $entry['fats'], ENT_QUOTES, 'UTF-8') ?>" min="0" required>
              </div>
            </div>

            <div class="form-group">
              <label class="form-label" for="note">Notes</label>
              <textarea id="note" name="note" class="form-control"><?= htmlspecialchars($oldInput['note'] ?? $entry['note'], ENT_QUOTES, 'UTF-8') ?></textarea>
            </div>

            <div class="flex-between mt-1 mb-0">
              <a href="journal-alimentaire.php" class="btn btn-outline">Retour au journal</a>
              <div style="display:flex;gap:8px;">
                <a href="../../controller/supprimer.php?id=<?= htmlspecialchars((string) $entry['id'], ENT_QUOTES, 'UTF-8') ?>&source=front" class="btn btn-danger btn-sm confirm-delete">Supprimer</a>
                <button type="submit" class="btn btn-primary">Mettre à jour</button>
              </div>
            </div>
          </form>
        </section>
      </main>
    </div>
  </div>
</body>
</html>
