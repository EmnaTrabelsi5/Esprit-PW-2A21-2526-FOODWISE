<?php
// views/offre/index.php
$pageTitle    = 'Le Marché LocalMarket';
$activeModule = 'offre';
require __DIR__ . '/../layouts/header.php';
 
$categorieLabels = [
  'fruits'   => ['🍎', 'Fruits'],
  'legumes'  => ['🥦', 'Légumes'],
  'viandes'  => ['🥩', 'Viandes'],
  'poissons' => ['🐟', 'Poissons'],
  'laitiers' => ['🥛', 'Produits laitiers'],
  'cereales' => ['🌾', 'Céréales & Légumineuses'],
  'epicerie' => ['🫙', 'Épicerie'],
  'autre'    => ['📦', 'Autre'],
];
?>
 
<div class="page-header-bar">
  <div>
    <h1 class="page-title">🛒 Le Marché Local</h1>
    <p class="page-subtitle">Offres fraîches de nos commerçants partenaires — circuit court & alimentation durable</p>
  </div>
  <a href="offre.php?action=create" class="btn btn-primary">+ Publier une offre</a>
</div>
 
<!-- Stats -->
<div class="stats-grid">
  <div class="stat-card">
    <div class="stat-icon">📦</div>
    <div class="stat-val"><?= $stats['total'] ?></div>
    <div class="stat-lbl">Offres totales</div>
  </div>
  <div class="stat-card stat-success">
    <div class="stat-icon">✅</div>
    <div class="stat-val"><?= $stats['disponibles'] ?></div>
    <div class="stat-lbl">Disponibles</div>
  </div>
  <div class="stat-card stat-warning">
    <div class="stat-icon">📭</div>
    <div class="stat-val"><?= $stats['epuisees'] ?></div>
    <div class="stat-lbl">Épuisées</div>
  </div>
  <div class="stat-card stat-danger">
    <div class="stat-icon">⏰</div>
    <div class="stat-val"><?= $stats['expire_bientot'] ?></div>
    <div class="stat-lbl">Expirent dans 48h</div>
  </div>
</div>
 
<!-- Filtres avancés -->
<div class="filter-bar">
  <form method="get" action="offre.php" style="display:flex;flex-wrap:wrap;gap:10px;align-items:center;width:100%">
    <input type="hidden" name="action" value="index">
 
    <div style="position:relative;flex:1;min-width:200px">
      <input type="text" name="search"
             value="<?= htmlspecialchars($filtres['search']) ?>"
             placeholder="🔍  Rechercher une offre..."
             style="width:100%;border:1.5px solid var(--brun-pale);border-radius:20px;padding:7px 14px;font-size:13px;font-family:'Lato',sans-serif;background:var(--creme);outline:none">
    </div>
 
    <select name="ville" style="border:1.5px solid var(--brun-pale);border-radius:20px;padding:7px 14px;font-size:13px;font-family:'Lato',sans-serif;background:var(--creme);outline:none;cursor:pointer">
      <option value="">📍 Toutes les villes</option>
      <?php foreach ($villes as $v): ?>
        <option value="<?= htmlspecialchars($v) ?>" <?= $filtres['ville'] === $v ? 'selected' : '' ?>>
          <?= htmlspecialchars($v) ?>
        </option>
      <?php endforeach; ?>
    </select>
 
    <select name="categorie" style="border:1.5px solid var(--brun-pale);border-radius:20px;padding:7px 14px;font-size:13px;font-family:'Lato',sans-serif;background:var(--creme);outline:none;cursor:pointer">
      <option value="">🗂 Toutes catégories</option>
      <?php foreach ($categorieLabels as $k => [$icon, $lbl]): ?>
        <option value="<?= $k ?>" <?= $filtres['categorie'] === $k ? 'selected' : '' ?>>
          <?= $icon ?> <?= $lbl ?>
        </option>
      <?php endforeach; ?>
    </select>
 
    <select name="statut" style="border:1.5px solid var(--brun-pale);border-radius:20px;padding:7px 14px;font-size:13px;font-family:'Lato',sans-serif;background:var(--creme);outline:none;cursor:pointer">
      <option value="disponible" <?= $filtres['statut'] === 'disponible' ? 'selected' : '' ?>>✅ Disponibles</option>
      <option value="" <?= $filtres['statut'] === '' ? 'selected' : '' ?>>Toutes</option>
      <option value="epuise"     <?= $filtres['statut'] === 'epuise'     ? 'selected' : '' ?>>📭 Épuisées</option>
      <option value="expire"     <?= $filtres['statut'] === 'expire'     ? 'selected' : '' ?>>⏰ Expirées</option>
    </select>
 
    <select name="tri" style="border:1.5px solid var(--brun-pale);border-radius:20px;padding:7px 14px;font-size:13px;font-family:'Lato',sans-serif;background:var(--creme);outline:none;cursor:pointer">
      <option value="recent"     <?= $filtres['tri'] === 'recent'     ? 'selected' : '' ?>>🕐 Plus récentes</option>
      <option value="expiration" <?= $filtres['tri'] === 'expiration' ? 'selected' : '' ?>>⏰ Expire bientôt</option>
      <option value="prix_asc"   <?= $filtres['tri'] === 'prix_asc'   ? 'selected' : '' ?>>💰 Prix croissant</option>
      <option value="prix_desc"  <?= $filtres['tri'] === 'prix_desc'  ? 'selected' : '' ?>>💰 Prix décroissant</option>
    </select>
 
    <button type="submit" class="btn btn-primary btn-sm">Filtrer</button>
    <a href="offre.php?action=index" class="btn btn-outline btn-sm">✕ Réinitialiser</a>
  </form>
