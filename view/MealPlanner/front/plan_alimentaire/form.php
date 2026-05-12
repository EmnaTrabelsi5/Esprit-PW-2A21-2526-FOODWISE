<?php
$pageTitle = 'Plan alimentaire — formulaire';
$sidebarActive = 'plan_alimentaire';
$area = 'front';
$crudFormScript = 'js/crud-validation.js';
include __DIR__ . '/../../../layouts/front/header.php';
?>
<div class="layout__main">
<?php require __DIR__ . '/../../partials/plan_alimentaire_form.php'; ?>
</div>
<?php include __DIR__ . '/../../../layouts/front/footer.php'; ?>


