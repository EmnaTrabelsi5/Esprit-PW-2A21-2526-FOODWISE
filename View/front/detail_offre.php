<?php
// views/offre/show.php
$pageTitle    = htmlspecialchars($offre['titre']);
$activeModule = 'offre';
require __DIR__ . '/../layouts/header.php';
 
$categorieLabels = [
  'fruits'   => ['🍎', 'Fruits'],
  'legumes'  => ['🥦', 'Légumes'],
  'viandes'  => ['🥩', 'Viandes'],
  'poissons' => ['🐟', 'Poissons'],
  'laitiers' => ['🥛', 'Produits laitiers'],
  'cereales' => ['🌾', 'Céréales'],
  'epicerie' => ['🫙', 'Épicerie'],
  'autre'    => ['📦', 'Autre'],
];
[$catIcon, $catLbl] = $categorieLabels[$offre['categorie']] ?? ['📦', $offre['categorie']];
 
$statusMap = [
  'disponible' => ['badge-vert',   '✅ Disponible'],
  'epuise'     => ['badge-orange', '📭 Épuisé'],
  'expire'     => ['badge-rouge',  '⏰ Expiré'],
  'suspendu'   => ['badge-brun',   '⏸ Suspendu'],
];
[$sc, $sl] = $statusMap[$offre['statut']] ?? ['badge-brun', $offre['statut']];
 
$pct        = $offre['stock_initial'] > 0 ? round(($offre['stock'] / $offre['stock_initial']) * 100) : 0;
$expiresIn  = (strtotime($offre['date_expiration']) - time()) / 3600;
$urgente    = $expiresIn > 0 && $expiresIn <= 48;
$expireSoon = $urgente ? sprintf('Expire dans %dh%02dm',
  floor($expiresIn), ($expiresIn - floor($expiresIn)) * 60) : '';
?>
 
<div class="page-header-bar">
  <div>
    <h1 class="page-title"><?= $catIcon ?> <?= htmlspecialchars($offre['titre']) ?></h1>
    <p class="page-subtitle">
      🏪 <?= htmlspecialchars($offre['commercant_nom']) ?>
      &nbsp;·&nbsp;
      📍 <?= htmlspecialchars($offre['commercant_ville']) ?>
    </p>
  </div>
  <div style="display:flex;gap:10px">
    <?php if ($offre['statut'] !== 'expire'): ?>
      <a href="offre.php?action=cloture&id=<?= $offre['id'] ?>"
         class="btn btn-outline"
         onclick="return confirm('Clôturer cette offre ?')">🔒 Clôturer</a>
    <?php endif; ?>
    <a href="offre.php?action=edit&id=<?= $offre['id'] ?>" class="btn btn-primary">✏️ Modifier</a>
    <a href="offre.php?action=index" class="btn btn-outline">← Retour</a>
  </div>
</div>
 
<?php if ($urgente): ?>
<div class="substitut-alert" style="border-color:var(--alerte-orange);background:#FEF9E7;margin-bottom:18px">
  <span>⚡</span>
  <strong><?= $expireSoon ?></strong>
  &nbsp;— Pensez à renouveler ou clôturer cette offre rapidement.
</div>
<?php endif; ?>
 
