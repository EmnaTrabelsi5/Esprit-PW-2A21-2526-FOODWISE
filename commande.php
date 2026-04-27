<?php
session_start();

require_once __DIR__ . '/Controller/CommandeController.php';

$controller = new CommandeController();
$controller->handleRequest();
