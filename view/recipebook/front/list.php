<?php
/**
 * FoodWise — Module RecipeBook
 * Vue : Liste des recettes (Front Office)
 * views/recipebook/front/list.php
 *
 * Variables transmises par RecipeController::index() :
 *   $recettes   array   Liste d'objets Recette
 *   $filtres    array   Valeurs de filtres actifs
 *   $pagination array   ['page', 'total_pages', 'total']
 */

$pageTitle = 'Recettes';
$activeNav = 'recettes';
$backoffice = false;
include __DIR__ . '/layout/header.php';
?>

<!-- ── En-tête page ── -->
<div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px; margin-bottom:20px;">
  <div>
    <h1 class="page-title">📖 Recettes</h1>
    <p class="page-subtitle">
      <?= $pagination['total'] ?? 0 ?> recette<?= ($pagination['total'] ?? 0) > 1 ? 's' : '' ?> disponible<?= ($pagination['total'] ?? 0) > 1 ? 's' : '' ?>
    </p>
  </div>
  <a href="?route=recettes/ajouter" class="btn btn-primary">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
    Nouvelle recette
  </a>
</div>

<!-- ── Barre de filtres ── -->
<form method="GET" action="?route=recettes">
<div class="filter-bar">
  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--texte-leger)" stroke-width="2" style="flex-shrink:0;"><path d="M22 3H2l8 9.46V19l4 2v-8.54z"/></svg>

  <input type="text" name="q" placeholder="Mot-clé, ingrédient..."
         value="<?= htmlspecialchars($filtres['q'] ?? '') ?>">

  <select name="regime">
    <option value="">Tous les régimes</option>
    <option value="vegetarien"  <?= ($filtres['regime'] ?? '') === 'vegetarien'  ? 'selected' : '' ?>>🥦 Végétarien</option>
    <option value="vegan"       <?= ($filtres['regime'] ?? '') === 'vegan'       ? 'selected' : '' ?>>🌱 Vegan</option>
    <option value="sans_gluten" <?= ($filtres['regime'] ?? '') === 'sans_gluten' ? 'selected' : '' ?>>🌾 Sans gluten</option>
  </select>

  <select name="difficulte">
    <option value="">Difficulté</option>
    <option value="facile"    <?= ($filtres['difficulte'] ?? '') === 'facile'    ? 'selected' : '' ?>>⭐ Facile</option>
    <option value="moyen"     <?= ($filtres['difficulte'] ?? '') === 'moyen'     ? 'selected' : '' ?>>⭐⭐ Moyen</option>
    <option value="difficile" <?= ($filtres['difficulte'] ?? '') === 'difficile' ? 'selected' : '' ?>>⭐⭐⭐ Difficile</option>
  </select>

  <select name="temps_max">
    <option value="">Temps de prépa.</option>
    <option value="15"  <?= ($filtres['temps_max'] ?? '') === '15'  ? 'selected' : '' ?>>≤ 15 min</option>
    <option value="30"  <?= ($filtres['temps_max'] ?? '') === '30'  ? 'selected' : '' ?>>≤ 30 min</option>
    <option value="60"  <?= ($filtres['temps_max'] ?? '') === '60'  ? 'selected' : '' ?>>≤ 1 heure</option>
  </select>

  <select name="calories_max">
    <option value="">Calories max</option>
    <option value="300"  <?= ($filtres['calories_max'] ?? '') === '300'  ? 'selected' : '' ?>>&lt; 300 kcal</option>
    <option value="500"  <?= ($filtres['calories_max'] ?? '') === '500'  ? 'selected' : '' ?>>&lt; 500 kcal</option>
    <option value="800"  <?= ($filtres['calories_max'] ?? '') === '800'  ? 'selected' : '' ?>>&lt; 800 kcal</option>
  </select>

  <button type="submit" class="btn btn-secondary btn-sm">Filtrer</button>

  <?php if (!empty(array_filter($filtres ?? []))): ?>
    <a href="?route=recettes" class="btn btn-outline btn-sm">✕ Effacer</a>
  <?php endif; ?>
</div>
</form>

