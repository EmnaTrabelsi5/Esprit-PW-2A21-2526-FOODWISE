<?php
/**
 * FoodWise — Module FoodLog
 * Vue : Résumé du jour (Front)
 */

require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../controller/JournalController.php';
require_once __DIR__ . '/../../../controller/SuiviSanteController.php';

if (session_status() === PHP_SESSION_NONE) session_start();
if (empty($_SESSION['user_id'])) {
    header('Location: ?route=module2/front/connexion');
    exit;
}

$pdo              = config::getConnexion();
$journalCtrl      = new JournalController($pdo);
$suiviCtrl        = new SuiviSanteController($pdo);
$userId           = (int) $_SESSION['user_id'];
$today            = date('Y-m-d');

$journalEntries        = $journalCtrl->listEntriesForUser($userId);
$todayEntries          = array_filter($journalEntries, fn($e) => $e['date'] === $today);
$totalCaloriesConsumed = $journalCtrl->getDailyCalories($userId, $today);
$journalStreak         = $journalCtrl->getStreak($userId);
$macros                = $journalCtrl->getMacroTotals($userId, $today);
$history7Days          = $journalCtrl->getLast7DaysSummary($userId);

$report               = $suiviCtrl->getDailyHealthReport($userId, $today, $journalCtrl);
$dailySuivis          = $suiviCtrl->getDailySuivis($userId, $today);
$totalCaloriesBurned  = $suiviCtrl->getDailyCaloriesBurned($userId, $today);
$totalWater           = $suiviCtrl->getDailyWaterIntake($userId, $today);
$totalActivityDuration= $suiviCtrl->getDailyActivityDuration($userId, $today);

$calorieBalance  = $totalCaloriesConsumed - $totalCaloriesBurned;
$waterProgress   = min(100, ($totalWater / 2000) * 100);
$activityProgress= min(100, ($totalActivityDuration / 60) * 100);

