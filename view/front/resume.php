<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/auth.php';
requireClient();

// Inclure les contrôleurs
require_once __DIR__ . '/../../controller/JournalController.php';
require_once __DIR__ . '/../../controller/SuiviSanteController.php';

$pdo = require __DIR__ . '/../../config/config.php';
$journalController = new JournalController($pdo);
$suiviController = new SuiviSanteController($pdo);

// Utilisateur et date
$userId = 1;
$today = date('Y-m-d');

// === DONNÉES JOURNAL ALIMENTAIRE ===
$journalEntries = $journalController->listEntriesForUser($userId);
$todayEntries = array_filter($journalEntries, fn($e) => $e['date'] === $today);
$totalCaloriesConsumed = $journalController->getDailyCalories($userId, $today);
$journalStreak = $journalController->getStreak($userId);

// === MACROS DU JOUR (vraies données) ===
$macros = $journalController->getMacroTotals($userId, $today);
$totalProteins = $macros['proteins'];
$totalCarbs    = $macros['carbs'];
$totalFats     = $macros['fats'];

// === HISTORIQUE 7 JOURS POUR LE COACHING IA ===
$history7Days = $journalController->getLast7DaysSummary($userId);

// === DONNÉES SUIVI SANTÉ ===
$suiviData = $suiviController->getDailyHealthReport($userId, $today, $journalController);
$dailySuivis = $suiviController->getDailySuivis($userId, $today);
$totalCaloriesBurned = $suiviController->getDailyCaloriesBurned($userId, $today);
$totalWater = $suiviController->getDailyWaterIntake($userId, $today);
$totalActivityDuration = $suiviController->getDailyActivityDuration($userId, $today);

// === CALCULS GLOBAUX ===
$calorieBalance = $totalCaloriesConsumed - $totalCaloriesBurned;
$waterProgress = min(100, ($totalWater / 2000) * 100); // Objectif 2L
$activityProgress = min(100, ($totalActivityDuration / 60) * 100); // Objectif 60min

