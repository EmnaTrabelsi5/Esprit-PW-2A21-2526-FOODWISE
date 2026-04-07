<?php
/**
 * Vue principale — Front-office Module 4 MealPlanner
 * Planning hebdomadaire, macros, score santé, alertes allergènes, liste de courses (aperçu)
 */
$pageTitle = 'Module 4 — MealPlanner — Plans alimentaires — FoodWise';
$sidebarActive = 'planning';

$semaineLabel = $semaineLabel ?? '7 Avril — 13 Avril 2026';

require __DIR__ . '/layouts/header.php';
require __DIR__ . '/layouts/sidebar.php';
?>

<div class="layout__main">
  <header class="page-header">
    <div class="page-header__row">
      <h1 class="page-title">Module 4 — MealPlanner — Plans alimentaires</h1>
      <div class="page-header__actions">
        <a class="header-action" href="#">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
          Mon Compte
        </a>
        <a class="header-action" href="#">Aide</a>
      </div>
    </div>
    <div class="search-bar search-bar--full" role="search">
      <svg class="search-bar__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
      <input class="search-bar__input" type="search" name="q" placeholder="Rechercher des Plans Alimentaires, Plats, ou Dates..." aria-label="Recherche">
    </div>
  </header>

  <div class="dashboard-grid dashboard-grid--client">
    <section class="card card--nutrition" aria-labelledby="bilan-heading">
      <div class="card__header" id="bilan-heading">
        <svg class="card__header-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
        Bilan Nutritionnel Hebdomadaire
      </div>
      <div class="card__body">
        <div class="nutrition-lines">
          <p><strong>Total Calories de la Semaine :</strong> 12&nbsp;850 kcal</p>
          <p><strong>Moyenne Journalière :</strong> 1&nbsp;835 kcal</p>
          <p><strong>Macros (semaine) :</strong> Prot. 420 g · Gluc. 1&nbsp;280 g · Lip. 380 g</p>
          <p><strong>Score de Santé Global :</strong> <span class="health-score-excellent">Excellent</span></p>
        </div>
        <div class="shopping-cta">
          <button type="button" class="btn btn--primary">Générer ma liste de courses</button>
        </div>
      </div>
    </section>

    <section class="card card--allergen-client" aria-labelledby="alertes-client-heading">
      <div class="card__header" id="alertes-client-heading">
        <svg class="card__header-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
        Alertes Allergènes
      </div>
      <div class="card__body">
        <p class="nutrition-lines">Votre planning est comparé aux allergènes de votre profil. En cas de conflit, une alerte s’affiche ici et dans les notifications.</p>
        <p class="nutrition-lines" style="margin-top:0.75rem"><strong>Statut :</strong> <span class="health-score-excellent">Aucun conflit détecté cette semaine</span></p>
      </div>
    </section>

    <section class="card card--meal-plan" aria-labelledby="plan-heading">
      <div class="card__header" id="plan-heading">
        <svg class="card__header-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
        Plan de Repas de la Semaine — <?= htmlspecialchars($semaineLabel, ENT_QUOTES, 'UTF-8') ?>
      </div>
      <div class="data-table-wrap">
        <table class="data-table">
          <thead>
            <tr>
              <th>Jour</th>
              <th>Créneau</th>
              <th>Plat / Recette</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $jours = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'];
            $dejeuner = ['Salade César poulet', 'Bowl quinoa légumes', 'Pâtes pesto', 'Wrap thon avocat', 'Riz sauté aux crevettes', 'Omelette champignons', 'Grillades & ratatouille'];
            $diner = ['Papillote saumon', 'Curry pois chiches', 'Soupe miso & tofu', 'Pizza margherita', 'Tajine légumes', 'Tartare tomate-mozza', 'Pot-au-feu maison'];
            foreach ($jours as $i => $jour) :
            ?>
            <tr>
              <td><?= htmlspecialchars($jour, ENT_QUOTES, 'UTF-8') ?></td>
              <td>Déjeuner</td>
              <td><?= htmlspecialchars($dejeuner[$i], ENT_QUOTES, 'UTF-8') ?></td>
              <td><button type="button" class="btn btn--primary">Editer/Remplacer</button></td>
            </tr>
            <tr>
              <td><?= htmlspecialchars($jour, ENT_QUOTES, 'UTF-8') ?></td>
              <td>Dîner</td>
              <td><?= htmlspecialchars($diner[$i], ENT_QUOTES, 'UTF-8') ?></td>
              <td><button type="button" class="btn btn--primary">Editer/Remplacer</button></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </section>
  </div>
</div>

<aside class="layout__notifications" aria-label="Notifications">
  <div class="notifications-panel">
    <div class="notifications-panel__header">Notifications</div>
    <div class="notifications-panel__body">
      <div class="notification-item notification-item--brown">
        <svg class="notification-item__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
        <span><strong>Alerte Allergène :</strong> vérifiez le plat du dîner « papillote » du mardi si vous avez des allergies poisson.</span>
      </div>
      <div class="notification-item notification-item--brown">
        <svg class="notification-item__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
        <span><strong>Nouveaux Plans Suggérés</strong> — des menus adaptés à votre profil sont disponibles.</span>
      </div>
      <div class="notification-item notification-item--green">
        <svg class="notification-item__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
        <span><strong>Derniers Plans Suggérés :</strong> 5 propositions à consulter.</span>
      </div>
    </div>
  </div>
</aside>

<?php require __DIR__ . '/layouts/footer.php'; ?>
