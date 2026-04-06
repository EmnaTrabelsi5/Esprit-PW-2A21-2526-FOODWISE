<?php
// views/commercant/show.php
$pageTitle    = htmlspecialchars($commercant['nom']);
$activeModule = 'commercant';
require __DIR__ . '/../layouts/header.php';
?>
 
<div class="page-header-bar">
  <div>
    <h1 class="page-title">🏪 <?= htmlspecialchars($commercant['nom']) ?></h1>
    <p class="page-subtitle">
      📍 <?= htmlspecialchars($commercant['adresse']) ?>, <?= htmlspecialchars($commercant['ville']) ?>
      <?= $commercant['code_postal'] ? ' ' . htmlspecialchars($commercant['code_postal']) : '' ?>
    </p>
  </div>
  <div style="display:flex;gap:10px">
    <a href="offre.php?action=create&commercant_id=<?= $commercant['id'] ?>"
       class="btn btn-secondary">+ Publier une offre</a>
    <a href="commercant.php?action=edit&id=<?= $commercant['id'] ?>"
       class="btn btn-primary">✏️ Modifier</a>
    <a href="commercant.php?action=index" class="btn btn-outline">← Retour</a>
  </div>
</div>
 
<div style="display:grid;grid-template-columns:340px 1fr;gap:22px;align-items:start">
 
  <!-- Colonne info -->
  <div>
    <div class="card">
      <div class="card-header">
        <span class="card-title">Informations</span>
        <?php
        $statutLabels = [
          'actif'    => ['badge-vert',  '✅ Actif'],
          'inactif'  => ['badge-brun',  '⏸ Inactif'],
          'suspendu' => ['badge-rouge', '🚫 Suspendu'],
        ];
        [$cls, $lbl] = $statutLabels[$commercant['statut']] ?? ['badge-brun', $commercant['statut']];
        ?>
        <span class="badge <?= $cls ?>"><?= $lbl ?></span>
      </div>
 
      <div class="info-row">
        <span class="info-icon">📧</span>
        <div>
          <div class="info-label">Email</div>
          <div><?= htmlspecialchars($commercant['email']) ?></div>
        </div>
      </div>
 
      <?php if ($commercant['telephone']): ?>
      <div class="info-row">
        <span class="info-icon">📞</span>
        <div>
          <div class="info-label">Téléphone</div>
          <div><?= htmlspecialchars($commercant['telephone']) ?></div>
        </div>
      </div>
      <?php endif; ?>
 
      <div class="info-row">
        <span class="info-icon">🏙️</span>
        <div>
          <div class="info-label">Localisation</div>
          <div><?= htmlspecialchars($commercant['ville']) ?>
            <?= $commercant['code_postal'] ? '(' . htmlspecialchars($commercant['code_postal']) . ')' : '' ?>
          </div>
        </div>
      </div>
 
      <div class="info-row">
        <span class="info-icon">📅</span>
        <div>
          <div class="info-label">Inscrit le</div>
          <div><?= date('d/m/Y à H:i', strtotime($commercant['date_inscription'])) ?></div>
        </div>
      </div>
 
      <?php if ($commercant['description']): ?>
      <div style="margin-top:14px;padding-top:14px;border-top:1px solid var(--creme-fonce)">
        <div class="info-label" style="margin-bottom:6px">📝 À propos</div>
        <p style="font-size:14px;color:var(--texte-moyen);line-height:1.6">
          <?= nl2br(htmlspecialchars($commercant['description'])) ?>
        </p>
      </div>
      <?php endif; ?>
    </div>
 
    <!-- Danger zone -->
    <div class="card" style="border:1.5px solid #FADBD8">
      <div class="card-title" style="color:var(--alerte-rouge);font-size:15px;margin-bottom:12px">
        ⚠️ Zone de danger
      </div>
      <p style="font-size:13px;color:var(--texte-moyen);margin-bottom:14px">
        La suppression du commerçant entraîne la suppression de toutes ses offres.
        Cette action est irréversible.
      </p>
      <button class="btn btn-danger btn-sm"
              onclick="confirmDelete(<?= $commercant['id'] ?>, '<?= htmlspecialchars(addslashes($commercant['nom'])) ?>')">
        🗑 Supprimer ce commerçant
      </button>
    </div>
  </div>
 
  <!-- Colonne offres -->
  <div>
    <div class="card-header" style="margin-bottom:16px">
      <span class="card-title" style="font-family:'Playfair Display',serif;font-size:20px;color:var(--brun-fonce)">
        Offres publiées
      </span>
      <span class="badge badge-brun"><?= count($commercant['offres']) ?> offre(s)</span>
    </div>
 
    <?php if (empty($commercant['offres'])): ?>
      <div class="card" style="text-align:center;padding:40px;color:var(--texte-leger)">
        <div style="font-size:40px;margin-bottom:10px">📦</div>
        <p>Aucune offre publiée pour ce commerçant.</p>
        <a href="offre.php?action=create&commercant_id=<?= $commercant['id'] ?>"
           class="btn btn-secondary" style="margin-top:14px">+ Publier la première offre</a>
      </div>
    <?php else: ?>
      <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(230px,1fr));gap:16px">
        <?php foreach ($commercant['offres'] as $o): ?>
        <?php
        $statusMap = [
          'disponible' => ['badge-vert',   '✅ Disponible'],
          'epuise'     => ['badge-orange', '📦 Épuisé'],
          'expire'     => ['badge-rouge',  '⏰ Expiré'],
          'suspendu'   => ['badge-brun',   '⏸ Suspendu'],
        ];
        [$sc, $sl] = $statusMap[$o['statut']] ?? ['badge-brun', $o['statut']];
        $pct = $o['stock_initial'] > 0 ? round(($o['stock'] / $o['stock_initial']) * 100) : 0;
        ?>
        <div class="offre-mini-card">
          <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:8px">
            <div style="font-weight:700;color:var(--brun-fonce);font-size:14px;line-height:1.3">
              <?= htmlspecialchars($o['titre']) ?>
            </div>
            <span class="badge <?= $sc ?>" style="flex-shrink:0;margin-left:8px"><?= $sl ?></span>
          </div>
 
          <div style="font-size:18px;font-weight:700;color:var(--brun-chaud);margin-bottom:6px">
            <?= number_format($o['prix_unitaire'], 2) ?> TND
            <span style="font-size:12px;font-weight:400;color:var(--texte-leger)">/ <?= htmlspecialchars($o['unite']) ?></span>
          </div>
 
          <!-- Barre de stock -->
          <div style="margin-bottom:6px">
            <div style="display:flex;justify-content:space-between;font-size:11px;color:var(--texte-leger);margin-bottom:3px">
              <span>Stock</span>
              <span><?= $o['stock'] ?> / <?= $o['stock_initial'] ?></span>
            </div>
            <div style="height:5px;background:var(--creme-fonce);border-radius:4px;overflow:hidden">
              <div style="height:100%;width:<?= $pct ?>%;background:<?= $pct > 30 ? 'var(--vert-moyen)' : ($pct > 0 ? 'var(--alerte-orange)' : 'var(--alerte-rouge)') ?>;border-radius:4px;transition:.3s"></div>
            </div>
          </div>
 
          <div style="font-size:11px;color:var(--texte-leger)">
            ⏰ Expire le <?= date('d/m/Y H:i', strtotime($o['date_expiration'])) ?>
          </div>
 
          <div style="display:flex;gap:6px;margin-top:10px">
            <a href="offre.php?action=edit&id=<?= $o['id'] ?>" class="btn btn-outline btn-sm" style="flex:1;justify-content:center">✏️</a>
            <a href="offre.php?action=show&id=<?= $o['id'] ?>" class="btn btn-secondary btn-sm" style="flex:1;justify-content:center">Voir</a>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</div>
 
<!-- Modal suppression -->
<div id="deleteModal" class="modal-overlay" style="display:none">
  <div class="modal-box">
    <div style="font-size:40px;text-align:center;margin-bottom:12px">⚠️</div>
    <h3 style="font-family:'Playfair Display',serif;color:var(--brun-fonce);text-align:center;margin-bottom:8px">
      Supprimer ce commerçant ?
    </h3>
    <p id="deleteMsg" style="text-align:center;font-size:14px;color:var(--texte-moyen);margin-bottom:20px"></p>
    <div style="display:flex;gap:10px;justify-content:center">
      <button class="btn btn-outline" onclick="document.getElementById('deleteModal').style.display='none'">Annuler</button>
      <a id="deleteConfirmLink" href="#" class="btn btn-danger">Supprimer définitivement</a>
    </div>
  </div>
</div>
 
<script>
function confirmDelete(id, nom) {
  document.getElementById('deleteMsg').textContent =
    'Supprimer "' + nom + '" ainsi que toutes ses offres ?';
  document.getElementById('deleteConfirmLink').href = 'commercant.php?action=delete&id=' + id;
  document.getElementById('deleteModal').style.display = 'flex';
}
</script>
 
<?php require __DIR__ . '/../layouts/footer.php'; ?>