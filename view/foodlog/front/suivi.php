<?php
/**
 * FoodWise — Module FoodLog
 * Vue : Suivi Santé (Front)
 */

require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../controller/SuiviSanteController.php';

if (session_status() === PHP_SESSION_NONE) session_start();
if (empty($_SESSION['user_id'])) {
    header('Location: /FOODWISE/?route=module2/front/connexion');
    exit;
}

$pdo        = config::getConnexion();
$controller = new SuiviSanteController($pdo);
$userId     = (int) $_SESSION['user_id'];
$today      = date('Y-m-d');

$sort          = $_GET['sort'] ?? 'date_desc';
$sortDirection = $sort === 'date_asc' ? 'ASC' : 'DESC';
$suivis        = $controller->getSuivisByUser($userId, $sortDirection);
$report        = $controller->getDailyHealthReport($userId, $today, null);

$alertMessage = '';
$alertClass   = 'success';
if (isset($_GET['created'])) {
    $alertMessage = $_GET['created'] === '1' ? 'Activité ajoutée avec succès.' : "Impossible d'ajouter l'activité.";
    $alertClass   = $_GET['created'] === '1' ? 'success' : 'error';
} elseif (isset($_GET['updated'])) {
    $alertMessage = $_GET['updated'] === '1' ? 'Activité modifiée avec succès.' : "Impossible de modifier l'activité.";
    $alertClass   = $_GET['updated'] === '1' ? 'success' : 'error';
} elseif (isset($_GET['deleted'])) {
    $alertMessage = $_GET['deleted'] === '1' ? 'Activité supprimée avec succès.' : "Impossible de supprimer l'activité.";
    $alertClass   = $_GET['deleted'] === '1' ? 'success' : 'error';
}

$pageTitle  = 'Suivi Santé';
$activeNav  = 'foodlog_suivi';
$backoffice = false;
include __DIR__ . '/../../layouts/front/header.php';
?>

<?php if ($alertMessage): ?>
  <div class="flash flash-<?= $alertClass === 'success' ? 'success' : 'error' ?>" id="flash-msg">
    <?= htmlspecialchars($alertMessage, ENT_QUOTES, 'UTF-8') ?>
    <button class="flash-close" onclick="this.parentElement.remove()">×</button>
  </div>
<?php endif; ?>

