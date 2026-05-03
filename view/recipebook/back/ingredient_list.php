<?php
/**
 * FoodWise — Vue : Liste des ingrédients (Back Office)
 * view/recipebook/back/ingredient_list.php 
 */
include __DIR__ . '/layout/header.php';

/* Afficher le message flash s'il existe */

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

?>

<!-- En-tête -->
<div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:22px;">
  <div>
    <h1 class="page-title">🧪 Base Ingrédients</h1>
    <p class="page-subtitle">Gestion de tous les ingrédients du système</p>
  </div>
  <a href="/FOODWISE/index.php?url=admin/ingredients/ajouter" class="btn btn-primary">
    + Nouvel ingrédient
  </a>
</div>
 
<!-- Message flash -->
<?php if ($flash): ?>
<div style="padding:12px 16px;border-radius:var(--radius);margin-bottom:18px;font-size:14px;
            background:<?= $flash['type'] === 'success' ? '#EAF3DE' : '#FADBD8' ?>;
            border:1.5px solid <?= $flash['type'] === 'success' ? '#5C7A3E' : '#C0392B' ?>;
            color:<?= $flash['type'] === 'success' ? '#3A5C1E' : '#922B21' ?>;">
  <?= htmlspecialchars($flash['message']) ?>
</div>
<?php endif; ?>
 
<!-- Statistiques -->
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:22px;">
  <?php
  $statCards = [
    ['label' => 'Total',         'val' => $stats['total']         ?? 0, 'color' => 'var(--brun-chaud)'],
    ['label' => 'Disponibles',   'val' => $stats['disponibles']   ?? 0, 'color' => 'var(--vert-moyen)'],
    ['label' => 'Indisponibles', 'val' => $stats['indisponibles'] ?? 0, 'color' => 'var(--alerte-orange)'],
    ['label' => 'Allergènes',    'val' => $stats['allergenes']    ?? 0, 'color' => 'var(--alerte-rouge)'],
  ];
  foreach ($statCards as $s): ?>
  <div style="background:var(--blanc);border-radius:var(--radius);box-shadow:var(--shadow);padding:16px 18px;">
    <div style="font-size:26px;font-weight:700;color:<?= $s['color'] ?>;"><?= $s['val'] ?></div>
    <div style="font-size:13px;color:var(--texte-leger);"><?= $s['label'] ?></div>
  </div>
  <?php endforeach; ?>
</div>
 
<!-- Filtres -->
<form method="GET" action="/FOODWISE/index.php">
  <input type="hidden" name="route" value="admin_ingredients">
  <div class="filter-bar" style="margin-bottom:18px;">
    <input type="text" name="q" placeholder="Rechercher un ingrédient..."
           value="<?= htmlspecialchars($filtres['q'] ?? '') ?>">
 
    <select name="categorie">
      <option value="">Toutes catégories</option>
      <?php foreach ($categories as $cat): ?>
        <option value="<?= $cat ?>" <?= ($filtres['categorie'] ?? '') === $cat ? 'selected' : '' ?>>
          <?= $cat ?>
        </option>
      <?php endforeach; ?>
    </select>
 
    <select name="est_allergene">
      <option value="">Allergène ?</option>
      <option value="1" <?= ($filtres['est_allergene'] ?? '') === '1' ? 'selected' : '' ?>>⚠️ Oui</option>
      <option value="0" <?= ($filtres['est_allergene'] ?? '') === '0' ? 'selected' : '' ?>>Non</option>
    </select>
 
    <select name="est_disponible">
      <option value="">Disponibilité</option>
      <option value="1" <?= ($filtres['est_disponible'] ?? '') === '1' ? 'selected' : '' ?>>✅ Disponible</option>
      <option value="0" <?= ($filtres['est_disponible'] ?? '') === '0' ? 'selected' : '' ?>>❌ Indisponible</option>
    </select>
 
    <select name="trier_par">
      <option value="nom_asc"  <?= ($filtres['trier_par'] ?? '') === 'nom_asc'  ? 'selected' : '' ?>>Nom A→Z</option>
      <option value="nom_desc" <?= ($filtres['trier_par'] ?? '') === 'nom_desc' ? 'selected' : '' ?>>Nom Z→A</option>
      <option value="cal_desc" <?= ($filtres['trier_par'] ?? '') === 'cal_desc' ? 'selected' : '' ?>>Calories ↓</option>
      <option value="cal_asc"  <?= ($filtres['trier_par'] ?? '') === 'cal_asc'  ? 'selected' : '' ?>>Calories ↑</option>
    </select>
 
    <button type="submit" class="btn btn-secondary btn-sm">Filtrer</button>
    <a href="/FOODWISE/index.php?url=admin/ingredients" class="btn btn-outline btn-sm">Réinitialiser</a>
  </div>
