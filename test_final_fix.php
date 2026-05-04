<?php
require_once __DIR__ . '/Config.php';

echo "<h1>Final Fix: Reset Status to Disponible</h1>";

$db = config::getConnexion();

echo "<p>Resetting all offers to 'disponible' status...</p>";
$db->query("UPDATE `Offre` SET statut = 'disponible' WHERE statut = 'expire'");
echo "<p style='color: green;'>✓ Done!</p>";

echo "<h2>Verification:</h2>";
$result = $db->query("SELECT COUNT(*) as count, statut FROM `Offre` GROUP BY statut");
$stats = $result->fetchAll();
echo "<table border='1'>";
foreach ($stats as $stat) {
    echo "<tr><td>" . $stat['statut'] . "</td><td>" . $stat['count'] . "</td></tr>";
}
echo "</table>";

echo "<p><a href='offre.php?action=index'>✓ Go to Offres List</a></p>";
?>
