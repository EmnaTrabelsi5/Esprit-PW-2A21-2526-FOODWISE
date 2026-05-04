<?php
$pageTitle = 'Détail objectif — Administration';
$sidebarActive = 'objectif';
$area = 'back';
require __DIR__ . '/../layouts/header.php';
require __DIR__ . '/../layouts/sidebar.php';
?>
<div class="layout__main">
<?php require __DIR__ . '/../../partials/objectif_show.php'; ?>
</div>
<?php require __DIR__ . '/../layouts/footer.php'; ?>