$pageTitle = 'Résumé du jour — FoodWise';
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
        <a href="ajouter-entree.php" class="nav-item"><span class="nav-ico" aria-hidden="true">➕</span> Ajouter une entrée</a>
        <a href="resume.php" class="nav-item active"><span class="nav-ico" aria-hidden="true">📊</span> Résumé du jour</a>
        <p class="fw-sidebar-section">Santé</p>
        <a href="suivi-sante-unifie.php" class="nav-item"><span class="nav-ico" aria-hidden="true">🏃</span> Suivi Santé</a>
        <a href="ajouter-suivi.php" class="nav-item"><span class="nav-ico" aria-hidden="true">➕</span> Ajouter activité</a>
        <p class="fw-sidebar-section">Compte</p>
        <a href="#" class="nav-item"><span class="nav-ico" aria-hidden="true">👤</span> Profil</a>
        <a href="#" class="nav-item"><span class="nav-ico" aria-hidden="true">⎋</span> Déconnexion</a>
      </nav>
    </aside>

    <div class="main-content">
      <header class="topbar">
        <label for="fw-nav-toggle" class="topbar-menu-btn" aria-label="Ouvrir le menu">☰</label>
        <form class="topbar-search" action="#" method="get" role="search">
          <input type="search" name="q" placeholder="Rechercher…" aria-label="Recherche" value="">
          <button type="button">OK</button>
        </form>
        <nav class="topbar-nav" aria-label="Raccourcis">
          <a href="journal-alimentaire.php">Journal</a>
          <a href="suivi-sante-unifie.php">Santé</a>
        </nav>
        <div class="topbar-user">
          <span class="user-avatar-initials" aria-hidden="true">MD</span>
          <span>Marie Dupont</span>
        </div>
      </header>

      <main class="page-body">
        <h1 class="page-title">Résumé du jour</h1>
        <p class="page-subtitle">Vue d'ensemble complète — <?= date('d/m/Y') ?></p>

        <!-- ===== SECTION 1: DASHBOARD GRAPHIQUES ===== -->
        <section class="mb-4">
          <div class="charts-container" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 24px; margin-bottom: 24px;">
            
            <!-- GRAPHIQUE 1: CALORIES (BAR CHART) -->
            <div class="card" style="background: #fff; box-shadow: 0 2px 12px #0001; border-radius: 14px; padding: 24px;">
              <h2 style="font-size: 1.3em; font-weight: 600; color: #6d4c1b; margin: 0 0 20px 0; display: flex; align-items: center; gap: 10px;">
                <span style="font-size: 1.5em;">🔥</span> Bilan Calorique
              </h2>
              <div style="position: relative; height: 280px;">
                <canvas id="caloriesChart"></canvas>
              </div>
              <div style="margin-top: 16px; padding-top: 16px; border-top: 1px solid #eee; display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div style="text-align: center;">
                  <div style="font-size: 0.85em; color: #999; margin-bottom: 4px;">Bilan Net</div>
                  <div style="font-size: 1.8em; font-weight: bold; color: <?= $calorieBalance >= 0 ? '#c0392b' : '#27ae60' ?>;">
                    <?= $calorieBalance >= 0 ? '+' : '' ?><?= number_format((int)$calorieBalance, 0, ',', ' ') ?>
                  </div>
                  <div style="font-size: 0.8em; color: #999;"><?= $calorieBalance >= 0 ? 'Surplus' : 'Déficit' ?></div>
                </div>
                <div style="text-align: center;">
                  <div style="font-size: 0.85em; color: #999; margin-bottom: 4px;">Objectif Jour</div>
                  <div style="font-size: 1.8em; font-weight: bold; color: #6d4c1b;">1800</div>
                  <div style="font-size: 0.8em; color: #999;">kcal</div>
                </div>
              </div>
            </div>

            <!-- GRAPHIQUE 2: EAU (DOUGHNUT CHART) -->
            <div class="card" style="background: #fff; box-shadow: 0 2px 12px #0001; border-radius: 14px; padding: 24px;">
              <h2 style="font-size: 1.3em; font-weight: 600; color: #2980b9; margin: 0 0 20px 0; display: flex; align-items: center; gap: 10px;">
                <span style="font-size: 1.5em;">💧</span> Hydratation
              </h2>
              <div style="position: relative; height: 280px; display: flex; align-items: center; justify-content: center;">
                <canvas id="waterChart"></canvas>
              </div>
              <div style="margin-top: 16px; padding-top: 16px; border-top: 1px solid #eee; text-align: center;">
                <div style="font-size: 0.9em; color: #999; margin-bottom: 8px;">Consommation</div>
                <div style="font-size: 2em; font-weight: bold; color: #2980b9;">
                  <?= number_format($totalWater / 1000, 2, ',', ' ') ?>L / 2L
                </div>
                <div style="font-size: 0.9em; color: #999; margin-top: 8px;">
                  <?= round($waterProgress) ?>% de l'objectif
                </div>
              </div>
            </div>
          </div>
        </section>

        <!-- ===== SECTION 4: AI COACHING & PROFILING ===== -->
        <section class="mb-4">
          <div class="section-header">
            <h2 class="section-title">
              <span class="section-icon">🤖</span>
              Coaching IA & Profil Comportemental
            </h2>
          </div>

          <div class="coaching-grid">
            <!-- PROFIL UTILISATEUR -->
            <div class="card coaching-card">
              <div class="card-header">
                <h3 style="margin: 0; color: #6d4c1b; display: flex; align-items: center; gap: 8px;">
                  <span style="font-size: 1.2em;">👤</span>
                  Votre Profil
                </h3>
              </div>
              <div id="user-profile-display" class="profile-display">
                <div class="profile-loading">
                  <div class="mini-spinner"></div>
                  <p>Analyse de votre profil...</p>
                </div>
              </div>
            </div>

            <!-- CONSEILS DE COACHING -->
            <div class="card coaching-card">
              <div class="card-header">
                <h3 style="margin: 0; color: #6d4c1b; display: flex; align-items: center; gap: 8px;">
                  <span style="font-size: 1.2em;">🎯</span>
                  Conseils Personnalisés
                </h3>
              </div>
              <div id="coaching-messages" class="coaching-messages">
                <div class="coaching-loading">
                  <div class="mini-spinner"></div>
                  <p>Génération de conseils...</p>
                </div>
              </div>
            </div>

            <!-- PLAN D'ACTION DU JOUR -->
            <div class="card coaching-card">
              <div class="card-header">
                <h3 style="margin: 0; color: #6d4c1b; display: flex; align-items: center; gap: 8px;">
                  <span style="font-size: 1.2em;">📋</span>
                  Plan d'action du jour
                </h3>
              </div>
              <div id="coaching-plan" class="coaching-messages">
                <div class="coaching-loading">
                  <div class="mini-spinner"></div>
                  <p>Génération du plan...</p>
                </div>
              </div>
          </div>
        </section>

        <!-- ===== SECTION 5: STATISTIQUES RAPIDES ===== -->
        <section class="mb-4">
          <div class="section-header">
            <h2 class="section-title">
              <span class="section-icon">📈</span>
              Statistiques rapides
            </h2>
          </div>
          
          <div class="summary-cards">
            <article class="summary-card">
              <h3>Calories Consommées</h3>
              <p class="big" style="color: #c0392b;"><?= number_format((int)$totalCaloriesConsumed, 0, ',', ' ') ?></p>
              <p class="summary-card-hint mb-0"><?= count($todayEntries) ?> entrée<?= count($todayEntries) > 1 ? 's' : '' ?></p>
            </article>
            <article class="summary-card">
              <h3>Calories Brûlées</h3>
              <p class="big" style="color: #27ae60;"><?= number_format((int)$totalCaloriesBurned, 0, ',', ' ') ?></p>
              <p class="summary-card-hint mb-0"><?= (int)$totalActivityDuration ?> min d'activité</p>
            </article>
            <article class="summary-card">
              <h3>Eau Consommée</h3>
              <p class="big" style="color: #2980b9;"><?= number_format($totalWater / 1000, 2, ',', ' ') ?>L</p>
              <p class="summary-card-hint mb-0"><?= round($waterProgress) ?>% objectif</p>
            </article>
            <article class="summary-card">
              <h3>Streak</h3>
              <p class="big" style="color: #6d4c1b;"><?= (int)$journalStreak ?> j.</p>
              <p class="summary-card-hint mb-0">Jours consécutifs</p>
            </article>
          </div>
        </section>

        <!-- ===== SECTION 3: JOURNAL ALIMENTAIRE DÉTAIL ===== -->
        <section class="mb-4">
          <div class="section-header">
            <h2 class="section-title">
              <span class="section-icon">🥗</span>
              Journal alimentaire
            </h2>
            <a href="journal-alimentaire.php" class="btn btn-sm btn-secondary">Voir tout</a>
          </div>
          
          <?php if (!empty($todayEntries)): ?>
          <div class="card">
            <div class="table-wrap">
              <table class="fw-table">
                <thead>
                  <tr>
                    <th>Repas</th>
                    <th>Aliment</th>
                    <th class="num">Quantité</th>
                    <th class="num">Calories</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach (array_slice($todayEntries, 0, 8) as $entry): ?>
                    <tr>
                      <td><span class="badge badge-brun"><?= htmlspecialchars($entry['meal_type'], ENT_QUOTES, 'UTF-8') ?></span></td>
                      <td><?= htmlspecialchars($entry['food'], ENT_QUOTES, 'UTF-8') ?></td>
                      <td class="num"><?= htmlspecialchars($entry['quantity'], ENT_QUOTES, 'UTF-8') ?></td>
                      <td class="num" style="color: #c0392b; font-weight: 600;"><?= htmlspecialchars((string)$entry['calories'], ENT_QUOTES, 'UTF-8') ?></td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </div>
          <?php else: ?>
          <div class="card">
            <p class="text-muted text-center mb-0 py-4">Aucune entrée enregistrée aujourd'hui.</p>
          </div>
          <?php endif; ?>
        </section>

        <!-- ===== SECTION 4: ACTIVITÉ PHYSIQUE DÉTAIL ===== -->
        <section>
          <div class="section-header">
            <h2 class="section-title">
              <span class="section-icon">🏃</span>
              Activité physique
            </h2>
            <a href="suivi-sante-unifie.php" class="btn btn-sm btn-secondary">Voir tout</a>
          </div>
          
          <?php if (!empty($dailySuivis)): ?>
          <div class="card">
            <div class="table-wrap">
              <table class="fw-table">
                <thead>
                  <tr>
                    <th>Type d'activité</th>
                    <th class="num">Durée</th>
                    <th>Intensité</th>
                    <th class="num">Calories</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach (array_slice($dailySuivis, 0, 8) as $suivi): ?>
                    <tr>
                      <td><?= htmlspecialchars($suivi['type_activite'], ENT_QUOTES, 'UTF-8') ?></td>
                      <td class="num"><?= (int)$suivi['duree'] ?> min</td>
                      <td>
                        <?php 
                        $intensityClass = match($suivi['intensite']) {
                            'faible' => 'badge-vert',
                            'moyen' => 'badge-brun',
                            'élevé' => 'badge-rouge',
                            default => 'badge-brun'
                        };
                        ?>
                        <span class="badge <?= $intensityClass ?>"><?= htmlspecialchars(ucfirst($suivi['intensite']), ENT_QUOTES, 'UTF-8') ?></span>
                      </td>
                      <td class="num" style="color: #27ae60; font-weight: 600;"><?= htmlspecialchars((string)$suivi['calories_brulees'], ENT_QUOTES, 'UTF-8') ?></td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </div>
          <?php else: ?>
          <div class="card">
            <p class="text-muted text-center mb-0 py-4">Aucune activité enregistrée aujourd'hui.</p>
          </div>
          <?php endif; ?>
        </section>

      </main>
    </div>
  </div>
