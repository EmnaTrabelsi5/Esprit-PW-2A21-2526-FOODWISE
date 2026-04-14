<?php
require_once __DIR__ . '/../../../Model/OffreModel.php';

$offreModel = new OffreModel();
$offres = $offreModel->findAll();

$pageTitle = "Liste des offres";
$activeModule = 'offre';

require __DIR__ . '/../../layouts/back/header.php';
?>

<div class="page-header-bar">
  <h1 class="page-title">📋 Liste des offres</h1>
</div>

<div class="card">
  <table class="table">
    <thead>
      <tr>
        <th>Titre</th>
        <th>Prix</th>
        <th>Stock</th>
        <th>Actions</th>
      </tr>
    </thead>

    <tbody>
      <?php foreach ($offres as $offre): ?>
      <tr>
        <td><?= htmlspecialchars($offre['titre']) ?></td>
        <td><?= $offre['prix_unitaire'] ?> TND</td>
        <td><?= $offre['stock'] ?></td>

      <td class="table-actions">
        <a href="offre.php?action=show&id=<?= $offre['id'] ?>" class="action-btn view">👁</a>
        <a href="offre.php?action=edit&id=<?= $offre['id'] ?>" class="action-btn edit">✏️</a>

        <form method="POST" action="offre.php?action=delete" style="display:inline;">
          <input type="hidden" name="id" value="<?= $offre['id'] ?>">
          <button class="action-btn delete">🗑</button>
        </form>
    </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<?php require __DIR__ . '/../../layouts/back/footer.php'; ?>