<?php
/**
 * Barre latérale Back-office — lien actif : $activeNav (ex. 'suivi_nutritionnel')
 */
declare(strict_types=1);

$activeNav = $activeNav ?? '';
$r = $routesModule2 ?? [];
?>
<aside class="fw-sidebar" role="navigation" aria-label="Navigation administration">
  <a class="fw-brand" href="<?= htmlspecialchars($r['back_dashboard_profils'] ?? '#', ENT_QUOTES, 'UTF-8') ?>">
    <span class="fw-brand__icon" aria-hidden="true">🍳</span>
    <span>FoodWise</span>
  </a>
  <ul class="fw-nav">
    <li><a href="#"><span class="fw-nav__ico" aria-hidden="true">▣</span> Tableau de Bord</a></li>
    <li><a href="#"><span class="fw-nav__ico" aria-hidden="true">📖</span> Gestion Recettes</a></li>
    <li><a href="#"><span class="fw-nav__ico" aria-hidden="true">🥗</span> Base Ingrédients</a></li>
    <li><a href="#"><span class="fw-nav__ico" aria-hidden="true">🤝</span> Offres Partenaires</a></li>
    <li>
      <a class="<?= $activeNav === 'suivi_nutritionnel' ? 'is-active' : '' ?>" href="<?= htmlspecialchars($r['back_dashboard_profils'] ?? '#', ENT_QUOTES, 'UTF-8') ?>">
        <span class="fw-nav__ico" aria-hidden="true">📊</span> Suivi Nutritionnel
      </a>
    </li>
    <li><a href="#"><span class="fw-nav__ico" aria-hidden="true">👥</span> Utilisateurs</a></li>
    <li><a href="#"><span class="fw-nav__ico" aria-hidden="true">⚙</span> Paramètres</a></li>
  </ul>
</aside>