</body>

<!-- Chart.js Library -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<script>
  // Données PHP en JSON pour Chart.js
  const chartData = {
    caloriesConsumed: <?= (int)$totalCaloriesConsumed ?>,
    caloriesBurned: <?= (int)$totalCaloriesBurned ?>,
    waterIntake: <?= (int)$totalWater ?>,
    waterGoal: 2000,
    calorieBalance: <?= (int)$calorieBalance ?>
  };

  // ===== GRAPHIQUE 1: BAR CHART - CALORIES =====
  const caloriesCtx = document.getElementById('caloriesChart');
  if (caloriesCtx) {
    new Chart(caloriesCtx, {
      type: 'bar',
      data: {
        labels: ['Consommées', 'Brûlées'],
        datasets: [
          {
            label: 'Calories',
            data: [chartData.caloriesConsumed, chartData.caloriesBurned],
            backgroundColor: [
              'rgba(192, 57, 43, 0.8)',
              'rgba(39, 174, 96, 0.8)'
            ],
            borderColor: [
              'rgba(192, 57, 43, 1)',
              'rgba(39, 174, 96, 1)'
            ],
            borderWidth: 2,
            borderRadius: 8,
            borderSkipped: false,
            barThickness: 60
          }
        ]
      },
      options: {
        indexAxis: 'y',
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
          legend: {
            display: false
          },
          tooltip: {
            backgroundColor: 'rgba(0, 0, 0, 0.8)',
            padding: 12,
            titleFont: { size: 14, weight: 'bold' },
            bodyFont: { size: 13 },
            callbacks: {
              label: function(context) {
                return context.parsed.x + ' kcal';
              }
            }
          }
        },
        scales: {
          x: {
            beginAtZero: true,
            ticks: {
              font: { size: 12 },
              callback: function(value) {
                return value + ' kcal';
              }
            },
            grid: {
              color: 'rgba(0, 0, 0, 0.05)'
            }
          },
          y: {
            ticks: {
              font: { size: 13, weight: '600' }
            },
            grid: {
              display: false
            }
          }
        }
      }
    });
  }

  // ===== GRAPHIQUE 2: DOUGHNUT CHART - EAU =====
  const waterCtx = document.getElementById('waterChart');
  if (waterCtx) {
    const waterConsumed = Math.min(chartData.waterIntake, chartData.waterGoal);
    const waterRemaining = chartData.waterGoal - waterConsumed;

    new Chart(waterCtx, {
      type: 'doughnut',
      data: {
        labels: ['Consommée', 'Restant'],
        datasets: [
          {
            data: [waterConsumed, Math.max(0, waterRemaining)],
            backgroundColor: [
              'rgba(41, 128, 185, 0.85)',
              'rgba(189, 195, 199, 0.3)'
            ],
            borderColor: [
              'rgba(41, 128, 185, 1)',
              'rgba(189, 195, 199, 1)'
            ],
            borderWidth: 2,
            borderRadius: [8, 8]
          }
        ]
      },
      options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
          legend: {
            position: 'bottom',
            labels: {
              font: { size: 12, weight: '600' },
              padding: 16,
              usePointStyle: true,
              pointStyle: 'circle'
            }
          },
          tooltip: {
            backgroundColor: 'rgba(0, 0, 0, 0.8)',
            padding: 12,
            titleFont: { size: 14, weight: 'bold' },
            bodyFont: { size: 13 },
            callbacks: {
              label: function(context) {
                const value = context.parsed;
                const percentage = ((value / chartData.waterGoal) * 100).toFixed(1);
                return value + ' mL (' + percentage + '%)';
              }
            }
          }
        }
      }
    });
  }
