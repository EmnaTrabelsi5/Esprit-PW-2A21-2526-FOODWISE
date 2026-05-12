<?php
/**
 * FoodWise — Module FoodLog
 * Vue : Journal alimentaire (Front)
 */

require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../controller/JournalController.php';

if (session_status() === PHP_SESSION_NONE) session_start();
if (empty($_SESSION['user_id'])) {
    header('Location: ?route=module2/front/connexion');
    exit;
}

$pdo        = config::getConnexion();
$controller = new JournalController($pdo);
$userId     = (int) $_SESSION['user_id'];
$today      = date('Y-m-d');

$sort          = $_GET['sort'] ?? 'date_desc';
$sortDirection = $sort === 'date_asc' ? 'ASC' : 'DESC';
$entries       = $controller->listEntriesForUser($userId, $sortDirection);
$totalCalories = $controller->getDailyCalories($userId, $today);
$streak        = $controller->getStreak($userId);
$macros        = $controller->getMacroTotals($userId, $today);
$history7Days  = $controller->getLast7DaysSummary($userId);

$alertMessage = '';
$alertClass   = 'success';
if (isset($_GET['created'])) {
    $alertMessage = $_GET['created'] === '1' ? 'Entrée ajoutée avec succès.' : "Impossible d'ajouter l'entrée.";
    $alertClass   = $_GET['created'] === '1' ? 'success' : 'error';
} elseif (isset($_GET['updated'])) {
    $alertMessage = $_GET['updated'] === '1' ? 'Entrée modifiée avec succès.' : "Impossible de modifier l'entrée.";
    $alertClass   = $_GET['updated'] === '1' ? 'success' : 'error';
} elseif (isset($_GET['deleted'])) {
    $alertMessage = $_GET['deleted'] === '1' ? 'Entrée supprimée avec succès.' : "Impossible de supprimer l'entrée.";
    $alertClass   = $_GET['deleted'] === '1' ? 'success' : 'error';
}