$pageTitle  = 'Résumé du jour';
$activeNav  = 'foodlog_resume';
$backoffice = false;
include __DIR__ . '/../../layouts/front/header.php';
?>

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
.fl-card--purple { border-left-color: #8e44ad; }
</style>

<div class="fl-header">
  <h1>📊 Résumé du jour</h1>
  <p>Vue d'ensemble complète — <?= date('d/m/Y') ?></p>
</div>

<div class="fl-cards">
  <div class="fl-card fl-card--red">
    <span class="fl-card__icon">🍽️</span>
    <span class="fl-card__label">Calories Consommées</span>
    <span class="fl-card__value"><?= number_format((int)$totalCaloriesConsumed, 0, ',', ' ') ?></span>
    <span class="fl-card__hint"><?= count($todayEntries) ?> entrée<?= count($todayEntries) > 1 ? 's' : '' ?> aujourd'hui</span>
  </div>
  <div class="fl-card fl-card--green">
    <span class="fl-card__icon">🔥</span>
    <span class="fl-card__label">Calories Brûlées</span>
    <span class="fl-card__value"><?= number_format((int)$totalCaloriesBurned, 0, ',', ' ') ?></span>
    <span class="fl-card__hint"><?= (int)$totalActivityDuration ?> min d'activité</span>
  </div>
  <div class="fl-card fl-card--blue">
    <span class="fl-card__icon">💧</span>
    <span class="fl-card__label">Eau Consommée</span>
    <span class="fl-card__value"><?= number_format($totalWater / 1000, 2, ',', ' ') ?>L</span>
    <span class="fl-card__hint"><?= round($waterProgress) ?>% de l'objectif</span>
  </div>
  <div class="fl-card fl-card--purple">
    <span class="fl-card__icon">⚡</span>
    <span class="fl-card__label">Streak</span>
    <span class="fl-card__value"><?= (int)$journalStreak ?> j.</span>
    <span class="fl-card__hint">Jours consécutifs</span>
  </div>
</div>

<!-- Graphiques -->
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(380px,1fr));gap:24px;margin-bottom:28px;">

  <!-- Bilan calorique -->
  <div class="card" style="padding:24px;">
    <h2 style="font-size:1.2em;font-weight:600;color:#6d4c1b;margin:0 0 16px;display:flex;align-items:center;gap:8px;">
      🔥 Bilan Calorique
    </h2>
    <div style="position:relative;height:240px;">
      <canvas id="caloriesChart"></canvas>
    </div>
    <div style="margin-top:16px;padding-top:16px;border-top:1px solid #eee;display:grid;grid-template-columns:1fr 1fr;gap:16px;text-align:center;">
      <div>
        <div style="font-size:.85em;color:#999;margin-bottom:4px;">Bilan Net</div>
        <div style="font-size:1.8em;font-weight:bold;color:<?= $calorieBalance >= 0 ? '#c0392b' : '#27ae60' ?>;">
          <?= $calorieBalance >= 0 ? '+' : '' ?><?= number_format((int)$calorieBalance, 0, ',', ' ') ?>
        </div>
        <div style="font-size:.8em;color:#999;"><?= $calorieBalance >= 0 ? 'Surplus' : 'Déficit' ?></div>
      </div>
      <div>
        <div style="font-size:.85em;color:#999;margin-bottom:4px;">Objectif Jour</div>
        <div style="font-size:1.8em;font-weight:bold;color:#6d4c1b;">1800</div>
        <div style="font-size:.8em;color:#999;">kcal</div>
      </div>
    </div>
  </div>

  <!-- Hydratation -->
  <div class="card" style="padding:24px;">
    <h2 style="font-size:1.2em;font-weight:600;color:#2980b9;margin:0 0 16px;display:flex;align-items:center;gap:8px;">
      💧 Hydratation
    </h2>
    <div style="position:relative;height:240px;display:flex;align-items:center;justify-content:center;">
      <canvas id="waterChart"></canvas>
    </div>
    <div style="margin-top:16px;padding-top:16px;border-top:1px solid #eee;text-align:center;">
      <div style="font-size:2em;font-weight:bold;color:#2980b9;">
        <?= number_format($totalWater / 1000, 2, ',', ' ') ?>L / 2L
      </div>
      <div style="font-size:.9em;color:#999;margin-top:8px;"><?= round($waterProgress) ?>% de l'objectif</div>
    </div>
  </div>
</div>

