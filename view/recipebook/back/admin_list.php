<?php
/**
 * FoodWise — Module RecipeBook
 * Vue : Gestion des recettes (Back Office)
 * views/recipebook/back/admin_list.php
 *
 * Variables transmises par AdminRecipeController::index() :
 *   $recettes     array   Liste d'objets Recette (avec count ingrédients, calories_totales)
 *   $stats        array   ['total','vegetariennes','sans_gluten','cette_semaine']
 *   $pagination   array   ['page','total_pages','total']
 *   $filtres      array   Filtres actifs
 */

$pageTitle  = 'Gestion des recettes';
$activeNav  = 'gestion_recettes';
$backoffice = true;
include __DIR__ . '/../../layout/header.php';
?>

<div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:22px;">
  <div>
    <h1 class="page-title">📋 Gestion des Recettes</h1>
    <p class="page-subtitle">Back Office — Module RecipeBook</p>
  </div>
  <a href="/admin/recettes/ajouter" class="btn btn-primary">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
    Ajouter une recette
  </a>
</div>

<!-- ── Statistiques ── -->
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:16px;margin-bottom:24px;">
  <?php
    $stat_cards = [
      ['label' => 'Total recettes',    'val' => $stats['total'] ?? 0,          'icon' => '📖', 'color' => 'var(--brun-chaud)'],
      ['label' => 'Végétariennes',     'val' => $stats['vegetariennes'] ?? 0,  'icon' => '🥦', 'color' => 'var(--vert-moyen)'],
      ['label' => 'Sans gluten',       'val' => $stats['sans_gluten'] ?? 0,    'icon' => '🌾', 'color' => '#2471A3'],
      ['label' => 'Ajoutées ce mois',  'val' => $stats['ce_mois'] ?? 0,        'icon' => '📅', 'color' => '#884EA0'],
    ];
    foreach ($stat_cards as $sc):
  ?>
  <div style="background:var(--blanc);border-radius:var(--radius);box-shadow:var(--shadow);padding:18px 20px;">
    <div style="font-size:24px;margin-bottom:6px;"><?= $sc['icon'] ?></div>
    <div style="font-size:28px;font-weight:700;color:<?= $sc['color'] ?>;"><?= $sc['val'] ?></div>
    <div style="font-size:13px;color:var(--texte-leger);"><?= $sc['label'] ?></div>
  </div>
  <?php endforeach; ?>
</div>

<!-- ── Filtres ── -->
<form method="GET" action="/admin/recettes">
<div class="filter-bar" style="margin-bottom:18px;">
  <input type="text" name="q" placeholder="Rechercher une recette..."
         value="<?= htmlspecialchars($filtres['q'] ?? '') ?>">
  <select name="regime">
    <option value="">Tous régimes</option>
    <option value="vegetarien"  <?= ($filtres['regime'] ?? '') === 'vegetarien'  ? 'selected' : '' ?>>🥦 Végétarien</option>
    <option value="vegan"       <?= ($filtres['regime'] ?? '') === 'vegan'       ? 'selected' : '' ?>>🌱 Vegan</option>
    <option value="sans_gluten" <?= ($filtres['regime'] ?? '') === 'sans_gluten' ? 'selected' : '' ?>>🌾 Sans gluten</option>
  </select>
  <select name="difficulte">
    <option value="">Difficulté</option>
    <option value="facile"    <?= ($filtres['difficulte'] ?? '') === 'facile'    ? 'selected' : '' ?>>Facile</option>
    <option value="moyen"     <?= ($filtres['difficulte'] ?? '') === 'moyen'     ? 'selected' : '' ?>>Moyen</option>
    <option value="difficile" <?= ($filtres['difficulte'] ?? '') === 'difficile' ? 'selected' : '' ?>>Difficile</option>
  </select>
  <select name="trier_par">
    <option value="date_desc" <?= ($filtres['trier_par'] ?? '') === 'date_desc' ? 'selected' : '' ?>>Plus récentes</option>
    <option value="nom_asc"   <?= ($filtres['trier_par'] ?? '') === 'nom_asc'   ? 'selected' : '' ?>>Nom A→Z</option>
    <option value="cal_desc"  <?= ($filtres['trier_par'] ?? '') === 'cal_desc'  ? 'selected' : '' ?>>Calories ↓</option>
  </select>
  <button type="submit" class="btn btn-secondary btn-sm">Filtrer</button>
  <a href="/admin/recettes" class="btn btn-outline btn-sm">Réinitialiser</a>
</div>
</form>

