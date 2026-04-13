<?php
declare(strict_types=1);

session_start();
$errors = $_SESSION['form_errors'] ?? [];
$oldInput = $_SESSION['form_old'] ?? [];
unset($_SESSION['form_errors'], $_SESSION['form_old']);

$pageTitle = 'Ajouter une entrée — FoodWise';
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
        <a href="ajouter-entree.php" class="nav-item active"><span class="nav-ico" aria-hidden="true">➕</span> Ajouter une entrée</a>
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
        <h1 class="page-title">Ajouter une entrée</h1>
        <p class="page-subtitle">Formulaire connecté au contrôleur MVC.</p>

        <section class="card">
          <div class="card-header">
            <h2 class="card-title">Nouvelle entrée alimentaire</h2>
            <span class="card-meta">Renseignez tous les champs nécessaires.</span>
          </div>

          <form id="entry-form" action="../../controller/ajouter.php" method="post">
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
                <input type="date" id="date" name="date" class="form-control" value="<?= htmlspecialchars($oldInput['date'] ?? date('Y-m-d'), ENT_QUOTES, 'UTF-8') ?>" required>
              </div>
              <div class="form-group mb-0">
                <label class="form-label" for="time">Heure</label>
                <input type="time" id="time" name="time" class="form-control" value="<?= date('H:i') ?>">
              </div>
              <div class="form-group mb-0">
                <label class="form-label" for="meal_type">Type de repas</label>
                <select id="meal_type" name="meal_type" class="form-control" required>
                  <option value="Petit-déjeuner" <?= (isset($oldInput['meal_type']) && $oldInput['meal_type'] === 'Petit-déjeuner') ? 'selected' : '' ?>>Petit-déjeuner</option>
                  <option value="Déjeuner" <?= (!isset($oldInput['meal_type']) || $oldInput['meal_type'] === 'Déjeuner') ? 'selected' : '' ?>>Déjeuner</option>
                  <option value="Collation" <?= (isset($oldInput['meal_type']) && $oldInput['meal_type'] === 'Collation') ? 'selected' : '' ?>>Collation</option>
                  <option value="Dîner" <?= (isset($oldInput['meal_type']) && $oldInput['meal_type'] === 'Dîner') ? 'selected' : '' ?>>Dîner</option>
                </select>
              </div>
            </div>

            <div class="form-group">
              <label class="form-label" for="food">Aliment / plat</label>
              <input type="text" id="food" name="food" class="form-control" placeholder="Ex. Salade César au poulet" required value="<?= htmlspecialchars($oldInput['food'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            </div>

            <div class="form-row">
              <div class="form-group mb-0">
                <label class="form-label" for="quantity">Quantité</label>
                <input type="text" id="quantity" name="quantity" class="form-control" placeholder="Ex. 250 g" required pattern="^[0-9]+(?:[.,][0-9]+)?\s*[a-zA-Z%°]*$" title="Ex. 250 g ou 1.5 portion" value="<?= htmlspecialchars($oldInput['quantity'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
              </div>
              <div class="form-group mb-0">
                <label class="form-label" for="calories">Calories (kcal)</label>
                <input type="number" id="calories" name="calories" class="form-control" placeholder="0" min="0" step="1" required value="<?= htmlspecialchars($oldInput['calories'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
              </div>
            </div>

            <div class="form-row">
              <div class="form-group mb-0">
                <label class="form-label" for="proteins">Protéines (g)</label>
                <input type="number" id="proteins" name="proteins" class="form-control" step="0.1" placeholder="0" min="0" required value="<?= htmlspecialchars($oldInput['proteins'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
              </div>
              <div class="form-group mb-0">
                <label class="form-label" for="carbs">Glucides (g)</label>
                <input type="number" id="carbs" name="carbs" class="form-control" step="0.1" placeholder="0" min="0" required value="<?= htmlspecialchars($oldInput['carbs'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
              </div>
              <div class="form-group mb-0">
                <label class="form-label" for="fats">Lipides (g)</label>
                <input type="number" id="fats" name="fats" class="form-control" step="0.1" placeholder="0" min="0" required value="<?= htmlspecialchars($oldInput['fats'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
              </div>
            </div>

            <div class="form-group">
              <label class="form-label" for="note">Notes (optionnel)</label>
              <textarea id="note" name="note" class="form-control" placeholder="Ex. sans sauce"><?= htmlspecialchars($oldInput['note'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
            </div>

            <div class="flex-between mt-1 mb-0">
              <a href="journal-alimentaire.php" class="btn btn-outline">Annuler</a>
              <button type="submit" class="btn btn-primary">Enregistrer</button>
            </div>
          </form>
        </section>
      </main>
    </div>
  </div>
</body>
</html>
