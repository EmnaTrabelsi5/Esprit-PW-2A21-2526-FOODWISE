<?php
/**
 * FoodWise — Module FoodLog
 * Vue : Gestion des suivis santé (Back Admin)
 */

require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../controller/SuiviSanteController.php';

if (session_status() === PHP_SESSION_NONE) session_start();
if (empty($_SESSION['admin_id'])) {
    header('Location: /FOODWISE/?route=module2/back/login');
    exit;
}

$pdo        = config::getConnexion();
$controller = new SuiviSanteController($pdo);

$page       = (int) ($_GET['page'] ?? 1);
$limit      = 20;
$offset     = ($page - 1) * $limit;
$allSuivis  = $controller->getAllSuivis();
$totalSuivis= count($allSuivis);
$totalPages = (int) ceil($totalSuivis / $limit);
$suivis     = array_slice($allSuivis, $offset, $limit);

$alertMessage = '';
$alertClass   = 'success';
if (isset($_GET['deleted'])) {
    $alertMessage = $_GET['deleted'] === '1' ? 'Activité supprimée avec succès.' : "Impossible de supprimer l'activité.";
    $alertClass   = $_GET['deleted'] === '1' ? 'success' : 'error';
}

$pageTitle  = 'Gestion Suivi Santé';
$activeNav  = 'foodlog_suivi_admin';
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
.afl-badge { display:inline-flex; align-items:center; justify-content:center; background:#f0e8dc; color:#7a5f3b; border-radius:20px; padding:3px 10px; font-size:11px; font-weight:700; }
.intensite-faible { background:#d1fae5; color:#065f46; border-radius:20px; padding:3px 10px; font-size:11px; font-weight:700; }
.intensite-moyen  { background:#fef3c7; color:#92400e; border-radius:20px; padding:3px 10px; font-size:11px; font-weight:700; }
.intensite-eleve  { background:#fee2e2; color:#991b1b; border-radius:20px; padding:3px 10px; font-size:11px; font-weight:700; }
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
      <h1 class="afl-header__title">🏃 Gestion Suivi Santé</h1>
      <p class="afl-header__sub">Administration des activités physiques et de l'hydratation — <?= $totalSuivis ?> suivi<?= $totalSuivis > 1 ? 's' : '' ?> enregistré<?= $totalSuivis > 1 ? 's' : '' ?></p>
    </div>
    <a href="?route=foodlog/admin/dashboard" class="btn btn-secondary">← Dashboard</a>
  </div>

  <div class="afl-section">
    <div class="afl-section__title">
      <span>Liste des suivis</span>
      <span class="afl-badge"><?= $totalSuivis ?> résultat<?= $totalSuivis > 1 ? 's' : '' ?></span>
    </div>

    <div class="table-wrap">
      <table class="fw-table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Utilisateur</th>
            <th>Date</th>
            <th>Activité</th>
            <th class="num">Durée</th>
            <th>Intensité</th>
            <th class="num">Cal. brûlées</th>
            <th class="num">Eau (L)</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($suivis)): ?>
            <tr><td colspan="9" style="text-align:center;padding:32px;color:#9b7355;">Aucun suivi à afficher.</td></tr>
          <?php else: ?>
            <?php foreach ($suivis as $suivi): ?>
              <tr>
                <td><span style="font-weight:600;color:#9b7355;">#<?= (int) $suivi['id'] ?></span></td>
                <td><span style="background:#f0e8dc;color:#7a5f3b;border-radius:20px;padding:2px 10px;font-size:12px;font-weight:600;">User <?= (int) $suivi['user_id'] ?></span></td>
                <td style="color:#5c3d20;font-weight:500;"><?= htmlspecialchars($suivi['date'], ENT_QUOTES, 'UTF-8') ?></td>
                <td>
                  <strong style="color:#3d2b1f;"><?= htmlspecialchars($suivi['type_activite'], ENT_QUOTES, 'UTF-8') ?></strong>
                  <?php if (!empty($suivi['note'])): ?>
                    <br><small style="color:#9b7355;"><?= htmlspecialchars(substr($suivi['note'], 0, 35), ENT_QUOTES, 'UTF-8') ?>...</small>
                  <?php endif; ?>
                </td>
                <td class="num"><strong><?= (int) $suivi['duree'] ?></strong> <span style="color:#9b7355;font-size:11px;">min</span></td>
                <td>
                  <?php
                    $cls = match($suivi['intensite']) {
                        'faible' => 'intensite-faible',
                        'élevé'  => 'intensite-eleve',
                        default  => 'intensite-moyen',
                    };
                  ?>
                  <span class="<?= $cls ?>"><?= htmlspecialchars(ucfirst($suivi['intensite']), ENT_QUOTES, 'UTF-8') ?></span>
                </td>
                <td class="num"><strong style="color:#e67e22;"><?= number_format((float) $suivi['calories_brulees'], 0) ?></strong></td>
                <td class="num"><strong style="color:#2980b9;"><?= number_format((float) $suivi['quantite_eau'] / 1000, 2) ?></strong></td>
                <td class="actions-cell">
                  <a href="?route=foodlog/modifier-suivi&id=<?= (int) $suivi['id'] ?>" class="btn btn-sm btn-secondary">✏️ Modifier</a>
                  <a href="/FOODWISE/?route=foodlog/supprimer&id=<?= (int) $suivi['id'] ?>&source=suivi&origin=back"
                     class="btn btn-sm btn-danger"
                     onclick="return confirm('Supprimer ce suivi ?')">🗑 Suppr.</a>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <?php if ($totalPages > 1): ?>
      <div style="display:flex;justify-content:center;gap:6px;padding:20px 0 4px;">
        <?php if ($page > 1): ?>
          <a href="?route=foodlog/admin/suivi&page=<?= $page - 1 ?>" class="btn btn-outline btn-sm">← Précédente</a>
        <?php endif; ?>
        <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
          <a href="?route=foodlog/admin/suivi&page=<?= $i ?>"
             class="btn btn-sm <?= $i === $page ? 'btn-primary' : 'btn-outline' ?>"><?= $i ?></a>
        <?php endfor; ?>
        <?php if ($page < $totalPages): ?>
          <a href="?route=foodlog/admin/suivi&page=<?= $page + 1 ?>" class="btn btn-outline btn-sm">Suivante →</a>
        <?php endif; ?>
      </div>
      <p style="text-align:center;font-size:12px;color:#9b7355;padding-bottom:8px;">Page <?= $page ?> / <?= $totalPages ?></p>
    <?php endif; ?>
  </div>

</div>

<?php include __DIR__ . '/../../layouts/back/footer.php'; ?>
