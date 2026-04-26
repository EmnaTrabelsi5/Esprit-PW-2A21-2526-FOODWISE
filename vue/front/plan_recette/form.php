<?php
$pageTitle = 'Recette de plan — formulaire';
$sidebarActive = 'recettes';
$area = 'front';
$crudFormScript = 'js/crud-validation.js';
require __DIR__ . '/../layouts/header.php';
require __DIR__ . '/../layouts/sidebar.php';
?>
<div class="layout__main">
<?php require __DIR__ . '/../../partials/plan_recette_form.php'; ?>
</div>
<?php require __DIR__ . '/../layouts/footer.php'; ?>
