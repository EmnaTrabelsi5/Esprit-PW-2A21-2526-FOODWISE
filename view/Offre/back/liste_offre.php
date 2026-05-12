<?php require __DIR__ . '/../../layouts/back/header.php'; ?>





<div class="card">

  <div class="card__header">
    📋 Liste des offres
  </div>

  <div class="card__body">

    <!-- ✅ ADD BUTTON HERE -->
    <a href="?route=admin/offres/create" class="btn btn--primary add-btn">
      ➕ Ajouter une offre
    </a>

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
          <td><?= number_format($offre['prix_unitaire'], 2) ?> TND</td>
          <td><?= $offre['stock'] ?></td>
          <td><?= htmlspecialchars($offre['statut']) ?></td>
          <td><?= htmlspecialchars($offre['commercant_nom'] ?? '') ?></td>
          <td><?= htmlspecialchars($offre['categorie'] ?? '') ?></td>
          <td><?= !empty($offre['date_expiration']) ? date('d/m/Y', strtotime($offre['date_expiration'])) : '-' ?></td>

          <td class="table-actions">
            <a href="?route=admin/offres/show&id=<?= $offre['id'] ?>" class="btn btn--secondary btn-sm" title="Voir les détails">👁</a>
            <a href="?route=admin/offres/edit&id=<?= $offre['id'] ?>" class="btn btn--primary btn-sm" title="Modifier">✏️</a>
            <a href="/FOODWISE1/commande.php?action=create&id_offre=<?= $offre['id'] ?>" class="btn btn--success btn-sm" title="Commander">🛒</a>

            <form method="POST" action="/FOODWISE1/router/offreRouter.php?action=delete" style="display:inline;">
              <input type="hidden" name="id" value="<?= $offre['id'] ?>">
              <button type="submit" class="btn btn--danger btn-sm" title="Supprimer" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette offre ?')">🗑</button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

  </div>
</div>

<?php require __DIR__ . '/../../layouts/back/footer.php'; ?>