</div>
 
<!-- Résultats -->
<?php if (empty($offres)): ?>
  <div class="card" style="text-align:center;padding:56px">
    <div style="font-size:56px;margin-bottom:14px">🛒</div>
    <h3 style="font-family:'Playfair Display',serif;color:var(--brun-fonce);margin-bottom:8px">
      Aucune offre trouvée
    </h3>
    <p style="color:var(--texte-leger)">Essayez d'autres filtres ou publiez la première offre.</p>
    <a href="offre.php?action=create" class="btn btn-primary" style="margin-top:16px">+ Publier une offre</a>
  </div>
 
<?php else: ?>
  <div style="font-size:13px;color:var(--texte-leger);margin-bottom:14px">
    <?= count($offres) ?> offre(s) trouvée(s)
  </div>
 
  <div class="recipe-grid">
    <?php foreach ($offres as $o):
      [$catIcon, $catLbl] = $categorieLabels[$o['categorie']] ?? ['📦', $o['categorie']];
      $statusMap = [
        'disponible' => ['badge-vert',   '✅ Disponible'],
        'epuise'     => ['badge-orange', '📭 Épuisé'],
        'expire'     => ['badge-rouge',  '⏰ Expiré'],
        'suspendu'   => ['badge-brun',   '⏸ Suspendu'],
      ];
      [$sc, $sl] = $statusMap[$o['statut']] ?? ['badge-brun', $o['statut']];
      $pct = $o['stock_initial'] > 0 ? round(($o['stock'] / $o['stock_initial']) * 100) : 0;
      $expiresIn = (strtotime($o['date_expiration']) - time()) / 3600;
      $urgente   = $expiresIn <= 48 && $o['statut'] === 'disponible';
    ?>
    <a href="offre.php?action=show&id=<?= $o['id'] ?>" class="recipe-card" style="position:relative">
      <?php if ($urgente): ?>
        <div class="urgence-ribbon">⚡ Expire bientôt</div>
      <?php endif; ?>
 
      <div class="recipe-card-img-placeholder">
        <span style="font-size:50px"><?= $catIcon ?></span>
      </div>
 
      <div class="recipe-card-body">
        <div class="recipe-card-title"><?= htmlspecialchars($o['titre']) ?></div>
 
        <div style="font-size:13px;color:var(--texte-leger);margin-bottom:8px">
          🏪 <?= htmlspecialchars($o['commercant_nom']) ?>
          &nbsp;·&nbsp;
          📍 <?= htmlspecialchars($o['commercant_ville']) ?>
        </div>
 
        <div style="font-size:20px;font-weight:700;color:var(--brun-chaud);margin-bottom:8px">
          <?= number_format($o['prix_unitaire'], 2) ?> TND
          <span style="font-size:12px;font-weight:400;color:var(--texte-leger)"> / <?= htmlspecialchars($o['unite']) ?></span>
        </div>
 
        <!-- Stock bar -->
        <div style="margin-bottom:8px">
          <div style="height:4px;background:var(--creme-fonce);border-radius:4px;overflow:hidden">
            <div style="height:100%;width:<?= $pct ?>%;background:<?= $pct > 30 ? 'var(--vert-moyen)' : ($pct > 0 ? 'var(--alerte-orange)' : 'var(--alerte-rouge)') ?>;border-radius:4px"></div>
          </div>
          <div style="font-size:11px;color:var(--texte-leger);margin-top:3px">
            Stock : <?= $o['stock'] ?> / <?= $o['stock_initial'] ?>
          </div>
        </div>
 
        <div class="recipe-card-meta">
          <span class="badge <?= $sc ?>"><?= $sl ?></span>
          <span class="badge badge-brun"><?= $catIcon ?> <?= $catLbl ?></span>
          <span class="badge badge-info" style="margin-left:auto">
            ⏰ <?= date('d/m', strtotime($o['date_expiration'])) ?>
          </span>
        </div>
      </div>
    </a>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
 
<?php require __DIR__ . '/../layouts/footer.php'; ?>