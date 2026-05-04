<?php
require_once __DIR__ . '/Config.php';

// Update all expired offers to disponible for testing
$db = config::getConnexion();
$db->query("UPDATE Offre SET statut = 'disponible' WHERE statut = 'expire'");
echo "<p style='color: green;'><strong>✓ Mise à jour:</strong> Les offres 'expire' sont maintenant 'disponible'</p>";

echo "<h2>Offre Table Structure:</h2>";
$result = $db->query("DESCRIBE Offre");
echo "<table border='1'>";
foreach ($result as $row) {
    echo "<tr>";
    foreach ($row as $cell) {
        echo "<td>" . htmlspecialchars($cell) . "</td>";
    }
    echo "</tr>";
}
echo "</table>";

echo "<hr><h2>Sample Offres (après mise à jour):</h2>";
$result = $db->query("SELECT id, titre, stock, statut FROM Offre LIMIT 5");
echo "<table border='1'>";
echo "<tr><th>ID</th><th>Titre</th><th>Stock</th><th>Statut</th></tr>";
foreach ($result as $row) {
    echo "<tr>";
    echo "<td>" . $row['id'] . "</td>";
    echo "<td>" . htmlspecialchars($row['titre']) . "</td>";
    echo "<td>" . $row['stock'] . "</td>";
    echo "<td>" . htmlspecialchars($row['statut']) . "</td>";
    echo "</tr>";
}
echo "</table>";

echo "<h2>Commande Table Structure:</h2>";
$result = $db->query("DESCRIBE commande");
echo "<table border='1'>";
foreach ($result as $row) {
    echo "<tr>";
    foreach ($row as $cell) {
        echo "<td>" . htmlspecialchars($cell) . "</td>";
    }
    echo "</tr>";
}
echo "</table>";
?>