<!-- ── Tableau ── -->
<div class="card" style="padding:0;overflow:hidden;">
  <div style="padding:18px 22px;border-bottom:1px solid var(--creme-fonce);display:flex;align-items:center;justify-content:space-between;">
    <h2 class="card-title" style="margin:0;">
      Liste des recettes
      <span style="font-size:13px;font-weight:400;color:var(--texte-leger);margin-left:8px;">
        (<?= $pagination['total'] ?? 0 ?> résultat<?= ($pagination['total'] ?? 0) > 1 ? 's' : '' ?>)
      </span>
    </h2>
    <div style="display:flex;gap:8px;">
      <a href="/admin/recettes/export?format=csv" class="btn btn-outline btn-sm">⬇ CSV</a>
    </div>
  </div>

  <div style="overflow-x:auto;">
  <table class="fw-table">
    <thead>
      <tr>
        <th style="width:40px;">#</th>
        <th>Nom</th>
        <th>Régimes</th>
        <th>Diff.</th>
        <th>Temps total</th>
        <th>Ingrédients</th>
        <th>Calories</th>
        <th>Date ajout</th>
        <th style="width:130px;">Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!empty($recettes)): ?>
        <?php foreach ($recettes as $r): ?>
        <tr>
          <td style="color:var(--texte-leger);font-size:12px;"><?= $r->id_recette ?></td>
          <td>
            <div style="font-weight:700;color:var(--brun-fonce);"><?= htmlspecialchars($r->nom) ?></div>
            <?php if (!empty($r->description)): ?>
              <div style="font-size:12px;color:var(--texte-leger);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:220px;">
                <?= htmlspecialchars(substr($r->description, 0, 70)) ?>...
              </div>
            <?php endif; ?>
          </td>
          <td>
            <div style="display:flex;gap:4px;flex-wrap:wrap;">
              <?php if ($r->est_vegetarien): ?><span class="badge badge-vert" style="font-size:10px;">Végé</span><?php endif; ?>
              <?php if ($r->est_vegan):      ?><span class="badge badge-vert" style="font-size:10px;">Vegan</span><?php endif; ?>
              <?php if ($r->est_sans_gluten):?><span class="badge badge-info" style="font-size:10px;">S/G</span><?php endif; ?>
              <?php if (!$r->est_vegetarien && !$r->est_vegan && !$r->est_sans_gluten): ?>
                <span class="badge badge-brun" style="font-size:10px;">Standard</span>
              <?php endif; ?>
            </div>
          </td>
          <td>
            <?php
              $niv = ['facile' => '⭐', 'moyen' => '⭐⭐', 'difficile' => '⭐⭐⭐'];
              echo $niv[$r->niveau_difficulte] ?? '-';
            ?>
          </td>
          <td style="white-space:nowrap;">
            <?= ($r->temps_prep ?? 0) + ($r->temps_cuisson ?? 0) ?> min
          </td>
          <td style="text-align:center;"><?= $r->nb_ingredients ?? '–' ?></td>
          <td>
            <?php if (!empty($r->calories_totales)): ?>
              <span style="font-weight:700;color:var(--brun-moyen);"><?= round($r->calories_totales) ?></span>
              <span style="font-size:11px;color:var(--texte-leger);"> kcal</span>
            <?php else: ?>
              <span style="color:var(--texte-leger);font-size:12px;">—</span>
            <?php endif; ?>
          </td>
          <td style="font-size:13px;color:var(--texte-leger);white-space:nowrap;">
            <?= date('d/m/Y', strtotime($r->date_creation)) ?>
          </td>
          <td>
            <div style="display:flex;gap:5px;align-items:center;">
              <a href="/recettes/<?= $r->id_recette ?>" class="btn btn-outline btn-sm" title="Voir">👁</a>
              <a href="/admin/recettes/<?= $r->id_recette ?>/modifier" class="btn btn-outline btn-sm" title="Modifier">✏️</a>
              <form method="POST" action="/admin/recettes/<?= $r->id_recette ?>/supprimer"
                    onsubmit="return confirm('Supprimer «<?= htmlspecialchars(addslashes($r->nom)) ?>» ?');"
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
          <td colspan="9" style="text-align:center;padding:40px;color:var(--texte-leger);">
            Aucune recette trouvée.
            <a href="/admin/recettes/ajouter" style="color:var(--brun-chaud);">Ajouter la première.</a>
          </td>
        </tr>
      <?php endif; ?>
    </tbody>
  </table>
  </div>

  <!-- Pagination -->
  <?php if (($pagination['total_pages'] ?? 1) > 1): ?>
  <div style="padding:16px 22px;border-top:1px solid var(--creme-fonce);">
    <div class="pagination">
      <?php if ($pagination['page'] > 1): ?>
        <a href="?page=<?= $pagination['page'] - 1 ?>">‹</a>
      <?php endif; ?>
      <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
        <?php if ($i == $pagination['page']): ?>
          <span class="current"><?= $i ?></span>
        <?php else: ?>
          <a href="?page=<?= $i ?>"><?= $i ?></a>
        <?php endif; ?>
      <?php endfor; ?>
      <?php if ($pagination['page'] < $pagination['total_pages']): ?>
        <a href="?page=<?= $pagination['page'] + 1 ?>">›</a>
      <?php endif; ?>
    </div>
  </div>
  <?php endif; ?>
</div><!-- /card tableau -->

<?php include __DIR__ . '/../../layout/footer.php'; ?>
