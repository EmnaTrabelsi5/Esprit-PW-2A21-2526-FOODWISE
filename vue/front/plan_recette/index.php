<?php
$pageTitle = 'Plans recettes — MealPlanner (client)';
$sidebarActive = 'recettes';
$area = 'front';
require __DIR__ . '/../layouts/header.php';
require __DIR__ . '/../layouts/sidebar.php';
?>
<div class="layout__main">
<?php require __DIR__ . '/../../partials/plan_recette_index.php'; ?>
</div>
<?php require __DIR__ . '/../layouts/footer.php'; ?>