<style>
.fl-header { margin-bottom: 24px; }
.fl-header h1 { font-size: 1.6rem; font-weight: 700; color: #3d2b1f; margin: 0 0 4px; display:flex; align-items:center; gap:10px; }
.fl-header p  { color: #9b7355; font-size: 14px; margin: 0; }
.fl-cards { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 24px; }
.fl-card { background: #fff; border-radius: 14px; padding: 20px 22px; box-shadow: 0 2px 8px rgba(78,44,14,.08); border-left: 4px solid #a0522d; display: flex; flex-direction: column; gap: 4px; }
.fl-card__icon  { font-size: 1.4rem; margin-bottom: 4px; }
.fl-card__label { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .08em; color: #9b7355; }
.fl-card__value { font-size: 2rem; font-weight: 800; color: #3d2b1f; line-height: 1.1; }
.fl-card__hint  { font-size: 12px; color: #b0a090; margin-top: 2px; }
.fl-card--green  { border-left-color: #27ae60; }
.fl-card--blue   { border-left-color: #2980b9; }
.fl-card--orange { border-left-color: #e67e22; }
.fl-card--red    { border-left-color: #e74c3c; }
.fl-toolbar { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; margin-bottom: 20px; padding: 14px 18px; background: #fff; border-radius: 12px; box-shadow: 0 1px 4px rgba(78,44,14,.06); }
.fl-toolbar__left  { display: flex; align-items: center; gap: 10px; font-size: 13px; color: #7a5f3b; }
.fl-toolbar__right { display: flex; gap: 10px; }
</style>

<div class="fl-header">
  <h1>🏃 Suivi Santé</h1>
  <p>Suivi des activités physiques et de l'hydratation — bilan du jour</p>
</div>

<div class="fl-cards">
  <div class="fl-card fl-card--orange">
    <span class="fl-card__icon">🔥</span>
    <span class="fl-card__label">Calories Brûlées</span>
    <span class="fl-card__value"><?= htmlspecialchars((string)($report['total_calories_burned'] ?? 0), ENT_QUOTES, 'UTF-8') ?></span>
    <span class="fl-card__hint"><?= ($report['suivis_count'] ?? 0) ?> activité<?= ($report['suivis_count'] ?? 0) > 1 ? 's' : '' ?></span>
  </div>
  <div class="fl-card fl-card--blue">
    <span class="fl-card__icon">💧</span>
    <span class="fl-card__label">Eau Consommée</span>
    <span class="fl-card__value"><?= number_format(($report['total_water'] ?? 0) / 1000, 2, ',', ' ') ?>L</span>
    <span class="fl-card__hint"><?= (($report['total_water'] ?? 0) >= 2000) ? '✓ Objectif atteint' : number_format((($report['total_water'] ?? 0) / 2000) * 100, 0) . '% objectif' ?></span>
  </div>
  <div class="fl-card <?= ($report['balance'] ?? 0) > 0 ? 'fl-card--red' : 'fl-card--green' ?>">
    <span class="fl-card__icon"><?= ($report['balance'] ?? 0) > 0 ? '📈' : '📉' ?></span>
    <span class="fl-card__label">Bilan Calorique</span>
    <span class="fl-card__value"><?= ($report['balance'] ?? 0) >= 0 ? '+' : '' ?><?= htmlspecialchars((string)($report['balance'] ?? 0), ENT_QUOTES, 'UTF-8') ?></span>
    <span class="fl-card__hint"><?= ($report['balance'] ?? 0) > 0 ? 'Surplus' : 'Déficit' ?></span>
  </div>
  <div class="fl-card fl-card--green">
    <span class="fl-card__icon">⏱️</span>
    <span class="fl-card__label">Durée Activité</span>
    <span class="fl-card__value"><?= (int)($report['total_activity_duration'] ?? 0) ?> <small style="font-size:1rem">min</small></span>
    <span class="fl-card__hint">Aujourd'hui</span>
  </div>
</div>

<div class="fl-toolbar">
  <div class="fl-toolbar__left">
    <span>Trier par date :</span>
    <a href="?route=foodlog/suivi&sort=<?= $sortDirection === 'ASC' ? 'date_desc' : 'date_asc' ?>" class="btn btn-secondary btn-sm">
      <?= $sortDirection === 'ASC' ? '↓ Date décroissante' : '↑ Date croissante' ?>
    </a>
  </div>
  <div class="fl-toolbar__right">
    <a href="?route=foodlog/ajouter-suivi" class="btn btn-primary">+ Ajouter une activité</a>
    <a href="?route=foodlog/resume" class="btn btn-secondary">📊 Voir le résumé</a>
  </div>
</div>

<section class="card mb-0">
  <div class="table-wrap">
    <table class="fw-table">
      <thead>
        <tr>
          <th>Date</th>
          <th>Type d'activité</th>
          <th class="num">Durée (min)</th>
          <th>Intensité</th>
          <th class="num">Cal. brûlées</th>
          <th class="num">Eau (mL)</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($suivis)): ?>
          <tr>
            <td colspan="7" class="text-muted">Aucune activité enregistrée pour le moment.</td>
          </tr>
        <?php else: ?>
          <?php foreach ($suivis as $suivi): ?>
            <tr>
              <td><?= htmlspecialchars($suivi['date'], ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= htmlspecialchars($suivi['type_activite'], ENT_QUOTES, 'UTF-8') ?></td>
              <td class="num"><?= (int) $suivi['duree'] ?></td>
              <td>
                <?php
                $badgeClass = match($suivi['intensite']) {
                    'faible' => 'badge-vert',
                    'élevé'  => 'badge-rouge',
                    default  => 'badge-brun',
                };
                ?>
                <span class="badge <?= $badgeClass ?>"><?= htmlspecialchars(ucfirst($suivi['intensite']), ENT_QUOTES, 'UTF-8') ?></span>
              </td>
              <td class="num"><?= htmlspecialchars((string) $suivi['calories_brulees'], ENT_QUOTES, 'UTF-8') ?></td>
              <td class="num"><?= htmlspecialchars((string) $suivi['quantite_eau'], ENT_QUOTES, 'UTF-8') ?></td>
              <td class="actions-cell">
                <a href="?route=foodlog/modifier-suivi&id=<?= (int) $suivi['id'] ?>" class="btn btn-sm btn-secondary">Modifier</a>
                <a href="/FOODWISE/?route=foodlog/supprimer&id=<?= (int) $suivi['id'] ?>&source=suivi&origin=front"
                   class="btn btn-sm btn-danger"
                   onclick="return confirm('Supprimer cette activité ?')">Suppr.</a>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</section>

<?php include __DIR__ . '/../../layouts/front/footer.php'; ?>
