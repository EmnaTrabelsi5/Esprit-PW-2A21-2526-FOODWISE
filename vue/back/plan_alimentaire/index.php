<?php
$pageTitle = 'Plans alimentaires — Administration';
$sidebarActive = 'plans';
$area = 'back';
require __DIR__ . '/../layouts/header.php';
require __DIR__ . '/../layouts/sidebar.php';
?>
<div class="layout__main">
<?php require __DIR__ . '/../../partials/plan_alimentaire_index.php'; ?>
</div>
<?php require __DIR__ . '/../layouts/footer.php'; ?>
