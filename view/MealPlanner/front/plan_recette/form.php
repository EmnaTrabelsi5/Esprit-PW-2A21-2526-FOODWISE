<?php
$pageTitle = 'Recette de plan — formulaire';
$sidebarActive = 'recettes';
$area = 'front';
$crudFormScript = 'js/crud-validation.js';
include __DIR__ . '/../../../layouts/front/header.php';
?>
<div class="layout__main">
<?php require __DIR__ . '/../../partials/plan_recette_form.php'; ?>
</div>
<?php include __DIR__ . '/../../../layouts/front/footer.php'; ?>


