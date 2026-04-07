<?php
$activeNav = $activeNav ?? '';
$logoSrc = $logoSrc ?? '../../../public/images/foodwise-logo.png';
?>
<aside class="sidebar" aria-label="Menu client">
    <div class="sidebar-logo">
        <img src="<?php echo htmlspecialchars($logoSrc, ENT_QUOTES, 'UTF-8'); ?>" alt="FoodWise" decoding="async">
    </div>
    <nav class="sidebar-nav">
        <a href="#" class="nav-item <?php echo $activeNav === 'accueil' ? 'active' : ''; ?>"><span class="nav-ico" aria-hidden="true">🏠</span> Accueil</a>
        <a href="smartcart.php" class="nav-item <?php echo $activeNav === 'smartcart' ? 'active' : ''; ?>"><span class="nav-ico" aria-hidden="true">🛒</span> SmartCart</a>
        <a href="#" class="nav-item <?php echo $activeNav === 'offres' ? 'active' : ''; ?>"><span class="nav-ico" aria-hidden="true">🏷️</span> Offres</a>
        <a href="#" class="nav-item <?php echo $activeNav === 'compte' ? 'active' : ''; ?>"><span class="nav-ico" aria-hidden="true">👤</span> Mon compte</a>
    </nav>
</aside>