<!-- ── Grille de recettes ── -->
<?php if (!empty($recettes)): ?>
<div class="recipe-grid">
  <?php foreach ($recettes as $r): ?>
  <a href="/FOODWISE/recettes/<?= $r->id_recette ?>" class="recipe-card">

    <div style="position:relative;">
    <?php if (!empty($r->image_url)): ?>
      <img src="<?= htmlspecialchars($r->image_url) ?>"
           alt="<?= htmlspecialchars($r->nom) ?>"
           class="recipe-card-img">
    <?php else: ?>
      <div class="recipe-card-img-placeholder">🍽️</div>
    <?php endif; ?>

    <!-- Bouton cœur favori -->
    <?php
      $estFavoriCarte = in_array($r->id_recette, $favorisIds ?? []);
    ?>
    <button
      class="btn-favori <?= $estFavoriCarte ? 'actif' : '' ?>"
      data-id="<?= $r->id_recette ?>"
      onclick="toggleFavori(event, this, <?= $r->id_recette ?>)"
      title="<?= $estFavoriCarte ? 'Retirer des favoris' : 'Ajouter aux favoris' ?>">
      <?= $estFavoriCarte ? '❤️' : '🤍' ?>
    </button>
    </div>

    

    <div class="recipe-card-body">
      <div class="recipe-card-title"><?= htmlspecialchars($r->nom) ?></div>

      <p style="font-size:13px;color:var(--texte-leger);margin-bottom:8px;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;">
        <?= htmlspecialchars($r->description ?? '') ?>
      </p>

      <!-- Info rapide -->
      <div style="display:flex;gap:14px;font-size:12px;color:var(--texte-leger);margin-bottom:10px;">
        <span>⏱ <?= $r->temps_prep + ($r->temps_cuisson ?? 0) ?> min</span>
        <span>👥 <?= $r->portions ?> pers.</span>
        <?php if (!empty($r->calories_totales)): ?>
          <span>🔥 <?= round($r->calories_totales) ?> kcal</span>
        <?php endif; ?>
      </div>

      <!-- Badges régime -->
      <div class="recipe-card-meta">
        <?php if ($r->est_vegetarien): ?><span class="badge badge-vert">🥦 Végé</span><?php endif; ?>
        <?php if ($r->est_vegan):      ?><span class="badge badge-vert">🌱 Vegan</span><?php endif; ?>
        <?php if ($r->est_sans_gluten):?><span class="badge badge-info">🌾 Sans gluten</span><?php endif; ?>
        <span class="badge badge-brun">
          <?php
            $diff = ['facile' => '⭐ Facile', 'moyen' => '⭐⭐ Moyen', 'difficile' => '⭐⭐⭐ Difficile'];
            echo $diff[$r->niveau_difficulte] ?? $r->niveau_difficulte;
          ?>
        </span>
      </div>
    </div>
  </a>
  <?php endforeach; ?>
</div>

<!-- Pagination -->
<?php if ($pagination['total_pages'] > 1): ?>
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
<?php endif; ?>

<?php else: ?>
<!-- État vide -->
<div style="text-align:center; padding:60px 20px; color:var(--texte-leger);">
  <div style="font-size:56px;margin-bottom:16px;">🔍</div>
  <h3 style="font-family:'Playfair Display',serif;color:var(--brun-fonce);margin-bottom:8px;">Aucune recette trouvée</h3>
  <p style="margin-bottom:20px;">Essayez d'ajuster vos filtres ou <a href="/recettes/ajouter" style="color:var(--brun-chaud);">ajoutez une nouvelle recette</a>.</p>
</div>
<?php endif; ?>

<script>
async function toggleFavori(event, btn, recetteId) {
    event.preventDefault();  /* Empêche la navigation vers la recette */
    event.stopPropagation();

    try {
        const resp = await fetch('/FOODWISE/index.php?url=recettes/' + recetteId + '/favori', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        const data = await resp.json();

        if (data.error === 'non_connecte') {
            window.location.href = '/FOODWISE/index.php?url=login';
            return;
        }

        if (data.action === 'added') {
            btn.textContent = '❤️';
            btn.classList.add('actif');
            btn.title = 'Retirer des favoris';
            /* Animation rapide */
            btn.style.transform = 'scale(1.35)';
            setTimeout(() => btn.style.transform = 'scale(1)', 200);
        } else {
            btn.textContent = '🤍';
            btn.classList.remove('actif');
            btn.title = 'Ajouter aux favoris';
        }

    } catch (err) {
        console.error('Erreur favori:', err);
    }
}
</script>

<?php include __DIR__ . '/layout/footer.php'; ?>