</script>

<!-- AI Coaching & Profiling Integration -->
<script>
  let coachingAiInitialized = false;

  function initializeCoachingAI() {
    if (coachingAiInitialized) return;
    coachingAiInitialized = true;

    const profileContainer = document.getElementById('user-profile-display');
    const coachingContainer = document.getElementById('coaching-messages');
    if (!profileContainer || !coachingContainer) return;

    const renderError = (container, message) => {
      container.innerHTML = `
        <div class="coaching-error">
          <strong>Coach IA indisponible</strong>
          <p>${message}</p>
        </div>
      `;
    };

    if (typeof window.nutritionCoach === 'undefined' || typeof window.userProfiler === 'undefined') {
      console.error('Coaching systems not loaded');
      renderError(profileContainer, 'Chargement du système de coaching IA impossible. Actualisez la page.');
      renderError(coachingContainer, 'Le module de coaching ne s\'est pas chargé. Actualisez la page.');
      return;
    }

    const userData = {
      calories: <?= (int)$totalCaloriesConsumed ?>,
      proteins: <?= round($totalProteins, 1) ?>,
      carbs: <?= round($totalCarbs, 1) ?>,
      fats: <?= round($totalFats, 1) ?>,
      sugar: 0, // non stocké en base actuellement
      water: <?= (int)$totalWater ?>,
      caloriesBurned: <?= (int)$totalCaloriesBurned ?>,
      goal: 'fitness',
      history: <?= json_encode(array_map(fn($d) => [
        'calories'      => (int)$d['calories'],
        'proteins'      => (float)$d['proteins'],
        'carbs'         => (float)$d['carbs'],
        'fats'          => (float)$d['fats'],
        'sugar'         => 0,
        'water'         => 0,
        'caloriesBurned'=> 0,
      ], $history7Days), JSON_THROW_ON_ERROR) ?>
    };

    const profileData = {
      ...userData,
      meals: 4,
      frequency: 4
    };

    const coachMessages = window.nutritionCoach.generateCoachMessages(userData);
    const coachPlan = window.nutritionCoach.generateCoachPlan(userData);
    const userProfile = window.userProfiler.profileUser(profileData);

    displayUserProfile(userProfile);
    displayCoachingMessages(coachMessages);
    displayCoachingPlan(coachPlan);
  }

  document.addEventListener('DOMContentLoaded', initializeCoachingAI);
  window.addEventListener('load', initializeCoachingAI);

  function displayUserProfile(profile) {
    const container = document.getElementById('user-profile-display');
    if (!container) return;

    const profileEmojis = {
      'healthy': '🥗',
      'junk_food': '🍔',
      'sportif': '🏃‍♂️'
    };

    const profileNames = {
      'healthy': 'Équilibré',
      'junk_food': 'À améliorer',
      'sportif': 'Sportif'
    };

    const profileColors = {
      'healthy': '#27ae60',
      'junk_food': '#e74c3c',
      'sportif': '#3498db'
    };

    const html = `
      <div class="profile-result">
        <div class="profile-hero" style="background: linear-gradient(135deg, ${lightenHex(profileColors[profile.profile], 30)}, ${profileColors[profile.profile]});">
          <div class="profile-hero-avatar">
            <div class="avatar-circle">
              <span class="avatar-icon">🤖</span>
              <div class="avatar-pulse"></div>
            </div>
            <div>
              <div class="profile-hero-title">${profileNames[profile.profile]}</div>
              <div class="profile-hero-subtitle">${getProfileGreeting(profile.profile)}</div>
            </div>
          </div>
          <div class="profile-hero-mission">
            <span>Mission</span>
            <strong>${getProfileMission(profile.profile)}</strong>
          </div>
        </div>

        <div class="profile-score-card">
          <div class="profile-score">
            <div class="score-number">${profile.score}</div>
            <div class="score-label">Indice global</div>
          </div>
        </div>

        <div class="profile-reasons">
          <h4>Pourquoi ce profil ?</h4>
          <div class="profile-chip-group">
            ${profile.reasons.map(reason => `<span class="profile-chip">${reason}</span>`).join('')}
          </div>
        </div>
      </div>
    `;

    container.innerHTML = html;
  }

  function displayCoachingMessages(messages) {
    const container = document.getElementById('coaching-messages');
    if (!container) return;

    if (messages.length === 0) {
      container.innerHTML = `
        <div class="coaching-empty">
          <span style="font-size: 2em;">✅</span>
          <p>Parfait ! Aucun conseil spécifique nécessaire aujourd'hui.</p>
        </div>
      `;
      return;
    }

    const html = messages.slice(0, 4).map((message, index) => {
      const icon = getCoachingIcon(message.type);
      const color = getCoachingColor(message.type);
      const title = getCoachingTitle(message.type);
      const priorityLabel = message.priority >= 4 ? 'Urgent' : message.priority === 3 ? 'Important' : 'Conseil';
      const delay = (index * 0.08).toFixed(2);

      return `
        <div class="coaching-message ${message.type}" style="border-left-color: ${color}; animation-delay: ${delay}s;">
          <div class="message-tag" style="background: ${color}22; color: ${color};">${priorityLabel}</div>
          <div class="message-icon">${icon}</div>
          <div class="message-content">
            <div class="message-title">${title}</div>
            <div class="message-text">${message.message}</div>
          </div>
        </div>
      `;
    }).join('');

    container.innerHTML = `<div class="coaching-list">${html}</div>`;
  }

  function getCoachingIcon(type) {
    switch(type) {
      case 'motivation': return '🎉';
      case 'warning': return '⚠️';
      case 'advice': return '💡';
      default: return 'ℹ️';
    }
  }

  function getCoachingColor(type) {
    switch(type) {
      case 'motivation': return '#27ae60';
      case 'warning': return '#e67e22';
      case 'advice': return '#3498db';
      default: return '#95a5a6';
    }
  }

  function getCoachingTitle(type) {
    switch(type) {
      case 'motivation': return 'Motivation';
      case 'warning': return 'Attention';
      case 'advice': return 'Conseil';
      default: return 'Info';
    }
  }

  function getProfileGreeting(profile) {
    switch(profile) {
      case 'healthy': return 'Vous êtes dans une belle dynamique équilibrée.';
      case 'junk_food': return 'Attention : quelques leviers à prioriser.';
      case 'sportif': return 'Force et discipline : continuez sur cette voie.';
      default: return 'Profil en cours d’analyse.';
    }
  }

  function getProfileMission(profile) {
    switch(profile) {
      case 'healthy': return 'Maintenir l’équilibre et rester constant.';
      case 'junk_food': return 'Alléger le sucre et stabiliser l’énergie.';
      case 'sportif': return 'Optimiser la récupération et l’endurance.';
      default: return 'Continuez à suivre vos habitudes.';
    }
  }

  function lightenHex(hex, percent) {
    if (!hex) return '#ffffff';
    const normalized = hex.replace('#', '');
    const num = parseInt(normalized, 16);
    const r = Math.min(255, Math.floor((num >> 16) * (1 + percent / 100)));
    const g = Math.min(255, Math.floor(((num >> 8) & 0x00FF) * (1 + percent / 100)));
    const b = Math.min(255, Math.floor((num & 0x0000FF) * (1 + percent / 100)));
    return `#${(r.toString(16).padStart(2, '0'))}${(g.toString(16).padStart(2, '0'))}${(b.toString(16).padStart(2, '0'))}`;
  }

  function displayCoachingPlan(plan) {
    const container = document.getElementById('coaching-plan');
    if (!container) return;

    if (!plan || plan.length === 0) {
      container.innerHTML = `
        <div class="coaching-empty">
          <span style="font-size: 2em;">✅</span>
          <p>Journée bien équilibrée. Aucun ajustement nécessaire.</p>
        </div>`;
      return;
    }

    const tagColors = {
      'Focus': '#9b59b6',
      'Action': '#e67e22',
      'Conseil': '#3498db',
      'Motivation': '#27ae60',
      'Urgent': '#e74c3c',
      'Maintien': '#7f8c8d'
    };

    const html = plan.map((item, index) => {
      const color = tagColors[item.tag] || '#6d4c1b';
      const delay = (index * 0.08).toFixed(2);
      return `
        <div class="coaching-message advice" style="border-left-color: ${color}; animation-delay: ${delay}s;">
          <div class="message-tag" style="background: ${color}22; color: ${color};">${item.tag}</div>
          <div class="message-content">
            <div class="message-title">${item.title}</div>
            <div class="message-text">${item.detail}</div>
          </div>
        </div>`;
    }).join('');

    container.innerHTML = `<div class="coaching-list">${html}</div>`;
  }
