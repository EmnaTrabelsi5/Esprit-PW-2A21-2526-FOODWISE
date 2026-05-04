<?php
session_start();
require_once __DIR__ . '/Model/CommandeModel.php';
require_once __DIR__ . '/Config.php';

echo "<h1>Test Form Submission</h1>";

// Simulate form data
$_POST['id_offre'] = 1;
$_POST['quantite'] = 2;
$_POST['adresse'] = "123 Rue Test";
$_POST['telephone'] = "21234567";
$_POST['mode_paiement'] = "carte";
$_POST['note'] = "Test commande";

echo "<h2>Form Data Received:</h2>";
echo "<pre>";
print_r($_POST);
echo "</pre>";

// Parse values like the controller does
$id_offre  = (int)($_POST['id_offre'] ?? 0);
$quantite  = (int)($_POST['quantite'] ?? 0);
$id_client = $_SESSION['user_id'] ?? 1;
$adresse = trim($_POST['adresse'] ?? '');
$telephone = trim($_POST['telephone'] ?? '');
$mode_paiement = $_POST['mode_paiement'] ?? '';
$note = trim($_POST['note'] ?? '');

echo "<h2>Parsed Values:</h2>";
echo "id_offre: $id_offre<br>";
echo "quantite: $quantite<br>";
echo "id_client: $id_client<br>";
echo "adresse: $adresse<br>";
echo "telephone: $telephone<br>";
echo "mode_paiement: $mode_paiement<br>";
echo "note: $note<br>";

// Validation like the controller does
echo "<h2>Server-side Validation:</h2>";
$errors = [];
if ($quantite <= 0) {
    $errors[] = "La quantité doit être supérieure à 0.";
}
if (empty($adresse)) {
    $errors[] = "L'adresse de livraison est requise.";
}
if (empty($telephone) || !preg_match('/^[0-9]{8}$/', $telephone)) {
    $errors[] = "Le numéro de téléphone doit contenir 8 chiffres.";
}
if (empty($mode_paiement)) {
    $errors[] = "Le mode de paiement est requis.";
}

if (!empty($errors)) {
    echo "<p style='color: red;'><strong>Validation Errors:</strong></p>";
    foreach ($errors as $error) {
        echo "✗ $error<br>";
    }
} else {
    echo "<p style='color: green;'>✓ All validation passed</p>";
}

// Test database insert
echo "<h2>Database Check:</h2>";
$db = config::getConnexion();

// First, ensure all offers are disponible
$db->query("UPDATE `Offre` SET statut = 'disponible' WHERE statut = 'expire'");

// Get the offer
$sql = "SELECT id, stock, statut FROM `Offre` WHERE id = ?";
$stmt = $db->prepare($sql);
$stmt->execute([$id_offre]);
$offre = $stmt->fetch();

if ($offre) {
    echo "<p>Offre: " . json_encode($offre) . "</p>";
    if ($offre['statut'] !== 'disponible') {
        echo "<p style='color: red;'>✗ Offre status is not 'disponible': {$offre['statut']}</p>";
    } else {
        echo "<p style='color: green;'>✓ Offre is disponible</p>";
    }
    
    if ($offre['stock'] < $quantite) {
        echo "<p style='color: red;'>✗ Stock insufficient: available={$offre['stock']}, requested=$quantite</p>";
    } else {
        echo "<p style='color: green;'>✓ Stock sufficient</p>";
    }
}

// Now call CommandeModel::create()
echo "<h2>CommandeModel::create() Test:</h2>";
$result = CommandeModel::create($id_offre, $id_client, $quantite, $adresse, $telephone, $mode_paiement, $note);

if ($result) {
    echo "<p style='color: green;'><strong>✓ SUCCESS!</strong> Commande created successfully</p>";
} else {
    echo "<p style='color: red;'><strong>✗ FAILED!</strong> CommandeModel::create() returned false</p>";
}

// Check error log for recent messages
echo "<h2>Recent Error Log:</h2>";
$error_log = "C:/xampp/apache/logs/error.log";
if (file_exists($error_log)) {
    $lines = file($error_log);
    $last_lines = array_slice($lines, -15);
    echo "<pre style='background: #f5f5f5; padding: 10px; overflow: auto; height: 200px; font-size: 11px;'>";
    foreach ($last_lines as $line) {
        if (strpos($line, 'Commande') !== false || strpos($line, 'Offre') !== false || strpos($line, 'Stock') !== false) {
            echo htmlspecialchars($line) . "\n";
        }
    }
    echo "</pre>";
}
?>
