<?php
/**
 * FoodWise — Module FoodLog
 * Vue : Liste des entrées (Back Admin)
 */

require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../controller/JournalController.php';

if (session_status() === PHP_SESSION_NONE) session_start();
if (empty($_SESSION['admin_id'])) {
    header('Location: ?route=module2/back/login');
    exit;
}

$pdo        = config::getConnexion();
$controller = new JournalController($pdo);
$entries    = $controller->listEntriesForAdmin();

$alertMessage = '';
$alertClass   = 'success';
if (isset($_GET['deleted'])) {
    $alertMessage = $_GET['deleted'] === '1' ? 'Entrée supprimée avec succès.' : "Impossible de supprimer l'entrée.";
    $alertClass   = $_GET['deleted'] === '1' ? 'success' : 'error';
}

$pageTitle  = 'Entrées utilisateurs';
$activeNav  = 'foodlog_entries';
$backoffice = true;
include __DIR__ . '/../../layouts/back/header.php';
?>

<style>
.afl-header { display:flex; align-items:flex-start; justify-content:space-between; flex-wrap:wrap; gap:16px; margin-bottom:28px; padding-bottom:20px; border-bottom:2px solid #f0e8dc; }
.afl-header__title { font-size:1.5rem; font-weight:800; color:#3d2b1f; margin:0 0 4px; }
.afl-header__sub   { font-size:13px; color:#9b7355; margin:0; }
.afl-header__label { font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:.1em; color:#c8a882; margin:0 0 6px; }
.afl-section { background:#fff; border-radius:14px; padding:24px; box-shadow:0 2px 10px rgba(78,44,14,.08); margin-bottom:24px; }
.afl-section__title { font-size:1rem; font-weight:700; color:#3d2b1f; margin:0 0 16px; display:flex; align-items:center; justify-content:space-between; gap:8px; }
.afl-filter { display:flex; gap:10px; flex-wrap:wrap; align-items:center; margin-bottom:20px; padding:14px 16px; background:#faf8f5; border-radius:10px; }
.afl-filter input { padding:8px 12px; border:1.5px solid #e8dcc8; border-radius:8px; font-size:13px; color:#3d2b1f; background:#fff; outline:none; }
.afl-filter input:focus { border-color:#a0522d; }
.afl-badge { display:inline-flex; align-items:center; justify-content:center; background:#f0e8dc; color:#7a5f3b; border-radius:20px; padding:3px 10px; font-size:11px; font-weight:700; }
</style>

<div class="page-body">

  <?php if ($alertMessage): ?>
    <div class="flash flash-<?= $alertClass === 'success' ? 'success' : 'error' ?>" id="flash-msg">
      <?= htmlspecialchars($alertMessage, ENT_QUOTES, 'UTF-8') ?>
      <button class="flash-close" onclick="this.parentElement.remove()">×</button>
    </div>
  <?php endif; ?>

  <div class="afl-header">
    <div>
      <p class="afl-header__label">FoodLog Admin</p>
      <h1 class="afl-header__title">📋 Entrées utilisateurs</h1>
      <p class="afl-header__sub">Liste dynamique des entrées alimentaires — <?= count($entries) ?> entrée<?= count($entries) > 1 ? 's' : '' ?> enregistrée<?= count($entries) > 1 ? 's' : '' ?></p>
    </div>
    <a href="?route=foodlog/admin/dashboard" class="btn btn-secondary">← Dashboard</a>
  </div>

  <div class="afl-section">
    <div class="afl-section__title">
      <span>Toutes les entrées</span>
      <span class="afl-badge"><?= count($entries) ?> résultat<?= count($entries) > 1 ? 's' : '' ?></span>
    </div>

    <div class="afl-filter">
      <input type="text" id="filterUser" placeholder="🔍 Filtrer par utilisateur (ID)...">
      <input type="date" id="filterDate">
      <button type="button" class="btn btn-primary btn-sm" onclick="filterTable()">Filtrer</button>
      <button type="button" class="btn btn-outline btn-sm" onclick="resetFilter()">Réinitialiser</button>
    </div>

    <div class="table-wrap">
      <table class="fw-table" id="entriesTable">
        <thead>
          <tr>
            <th>ID</th>
            <th>Utilisateur</th>
            <th>Date</th>
            <th>Repas</th>
            <th>Aliment</th>
            <th>Quantité</th>
            <th class="num">kcal</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($entries)): ?>
            <tr><td colspan="8" style="text-align:center;padding:32px;color:#9b7355;">Aucune entrée enregistrée.</td></tr>
          <?php else: ?>
            <?php foreach ($entries as $entry): ?>
              <tr data-user="<?= (int) $entry['user_id'] ?>" data-date="<?= htmlspecialchars($entry['date'], ENT_QUOTES, 'UTF-8') ?>">
                <td><span style="font-weight:600;color:#9b7355;">#<?= (int) $entry['id'] ?></span></td>
                <td><span style="background:#f0e8dc;color:#7a5f3b;border-radius:20px;padding:2px 10px;font-size:12px;font-weight:600;">User <?= (int) $entry['user_id'] ?></span></td>
                <td style="color:#5c3d20;font-weight:500;"><?= htmlspecialchars($entry['date'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><span class="badge badge-brun"><?= htmlspecialchars($entry['meal_type'], ENT_QUOTES, 'UTF-8') ?></span></td>
                <td style="font-weight:500;"><?= htmlspecialchars($entry['food'], ENT_QUOTES, 'UTF-8') ?></td>
                <td style="color:#7a5f3b;"><?= htmlspecialchars($entry['quantity'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                <td class="num"><strong style="color:#c0392b;"><?= (int) $entry['calories'] ?></strong></td>
                <td class="actions-cell">
                  <a href="?route=foodlog/modifier-entree&id=<?= (int) $entry['id'] ?>" class="btn btn-sm btn-secondary">✏️ Modifier</a>
                  <a href="?route=foodlog/supprimer&id=<?= (int) $entry['id'] ?>&source=back"
                     class="btn btn-sm btn-danger"
                     onclick="return confirm('Supprimer cette entrée ?')">🗑 Suppr.</a>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

</div>

<script>
function filterTable() {
  var user = document.getElementById('filterUser').value.trim().toLowerCase();
  var date = document.getElementById('filterDate').value;
  document.querySelectorAll('#entriesTable tbody tr').forEach(function(row) {
    var rowUser = (row.dataset.user || '').toLowerCase();
    var rowDate = row.dataset.date || '';
    var show = (!user || rowUser.includes(user)) && (!date || rowDate === date);
    row.style.display = show ? '' : 'none';
  });
}
function resetFilter() {
  document.getElementById('filterUser').value = '';
  document.getElementById('filterDate').value = '';
  document.querySelectorAll('#entriesTable tbody tr').forEach(function(row) { row.style.display = ''; });
}
</script>

<?php include __DIR__ . '/../../layouts/back/footer.php'; ?>