<div style="display:grid;grid-template-columns:1fr 340px;gap:22px;align-items:start">
 
  <!-- Détails -->
  <div>
    <div class="card">
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px">
        <span class="badge <?= $sc ?>" style="font-size:14px;padding:6px 16px"><?= $sl ?></span>
        <span class="badge badge-brun"><?= $catIcon ?> <?= $catLbl ?></span>
      </div>
 
      <div style="font-size:32px;font-weight:700;color:var(--brun-chaud);margin-bottom:4px">
        <?= number_format($offre['prix_unitaire'], 2) ?> TND
        <span style="font-size:16px;font-weight:400;color:var(--texte-leger)">/ <?= htmlspecialchars($offre['unite']) ?></span>
      </div>
 
      <?php if ($offre['description']): ?>
      <p style="color:var(--texte-moyen);font-size:15px;line-height:1.7;margin:14px 0">
        <?= nl2br(htmlspecialchars($offre['description'])) ?>
      </p>
      <?php endif; ?>
 
      <!-- Jauge stock -->
      <div style="background:var(--creme-fonce);border-radius:var(--radius);padding:16px;margin-top:14px">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px">
          <span style="font-weight:700;color:var(--brun-fonce)">📦 Stock disponible</span>
          <span style="font-weight:700;font-size:18px;color:var(--brun-chaud)">
            <?= $offre['stock'] ?> / <?= $offre['stock_initial'] ?>
            <span style="font-size:13px;color:var(--texte-leger)"><?= htmlspecialchars($offre['unite']) ?></span>
          </span>
        </div>
        <div style="height:10px;background:rgba(255,255,255,.7);border-radius:8px;overflow:hidden">
          <div style="height:100%;width:<?= $pct ?>%;background:<?= $pct > 30 ? 'var(--vert-moyen)' : ($pct > 0 ? 'var(--alerte-orange)' : 'var(--alerte-rouge)') ?>;border-radius:8px;transition:.5s ease"></div>
        </div>
        <div style="text-align:right;font-size:12px;color:var(--texte-leger);margin-top:4px">
          <?= $pct ?>% restant
        </div>
      </div>
    </div>
 
    <!-- Dates -->
    <div class="card">
      <div class="card-header">
        <span class="card-title">📅 Période de l'offre</span>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
        <div>
          <div class="info-label">Date de début</div>
          <div style="font-weight:700;color:var(--brun-fonce)">
            <?= date('d/m/Y à H:i', strtotime($offre['date_debut'])) ?>
          </div>
        </div>
        <div>
          <div class="info-label">Date d'expiration</div>
          <div style="font-weight:700;color:<?= $expiresIn <= 0 ? 'var(--alerte-rouge)' : ($urgente ? 'var(--alerte-orange)' : 'var(--brun-fonce)') ?>">
            <?= date('d/m/Y à H:i', strtotime($offre['date_expiration'])) ?>
            <?php if ($urgente): ?> <span style="font-size:12px">(<?= $expireSoon ?>)</span><?php endif; ?>
            <?php if ($expiresIn <= 0): ?> <span style="font-size:12px">(Expirée)</span><?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </div>
 
  <!-- Colonne commerçant + actions -->
  <div>
    <div class="card">
      <div class="card-header">
        <span class="card-title">🏪 Commerçant</span>
      </div>
      <div class="info-row">
        <span class="info-icon">🏬</span>
        <div>
          <div style="font-weight:700;color:var(--brun-fonce)"><?= htmlspecialchars($offre['commercant_nom']) ?></div>
          <a href="commercant.php?action=show&id=<?= $offre['commercant_id'] ?>"
             style="font-size:12px;color:var(--brun-chaud)">Voir le profil →</a>
        </div>
      </div>
      <div class="info-row">
        <span class="info-icon">📍</span>
        <div>
          <div class="info-label">Localisation</div>
          <div><?= htmlspecialchars($offre['commercant_ville']) ?></div>
        </div>
      </div>
      <div class="info-row">
        <span class="info-icon">🏠</span>
        <div>
          <div class="info-label">Adresse</div>
          <div style="font-size:13px"><?= htmlspecialchars($offre['commercant_adresse']) ?></div>
        </div>
      </div>
      <?php if (!empty($offre['commercant_tel'])): ?>
      <div class="info-row">
        <span class="info-icon">📞</span>
        <div>
          <div class="info-label">Téléphone</div>
          <div><?= htmlspecialchars($offre['commercant_tel']) ?></div>
        </div>
      </div>
      <?php endif; ?>
    </div>
 
    <!-- Actions rapides -->
    <div class="card" style="border:1.5px solid var(--creme-fonce)">
      <div class="card-title" style="margin-bottom:12px">⚡ Actions rapides</div>
      <div style="display:flex;flex-direction:column;gap:8px">
        <a href="offre.php?action=edit&id=<?= $offre['id'] ?>" class="btn btn-primary" style="justify-content:center">
          ✏️ Modifier l'offre
        </a>
        <?php if ($offre['statut'] !== 'expire'): ?>
        <a href="offre.php?action=cloture&id=<?= $offre['id'] ?>"
           class="btn btn-outline" style="justify-content:center"
           onclick="return confirm('Clôturer cette offre définitivement ?')">
          🔒 Clôturer
        </a>
        <?php endif; ?>
        <button class="btn btn-danger" style="justify-content:center"
                onclick="document.getElementById('deleteModal').style.display='flex'">
          🗑 Supprimer
        </button>
      </div>
    </div>
  </div>
</div>
 
<!-- Modal suppression -->
<div id="deleteModal" class="modal-overlay" style="display:none">
  <div class="modal-box">
    <div style="font-size:40px;text-align:center;margin-bottom:12px">⚠️</div>
    <h3 style="font-family:'Playfair Display',serif;color:var(--brun-fonce);text-align:center;margin-bottom:8px">
      Supprimer cette offre ?
    </h3>
    <p style="text-align:center;font-size:14px;color:var(--texte-moyen);margin-bottom:20px">
      "<?= htmlspecialchars($offre['titre']) ?>" sera définitivement supprimée.
    </p>
    <div style="display:flex;gap:10px;justify-content:center">
      <button class="btn btn-outline" onclick="document.getElementById('deleteModal').style.display='none'">Annuler</button>
      <a href="offre.php?action=delete&id=<?= $offre['id'] ?>" class="btn btn-danger">Supprimer</a>
    </div>
  </div>
</div>
 
<?php require __DIR__ . '/../layouts/footer.php'; ?>