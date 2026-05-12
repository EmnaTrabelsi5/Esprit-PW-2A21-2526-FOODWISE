<?php
/**
 * FoodWise — Module RecipeBook
 * Vue : Détail d'une recette (Front Office)
 * views/recipebook/front/detail.php
 *
 * Variables transmises par RecipeController::show($id) :
 *   $recette      object   Objet Recette
 *   $ingredients  array    Objets RecetteIngredient + Ingredient jointure
 *   $substituts   array    Suggestions de substitution (si allergène/indispo)
 *   $nutrition    array    ['calories','proteines','glucides','lipides']
 *   $score        int|null Score de correspondance avec le profil utilisateur (0-100)
 */


$pageTitle = $recette->nom ?? 'Détail recette';
$activeNav = 'recettes';
$backoffice = false;
include __DIR__ . '/layout/header.php';
?>

<!-- ── Fil d'Ariane ── -->
<nav style="font-size:13px;color:var(--texte-leger);margin-bottom:16px;">
  <a href="?route=recettes" style="color:var(--brun-chaud);text-decoration:none;">Mes Recettes</a>
  <span style="margin:0 6px;">›</span>
  <span><?= htmlspecialchars($recette->nom) ?></span>
</nav>

<div style="display:grid; grid-template-columns: 1fr 340px; gap:28px; align-items:start;">

  <!-- ══ Colonne principale ══ -->
  <div>

    <!-- Image + titre -->
    <div class="card" style="padding:0;overflow:hidden;">
      <?php if (!empty($recette->image_url)): ?>
        <img src="<?= htmlspecialchars($recette->image_url) ?>"
             alt="<?= htmlspecialchars($recette->nom) ?>"
             style="width:100%;height:280px;object-fit:cover;">
      <?php else: ?>
        <div style="width:100%;height:200px;background:var(--creme-fonce);display:flex;align-items:center;justify-content:center;font-size:64px;">🍽️</div>
      <?php endif; ?>

      <div style="padding:24px;">
        <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:12px;flex-wrap:wrap;">
          <h1 class="page-title" style="margin-bottom:8px;">
            <?= htmlspecialchars($recette->nom) ?>
          </h1>
          <div style="display:flex;gap:8px;flex-wrap:wrap;">
            <?php if (!$backoffice): ?>
             <a href="?route=recettes/<?= $recette->id_recette ?>/modifier" class="btn btn-outline btn-sm" style="flex:1;">  Modifier</a>
            <?php endif; ?>
             <form method="POST" action="?route=recettes/<?= $recette->id_recette ?>/supprimer"
                  onsubmit="return confirm('Supprimer cette recette ?');" style="display:inline;flex:1;">
              <input type="hidden" name="_method" value="DELETE">
              <button type="submit" class="btn btn-danger btn-sm">🗑 Supprimer</button>
            </form>
          </div>
        </div>

        <!-- Badges -->
        <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:14px;">
          <?php if ($recette->est_vegetarien): ?><span class="badge badge-vert">🥦 Végétarien</span><?php endif; ?>
          <?php if ($recette->est_vegan):      ?><span class="badge badge-vert">🌱 Vegan</span><?php endif; ?>
          <?php if ($recette->est_sans_gluten):?><span class="badge badge-info">🌾 Sans gluten</span><?php endif; ?>
          <?php
            $niveaux = ['facile' => 'badge-vert', 'moyen' => 'badge-orange', 'difficile' => 'badge-rouge'];
            $cls = $niveaux[$recette->niveau_difficulte] ?? 'badge-brun';
          ?>
          <span class="badge <?= $cls ?>">
            <?= ['facile'=>'⭐ Facile','moyen'=>'⭐⭐ Moyen','difficile'=>'⭐⭐⭐ Difficile'][$recette->niveau_difficulte] ?? '' ?>
          </span>
        </div>

        <!-- Méta-infos rapides -->
        <div style="display:flex;gap:24px;flex-wrap:wrap;font-size:14px;color:var(--texte-moyen);margin-bottom:16px;padding:12px 0;border-top:1px solid var(--creme-fonce);border-bottom:1px solid var(--creme-fonce);">
          <span>⏱ Préparation : <strong><?= $recette->temps_prep ?> min</strong></span>
          <span>🍳 Cuisson : <strong><?= $recette->temps_cuisson ?? 0 ?> min</strong></span>
          <span>👥 Portions : <strong><?= $recette->portions ?></strong></span>
          <span>📅 Ajoutée le <strong><?= date('d/m/Y', strtotime($recette->date_creation)) ?></strong></span>
        </div>

        <p style="font-size:15px;color:var(--texte-sombre);line-height:1.7;">
          <?= nl2br(htmlspecialchars($recette->description ?? '')) ?>
        </p>
      </div>
    </div>

    <!-- Liste des ingrédients -->
    <div class="card">
      <div class="card-header">
        <h2 class="card-title">🧺 Ingrédients</h2>
        <span style="font-size:13px;color:var(--texte-leger);"><?= count($ingredients) ?> ingrédient<?= count($ingredients) > 1 ? 's' : '' ?></span>
      </div>

      <ul style="list-style:none;display:flex;flex-direction:column;gap:10px;">
        <?php foreach ($ingredients as $ri): ?>
        <li style="display:flex;align-items:center;justify-content:space-between;padding:10px 14px;background:var(--creme);border-radius:8px;">
          <div style="display:flex;align-items:center;gap:10px;">
            <div style="width:36px;height:36px;border-radius:50%;background:var(--vert-pale);display:flex;align-items:center;justify-content:center;font-size:18px;">🥗</div>
            <div>
              <div style="font-weight:700;font-size:14px;color:var(--texte-sombre);">
                <?= htmlspecialchars($ri->ingredient_nom) ?>
              </div>
              <?php if ($ri->est_optionnel): ?>
                <span style="font-size:11px;color:var(--texte-leger);">optionnel</span>
              <?php endif; ?>
            </div>
          </div>
          <div style="text-align:right;">
            <strong style="font-size:15px;color:var(--brun-moyen);">
              <?= $ri->quantite ?> <?= htmlspecialchars($ri->unite) ?>
            </strong>

            <!-- Alerte allergène / indisponible -->
            <?php if ($ri->est_allergene || !$ri->est_disponible): ?>
              <div style="font-size:11px;color:var(--alerte-orange);">
                <?= $ri->est_allergene ? '⚠️ Allergène' : '❌ Indisponible' ?>
              </div>
            <?php endif; ?>
          </div>
        </li>
        <?php endforeach; ?>
      </ul>
    </div>

    <!-- Suggestions de substitution -->
    <?php if (!empty($substituts)): ?>
    <div class="card">
      <div class="card-header">
        <h2 class="card-title">🔄 Suggestions de substitution</h2>
      </div>
      <?php foreach ($substituts as $sub): ?>
      <div class="substitut-alert">
        <span style="font-size:18px;">⚠️</span>
        <div>
          <strong><?= htmlspecialchars($sub->nom_source) ?></strong>
          <?= isset($sub->ingredient_source) && $sub->ingredient_source ? ($sub->ingredient_source->est_allergene ? '(allergène)' : '(indisponible)') : '(indisponible)' ?>
          <span style="margin:0 6px;">→</span>
          <strong style="color:var(--vert-fonce);">
            <?= htmlspecialchars($sub->nom_substitut) ?>
          </strong>
          <?php if ($sub->ratio_conversion != 1): ?>
            <span style="font-size:12px;"> (×<?= $sub->ratio_conversion ?> la quantité)</span>
          <?php endif; ?>
          <?php if (!empty($sub->raison)): ?>
            <div style="font-size:12px;margin-top:3px;opacity:0.85;"><?= htmlspecialchars($sub->raison) ?></div>
          <?php endif; ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

  </div><!-- /colonne principale -->

  <!-- ══ Colonne latérale ══ -->
  <div>

    <!-- Score de correspondance -->
    <?php if (!is_null($score)): ?>
    <div class="card" style="text-align:center;margin-bottom:22px;">
      <div style="font-size:13px;font-weight:700;color:var(--texte-leger);text-transform:uppercase;letter-spacing:0.5px;margin-bottom:8px;">Score nutritionnel</div>
      <?php $couleur = $score >= 80 ? 'var(--alerte-vert)' : ($score >= 50 ? 'var(--alerte-orange)' : 'var(--alerte-rouge)'); ?>
      <div style="width:80px;height:80px;border-radius:50%;border:5px solid <?= $couleur ?>;margin:0 auto 10px;display:flex;align-items:center;justify-content:center;">
        <span style="font-size:22px;font-weight:700;color:<?= $couleur ?>;"><?= $score ?>%</span>
      </div>
      <p style="font-size:13px;color:var(--texte-leger);">Correspondance avec votre profil</p>
    </div>
    <?php endif; ?>

    <!-- Valeurs nutritionnelles -->
    <div class="card">
      <div class="card-header">
        <h2 class="card-title">🔬 Valeurs nutritionnelles</h2>
      </div>
      <p style="font-size:12px;color:var(--texte-leger);margin-bottom:14px;">Pour <?= $recette->portions ?> portion<?= $recette->portions > 1 ? 's' : '' ?> </p>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
        <div style="padding:14px;background:var(--creme);border-radius:8px;text-align:center;">
          <div style="font-size:11px;font-weight:700;color:var(--texte-leger);text-transform:uppercase;margin-bottom:4px;">Calories</div>
          <div style="font-size:24px;font-weight:700;color:var(--brun-fonce);"><?= round($nutrition['calories'] ?? 0) ?></div>
          <div style="font-size:11px;color:var(--texte-leger);">kcal</div>
        </div>
        <div style="padding:14px;background:var(--creme);border-radius:8px;text-align:center;">
          <div style="font-size:11px;font-weight:700;color:var(--texte-leger);text-transform:uppercase;margin-bottom:4px;">Protéines</div>
          <div style="font-size:24px;font-weight:700;color:var(--vert-fonce);"><?= round($nutrition['proteines'] ?? 0) ?></div>
          <div style="font-size:11px;color:var(--texte-leger);">g</div>
        </div>
        <div style="padding:14px;background:var(--creme);border-radius:8px;text-align:center;">
          <div style="font-size:11px;font-weight:700;color:var(--texte-leger);text-transform:uppercase;margin-bottom:4px;">Glucides</div>
          <div style="font-size:24px;font-weight:700;color:var(--brun-clair);"><?= round($nutrition['glucides'] ?? 0) ?></div>
          <div style="font-size:11px;color:var(--texte-leger);">g</div>
        </div>
        <div style="padding:14px;background:var(--creme);border-radius:8px;text-align:center;">
          <div style="font-size:11px;font-weight:700;color:var(--texte-leger);text-transform:uppercase;margin-bottom:4px;">Lipides</div>
          <div style="font-size:24px;font-weight:700;color:var(--alerte-orange);"><?= round($nutrition['lipides'] ?? 0) ?></div>
          <div style="font-size:11px;color:var(--texte-leger);">g</div>
        </div>
      </div>

      <!-- Barre macros -->
      <?php
        $total_macros = ($nutrition['proteines'] ?? 0) + ($nutrition['glucides'] ?? 0) + ($nutrition['lipides'] ?? 0);
        $p_prot = $total_macros > 0 ? round(($nutrition['proteines'] ?? 0) / $total_macros * 100) : 0;
        $p_gluc = $total_macros > 0 ? round(($nutrition['glucides'] ?? 0) / $total_macros * 100) : 0;
        $p_lip  = 100 - $p_prot - $p_gluc;
      ?>
      <div style="margin-top:14px;">
        <div style="display:flex;height:10px;border-radius:5px;overflow:hidden;gap:2px;">
          <div style="width:<?= $p_prot ?>%;background:var(--vert-moyen);" title="Protéines <?= $p_prot ?>%"></div>
          <div style="width:<?= $p_gluc ?>%;background:var(--brun-clair);" title="Glucides <?= $p_gluc ?>%"></div>
          <div style="width:<?= $p_lip  ?>%;background:#E67E22;"            title="Lipides <?= $p_lip ?>%"></div>
        </div>
        <div style="display:flex;gap:14px;font-size:11px;margin-top:6px;color:var(--texte-leger);">
          <span><span style="color:var(--vert-moyen);font-weight:700;">■</span> Protéines <?= $p_prot ?>%</span>
          <span><span style="color:var(--brun-clair);font-weight:700;">■</span> Glucides <?= $p_gluc ?>%</span>
          <span><span style="color:#E67E22;font-weight:700;">■</span> Lipides <?= $p_lip ?>%</span>
        </div>
      </div>
    </div>

    <!-- Actions -->
