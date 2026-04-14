<?php
session_start();

require_once __DIR__ . '/Controller/OffreController.php';

$controller = new OffreController();
$controller->handleRequest();