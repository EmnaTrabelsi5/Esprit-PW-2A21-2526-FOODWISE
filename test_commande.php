<?php
session_start();
require_once __DIR__ . '/Model/CommandeModel.php';
require_once __DIR__ . '/Model/OffreModel.php';

$id_offre = isset($_GET['id_offre']) ? (int)$_GET['id_offre'] : 1;

// Test 1: Database Connection
echo "<h2>Test 1: Database Connection</h2>";
try {
    require_once __DIR__ . '/Config.php';
    $db = config::getConnexion();
    echo "<p style='color: green;'>✓ Database connected</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Database error: " . $e->getMessage() . "</p>";
    exit;
}

// Test 2: Query Offre table
echo "<h2>Test 2: Query Offre Table (ID: $id_offre)</h2>";

// FIRST: Update any expired offers to disponible
echo "<p><strong>Updating expired offers...</strong></p>";
$updateSql = "UPDATE `Offre` SET statut = 'disponible' WHERE statut = 'expire'";
$updateStmt = $db->prepare($updateSql);
$updateStmt->execute();
$rowsAffected = $updateStmt->rowCount();
echo "<p>Updated $rowsAffected offers from 'expire' to 'disponible'</p>";

$sql = "SELECT id, stock, statut FROM `Offre` WHERE id = ?";
$stmt = $db->prepare($sql);
$stmt->execute([$id_offre]);
$offre = $stmt->fetch();

if ($offre) {
    echo "<p style='color: green;'>✓ Offre found</p>";
    echo "<pre>";
    print_r($offre);
    echo "</pre>";
} else {
    echo "<p style='color: red;'>✗ Offre not found</p>";
    exit;
}

// Test 3: Insert test commande
echo "<h2>Test 3: Test Insert Commande</h2>";
$quantite = 2;
$id_client = 1;
$adresse = "123 Rue Test";
$telephone = "21234567";
$mode_paiement = "carte";
$note = "Test";

try {
    $sql = "INSERT INTO `commande` (id_offre, id_client, quantite, statut, paiement_status, adresse_livraison, telephone, mode_paiement, note, date_commande)
            VALUES (?, ?, ?, 'en_attente', 'non_paye', ?, ?, ?, ?, NOW())";
    $stmt = $db->prepare($sql);
    $result = $stmt->execute([$id_offre, $id_client, $quantite, $adresse, $telephone, $mode_paiement, $note]);
    
    if ($result) {
        echo "<p style='color: green;'>✓ Commande inserted successfully</p>";
        $lastId = $db->lastInsertId();
        echo "<p>Last Insert ID: $lastId</p>";
    } else {
        echo "<p style='color: red;'>✗ Insert failed (no exception)</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Insert error: " . $e->getMessage() . "</p>";
}

// Test 4: Test via CommandeModel::create()
echo "<h2>Test 4: Test via CommandeModel::create()</h2>";
$result = CommandeModel::create($id_offre, $id_client, $quantite, $adresse, $telephone, $mode_paiement, $note);
if ($result) {
    echo "<p style='color: green;'>✓ CommandeModel::create() returned true</p>";
} else {
    echo "<p style='color: red;'>✗ CommandeModel::create() returned false</p>";
}

// Test 5: Check error log
echo "<h2>Test 5: Check Error Log (last 20 lines)</h2>";
$error_log = "C:/xampp/apache/logs/error.log";
if (file_exists($error_log)) {
    $lines = file($error_log);
    $last_lines = array_slice($lines, -20);
    echo "<pre style='background: #f5f5f5; padding: 10px; overflow: auto; height: 300px;'>";
    foreach ($last_lines as $line) {
        echo htmlspecialchars($line);
    }
    echo "</pre>";
} else {
    echo "<p>Error log not found</p>";
}
?>
