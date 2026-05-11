<?php require __DIR__ . '/../../layouts/front/header.php'; ?>

<div class="fw-container">

  <div class="fw-card">

    <!-- HEADER -->
    <div class="fw-card-header">
      <h2><?= htmlspecialchars($offre['titre']) ?></h2>
      <span class="badge <?= $offre['statut'] === 'disponible' ? 'badge--success' : 'badge--danger' ?>">
        <?= htmlspecialchars($offre['statut']) ?>
      </span>
    </div>

    <!-- BODY -->
    <div class="fw-card-body">

      <p class="fw-description">
        <?= nl2br(htmlspecialchars($offre['description'])) ?>
      </p>

      <div class="fw-grid">
        <div>
          <strong>💰 Prix:</strong><br>
          <?= number_format($offre['prix_unitaire'], 2) ?> TND
        </div>

        <div>
          <strong>📦 Stock:</strong><br>
          <?= $offre['stock'] ?>
        </div>

        <div>
          <strong>🏪 Commerçant:</strong><br>
          <?= htmlspecialchars($offre['commercant_nom']) ?>
        </div>

        <div>
          <strong>📍 Ville:</strong><br>
          <?= htmlspecialchars($offre['commercant_ville']) ?>
        </div>
      </div>

    </div>

    <!-- FOOTER ACTIONS -->
    <div class="fw-card-footer">

      <a href="?route=offres/index" class="btn btn--secondary">
        ⬅ Retour
      </a>


      <?php if ($offre['statut'] === 'disponible'): ?>
        <a href="?route=commandes/create&id_offre=<?= $offre['id'] ?>" class="btn btn--success">
          🛒 Commander
        </a>
      <?php else: ?>
        <button class="btn btn--disabled" disabled>Indisponible</button>
      <?php endif; ?>

    </div>

  </div>

</div>

<?php require __DIR__ . '/../../layouts/front/footer.php'; ?>



