<?php
/** @var string|null $activeNav Clé de l’entrée active */
$activeNav = $activeNav ?? '';
$logoSrc = $logoSrc ?? '../../../public/images/foodwise-logo.png';
?>
<aside class="sidebar" aria-label="Navigation principale">
    <div class="sidebar-logo">
        <img src="<?php echo htmlspecialchars($logoSrc, ENT_QUOTES, 'UTF-8'); ?>" alt="FoodWise" decoding="async">
    </div>
    <nav class="sidebar-nav">
        <a href="#" class="nav-item <?php echo $activeNav === 'dashboard' ? 'active' : ''; ?>"><span class="nav-ico" aria-hidden="true">📊</span> Tableau de Bord</a>
        <a href="commandes.php" class="nav-item <?php echo $activeNav === 'commandes' ? 'active' : ''; ?>"><span class="nav-ico" aria-hidden="true">🛒</span> Gestion Commandes</a>
        <a href="#" class="nav-item <?php echo $activeNav === 'ingredients' ? 'active' : ''; ?>"><span class="nav-ico" aria-hidden="true">🥬</span> Base Ingrédients</a>
        <a href="#" class="nav-item <?php echo $activeNav === 'offres' ? 'active' : ''; ?>"><span class="nav-ico" aria-hidden="true">🤝</span> Offres Partenaires</a>
        <a href="#" class="nav-item <?php echo $activeNav === 'nutrition' ? 'active' : ''; ?>"><span class="nav-ico" aria-hidden="true">📈</span> Suivi Nutritionnel</a>
        <a href="#" class="nav-item <?php echo $activeNav === 'users' ? 'active' : ''; ?>"><span class="nav-ico" aria-hidden="true">👥</span> Utilisateurs</a>
        <a href="#" class="nav-item <?php echo $activeNav === 'settings' ? 'active' : ''; ?>"><span class="nav-ico" aria-hidden="true">⚙️</span> Paramètres</a>
    </nav>
</aside>
