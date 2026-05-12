<?php
/**
 * FoodWise — Module FoodLog
 * Vue : Modifier une entrée alimentaire (Front)
 */

require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../controller/JournalController.php';

if (session_status() === PHP_SESSION_NONE) session_start();
if (empty($_SESSION['user_id'])) {
    header('Location: ?route=module2/front/connexion');
    exit;
}

$errors   = $_SESSION['form_errors'] ?? [];
$oldInput = $_SESSION['form_old'] ?? [];
unset($_SESSION['form_errors'], $_SESSION['form_old']);

$pdo        = config::getConnexion();
$controller = new JournalController($pdo);
$id         = (int) ($_GET['id'] ?? 0);

if ($id <= 0) {
    header('Location: ?route=foodlog/journal');
    exit;
}

$entry = $controller->getEntry($id);
if ($entry === null) {
    header('Location: ?route=foodlog/journal');
    exit;
}

$pageTitle  = 'Modifier une entrée';
$activeNav  = 'foodlog_journal';
$backoffice = false;
include __DIR__ . '/../../layouts/front/header.php';
?>

<h1 class="page-title">✏️ Modifier une entrée</h1>
<p class="page-subtitle">Édition de l'entrée enregistrée.</p>

<section class="card">
  <div class="card-header">
    <h2 class="card-title">Édition — <?= htmlspecialchars($entry['meal_type'], ENT_QUOTES, 'UTF-8') ?></h2>
    <span class="card-meta"><?= htmlspecialchars($entry['date'], ENT_QUOTES, 'UTF-8') ?></span>
  </div>

  <form id="entry-form" action="?route=foodlog/modifier-entree&id=<?= (int) $entry['id'] ?>" method="post">
    <input type="hidden" name="id" value="<?= (int) $entry['id'] ?>">
    <input type="hidden" name="source" value="front">

    <?php if (!empty($errors)): ?>
      <div class="flash flash-error" style="margin-bottom:18px;">
        <ul style="margin:0;padding-left:18px;">
          <?php foreach ($errors as $error): ?>
            <li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <div class="form-row">
      <div class="form-group mb-0">
        <label class="form-label" for="date">Date</label>
        <input type="date" id="date" name="date" class="form-control"
               value="<?= htmlspecialchars($oldInput['date'] ?? $entry['date'], ENT_QUOTES, 'UTF-8') ?>" required>
      </div>
      <div class="form-group mb-0">
        <label class="form-label" for="time">Heure</label>
        <input type="time" id="time" name="time" class="form-control"
               value="<?= htmlspecialchars($oldInput['time'] ?? ($entry['time'] ?: ''), ENT_QUOTES, 'UTF-8') ?>">
      </div>
      <div class="form-group mb-0">
        <label class="form-label" for="meal_type">Type de repas</label>
        <select id="meal_type" name="meal_type" class="form-control" required>
          <?php foreach (['Petit-déjeuner', 'Déjeuner', 'Collation', 'Dîner'] as $mt): ?>
            <option value="<?= htmlspecialchars($mt, ENT_QUOTES, 'UTF-8') ?>"
              <?= ($oldInput['meal_type'] ?? $entry['meal_type']) === $mt ? 'selected' : '' ?>>
              <?= htmlspecialchars($mt, ENT_QUOTES, 'UTF-8') ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>

    <div class="form-group">
      <label class="form-label" for="food">Aliment / plat</label>
      <input type="text" id="food" name="food" class="form-control" required
             value="<?= htmlspecialchars($oldInput['food'] ?? $entry['food'], ENT_QUOTES, 'UTF-8') ?>">
    </div>

    <div class="form-row">
      <div class="form-group mb-0">
        <label class="form-label" for="quantity">Quantité</label>
        <input type="text" id="quantity" name="quantity" class="form-control" required
               pattern="^[0-9]+(?:[.,][0-9]+)?\s*[a-zA-Z%°]*$"
               value="<?= htmlspecialchars($oldInput['quantity'] ?? $entry['quantity'], ENT_QUOTES, 'UTF-8') ?>">
      </div>
      <div class="form-group mb-0">
        <label class="form-label" for="calories">Calories (kcal)</label>
        <input type="number" id="calories" name="calories" class="form-control" min="0" step="1" required
               value="<?= htmlspecialchars($oldInput['calories'] ?? (string) $entry['calories'], ENT_QUOTES, 'UTF-8') ?>">
      </div>
    </div>

    <div class="form-row">
      <div class="form-group mb-0">
        <label class="form-label" for="proteins">Protéines (g)</label>
        <input type="number" id="proteins" name="proteins" class="form-control" step="0.1" min="0" required
               value="<?= htmlspecialchars($oldInput['proteins'] ?? (string) $entry['proteins'], ENT_QUOTES, 'UTF-8') ?>">
      </div>
      <div class="form-group mb-0">
        <label class="form-label" for="carbs">Glucides (g)</label>
        <input type="number" id="carbs" name="carbs" class="form-control" step="0.1" min="0" required
               value="<?= htmlspecialchars($oldInput['carbs'] ?? (string) $entry['carbs'], ENT_QUOTES, 'UTF-8') ?>">
      </div>
      <div class="form-group mb-0">
        <label class="form-label" for="fats">Lipides (g)</label>
        <input type="number" id="fats" name="fats" class="form-control" step="0.1" min="0" required
               value="<?= htmlspecialchars($oldInput['fats'] ?? (string) $entry['fats'], ENT_QUOTES, 'UTF-8') ?>">
      </div>
    </div>

    <div class="form-group">
      <label class="form-label" for="note">Notes</label>
      <textarea id="note" name="note" class="form-control"><?= htmlspecialchars($oldInput['note'] ?? ($entry['note'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
    </div>

    <div class="flex-between mt-1 mb-0">
      <a href="?route=foodlog/journal" class="btn btn-outline">Retour au journal</a>
      <div style="display:flex;gap:8px;">
        <a href="?route=foodlog/supprimer&id=<?= (int) $entry['id'] ?>&source=front"
           class="btn btn-danger btn-sm"
           onclick="return confirm('Supprimer cette entrée ?')">Supprimer</a>
        <button type="submit" class="btn btn-primary">Mettre à jour</button>
      </div>
    </div>
  </form>
</section>

<?php include __DIR__ . '/../../layouts/front/footer.php'; ?>