<!-- Section AI Coaching -->
<section style="margin-bottom:28px;">
  <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:16px;margin-bottom:20px;padding-bottom:16px;border-bottom:2px solid #a67c52;">
    <div style="display:flex;align-items:center;gap:14px;">
      <div style="width:40px;height:40px;border-radius:12px;background:linear-gradient(135deg,#4e2c0e,#a0522d);color:#fff;font-size:11px;font-weight:900;letter-spacing:.05em;display:flex;align-items:center;justify-content:center;box-shadow:0 4px 12px rgba(78,44,14,.3);">AI</div>
      <div>
        <h2 style="margin:0;font-size:1.2rem;font-weight:700;color:#2c1a0a;">Coaching Intelligence</h2>
        <p style="margin:2px 0 0;font-size:12px;color:#9b7355;">Analyse personnalisée de votre journée</p>
      </div>
    </div>
    <div id="personality-switcher" style="display:flex;gap:6px;background:#f5ede0;border-radius:14px;padding:5px;" role="group" aria-label="Choisir le style du coach">
      <button class="ai-pers-btn" data-p="strict" aria-pressed="false" style="display:flex;align-items:center;gap:6px;padding:7px 14px;border-radius:10px;border:none;background:transparent;cursor:pointer;font-size:12px;font-weight:600;color:#7a5f3b;transition:all .2s;">😡 <span>Strict</span></button>
      <button class="ai-pers-btn" data-p="friendly" aria-pressed="false" style="display:flex;align-items:center;gap:6px;padding:7px 14px;border-radius:10px;border:none;background:transparent;cursor:pointer;font-size:12px;font-weight:600;color:#7a5f3b;transition:all .2s;">😊 <span>Friendly</span></button>
      <button class="ai-pers-btn" data-p="funny" aria-pressed="false" style="display:flex;align-items:center;gap:6px;padding:7px 14px;border-radius:10px;border:none;background:transparent;cursor:pointer;font-size:12px;font-weight:600;color:#7a5f3b;transition:all .2s;">😂 <span>Funny</span></button>
    </div>
  </div>

  <div style="display:grid;grid-template-columns:280px 1fr 1fr;gap:18px;align-items:start;">
    <div class="card" style="overflow:hidden;">
      <div style="display:flex;align-items:center;gap:10px;padding:16px 20px;background:linear-gradient(135deg,#fdf6ec,#f5e9d0);border-bottom:1px solid rgba(200,175,145,.25);">
        <span>👤</span>
        <span style="font-size:13px;font-weight:700;color:#4e2c0e;text-transform:uppercase;letter-spacing:.06em;">Votre Profil</span>
      </div>
      <div id="user-profile-display" style="padding:16px;">
        <div style="text-align:center;color:#9b7355;">Analyse...</div>
      </div>
    </div>
    <div class="card" style="overflow:hidden;">
      <div style="display:flex;align-items:center;gap:10px;padding:16px 20px;background:linear-gradient(135deg,#fdf6ec,#f5e9d0);border-bottom:1px solid rgba(200,175,145,.25);">
        <span>🎯</span>
        <span style="font-size:13px;font-weight:700;color:#4e2c0e;text-transform:uppercase;letter-spacing:.06em;">Conseils du Coach</span>
        <span id="coach-msg-count" style="margin-left:auto;background:#a0522d;color:#fff;border-radius:20px;padding:2px 8px;font-size:11px;font-weight:700;"></span>
      </div>
      <div id="coaching-messages" style="padding:16px;">
        <div style="text-align:center;color:#9b7355;">Génération...</div>
      </div>
    </div>
    <div class="card" style="overflow:hidden;">
      <div style="display:flex;align-items:center;gap:10px;padding:16px 20px;background:linear-gradient(135deg,#fdf6ec,#f5e9d0);border-bottom:1px solid rgba(200,175,145,.25);">
        <span>📋</span>
        <span style="font-size:13px;font-weight:700;color:#4e2c0e;text-transform:uppercase;letter-spacing:.06em;">Plan d'action</span>
      </div>
      <div id="coaching-plan" style="padding:16px;">
        <div style="text-align:center;color:#9b7355;">Calcul...</div>
      </div>
    </div>
  </div>
</section>

<!-- Journal alimentaire du jour -->
<section style="margin-bottom:28px;">
  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;padding-bottom:8px;border-bottom:2px solid #a67c52;">
    <h2 style="margin:0;font-size:1.25rem;color:#6d4c1b;font-weight:600;display:flex;align-items:center;gap:10px;">🥗 Journal alimentaire</h2>
    <a href="?route=foodlog/journal" class="btn btn-sm btn-secondary">Voir tout</a>
  </div>
  <?php if (!empty($todayEntries)): ?>
  <div class="card">
    <div class="table-wrap">
      <table class="fw-table">
        <thead><tr><th>Repas</th><th>Aliment</th><th class="num">Quantité</th><th class="num">Calories</th></tr></thead>
        <tbody>
          <?php foreach (array_slice($todayEntries, 0, 8) as $entry): ?>
            <tr>
              <td><span class="badge badge-brun"><?= htmlspecialchars($entry['meal_type'], ENT_QUOTES, 'UTF-8') ?></span></td>
              <td><?= htmlspecialchars($entry['food'], ENT_QUOTES, 'UTF-8') ?></td>
              <td class="num"><?= htmlspecialchars($entry['quantity'], ENT_QUOTES, 'UTF-8') ?></td>
              <td class="num" style="color:#c0392b;font-weight:600;"><?= (int)$entry['calories'] ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
  <?php else: ?>
  <div class="card"><p class="text-muted" style="text-align:center;padding:24px 0;margin:0;">Aucune entrée enregistrée aujourd'hui.</p></div>
  <?php endif; ?>
