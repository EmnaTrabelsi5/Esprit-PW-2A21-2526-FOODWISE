<?php
session_start();

require_once __DIR__ . '/../controller/CommandeController.php';
$model = new CommandeModel();
$model->cancelExpiredOrders();

$controller = new CommandeController();
$controller->handleRequest();


