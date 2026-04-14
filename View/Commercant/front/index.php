<?php
// views/commercant/index.php
$pageTitle   = 'Commerçants';
$activeModule = 'commercant';
require __DIR__ . '/../layouts/header.php';
?>
 
<div class="page-header-bar">
  <div>
    <h1 class="page-title">🏪 Commerçants Partenaires</h1>
    <p class="page-subtitle">Gérez les commerçants inscrits sur la plateforme FoodWise LocalMarket</p>
  </div>
  <a href="commercant.php?action=create" class="btn btn-primary">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
      <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
    </svg>
    Inscrire un commerçant
  </a>
</div>
 
<!-- Filtres -->
<div class="filter-bar">
  <form method="get" action="commercant.php" style="display:flex;flex-wrap:wrap;gap:10px;align-items:center;width:100%">
    <input type="hidden" name="action" value="index">
 
    <select name="ville" class="form-control" style="max-width:180px">
      <option value="">📍 Toutes les villes</option>
      <?php foreach ($villes as $v): ?>
        <option value="<?= htmlspecialchars($v) ?>"
                <?= $ville === $v ? 'selected' : '' ?>>
          <?= htmlspecialchars($v) ?>
        </option>
      <?php endforeach; ?>
    </select>
 
    <select name="statut" class="form-control" style="max-width:160px">
      <option value="">Tous les statuts</option>
      <option value="actif"     <?= $statut === 'actif'     ? 'selected' : '' ?>>✅ Actif</option>
      <option value="inactif"   <?= $statut === 'inactif'   ? 'selected' : '' ?>>⏸ Inactif</option>
      <option value="suspendu"  <?= $statut === 'suspendu'  ? 'selected' : '' ?>>🚫 Suspendu</option>
    </select>
 
    <button type="submit" class="btn btn-primary btn-sm">Filtrer</button>
    <a href="commercant.php?action=index" class="btn btn-outline btn-sm">Réinitialiser</a>
  </form>
</div>
 
<!-- Tableau -->
<div class="card" style="padding:0;overflow:hidden">
  <?php if (empty($commercants)): ?>
    <div style="padding:48px;text-align:center;color:var(--texte-leger)">
      <div style="font-size:48px;margin-bottom:12px">🏪</div>
      <p style="font-size:16px">Aucun commerçant trouvé.</p>
      <a href="commercant.php?action=create" class="btn btn-primary" style="margin-top:16px">
        Inscrire le premier commerçant
      </a>
    </div>
  <?php else: ?>
  <table class="fw-table">
    <thead>
      <tr>
        <th>#</th>
        <th>Commerçant</th>
        <th>Ville</th>
        <th>Contact</th>
        <th>Statut</th>
        <th>Inscrit le</th>
        <th style="text-align:center">Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($commercants as $c): ?>
      <tr>
        <td style="color:var(--texte-leger);font-size:13px">#<?= $c['id'] ?></td>
        <td>
          <div style="font-weight:700;color:var(--brun-fonce)"><?= htmlspecialchars($c['nom']) ?></div>
          <div style="font-size:12px;color:var(--texte-leger)"><?= htmlspecialchars($c['email']) ?></div>
        </td>
        <td>
          <span class="badge badge-info">
            📍 <?= htmlspecialchars($c['ville']) ?>
          </span>
        </td>
        <td style="font-size:13px"><?= htmlspecialchars($c['telephone'] ?? '—') ?></td>
        <td>
          <?php
          $statutLabels = [
            'actif'    => ['badge-vert',  '✅ Actif'],
            'inactif'  => ['badge-brun',  '⏸ Inactif'],
            'suspendu' => ['badge-rouge', '🚫 Suspendu'],
          ];
          [$cls, $lbl] = $statutLabels[$c['statut']] ?? ['badge-brun', $c['statut']];
          ?>
          <span class="badge <?= $cls ?>"><?= $lbl ?></span>
        </td>
        <td style="font-size:13px;color:var(--texte-leger)">
          <?= date('d/m/Y', strtotime($c['date_inscription'])) ?>
        </td>
        <td style="text-align:center">
          <div style="display:flex;gap:6px;justify-content:center">
            <a href="commercant.php?action=show&id=<?= $c['id'] ?>"
               class="btn btn-outline btn-sm" title="Voir le profil">👁</a>
            <a href="commercant.php?action=edit&id=<?= $c['id'] ?>"
               class="btn btn-secondary btn-sm" title="Modifier">✏️</a>
            <button class="btn btn-danger btn-sm"
                    title="Supprimer"
                    onclick="confirmDelete(<?= $c['id'] ?>, '<?= htmlspecialchars(addslashes($c['nom'])) ?>')">
              🗑
            </button>
          </div>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <?php endif; ?>
</div>
 
<!-- Compteur -->
<p style="text-align:right;font-size:13px;color:var(--texte-leger);margin-top:-10px">
  <?= count($commercants) ?> commerçant(s) trouvé(s)
</p>
 
<!-- Modal confirm suppression -->
<div id="deleteModal" class="modal-overlay" style="display:none">
  <div class="modal-box">
    <div style="font-size:40px;text-align:center;margin-bottom:12px">⚠️</div>
    <h3 style="font-family:var(--font-playfair,serif);color:var(--brun-fonce);text-align:center;margin-bottom:8px">
      Supprimer ce commerçant ?
    </h3>
    <p id="deleteMsg" style="text-align:center;font-size:14px;color:var(--texte-moyen);margin-bottom:20px"></p>
    <div style="display:flex;gap:10px;justify-content:center">
      <button class="btn btn-outline" onclick="document.getElementById('deleteModal').style.display='none'">
        Annuler
      </button>
      <a id="deleteConfirmLink" href="#" class="btn btn-danger">Supprimer définitivement</a>
    </div>
  </div>
</div>
 
<script>
function confirmDelete(id, nom) {
  document.getElementById('deleteMsg').textContent =
    'Supprimer "' + nom + '" ? Toutes ses offres seront également supprimées.';
  document.getElementById('deleteConfirmLink').href =
    'commercant.php?action=delete&id=' + id;
  document.getElementById('deleteModal').style.display = 'flex';
}
</script>
 
<?php require __DIR__ . '/../layouts/footer.php'; ?>