</form>
 
<!-- Tableau -->
<div class="card" style="padding:0;overflow:hidden;">
  <div style="padding:16px 20px;border-bottom:1px solid var(--creme-fonce);display:flex;align-items:center;justify-content:space-between;">
    <h2 class="card-title">
      Ingrédients
      <span style="font-size:13px;font-weight:400;color:var(--texte-leger);margin-left:8px;">
        (<?= $pagination['total'] ?> résultat<?= $pagination['total'] > 1 ? 's' : '' ?>)
      </span>
    </h2>
  </div>
 
  <div style="overflow-x:auto;">
  <table class="fw-table">
    <thead>
      <tr>
        <th>#</th>
        <th>Nom</th>
        <th>Catégorie</th>
        <th>Cal./100g</th>
        <th>Prot. (g)</th>
        <th>Gluc. (g)</th>
        <th>Lip. (g)</th>
        <th>Unité</th>
        <th>Allergène</th>
        <th>Dispo.</th>
        <th style="width:100px;">Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!empty($ingredients)): ?>
        <?php foreach ($ingredients as $ing): ?>
        <tr>
          <td style="font-size:12px;color:var(--texte-leger);"><?= $ing->id_ingredient ?></td>
          <td style="font-weight:700;color:var(--brun-fonce);"><?= htmlspecialchars($ing->nom) ?></td>
          <td>
            <?php if ($ing->categorie): ?>
              <span class="badge badge-brun"><?= htmlspecialchars($ing->categorie) ?></span>
            <?php else: ?>
              <span style="color:var(--texte-leger);font-size:12px;">—</span>
            <?php endif; ?>
          </td>
          <td><?= $ing->calories_100g  ?> kcal</td>
          <td><?= $ing->proteines_100g ?></td>
          <td><?= $ing->glucides_100g  ?></td>
          <td><?= $ing->lipides_100g   ?></td>
          <td><span class="badge badge-info"><?= htmlspecialchars($ing->unite_defaut) ?></span></td>
          <td>
            <?php if ($ing->est_allergene): ?>
              <span class="badge badge-rouge">⚠️ Oui</span>
            <?php else: ?>
              <span style="font-size:13px;color:var(--texte-leger);">Non</span>
            <?php endif; ?>
          </td>
          <td>
            <?php if ($ing->est_disponible): ?>
              <span class="badge badge-vert">✅ Oui</span>
            <?php else: ?>
              <span class="badge badge-rouge">❌ Non</span>
            <?php endif; ?>
          </td>
          <td>
            <div style="display:flex;gap:5px;">
              <a href="/FOODWISE/index.php?url=admin/ingredients/<?= $ing->id_ingredient ?>/modifier"
                 class="btn btn-outline btn-sm" title="Modifier">✏️</a>
              <form method="POST"
                    action="/FOODWISE/index.php?url=admin/ingredients/<?= $ing->id_ingredient ?>/supprimer"
                    onsubmit="return confirm('Supprimer «<?= htmlspecialchars(addslashes($ing->nom)) ?>» ?');"
                    style="display:inline;">
                <input type="hidden" name="_method" value="DELETE">
                <button type="submit" class="btn btn-danger btn-sm" title="Supprimer">🗑</button>
              </form>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr>
          <td colspan="11" style="text-align:center;padding:40px;color:var(--texte-leger);">
            Aucun ingrédient trouvé.
            <a href="/FOODWISE/index.php?url=admin/ingredients/ajouter"
               style="color:var(--brun-chaud);">Ajouter le premier.</a>
          </td>
        </tr>
      <?php endif; ?>
    </tbody>
  </table>
  </div>
 
  <!-- Pagination -->
  <?php if ($pagination['total_pages'] > 1): ?>
  <div style="padding:14px 20px;border-top:1px solid var(--creme-fonce);">
    <div class="pagination">
      <?php if ($pagination['page'] > 1): ?>
        <a href="?url=admin/ingredients&page=<?= $pagination['page'] - 1 ?>">‹</a>
      <?php endif; ?>
      <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
        <?php if ($i == $pagination['page']): ?>
          <span class="current"><?= $i ?></span>
        <?php else: ?>
          <a href="?url=admin/ingredients&page=<?= $i ?>"><?= $i ?></a>
        <?php endif; ?>
      <?php endfor; ?>
      <?php if ($pagination['page'] < $pagination['total_pages']): ?>
        <a href="?url=admin/ingredients&page=<?= $pagination['page'] + 1 ?>">›</a>
      <?php endif; ?>
    </div>
  </div>
  <?php endif; ?>
</div>

<?php include __DIR__ . '/layout/footer.php'; ?>