</script>

<style>
  @keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
  }

  .charts-container {
    margin-bottom: 24px;
  }

  .section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 16px;
    padding-bottom: 8px;
    border-bottom: 2px solid #a67c52;
  }

  .section-title {
    display: flex;
    align-items: center;
    gap: 10px;
    margin: 0;
    font-size: 1.25rem;
    color: #6d4c1b;
    font-weight: 600;
  }

  .spinner {
    width: 42px;
    height: 42px;
    border: 5px solid #f1f3f5;
    border-top: 5px solid #6d4c1b;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin: 0 auto;
  }

  .summary-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 16px;
    margin-bottom: 16px;
  }

  /* ===== AI COACHING & PROFILING STYLES ===== */
  .coaching-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: 24px;
    margin-bottom: 24px;
  }

  .coaching-card {
    background: #ffffff;
    border-radius: 16px;
    box-shadow: 0 8px 32px rgba(15, 23, 42, 0.08);
    border: 1px solid rgba(140, 101, 64, 0.12);
    overflow: hidden;
  }

  .coaching-card .card-header {
    padding: 20px 24px;
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    border-bottom: 1px solid rgba(0, 0, 0, 0.06);
  }

  .coaching-card .card-header h3 {
    font-size: 1.1rem;
    font-weight: 600;
  }

  /* Profile Display */
  .profile-display {
    padding: 24px;
  }

  .profile-loading, .coaching-loading {
    text-align: center;
    padding: 40px 20px;
  }

  .mini-spinner {
    width: 32px;
    height: 32px;
    border: 3px solid #f1f3f5;
    border-top: 3px solid #6d4c1b;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin: 0 auto 12px;
  }

  .profile-result {
    display: flex;
    flex-direction: column;
    gap: 20px;
  }

  .profile-badge {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 16px 20px;
    border-radius: 12px;
    color: white;
    font-weight: 600;
  }

  .profile-emoji {
    font-size: 1.5em;
  }

  .profile-name {
    font-size: 1.1em;
  }

  .profile-hero {
    display: grid;
    gap: 18px;
    padding: 24px;
    border-radius: 22px;
    color: white;
    overflow: hidden;
    box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.12);
    position: relative;
  }

  .profile-hero::before {
    content: '';
    position: absolute;
    top: 20px;
    right: 20px;
    width: 100px;
    height: 100px;
    background: rgba(255, 255, 255, 0.12);
    border-radius: 50%;
    filter: blur(18px);
  }

  .profile-hero-avatar {
    display: flex;
    align-items: center;
    gap: 18px;
  }

  .avatar-circle {
    width: 76px;
    height: 76px;
    border-radius: 24px;
    background: rgba(255, 255, 255, 0.22);
    display: grid;
    place-items: center;
    position: relative;
    overflow: hidden;
    border: 1px solid rgba(255, 255, 255, 0.38);
    box-shadow: 0 18px 40px rgba(15, 23, 42, 0.12);
  }

  .avatar-circle::before {
    content: '';
    position: absolute;
    inset: 0;
    border-radius: 24px;
    border: 1px solid rgba(255, 255, 255, 0.35);
    box-shadow: inset 0 0 30px rgba(255, 255, 255, 0.22);
  }

  .avatar-icon {
    font-size: 2rem;
    position: relative;
    z-index: 2;
  }

  .avatar-pulse {
    position: absolute;
    width: 110%;
    height: 110%;
    top: -5%;
    left: -5%;
    border-radius: 28px;
    background: rgba(255, 255, 255, 0.24);
    animation: pulseAvatar 2.5s ease-in-out infinite;
    z-index: 1;
  }

  .profile-hero-title {
    font-size: 1.2rem;
    font-weight: 700;
  }

  .profile-hero-subtitle {
    margin-top: 4px;
    font-size: 0.95rem;
    color: rgba(255, 255, 255, 0.9);
  }

  .profile-hero-title {
    font-size: 1.2rem;
    font-weight: 700;
  }

  .profile-hero-subtitle {
    margin-top: 4px;
    font-size: 0.95rem;
    color: rgba(255, 255, 255, 0.85);
  }

  .profile-hero-mission {
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
    padding: 16px 18px;
    border-radius: 18px;
    background: rgba(255, 255, 255, 0.18);
    border: 1px solid rgba(255, 255, 255, 0.18);
    font-weight: 600;
    letter-spacing: 0.01em;
  }

  .profile-score-card {
    border-radius: 18px;
    overflow: hidden;
    box-shadow: 0 14px 48px rgba(15, 23, 42, 0.08);
    border: 1px solid rgba(108, 86, 56, 0.12);
  }

  .profile-score-card::before {
    content: '';
    display: block;
    height: 4px;
    background: linear-gradient(90deg, rgba(23, 198, 113, 1), rgba(15, 130, 82, 1));
  }

  .profile-score {
    text-align: center;
    padding: 28px 20px;
    background: linear-gradient(135deg, #f8fafc 0%, #ffffff 100%);
  }

  .score-number {
    font-size: 3rem;
    font-weight: 900;
    color: #6d4c1b;
    line-height: 1;
  }

  .score-label {
    font-size: 0.85rem;
    color: #7a5f3b;
    margin-top: 6px;
  }

  .profile-chip-group {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-top: 16px;
  }

  .profile-chip {
    display: inline-flex;
    align-items: center;
    padding: 10px 14px;
    border-radius: 999px;
    background: rgba(38, 70, 83, 0.07);
    color: #2f3940;
    font-size: 0.88rem;
  }

  .score-number {
    font-size: 2.5em;
    font-weight: 800;
    color: #6d4c1b;
    line-height: 1;
  }

  .score-label {
    font-size: 0.9em;
    color: #666;
    margin-top: 4px;
  }

  .profile-reasons h4 {
    margin: 0 0 12px 0;
    font-size: 1rem;
    color: #374151;
    font-weight: 600;
  }

  .profile-reasons ul {
    margin: 0;
    padding-left: 20px;
  }

  .profile-reasons li {
    margin-bottom: 6px;
    color: #6b7280;
    line-height: 1.4;
  }

  /* Coaching Messages */
  .coaching-messages {
    padding: 24px;
  }

  .coaching-empty {
    text-align: center;
    padding: 40px 20px;
    color: #27ae60;
  }

  .coaching-empty span {
    display: block;
    margin-bottom: 12px;
  }

  .coaching-plan {
    padding: 24px;
  }

  .plan-summary {
    margin-bottom: 18px;
    color: #475569;
  }

  .plan-list {
    display: grid;
    gap: 14px;
    margin: 0;
    padding: 0;
    list-style: none;
  }

  .plan-item {
    display: flex;
    gap: 14px;
    align-items: flex-start;
    background: #f9fbff;
    border: 1px solid rgba(84, 112, 255, 0.12);
    border-radius: 16px;
    padding: 16px 18px;
  }

  .plan-dot {
    width: 12px;
    height: 12px;
    margin-top: 8px;
    border-radius: 50%;
    background: linear-gradient(180deg, #3b82f6, #6366f1);
    flex-shrink: 0;
  }

  .plan-item strong {
    display: block;
    margin-bottom: 6px;
    color: #1f2937;
    font-size: 0.98rem;
  }

  .plan-item p {
    margin: 0;
    color: #475569;
    line-height: 1.6;
  }

  .plan-extra {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 14px;
    margin-top: 18px;
  }

  .plan-card {
    padding: 16px 18px;
    border-radius: 16px;
    background: #eff6ff;
    border: 1px solid rgba(59, 130, 246, 0.18);
  }

  .plan-card h4 {
    margin: 0 0 8px 0;
    font-size: 0.95rem;
    color: #1d4ed8;
  }

  .plan-card p {
    margin: 0;
    color: #475569;
    line-height: 1.6;
  }

  .coaching-error {
    padding: 28px 20px;
    border-radius: 18px;
    background: #fff4f0;
    border: 1px solid #f5c6bc;
    color: #a33a1f;
    box-shadow: 0 12px 24px rgba(163, 58, 31, 0.08);
  }

  .coaching-error strong {
    display: block;
    margin-bottom: 10px;
    font-size: 1rem;
  }

  .coaching-error p {
    margin: 0;
    line-height: 1.6;
    color: #7a3b2c;
  }

  .coaching-empty p {
    margin: 0;
    color: #6b7280;
    font-size: 1rem;
  }

  .coach-plan {
    padding: 24px;
  }

  .coach-plan-summary {
    background: #eef6ff;
    border: 1px solid rgba(52, 152, 219, 0.16);
    color: #1f4d7a;
    border-radius: 16px;
    padding: 18px 20px;
    margin-bottom: 18px;
    font-size: 0.95rem;
    line-height: 1.7;
  }

  .coach-plan-list {
    display: grid;
    gap: 14px;
  }

  .coach-plan-step {
    display: grid;
    grid-template-columns: auto 1fr;
    gap: 16px;
    align-items: start;
    padding: 18px 20px;
    border-radius: 18px;
    background: #ffffff;
    border: 1px solid rgba(52, 152, 219, 0.12);
    box-shadow: 0 14px 32px rgba(15, 23, 42, 0.05);
  }

  .plan-step-badge {
    min-width: 88px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 8px 12px;
    border-radius: 999px;
    background: rgba(52, 152, 219, 0.1);
    color: #1f4d7a;
    font-size: 0.78rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.08em;
  }

  .plan-step-title {
    margin: 0 0 8px 0;
    font-weight: 700;
    color: #2c3e50;
  }

  .coach-plan-step p {
    margin: 0;
    color: #475569;
    line-height: 1.7;
  }

  .coaching-list {
    display: flex;
    flex-direction: column;
    gap: 16px;
  }

  .coaching-message {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 18px 20px;
    border-radius: 18px;
    background: #f8fbff;
    border-left: 6px solid transparent;
    box-shadow: 0 16px 40px rgba(15, 23, 42, 0.04);
    transition: transform 0.25s ease, background 0.25s ease;
    animation: fadeInUp 0.5s ease forwards;
    opacity: 0;
    transform: translateY(18px);
  }

  .coaching-message:hover {
    background: #eef6ff;
    transform: translateX(4px);
  }

  .message-tag {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 82px;
    padding: 8px 12px;
    border-radius: 999px;
    font-size: 0.75rem;
    font-weight: 700;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    border: 1px solid rgba(0,0,0,0.05);
    box-shadow: inset 0 1px 0 rgba(255,255,255,0.4);
  }

  .message-icon {
    width: 40px;
    min-width: 40px;
    height: 40px;
    display: grid;
    place-items: center;
    border-radius: 10px;
    background: #ffffff;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    font-size: 1.2em;
  }

  .message-tag {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 76px;
    padding: 6px 10px;
    border-radius: 999px;
    font-size: 0.75rem;
    font-weight: 700;
    letter-spacing: 0.04em;
    text-transform: uppercase;
  }

  .message-content {
    flex: 1;
  }

  .message-title {
    font-weight: 600;
    font-size: 0.95rem;
    color: #374151;
    margin-bottom: 6px;
  }

  .message-text {
    color: #475569;
    line-height: 1.7;
    font-size: 0.95rem;
  }

  .coaching-message.motivation {
    border-left-color: #27ae60;
  }

  @keyframes fadeInUp {
    to {
      opacity: 1;
      transform: translateY(0);
    }
  }

  @keyframes pulseAvatar {
    0%, 100% {
      transform: scale(1);
      opacity: 0.55;
    }
    50% {
      transform: scale(1.12);
      opacity: 0.18;
    }
  }

  .coaching-message.warning {
    border-left-color: #e67e22;
  }

  .coaching-message.advice {
    border-left-color: #3498db;
  }

  .summary-card {
    background: #fff;
    border: 1px solid #e0e0e0;
    border-radius: 12px;
    padding: 20px;
    text-align: center;
    box-shadow: 0 1px 4px rgba(0, 0, 0, 0.05);
    transition: all 0.3s ease;
  }

  .summary-card:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    transform: translateY(-2px);
  }

  .summary-card h3 {
    margin: 0 0 8px 0;
    font-size: 0.9em;
    color: #999;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }

  .summary-card .big {
    font-size: 2em;
    font-weight: 700;
    color: #333;
    margin: 8px 0;
  }

  .summary-card-hint {
    font-size: 0.85em;
    color: #999;
  }

  .badge {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 0.8em;
    font-weight: 600;
  }

  .badge-brun {
    background: #a67c52;
    color: white;
  }

  .badge-vert {
    background: #27ae60;
    color: white;
  }

  .badge-rouge {
    background: #c0392b;
    color: white;
  }

  .card {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.05);
    overflow: hidden;
  }

  .fw-table {
    width: 100%;
    border-collapse: collapse;
  }

  .fw-table thead {
    background: #f8f8f8;
  }

  .fw-table thead th {
    padding: 12px;
    text-align: left;
    font-weight: 600;
    color: #666;
    border-bottom: 2px solid #e0e0e0;
  }

  .fw-table tbody td {
    padding: 12px;
    border-bottom: 1px solid #f0f0f0;
  }

  .fw-table tbody tr:hover {
    background: #fafafa;
  }

  .fw-table .num {
    text-align: right;
  }

  .text-muted {
    color: #999;
  }

  .text-center {
    text-align: center;
  }

  .mb-0 { margin-bottom: 0; }
  .mb-4 { margin-bottom: 24px; }
  .mt-3 { margin-top: 16px; }
  .mt-4 { margin-top: 24px; }
  .py-4 { padding: 24px 0; }

  .btn {
    display: inline-block;
    padding: 8px 16px;
    border-radius: 6px;
    font-weight: 600;
    text-decoration: none;
    font-size: 0.85em;
    cursor: pointer;
    transition: all 0.3s ease;
  }

  .btn-secondary {
    background: #e8e8e8;
    color: #333;
  }

  .btn-secondary:hover {
    background: #d8d8d8;
  }

  @media (max-width: 768px) {
    .charts-container {
      grid-template-columns: 1fr !important;
    }

    .section-header {
      flex-direction: column;
      align-items: flex-start;
      gap: 12px;
    }

    .summary-cards {
      grid-template-columns: 1fr;
    }
  }
</style>

</html>
