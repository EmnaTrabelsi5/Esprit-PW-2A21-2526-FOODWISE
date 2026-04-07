<?php
/** @var string $pageTitle */
/** @var string|null $cssHref Chemin relatif vers la feuille de style */
if (!isset($pageTitle)) {
    $pageTitle = 'FoodWise — Administration';
}
if (!isset($cssHref)) {
    $cssHref = '../../../public/css/smartcart.css';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?></title>
    <link rel="stylesheet" href="<?php echo htmlspecialchars($cssHref, ENT_QUOTES, 'UTF-8'); ?>">
</head>
<body class="theme-admin">
