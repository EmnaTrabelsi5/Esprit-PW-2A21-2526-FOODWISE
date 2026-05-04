<?php
require_once __DIR__ . '/Config.php';

echo "<h1>Offer Expiration Analysis</h1>";

$db = config::getConnexion();

echo "<h2>Current Offer Status vs Expiration Dates:</h2>";
$result = $db->query("SELECT id, titre, statut, date_expiration FROM `Offre`");
$offers = $result->fetchAll();

echo "<table border='1' cellpadding='5'>";
echo "<tr><th>ID</th><th>Titre</th><th>Statut</th><th>Date Expiration</th><th>Expired?</th></tr>";
$now = new DateTime();
foreach ($offers as $offer) {
    $expireDate = new DateTime($offer['date_expiration']);
    $expiredTime = $expireDate < $now ? 'YES' : 'NO';
    echo "<tr>";
    echo "<td>" . $offer['id'] . "</td>";
    echo "<td>" . $offer['titre'] . "</td>";
    echo "<td>" . $offer['statut'] . "</td>";
    echo "<td>" . $offer['date_expiration'] . "</td>";
    echo "<td style='color: " . ($expiredTime === 'YES' ? 'red' : 'green') . "'>" . $expiredTime . "</td>";
    echo "</tr>";
}
echo "</table>";

echo "<h2>Solution: Update All Expiration Dates to Future</h2>";
$futureDate = date('Y-m-d H:i:s', strtotime('+30 days'));
echo "<p>Setting all expiration dates to: <strong>$futureDate</strong></p>";
$updateSql = "UPDATE `Offre` SET date_expiration = ? WHERE date_expiration < NOW()";
$stmt = $db->prepare($updateSql);
$stmt->execute([$futureDate]);
$rowsAffected = $stmt->rowCount();
echo "<p style='color: green;'>✓ Updated $rowsAffected offers</p>";

echo "<h2>After Fix - New Dates:</h2>";
$result = $db->query("SELECT id, titre, statut, date_expiration FROM `Offre`");
$offers = $result->fetchAll();
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>ID</th><th>Titre</th><th>Statut</th><th>Date Expiration</th></tr>";
foreach ($offers as $offer) {
    echo "<tr>";
    echo "<td>" . $offer['id'] . "</td>";
    echo "<td>" . $offer['titre'] . "</td>";
    echo "<td>" . $offer['statut'] . "</td>";
    echo "<td>" . $offer['date_expiration'] . "</td>";
    echo "</tr>";
}
echo "</table>";
?>
