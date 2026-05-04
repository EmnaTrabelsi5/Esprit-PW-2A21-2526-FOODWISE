<?php
/**
 * Ancienne maquette statique : l’application passe par le routeur.
 * Redirection vers le point d'entrée public unique.
 */
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '/index.php');
$dir = dirname($script);
// Remonter de views/front/
$base = dirname(dirname($dir));
$publicPath = ($base === '/' || $base === '.') ? '/public/index.php' : $base . '/public/index.php';
$url = $protocol . '://' . $host . $publicPath . '?area=front&resource=home&action=index';

header('Location: ' . $url, true, 302);
exit;
