<?php require __DIR__ . '/../../layouts/front/header.php'; ?>





<div class="card">

  <div class="card__header">
    📋 Liste des offres
  </div>

  <div class="card__body">

    <!-- ✅ ADD BUTTON HERE -->
    <a href="offre.php?action=create" class="btn btn--primary add-btn">
      ➕ Ajouter une offre
    </a>

    <!-- ✅ TABLE MUST BE HERE -->
    <table class="fw-table">
      <thead>
        <tr>
          <th>Titre</th>
          <th>Prix</th>
          <th>Stock</th>
          <th>Statut</th>
          <th>Actions</th>
        </tr>
      </thead>

      <tbody>
        <?php foreach ($offres as $offre): ?>
        <tr>
          <td><?= htmlspecialchars($offre['titre']) ?></td>
          <td><?= $offre['prix_unitaire'] ?> TND</td>
          <td><?= $offre['stock'] ?></td>
          <td><?= $offre['statut'] ?></td>

          <td class="table-actions">
            <a href="offre.php?action=show&id=<?= $offre['id'] ?>">👁</a>
            <a href="offre.php?action=edit&id=<?= $offre['id'] ?>">✏️</a>

            <form method="POST" action="offre.php?action=delete" style="display:inline;">
              <input type="hidden" name="id" value="<?= $offre['id'] ?>">
              <button class="btn btn--danger">🗑</button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

  </div>
</div>

<?php require __DIR__ . '/../../layouts/front/footer.php'; ?>