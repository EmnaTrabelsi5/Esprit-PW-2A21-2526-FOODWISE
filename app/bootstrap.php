<?php
declare(strict_types=1);

require __DIR__ . '/Config.php';
require __DIR__ . '/helpers.php';
require __DIR__ . '/Models/UtilisateurModel.php';
require __DIR__ . '/Models/ProfilNutritionnelModel.php';
require __DIR__ . '/Controllers/FrontController.php';
require __DIR__ . '/Controllers/BackController.php';

session_start();
$pdo = getPdoConnection();
