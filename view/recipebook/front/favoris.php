<?php
include __DIR__ . '/layout/header.php';

if (!isset($recettes)) {
    $recettes = [];
}
?>

<div style="display:flex;align-items:center;justify-content:space-between;
            flex-wrap:wrap;gap:12px;margin-bottom:22px;">
  <div>
    <h1 class="page-title">❤️ Mes Favoris</h1>
    <p class="page-subtitle"><?= count($recettes) ?> recette<?= count($recettes) > 1 ? 's' : '' ?> sauvegardée<?= count($recettes) > 1 ? 's' : '' ?></p>
  </div>
  <a href="?route=recettes" class="btn btn-outline btn-sm">← Toutes les recettes</a>
</div>

<?php if (!empty($recettes)): ?>
<div class="recipe-grid">
  <?php foreach ($recettes as $r): ?>
  <a href="?route=recettes/<?= $r->id_recette ?>" class="recipe-card">
    <div style="position:relative;">
      <?php if (!empty($r->image_url)): ?>
        <img src="<?= htmlspecialchars($r->image_url) ?>"
             alt="<?= htmlspecialchars($r->nom) ?>" class="recipe-card-img">
      <?php else: ?>
        <div class="recipe-card-img-placeholder">🍽️</div>
      <?php endif; ?>
      <button
        class="btn-favori actif"
        data-id="<?= $r->id_recette ?>"
        onclick="toggleFavori(event, this, <?= $r->id_recette ?>)"
        title="Retirer des favoris">❤️</button>
    </div>
    <div class="recipe-card-body">
      <div class="recipe-card-title"><?= htmlspecialchars($r->nom) ?></div>
      <div style="font-size:12px;color:var(--texte-leger);margin-bottom:8px;">
        ⏱ <?= ($r->temps_prep ?? 0) + ($r->temps_cuisson ?? 0) ?> min &nbsp;·&nbsp;
        👥 <?= $r->portions ?> pers.
      </div>
      <div class="recipe-card-meta">
        <?php if ($r->est_vegetarien): ?><span class="badge badge-vert">🥦 Végé</span><?php endif; ?>
        <?php if ($r->est_vegan):      ?><span class="badge badge-vert">🌱 Vegan</span><?php endif; ?>
        <?php if ($r->est_sans_gluten):?><span class="badge badge-info">🌾 S/G</span><?php endif; ?>
      </div>
    </div>
  </a>
  <?php endforeach; ?>
</div>

<?php else: ?>
<div style="text-align:center;padding:60px 20px;color:var(--texte-leger);">
  <div style="font-size:56px;margin-bottom:16px;">🤍</div>
  <h3 style="font-family:'Playfair Display',serif;color:var(--brun-fonce);margin-bottom:8px;">
    Aucun favori pour l'instant
  </h3>
  <p style="margin-bottom:20px;">Cliquez sur le cœur d'une recette pour la sauvegarder ici.</p>
  <a href="?url=recettes" class="btn btn-primary">Explorer les recettes</a>
</div>
<?php endif; ?>

<style>
.btn-favori {
    position:absolute;top:8px;right:8px;
    background:rgba(255,255,255,0.85);border:none;border-radius:50%;
    width:34px;height:34px;font-size:17px;cursor:pointer;
    display:flex;align-items:center;justify-content:center;
    box-shadow:0 1px 6px rgba(0,0,0,0.15);
    transition:transform 0.15s,background 0.15s;z-index:2;padding:0;
}
.btn-favori:hover { transform:scale(1.18); }
.btn-favori.actif { background:rgba(255,230,235,0.95); }
</style>

<script>
async function toggleFavori(event, btn, recetteId) {
    event.preventDefault();
    event.stopPropagation();
    try {
        const resp = await fetch('index.php?url=recettes/' + recetteId + '/favori', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        const data = await resp.json();
        if (data.action === 'removed') {
            /* Sur la page favoris : supprimer la carte */
            btn.closest('.recipe-card').remove();
            /* Mettre à jour le compteur */
            const remaining = document.querySelectorAll('.recipe-card').length;
            document.querySelector('.page-subtitle').textContent =
                remaining + ' recette' + (remaining > 1 ? 's' : '') + ' sauvegardée' + (remaining > 1 ? 's' : '');
        }
    } catch(err) { console.error(err); }
}
</script>

<?php include __DIR__ . '/layout/footer.php'; ?>