</section>

<!-- Activité physique du jour -->
<section>
  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;padding-bottom:8px;border-bottom:2px solid #a67c52;">
    <h2 style="margin:0;font-size:1.25rem;color:#6d4c1b;font-weight:600;display:flex;align-items:center;gap:10px;">🏃 Activité physique</h2>
    <a href="?route=foodlog/suivi" class="btn btn-sm btn-secondary">Voir tout</a>
  </div>
  <?php if (!empty($dailySuivis)): ?>
  <div class="card">
    <div class="table-wrap">
      <table class="fw-table">
        <thead><tr><th>Type d'activité</th><th class="num">Durée</th><th>Intensité</th><th class="num">Calories</th></tr></thead>
        <tbody>
          <?php foreach (array_slice($dailySuivis, 0, 8) as $suivi): ?>
            <tr>
              <td><?= htmlspecialchars($suivi['type_activite'], ENT_QUOTES, 'UTF-8') ?></td>
              <td class="num"><?= (int)$suivi['duree'] ?> min</td>
              <td>
                <?php $ic = match($suivi['intensite']) { 'faible' => 'badge-vert', 'élevé' => 'badge-rouge', default => 'badge-brun' }; ?>
                <span class="badge <?= $ic ?>"><?= htmlspecialchars(ucfirst($suivi['intensite']), ENT_QUOTES, 'UTF-8') ?></span>
              </td>
              <td class="num" style="color:#27ae60;font-weight:600;"><?= (int)$suivi['calories_brulees'] ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
  <?php else: ?>
  <div class="card"><p class="text-muted" style="text-align:center;padding:24px 0;margin:0;">Aucune activité enregistrée aujourd'hui.</p></div>
  <?php endif; ?>
</section>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
const chartData = {
  caloriesConsumed: <?= (int)$totalCaloriesConsumed ?>,
  caloriesBurned:   <?= (int)$totalCaloriesBurned ?>,
  waterIntake:      <?= (int)$totalWater ?>,
  waterGoal:        2000
};
const caloriesCtx = document.getElementById('caloriesChart');
if (caloriesCtx) {
  new Chart(caloriesCtx, {
    type: 'bar',
    data: {
      labels: ['Consommées', 'Brûlées'],
      datasets: [{ label: 'Calories', data: [chartData.caloriesConsumed, chartData.caloriesBurned],
        backgroundColor: ['rgba(192,57,43,.8)', 'rgba(39,174,96,.8)'],
        borderColor: ['rgba(192,57,43,1)', 'rgba(39,174,96,1)'],
        borderWidth: 2, borderRadius: 8, borderSkipped: false, barThickness: 60 }]
    },
    options: { indexAxis: 'y', responsive: true, maintainAspectRatio: true,
      plugins: { legend: { display: false }, tooltip: { callbacks: { label: ctx => ctx.parsed.x + ' kcal' } } },
      scales: { x: { beginAtZero: true, ticks: { callback: v => v + ' kcal' } }, y: { grid: { display: false } } }
    }
  });
}
const waterCtx = document.getElementById('waterChart');
if (waterCtx) {
  const wc = Math.min(chartData.waterIntake, chartData.waterGoal);
  new Chart(waterCtx, {
    type: 'doughnut',
    data: {
      labels: ['Consommée', 'Restant'],
      datasets: [{ data: [wc, Math.max(0, chartData.waterGoal - wc)],
        backgroundColor: ['rgba(41,128,185,.85)', 'rgba(189,195,199,.3)'],
        borderColor: ['rgba(41,128,185,1)', 'rgba(189,195,199,1)'],
        borderWidth: 2, borderRadius: [8, 8] }]
    },
    options: { responsive: true, maintainAspectRatio: true,
      plugins: { legend: { position: 'bottom' }, tooltip: { callbacks: { label: ctx => ctx.parsed + ' mL (' + ((ctx.parsed / chartData.waterGoal) * 100).toFixed(1) + '%)' } } }
    }
  });
}
</script>