<div class="card">
  <a href="?route=recettes/<?= $recette->id_recette ?>/courses"
     class="btn btn-secondary" style="width:100%;justify-content:center;margin-bottom:10px;">
    🛒 Voir la liste de courses
  </a>
  <a href="#" class="btn btn-secondary" style="width:100%;justify-content:center;margin-bottom:10px;">
    📅 Ajouter au planificateur
  </a>
  <button id="btn-favori-detail"
          onclick="toggleFavoriDetail(<?= $recette->id_recette ?>)"
          class="btn <?= $estFavori ? 'btn-danger' : 'btn-outline' ?>"
          style="width:100%;justify-content:center;">
    <?= $estFavori ? '❤️ Dans vos favoris' : '🤍 Ajouter aux favoris' ?>
  </button>
</div>

  </div><!-- /sidebar -->
</div><!-- /grid -->

<script>
async function toggleFavoriDetail(recetteId) {
    const btn = document.getElementById('btn-favori-detail');
    try {
        const resp = await fetch('?route=recettes/' + recetteId + '/favori', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        const data = await resp.json();

        if (data.error === 'non_connecte') {
            window.location.href = '?route=module2/front/connexion';
            return;
        }

        if (data.action === 'added') {
            btn.textContent  = '❤️ Dans vos favoris';
            btn.className    = 'btn btn-danger';
            btn.style.width  = '100%';
            btn.style.justifyContent = 'center';
        } else {
            btn.textContent  = '🤍 Ajouter aux favoris';
            btn.className    = 'btn btn-outline';
            btn.style.width  = '100%';
            btn.style.justifyContent = 'center';
        }
    } catch (err) {
        console.error('Erreur favori:', err);
    }
}
</script>

<?php include __DIR__ . '/layout/footer.php'; ?>

