<?php
require_once __DIR__ . '/Config.php';

echo "<h1>Direct Database Update Test</h1>";

$db = config::getConnexion();

// First, show current state
echo "<h2>Before Update:</h2>";
$result = $db->query("SELECT id, titre, statut FROM `Offre` LIMIT 5");
$data = $result->fetchAll();
echo "<pre>";
foreach ($data as $row) {
    echo $row['id'] . " | " . $row['titre'] . " | " . $row['statut'] . "\n";
}
echo "</pre>";

// Update
echo "<h2>Running Update Query:</h2>";
$query = "UPDATE `Offre` SET statut = 'disponible' WHERE statut = 'expire'";
echo "<p>Query: <code>$query</code></p>";
try {
    $stmt = $db->prepare($query);
    $result = $stmt->execute();
    $rowsAffected = $stmt->rowCount();
    echo "<p style='color: green;'>✓ Query executed successfully. Rows affected: $rowsAffected</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Query failed: " . $e->getMessage() . "</p>";
}

// After update
echo "<h2>After Update (verify with fresh query):</h2>";
$result = $db->query("SELECT id, titre, statut FROM `Offre` LIMIT 5");
$data = $result->fetchAll();
echo "<pre>";
foreach ($data as $row) {
    echo $row['id'] . " | " . $row['titre'] . " | " . $row['statut'] . "\n";
}
echo "</pre>";

// Check total offer stats
echo "<h2>Total Offer Stats:</h2>";
$stats = $db->query("SELECT statut, COUNT(*) as count FROM `Offre` GROUP BY statut")->fetchAll();
echo "<pre>";
foreach ($stats as $stat) {
    echo $stat['statut'] . ": " . $stat['count'] . "\n";
}
echo "</pre>";
?>