<!-- AI Coaching -->
<script src="assets/nutrition-coach.js" defer></script>
<script src="assets/user-profiling.js" defer></script>
<script>
(function() {
  var STORAGE_KEY = 'fw_coach_personality';
  var userData = {
    calories: <?= (int)$totalCaloriesConsumed ?>,
    proteins: <?= round($macros['proteins'], 1) ?>,
    carbs:    <?= round($macros['carbs'], 1) ?>,
    fats:     <?= round($macros['fats'], 1) ?>,
    sugar: 0, water: <?= (int)$totalWater ?>, caloriesBurned: <?= (int)$totalCaloriesBurned ?>,
    goal: 'fitness',
    history: <?= json_encode(array_map(fn($d) => ['calories'=>(int)$d['calories'],'proteins'=>(float)$d['proteins'],'carbs'=>(float)$d['carbs'],'fats'=>(float)$d['fats'],'sugar'=>0,'water'=>0,'caloriesBurned'=>0], $history7Days)) ?>
  };
  var profileData = Object.assign({}, userData, { meals: 4, frequency: 4 });
  var PROFILE_CFG = {
    healthy:   { name: 'Équilibré',   color: '#10b981', bg: 'linear-gradient(135deg,#d1fae5,#6ee7b7)', icon: '🥗' },
    junk_food: { name: 'À améliorer', color: '#ef4444', bg: 'linear-gradient(135deg,#fee2e2,#fca5a5)', icon: '🍔' },
    sportif:   { name: 'Sportif',     color: '#3b82f6', bg: 'linear-gradient(135deg,#dbeafe,#93c5fd)', icon: '🏃' }
  };
  var MSG_TYPE = { warning: { icon: '⚠️', label: 'Alerte', color: '#f59e0b' }, advice: { icon: '💡', label: 'Conseil', color: '#3b82f6' }, motivation: { icon: '✅', label: 'Bravo', color: '#10b981' } };
  var TAG_COLORS = { Focus: '#8b5cf6', Action: '#f59e0b', Conseil: '#3b82f6', Motivation: '#10b981', Urgent: '#ef4444', Maintien: '#6b7280' };

  function renderProfile(profile) {
    var el = document.getElementById('user-profile-display');
    if (!el) return;
    var cfg = PROFILE_CFG[profile.profile] || PROFILE_CFG.healthy;
    el.innerHTML = '<div style="background:' + cfg.bg + ';border-radius:12px;padding:16px;text-align:center;margin-bottom:12px;">' +
      '<div style="font-size:2em;">' + cfg.icon + '</div>' +
      '<div style="font-weight:700;color:' + cfg.color + ';font-size:1.1em;">' + cfg.name + '</div>' +
      '<div style="font-size:1.8em;font-weight:bold;color:' + cfg.color + ';">' + profile.score + '<span style="font-size:.6em;color:#999;"> / 100</span></div>' +
      '</div>' +
      '<div style="display:flex;flex-wrap:wrap;gap:6px;">' +
      profile.reasons.slice(0,3).map(function(r){ return '<span style="background:#f5ede0;color:#7a5f3b;border-radius:20px;padding:3px 10px;font-size:11px;font-weight:600;">' + r + '</span>'; }).join('') +
      '</div>';
  }

  function renderMessages(messages) {
    var el = document.getElementById('coaching-messages');
    var badge = document.getElementById('coach-msg-count');
    if (!el) return;
    var top = messages.slice(0, 4);
    if (badge) badge.textContent = top.length;
    if (!top.length) { el.innerHTML = '<div style="text-align:center;color:#10b981;padding:16px;">✅ Tout est parfait aujourd\'hui !</div>'; return; }
    el.innerHTML = top.map(function(m) {
      var t = MSG_TYPE[m.type] || MSG_TYPE.advice;
      return '<div style="display:flex;gap:10px;padding:10px 0;border-bottom:1px solid #f5ede0;">' +
        '<div style="font-size:1.2em;flex-shrink:0;">' + t.icon + '</div>' +
        '<div><span style="font-size:10px;font-weight:800;text-transform:uppercase;color:' + t.color + ';background:' + t.color + '18;padding:2px 7px;border-radius:20px;">' + t.label + '</span>' +
        '<p style="margin:4px 0 0;font-size:12.5px;color:#4a3728;line-height:1.55;">' + m.message + '</p></div></div>';
    }).join('');
  }

  function renderPlan(plan) {
    var el = document.getElementById('coaching-plan');
    if (!el) return;
    if (!plan || !plan.length) { el.innerHTML = '<div style="text-align:center;color:#10b981;padding:16px;">✅ Aucun ajustement nécessaire.</div>'; return; }
    el.innerHTML = plan.map(function(item) {
      var c = TAG_COLORS[item.tag] || '#6b7280';
      return '<div style="display:flex;gap:10px;padding:10px 0;border-bottom:1px solid #f5ede0;">' +
        '<div style="width:3px;background:' + c + ';border-radius:2px;flex-shrink:0;"></div>' +
        '<div><div style="font-size:10px;font-weight:800;text-transform:uppercase;color:' + c + ';background:' + c + '18;padding:2px 7px;border-radius:20px;display:inline-block;margin-bottom:4px;">' + item.tag + '</div>' +
        '<div style="font-weight:700;font-size:13px;color:#2c1a0a;">' + item.title + '</div>' +
        '<div style="font-size:12px;color:#7a5f3b;margin-top:2px;">' + item.detail + '</div></div></div>';
    }).join('');
  }

  function setPersonality(personality) {
    var switcher = document.getElementById('personality-switcher');
    if (switcher) {
      switcher.querySelectorAll('.ai-pers-btn').forEach(function(btn) {
        var on = btn.dataset.p === personality;
        btn.style.background = on ? '#fff' : 'transparent';
        btn.style.boxShadow = on ? '0 2px 8px rgba(78,44,14,.12)' : 'none';
        btn.setAttribute('aria-pressed', String(on));
      });
    }
    localStorage.setItem(STORAGE_KEY, personality);
    if (typeof window.nutritionCoach !== 'undefined') {
      renderMessages(window.nutritionCoach.generateCoachMessages(userData, personality));
      renderPlan(window.nutritionCoach.generateCoachPlan ? window.nutritionCoach.generateCoachPlan(userData, personality) : []);
    }
    if (typeof window.userProfiler !== 'undefined') {
      renderProfile(window.userProfiler.profileUser(profileData));
    }
  }

  var tries = 0;
  function init() {
    if (typeof window.nutritionCoach === 'undefined' || typeof window.userProfiler === 'undefined') {
      if (++tries < 100) setTimeout(init, 50);
      return;
    }
    var saved = localStorage.getItem(STORAGE_KEY) || 'friendly';
    var switcher = document.getElementById('personality-switcher');
    if (switcher) {
      switcher.querySelectorAll('.ai-pers-btn').forEach(function(btn) {
        btn.addEventListener('click', function() { setPersonality(this.dataset.p); });
      });
    }
    setPersonality(saved);
  }
  if (document.readyState === 'loading') { document.addEventListener('DOMContentLoaded', init); } else { init(); }
})();
</script>

<?php include __DIR__ . '/../../layouts/front/footer.php'; ?>