$pageTitle  = 'Journal alimentaire';
$activeNav  = 'foodlog_journal';
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
/* ── FoodLog summary cards ── */
.fl-header { margin-bottom: 24px; }
.fl-header h1 { font-size: 1.6rem; font-weight: 700; color: #3d2b1f; margin: 0 0 4px; display:flex; align-items:center; gap:10px; }
.fl-header p  { color: #9b7355; font-size: 14px; margin: 0; }

.fl-cards {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 16px;
  margin-bottom: 24px;
}
.fl-card {
  background: #fff;
  border-radius: 14px;
  padding: 20px 22px;
  box-shadow: 0 2px 8px rgba(78,44,14,.08);
  border-left: 4px solid #a0522d;
  display: flex;
  flex-direction: column;
  gap: 4px;
}
.fl-card__icon { font-size: 1.4rem; margin-bottom: 4px; }
.fl-card__label { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .08em; color: #9b7355; }
.fl-card__value { font-size: 2rem; font-weight: 800; color: #3d2b1f; line-height: 1.1; }
.fl-card__hint  { font-size: 12px; color: #b0a090; margin-top: 2px; }
.fl-card--green  { border-left-color: #27ae60; }
.fl-card--blue   { border-left-color: #2980b9; }
.fl-card--orange { border-left-color: #e67e22; }
.fl-card--purple { border-left-color: #8e44ad; }

.fl-toolbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 12px;
  margin-bottom: 20px;
  padding: 14px 18px;
  background: #fff;
  border-radius: 12px;
  box-shadow: 0 1px 4px rgba(78,44,14,.06);
}
.fl-toolbar__left  { display: flex; align-items: center; gap: 10px; font-size: 13px; color: #7a5f3b; }
.fl-toolbar__right { display: flex; gap: 10px; }
</style>

<div class="fl-header">
  <h1>📋 Journal alimentaire</h1>
  <p>Suivi des repas et collations — total calories aujourd'hui</p>
</div>

<div class="fl-cards">
  <div class="fl-card fl-card--orange">
    <span class="fl-card__icon">🔥</span>
    <span class="fl-card__label">Calories</span>
    <span class="fl-card__value"><?= htmlspecialchars((string) $totalCalories, ENT_QUOTES, 'UTF-8') ?></span>
    <span class="fl-card__hint">kcal aujourd'hui</span>
  </div>
  <div class="fl-card fl-card--green">
    <span class="fl-card__icon">💪</span>
    <span class="fl-card__label">Protéines</span>
    <span class="fl-card__value"><?= round($macros['proteins'], 0) ?>g</span>
    <span class="fl-card__hint">Objectif 75g</span>
  </div>
  <div class="fl-card fl-card--purple">
    <span class="fl-card__icon">🔥</span>
    <span class="fl-card__label">Streak</span>
    <span class="fl-card__value"><?= htmlspecialchars((string) $streak, ENT_QUOTES, 'UTF-8') ?> j.</span>
    <span class="fl-card__hint">Jours consécutifs</span>
  </div>
  <div class="fl-card fl-card--blue">
    <span class="fl-card__icon">📝</span>
    <span class="fl-card__label">Entrées</span>
    <span class="fl-card__value"><?= count($entries) ?></span>
    <span class="fl-card__hint">Enregistrements</span>
  </div>
</div>

<div class="fl-toolbar">
  <div class="fl-toolbar__left">
    <span>Trier par date :</span>
    <a href="?route=foodlog/journal&sort=<?= $sortDirection === 'ASC' ? 'date_desc' : 'date_asc' ?>" class="btn btn-secondary btn-sm">
      <?= $sortDirection === 'ASC' ? '↓ Date décroissante' : '↑ Date croissante' ?>
    </a>
  </div>
  <div class="fl-toolbar__right">
    <a href="?route=foodlog/ajouter-entree" class="btn btn-primary">+ Ajouter une entrée</a>
    <a href="?route=foodlog/resume" class="btn btn-secondary">📊 Voir le résumé</a>
  </div>
</div>

<!-- Toast coaching IA -->
<div id="coach-toast-container" aria-live="polite" aria-label="Notifications coaching IA"></div>

<section class="card mb-0">
  <div class="table-wrap">
    <table class="fw-table">
      <thead>
        <tr>
          <th>Date</th>
          <th>Repas</th>
          <th>Aliment</th>
          <th class="num">Quantité</th>
          <th class="num">kcal</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($entries)): ?>
          <tr>
            <td colspan="6" class="text-muted">Aucune entrée enregistrée pour le moment.</td>
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
                <a href="?route=foodlog/modifier-entree&id=<?= (int) $entry['id'] ?>" class="btn btn-sm btn-secondary">Modifier</a>
                <a href="?route=foodlog/supprimer&id=<?= (int) $entry['id'] ?>&source=front"
                   class="btn btn-sm btn-danger confirm-delete"
                   onclick="return confirm('Supprimer cette entrée ?')">Suppr.</a>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</section>

<!-- Coaching IA -->
<style>
#coach-toast-container{position:fixed;top:76px;right:240px;z-index:9999;display:flex;flex-direction:column;gap:10px;width:310px;pointer-events:none;}
.coach-toast{pointer-events:all;background:#fff;border-radius:16px;overflow:hidden;opacity:0;transform:translateX(16px) scale(0.96);transition:opacity .28s cubic-bezier(.4,0,.2,1),transform .28s cubic-bezier(.4,0,.2,1);box-shadow:0 1px 3px rgba(0,0,0,.06),0 6px 20px rgba(0,0,0,.10),0 0 0 1px rgba(0,0,0,.04);}
.coach-toast.show{opacity:1;transform:translateX(0) scale(1);}
.coach-toast.hide{opacity:0;transform:translateX(16px) scale(0.96);}
.coach-toast-accent{height:3px;width:100%;}
.coach-toast-body-wrap{display:flex;align-items:flex-start;gap:11px;padding:12px 14px 14px;}
.coach-toast-avatar{width:34px;height:34px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1.15em;flex-shrink:0;}
.coach-toast-content{flex:1;min-width:0;}
.coach-toast-meta{display:flex;align-items:center;justify-content:space-between;margin-bottom:3px;}
.coach-toast-tag{font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:.1em;padding:2px 7px;border-radius:20px;}
.coach-toast-time{font-size:10px;color:#b0a090;}
.coach-toast-msg{font-size:12.5px;color:#4a3728;line-height:1.55;margin:0;}
.coach-toast-close{background:none;border:none;cursor:pointer;color:#c8b8a8;font-size:13px;padding:0;line-height:1;flex-shrink:0;font-weight:700;}
.coach-toast-close:hover{color:#7a5f3b;}
.coach-toast-progress{height:2px;animation:toastShrink linear forwards;opacity:.5;}
@keyframes toastShrink{from{width:100%}to{width:0%}}
.coach-toast.p-strict .coach-toast-accent{background:linear-gradient(90deg,#e74c3c,#c0392b);}
.coach-toast.p-strict .coach-toast-avatar{background:#fff0ee;}
.coach-toast.p-strict .coach-toast-tag{background:#fff0ee;color:#c0392b;}
.coach-toast.p-strict .coach-toast-progress{background:#e74c3c;}
.coach-toast.p-friendly .coach-toast-accent{background:linear-gradient(90deg,#2ecc71,#27ae60);}
.coach-toast.p-friendly .coach-toast-avatar{background:#f0fff6;}
.coach-toast.p-friendly .coach-toast-tag{background:#f0fff6;color:#1e8449;}
.coach-toast.p-friendly .coach-toast-progress{background:#27ae60;}
.coach-toast.p-funny .coach-toast-accent{background:linear-gradient(90deg,#a855f7,#8e44ad);}
.coach-toast.p-funny .coach-toast-avatar{background:#fdf4ff;}
.coach-toast.p-funny .coach-toast-tag{background:#fdf4ff;color:#7d3c98;}
.coach-toast.p-funny .coach-toast-progress{background:#8e44ad;}
#coach-personality-picker{position:fixed;top:76px;right:24px;z-index:10000;background:#fff;border-radius:20px;padding:14px 16px;box-shadow:0 2px 4px rgba(0,0,0,.04),0 8px 24px rgba(0,0,0,.10),0 0 0 1px rgba(0,0,0,.05);font-family:'Lato',sans-serif;width:200px;}
.picker-header{display:flex;align-items:center;gap:7px;margin-bottom:12px;padding-bottom:10px;border-bottom:1px solid #f0ece6;}
.picker-header-dot{width:8px;height:8px;border-radius:50%;background:linear-gradient(135deg,#a0522d,#7b3f1a);flex-shrink:0;}
.picker-header-text{font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:.12em;color:#9b7355;}
.picker-options{display:flex;flex-direction:column;gap:6px;}
.picker-btn{display:flex;align-items:center;gap:10px;width:100%;padding:9px 12px;border-radius:12px;border:1.5px solid transparent;background:#faf8f5;font-family:'Lato',sans-serif;font-size:13px;font-weight:600;cursor:pointer;transition:all .18s ease;color:#5c3d20;text-align:left;}
.picker-btn:hover{background:#f5ede0;transform:translateX(2px);}
.picker-btn-emoji{font-size:1.2em;width:24px;text-align:center;flex-shrink:0;}
.picker-btn-info{flex:1;}
.picker-btn-name{display:block;font-size:12px;font-weight:700;line-height:1.2;}
.picker-btn-desc{display:block;font-size:10px;font-weight:400;opacity:.6;margin-top:1px;}
.picker-btn-check{width:16px;height:16px;border-radius:50%;border:1.5px solid #ddd;flex-shrink:0;display:flex;align-items:center;justify-content:center;font-size:9px;transition:all .18s;}
.picker-btn.active[data-p="strict"]{background:linear-gradient(135deg,#fff0ee,#ffe4e1);border-color:#e74c3c;color:#c0392b;}
.picker-btn.active[data-p="friendly"]{background:linear-gradient(135deg,#f0fff6,#e0f7ea);border-color:#27ae60;color:#1e8449;}
.picker-btn.active[data-p="funny"]{background:linear-gradient(135deg,#fdf4ff,#f3e5ff);border-color:#8e44ad;color:#7d3c98;}
.picker-btn.active[data-p="strict"] .picker-btn-check{background:#e74c3c;border-color:#e74c3c;color:#fff;}
.picker-btn.active[data-p="friendly"] .picker-btn-check{background:#27ae60;border-color:#27ae60;color:#fff;}
.picker-btn.active[data-p="funny"] .picker-btn-check{background:#8e44ad;border-color:#8e44ad;color:#fff;}
@media(max-width:700px){#coach-toast-container{width:calc(100vw - 32px);right:16px;top:auto;bottom:80px;}#coach-personality-picker{top:auto;bottom:16px;right:16px;width:auto;padding:8px 10px;}.picker-options{flex-direction:row;gap:4px;}.picker-btn-desc,.picker-btn-name{display:none;}.picker-btn{padding:6px 8px;}.picker-header{display:none;}}
</style>

<script src="assets/nutrition-coach.js" defer></script>
<script>
(function () {
  var userData = {
    calories:       <?= (int)$totalCalories ?>,
    proteins:       <?= round($macros['proteins'], 1) ?>,
    carbs:          <?= round($macros['carbs'], 1) ?>,
    fats:           <?= round($macros['fats'], 1) ?>,
    sugar: 0, water: 0, caloriesBurned: 0, goal: 'fitness',
    history: <?= json_encode(array_map(fn($d) => ['calories'=>(int)$d['calories'],'proteins'=>(float)$d['proteins'],'carbs'=>(float)$d['carbs'],'fats'=>(float)$d['fats'],'sugar'=>0,'water'=>0,'caloriesBurned'=>0], $history7Days)) ?>
  };
  var DURATION='8000',STORAGE_KEY='fw_coach_personality';
  var TYPE_ICONS={warning:'⚠️',advice:'💡',motivation:'✅'};
  var TYPE_TAGS={warning:'Alert',advice:'Tip',motivation:'Win'};
  var P_CONFIG={strict:{emoji:'😡',name:'Strict',desc:'No excuses',cls:'p-strict'},friendly:{emoji:'😊',name:'Friendly',desc:'Supportive',cls:'p-friendly'},funny:{emoji:'😂',name:'Funny',desc:'Light & fun',cls:'p-funny'}};
  function buildPicker(){if(document.getElementById('coach-personality-picker'))return document.getElementById('coach-personality-picker');var el=document.createElement('div');el.id='coach-personality-picker';var opts=Object.keys(P_CONFIG).map(function(key){var p=P_CONFIG[key];return'<button class="picker-btn" data-p="'+key+'" aria-pressed="false"><span class="picker-btn-emoji">'+p.emoji+'</span><span class="picker-btn-info"><span class="picker-btn-name">'+p.name+'</span><span class="picker-btn-desc">'+p.desc+'</span></span><span class="picker-btn-check">✓</span></button>';}).join('');el.innerHTML='<div class="picker-header"><div class="picker-header-dot"></div><span class="picker-header-text">AI Coach</span></div><div class="picker-options">'+opts+'</div>';document.body.appendChild(el);return el;}
  function getContainer(){return document.getElementById('coach-toast-container');}
  function showToast(msg,personality,delay){var container=getContainer();if(!container)return;var type=TYPE_ICONS[msg.type]?msg.type:'advice';var icon=TYPE_ICONS[type];var tag=TYPE_TAGS[type];var pcfg=P_CONFIG[personality]||P_CONFIG.friendly;var now=new Date().toLocaleTimeString('fr-FR',{hour:'2-digit',minute:'2-digit'});var toast=document.createElement('div');toast.className='coach-toast '+pcfg.cls;toast.setAttribute('role','alert');toast.innerHTML='<div class="coach-toast-accent"></div><div class="coach-toast-body-wrap"><div class="coach-toast-avatar">'+icon+'</div><div class="coach-toast-content"><div class="coach-toast-meta"><span class="coach-toast-tag">'+tag+'</span><span class="coach-toast-time">'+pcfg.name+' · '+now+'</span></div><p class="coach-toast-msg">'+msg.message+'</p></div><button class="coach-toast-close" aria-label="Close">✕</button></div><div class="coach-toast-progress" style="animation-duration:'+DURATION+'ms;"></div>';getContainer().appendChild(toast);var autoClose=setTimeout(function(){dismiss(toast);},delay+parseInt(DURATION));setTimeout(function(){toast.classList.add('show');},delay+60);toast.querySelector('.coach-toast-close').addEventListener('click',function(){clearTimeout(autoClose);dismiss(toast);});}
  function dismiss(t){t.classList.remove('show');t.classList.add('hide');setTimeout(function(){if(t.parentNode)t.parentNode.removeChild(t);},300);}
  function clearToasts(){var c=getContainer();if(c){Array.prototype.slice.call(c.children).forEach(function(t){dismiss(t);});}}
  function renderToasts(personality){clearToasts();var msgs=window.nutritionCoach.generateCoachMessages(userData,personality).slice(0,4);msgs.forEach(function(m,i){showToast(m,personality,i*500);});}
  function setActive(picker,personality){picker.querySelectorAll('.picker-btn').forEach(function(btn){var on=btn.dataset.p===personality;btn.classList.toggle('active',on);btn.setAttribute('aria-pressed',String(on));});}
  var tries=0;
  function init(){if(typeof window.nutritionCoach==='undefined'){if(++tries<100)setTimeout(init,50);return;}var saved=localStorage.getItem(STORAGE_KEY)||'friendly';var picker=buildPicker();setActive(picker,saved);renderToasts(saved);picker.querySelectorAll('.picker-btn').forEach(function(btn){btn.addEventListener('click',function(){var p=this.dataset.p;localStorage.setItem(STORAGE_KEY,p);setActive(picker,p);renderToasts(p);});});}
  if(document.readyState==='loading'){document.addEventListener('DOMContentLoaded',init);}else{init();}
})();
</script>

<?php include __DIR__ . '/../../layouts/front/footer.php'; ?>

