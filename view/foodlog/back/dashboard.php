<?php
/**
 * FoodWise — Module FoodLog
 * Vue : Dashboard Admin FoodLog (Back)
 */

require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../controller/JournalController.php';
require_once __DIR__ . '/../../../controller/SuiviSanteController.php';
require_once __DIR__ . '/../../../controller/ResumeController.php';

if (session_status() === PHP_SESSION_NONE) session_start();
if (empty($_SESSION['admin_id'])) {
    header('Location: ?route=module2/back/login');
    exit;
}

$pdo            = config::getConnexion();
$journalCtrl    = new JournalController($pdo);
$suiviCtrl      = new SuiviSanteController($pdo);
$resumeCtrl     = new ResumeController($pdo);

$summary        = $journalCtrl->getGlobalSummary();
$suiviTotal     = $suiviCtrl->countSuivis();
$suiviStats     = $suiviCtrl->getGlobalStats();
$avgDailyWater  = round($suiviStats['days_tracked'] > 0 ? $suiviStats['total_water'] / $suiviStats['days_tracked'] : 0);
$waterObjective = $resumeCtrl->getObjectifs(0)['eau'] ?? 2000;
$latestEntries  = array_slice($journalCtrl->listEntriesForAdmin(), 0, 5);

$hwPct = $waterObjective > 0 ? min(100, round($avgDailyWater / $waterObjective * 100)) : 0;
if ($hwPct >= 90)     { $hwStatusLabel = 'Optimal'; $hwStatusColor = '#10b981'; }
elseif ($hwPct >= 60) { $hwStatusLabel = 'Moyen';   $hwStatusColor = '#f59e0b'; }
else                  { $hwStatusLabel = 'Faible';  $hwStatusColor = '#ef4444'; }

$trendCalorieFill = min(100, round($summary['todayCalories'] / 2500 * 100));

$pageTitle  = 'Dashboard FoodLog';
$activeNav  = 'foodlog_admin';
$backoffice = true;
include __DIR__ . '/../../layouts/back/header.php';
?>

