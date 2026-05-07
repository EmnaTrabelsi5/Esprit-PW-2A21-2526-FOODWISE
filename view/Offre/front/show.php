

<?php require __DIR__ . '/../../layouts/front/header.php'; ?>


<h1><?= htmlspecialchars($offre['titre']) ?></h1>

<p><strong>Description:</strong> <?= $offre['description'] ?></p>
<p><strong>Prix:</strong> <?= $offre['prix_unitaire'] ?> TND</p>
<p><strong>Stock:</strong> <?= $offre['stock'] ?></p>
<p><strong>Statut:</strong> <?= $offre['statut'] ?></p>

<hr>

<p><strong>Commerçant:</strong> <?= $offre['commercant_nom'] ?></p>
<p><strong>Ville:</strong> <?= $offre['commercant_ville'] ?></p>

<hr>
<a href="/FOODWISE1/router/offreRouter.php?action=edit&id=<?= $offre['id'] ?>">✏️ Modifier</a>
<a href="/FOODWISE1/router/offreRouter.php?action=cloture&id=<?= $offre['id'] ?>">🚫 Clôturer</a>
<a href="/FOODWISE1/router/offreRouter.php?action=index">⬅ Retour</a>
<?php if ($offre['statut'] === 'disponible'): ?>
<a href="/FOODWISE1/router/commandeRouter.php?action=create&id_offre=<?= $offre['id'] ?>">
    Commander
</a>
<?php else: ?>
    <button disabled>Indisponible</button>
<?php endif; ?>

<?php require __DIR__ . '/../../layouts/front/footer.php'; ?>



