<?php
/**
 * Barre latérale Front-office — lien actif : $activeNav (ex. 'mon_profil')
 */
declare(strict_types=1);

$activeNav = $activeNav ?? '';
$r = $routesModule2 ?? [];
?>
<aside class="fw-sidebar" role="navigation" aria-label="Navigation principale">
  <a class="fw-brand" href="<?= htmlspecialchars($r['front_mon_profil'] ?? '#', ENT_QUOTES, 'UTF-8') ?>">
    <span class="fw-brand__icon" aria-hidden="true">🍳</span>
    <span>FoodWise</span>
  </a>
  <ul class="fw-nav">
    <li><a href="#"><span class="fw-nav__ico" aria-hidden="true">▣</span> Tableau de Bord</a></li>
    <li>
      <a class="<?= $activeNav === 'mon_profil' ? 'is-active' : '' ?>" href="<?= htmlspecialchars($r['front_mon_profil'] ?? '#', ENT_QUOTES, 'UTF-8') ?>">
        <span class="fw-nav__ico" aria-hidden="true">👤</span> Mon Profil
      </a>
    </li>
    <li><a href="#"><span class="fw-nav__ico" aria-hidden="true">📖</span> Gestion Recettes</a></li>
    <li><a href="#"><span class="fw-nav__ico" aria-hidden="true">🥗</span> Base Ingrédients</a></li>
    <li><a href="#"><span class="fw-nav__ico" aria-hidden="true">🤝</span> Offres Partenaires</a></li>
    <li><a href="#"><span class="fw-nav__ico" aria-hidden="true">📊</span> Suivi Nutritionnel</a></li>
    <li>
      <a class="<?= $activeNav === 'messages' ? 'is-active' : '' ?>" href="<?= htmlspecialchars($r['front_users_list'] ?? '#', ENT_QUOTES, 'UTF-8') ?>">
        <span class="fw-nav__ico" aria-hidden="true">💬</span> Messages
        <span id="fw-unread-badge" style="display: none; margin-left: 0.5rem; background: #d32f2f; color: white; border-radius: 50%; width: 20px; height: 20px; display: inline-flex; align-items: center; justify-content: center; font-size: 0.75rem; font-weight: bold;"></span>
      </a>
    </li>
    <li><a href="#"><span class="fw-nav__ico" aria-hidden="true">⚙</span> Paramètres</a></li>
  </ul>
</aside>