<style>
/* ── Admin FoodLog shared styles ── */
.afl-cards { display:grid; grid-template-columns:repeat(4,1fr); gap:18px; margin-bottom:28px; }
.afl-card  { background:#fff; border-radius:14px; padding:22px 24px; box-shadow:0 2px 10px rgba(78,44,14,.08); border-top:4px solid #a0522d; }
.afl-card__icon  { font-size:1.6rem; margin-bottom:8px; display:block; }
.afl-card__label { font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.08em; color:#9b7355; margin-bottom:4px; }
.afl-card__value { font-size:2.2rem; font-weight:800; color:#3d2b1f; line-height:1; margin-bottom:4px; }
.afl-card__hint  { font-size:12px; color:#b0a090; }
.afl-card--green  { border-top-color:#27ae60; }
.afl-card--blue   { border-top-color:#2980b9; }
.afl-card--orange { border-top-color:#e67e22; }
.afl-card--red    { border-top-color:#e74c3c; }

.afl-header { display:flex; align-items:flex-start; justify-content:space-between; flex-wrap:wrap; gap:16px; margin-bottom:28px; padding-bottom:20px; border-bottom:2px solid #f0e8dc; }
.afl-header__title { font-size:1.5rem; font-weight:800; color:#3d2b1f; margin:0 0 4px; }
.afl-header__sub   { font-size:13px; color:#9b7355; margin:0; }
.afl-header__label { font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:.1em; color:#c8a882; margin:0 0 6px; }

.afl-grid2 { display:grid; grid-template-columns:1fr 1fr; gap:24px; margin-bottom:24px; }
.afl-section { background:#fff; border-radius:14px; padding:24px; box-shadow:0 2px 10px rgba(78,44,14,.08); }
.afl-section__title { font-size:1rem; font-weight:700; color:#3d2b1f; margin:0 0 16px; display:flex; align-items:center; gap:8px; }
.afl-mini-grid { display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:16px; }
.afl-mini-card { background:#faf8f5; border-radius:10px; padding:14px; text-align:center; }
.afl-mini-card__label { font-size:11px; color:#9b7355; margin-bottom:4px; }
.afl-mini-card__value { font-size:1.6rem; font-weight:800; color:#4e2c0e; }
.afl-mini-card__hint  { font-size:11px; color:#b0a090; margin-top:2px; }
.afl-bar-wrap { margin-bottom:14px; }
.afl-bar-label { display:flex; justify-content:space-between; font-size:12px; color:#7a5f3b; margin-bottom:5px; }
.afl-bar { background:#f0e8dc; border-radius:8px; height:10px; overflow:hidden; }
.afl-bar-fill { height:100%; border-radius:8px; transition:width .6s; }
</style>

<div class="page-body">

  <div class="afl-header">
    <div>
      <p class="afl-header__label">Tableau de bord</p>
      <h1 class="afl-header__title">📊 Insights Admin FoodLog</h1>
      <p class="afl-header__sub">Suivi en temps réel des utilisateurs, entrées et suivis santé.</p>
    </div>
    <div style="display:flex;gap:10px;flex-wrap:wrap;">
      <a href="?route=foodlog/admin/entries" class="btn btn-primary">📋 Entrées utilisateurs</a>
      <a href="?route=foodlog/admin/suivi" class="btn btn-secondary">🏃 Suivi santé</a>
    </div>
  </div>

  <!-- Cartes stats -->
  <div class="afl-cards">
    <div class="afl-card">
      <span class="afl-card__icon">📝</span>
      <div class="afl-card__label">Entrées totales</div>
      <div class="afl-card__value"><?= $summary['entries'] ?></div>
      <div class="afl-card__hint">Toutes les entrées</div>
    </div>
    <div class="afl-card afl-card--green">
      <span class="afl-card__icon">👥</span>
      <div class="afl-card__label">Utilisateurs</div>
      <div class="afl-card__value"><?= $summary['users'] ?></div>
      <div class="afl-card__hint">Comptes distincts</div>
    </div>
    <div class="afl-card afl-card--orange">
      <span class="afl-card__icon">🏃</span>
      <div class="afl-card__label">Suivis santé</div>
      <div class="afl-card__value"><?= $suiviTotal ?></div>
      <div class="afl-card__hint">Suivis enregistrés</div>
    </div>
    <div class="afl-card afl-card--red">
      <span class="afl-card__icon">🔥</span>
      <div class="afl-card__label">Calories aujourd'hui</div>
      <div class="afl-card__value"><?= $summary['todayCalories'] ?></div>
      <div class="afl-card__hint">Total plateforme</div>
    </div>
  </div>

  <div class="afl-grid2">

    <!-- Vue opérationnelle -->
    <div class="afl-section">
      <h2 class="afl-section__title">⚡ Vue opérationnelle <span style="font-size:11px;color:#9b7355;font-weight:400;">Indicateurs clés</span></h2>
      <div class="afl-mini-grid">
        <div class="afl-mini-card">
          <div class="afl-mini-card__label">Entrées/jour</div>
          <div class="afl-mini-card__value"><?= round($summary['entries'] / max(1, 7), 0) ?></div>
          <div class="afl-mini-card__hint">Moyenne 7 jours</div>
        </div>
        <div class="afl-mini-card">
          <div class="afl-mini-card__label">Suivis santé</div>
          <div class="afl-mini-card__value"><?= $suiviTotal ?></div>
          <div class="afl-mini-card__hint">Total enregistrés</div>
        </div>
        <div class="afl-mini-card">
          <div class="afl-mini-card__label">Calories/jour</div>
          <div class="afl-mini-card__value"><?= $summary['todayCalories'] ?></div>
          <div class="afl-mini-card__hint">Relevé journalier</div>
        </div>
        <div class="afl-mini-card">
          <div class="afl-mini-card__label">Hydratation</div>
          <div class="afl-mini-card__value"><?= $hwPct ?>%</div>
          <div class="afl-mini-card__hint">Objectif moyen</div>
        </div>
      </div>
      <div class="afl-bar-wrap">
        <div class="afl-bar-label"><span>Objectif calories journalier</span><strong><?= $trendCalorieFill ?>%</strong></div>
        <div class="afl-bar"><div class="afl-bar-fill" style="width:<?= $trendCalorieFill ?>%;background:linear-gradient(90deg,#a0522d,#e74c3c);"></div></div>
      </div>
      <div style="display:flex;gap:8px;margin-top:16px;">
        <a href="?route=foodlog/admin/entries" class="btn btn-primary btn-sm">📋 Entrées</a>
        <a href="?route=foodlog/admin/suivi" class="btn btn-secondary btn-sm">🏃 Suivi</a>
      </div>
    </div>

    <!-- Hydratation -->
    <div class="afl-section">
      <h2 class="afl-section__title">💧 Hydratation
        <span style="margin-left:auto;background:<?= $hwStatusColor ?>18;color:<?= $hwStatusColor ?>;padding:3px 12px;border-radius:20px;font-size:11px;font-weight:700;"><?= $hwStatusLabel ?></span>
      </h2>
      <div style="text-align:center;margin-bottom:16px;">
        <div style="font-size:2.8rem;font-weight:800;color:#2980b9;"><?= number_format($avgDailyWater) ?> <span style="font-size:1.2rem;color:#9b7355;">mL</span></div>
        <div style="font-size:13px;color:#9b7355;margin-top:4px;">Consommation moyenne journalière</div>
      </div>
      <div class="afl-bar-wrap">
        <div class="afl-bar-label"><span>0 mL</span><span><?= number_format($waterObjective) ?> mL</span></div>
        <div class="afl-bar" style="height:14px;"><div class="afl-bar-fill" style="width:<?= $hwPct ?>%;background:<?= $hwStatusColor ?>;"></div></div>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-top:16px;">
        <div class="afl-mini-card">
          <div class="afl-mini-card__label">Taux atteinte</div>
          <div class="afl-mini-card__value" style="color:#0ea5e9;"><?= $hwPct ?>%</div>
        </div>
        <div class="afl-mini-card">
          <div class="afl-mini-card__label">Restant</div>
          <div class="afl-mini-card__value" style="color:#f59e0b;"><?= max(0, number_format($waterObjective - $avgDailyWater)) ?></div>
          <div class="afl-mini-card__hint">mL</div>
        </div>
      </div>
    </div>
  </div>

  <!-- Dernières entrées -->
  <div class="afl-section">
    <h2 class="afl-section__title">📋 Dernières entrées <span style="font-size:11px;color:#9b7355;font-weight:400;">5 dernières mises à jour</span></h2>
    <div class="table-wrap">
      <table class="fw-table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Utilisateur</th>
            <th>Date</th>
            <th>Repas</th>
            <th>Aliment</th>
            <th class="num">kcal</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($latestEntries)): ?>
            <tr><td colspan="6" class="text-muted" style="text-align:center;padding:24px;">Aucune entrée disponible.</td></tr>
          <?php else: ?>
            <?php foreach ($latestEntries as $entry): ?>
              <tr>
                <td><span style="font-weight:600;color:#9b7355;">#<?= (int) $entry['id'] ?></span></td>
                <td>Utilisateur <?= (int) $entry['user_id'] ?></td>
                <td><?= htmlspecialchars($entry['date'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><span class="badge badge-brun"><?= htmlspecialchars($entry['meal_type'], ENT_QUOTES, 'UTF-8') ?></span></td>
                <td><?= htmlspecialchars($entry['food'], ENT_QUOTES, 'UTF-8') ?></td>
                <td class="num"><strong><?= (int) $entry['calories'] ?></strong></td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
    <div style="margin-top:12px;">
      <a href="?route=foodlog/admin/entries" class="btn btn-outline btn-sm">Voir toutes les entrées →</a>
    </div>
  </div>

</div>

<?php include __DIR__ . '/../../layouts/back/footer.php'; ?>

