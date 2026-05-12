<?php require __DIR__ . '/../../layouts/front/header.php'; ?>


<?php if (!isset($offres) || !is_array($offres)): ?>
    <p>Aucune donnée disponible.</p>
    <?php return; ?>
<?php endif; ?>


<div class="card">

  <div class="card__header">
    📋 Liste des offres
  </div>

  <div class="card__body">

    

    <!-- ✅ TABLE MUST BE HERE -->
    <table class="fw-table">
      <thead>
        <tr>
          <th>Titre</th>
          <th>Description</th>
          <th>Prix</th>
          <th>Stock</th>
          <th>Statut</th>
          <th>Commerçant</th>
          <th>Catégorie</th>
          <th>Expire le</th>
          <th>Actions</th>
        </tr>
      </thead>

      <tbody>
        <?php foreach ($offres as $offre): ?>

        <tr>
          <td><?= htmlspecialchars($offre['titre']) ?></td>
          <td><?= htmlspecialchars(strlen($offre['description']) > 80 ? substr($offre['description'], 0, 80) . '...' : $offre['description']) ?></td>
          <td>
    <?php if (!empty($offre['remise']) && $offre['remise'] > 0): ?>
        
        <span style="text-decoration: line-through; color:#999;">
            <?= number_format($offre['prix_unitaire'], 2) ?> TND
        </span>
        <br>

        <strong style="color:#2c6f37;">
            <?= number_format($offre['prix_final'], 2) ?> TND
        </strong>
        <br>

        <span class="badge badge--warning">
            -<?= $offre['remise'] ?>% 🔥 Anti-gaspillage
        </span>

    <?php else: ?>
        <?= number_format($offre['prix_unitaire'], 2) ?> TND
    <?php endif; ?>
</td>
<?php if (!empty($offre['remise']) && $offre['remise'] > 0): ?>
    <div style="color:#a86b1d; font-size:0.8rem;">
        ⏳ Expire bientôt
    </div>
<?php endif; ?>
          <td><?= $offre['stock'] ?></td>
          <td><?= htmlspecialchars($offre['statut']) ?></td>
          <td><?= htmlspecialchars($offre['commercant_nom'] ?? '') ?></td>
          <td><?= htmlspecialchars($offre['categorie'] ?? '') ?></td>
          <td><?= !empty($offre['date_expiration']) ? date('d/m/Y', strtotime($offre['date_expiration'])) : '-' ?></td>

          <td class="table-actions">
            <a href="?route=offres/show&id=<?= $offre['id'] ?>" class="btn btn--secondary btn-sm" title="Voir les détails">👁</a>
            <a href="?route=commandes/create&id_offre=<?= $offre['id'] ?>" class="btn btn--success btn-sm" title="Commander">🛒</a>

          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

  </div>
</div>

<?php require __DIR__ . '/../../layouts/front/footer.php'; ?>
