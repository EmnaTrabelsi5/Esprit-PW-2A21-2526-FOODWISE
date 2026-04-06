<?php
/**
 * FoodWise — Module RecipeBook
 * Vue : Gestion des ingrédients + substituts (Back Office)
 * views/recipebook/back/admin_ingredients.php
 *
 * Variables transmises par AdminIngredientController::index() :
 *   $ingredients  array   Liste d'objets Ingredient
 *   $substituts   array   Liste d'objets Substitut (avec jointure ingredient_source + sub)
 *   $pagination   array   ['page','total_pages','total']
 */

$pageTitle  = 'Base Ingrédients';
$activeNav  = 'gestion_ingredients';
$backoffice = true;
include __DIR__ . '/../../layout/header.php';
?>

<div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:22px;">
  <div>
    <h1 class="page-title">🧪 Base Ingrédients</h1>
    <p class="page-subtitle">Gestion des ingrédients et table de substitution</p>
  </div>
  <div style="display:flex;gap:10px;">
    <button type="button" class="btn btn-secondary" onclick="toggleModal('modal-ing')">+ Nouvel ingrédient</button>
    <button type="button" class="btn btn-outline"   onclick="toggleModal('modal-sub')">🔄 Nouveau substitut</button>
  </div>
</div>

<!-- ── Onglets ── -->
<div style="display:flex;gap:0;border-bottom:2px solid var(--creme-fonce);margin-bottom:22px;">
  <button class="tab-btn active" id="tab-ing-btn" onclick="switchTab('ing')"
          style="padding:10px 22px;background:none;border:none;border-bottom:3px solid var(--brun-chaud);font-size:14px;font-weight:700;color:var(--brun-fonce);cursor:pointer;">
    🥕 Ingrédients (<?= count($ingredients ?? []) ?>)
  </button>
  <button class="tab-btn" id="tab-sub-btn" onclick="switchTab('sub')"
          style="padding:10px 22px;background:none;border:none;border-bottom:3px solid transparent;font-size:14px;color:var(--texte-leger);cursor:pointer;">
    🔄 Substituts (<?= count($substituts ?? []) ?>)
  </button>
</div>

<!-- ══ ONGLET INGRÉDIENTS ══ -->
<div id="tab-ing">
<div class="card" style="padding:0;overflow:hidden;">
  <div style="overflow-x:auto;">
  <table class="fw-table">
    <thead>
      <tr>
        <th>#</th>
        <th>Nom</th>
        <th>Catégorie</th>
        <th>Cal./100g</th>
        <th>Prot.</th>
        <th>Gluc.</th>
        <th>Lip.</th>
        <th>Allergène</th>
        <th>Disponible</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!empty($ingredients)): ?>
        <?php foreach ($ingredients as $ing): ?>
        <tr>
          <td style="color:var(--texte-leger);font-size:12px;"><?= $ing->id_ingredient ?></td>
          <td style="font-weight:700;color:var(--brun-fonce);"><?= htmlspecialchars($ing->nom) ?></td>
          <td><span class="badge badge-brun"><?= htmlspecialchars($ing->categorie ?? '—') ?></span></td>
          <td><?= $ing->calories_100g ?? '—' ?> kcal</td>
          <td><?= $ing->proteines_100g ?? '—' ?> g</td>
          <td><?= $ing->glucides_100g ?? '—' ?> g</td>
          <td><?= $ing->lipides_100g ?? '—' ?> g</td>
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
              <button class="btn btn-outline btn-sm"
                      onclick="editIngredient(<?= htmlspecialchars(json_encode($ing)) ?>)">✏️</button>
              <form method="POST" action="/admin/ingredients/<?= $ing->id_ingredient ?>/supprimer"
                    onsubmit="return confirm('Supprimer cet ingrédient ?');" style="display:inline;">
                <input type="hidden" name="_method" value="DELETE">
                <button type="submit" class="btn btn-danger btn-sm">🗑</button>
              </form>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr><td colspan="10" style="text-align:center;padding:30px;color:var(--texte-leger);">Aucun ingrédient enregistré.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
  </div>
</div>
</div>

<!-- ══ ONGLET SUBSTITUTS ══ -->
<div id="tab-sub" style="display:none;">
<div class="card" style="padding:0;overflow:hidden;">
  <table class="fw-table">
    <thead>
      <tr>
        <th>#</th>
        <th>Ingrédient source</th>
        <th>Raison</th>
        <th>Substitut proposé</th>
        <th>Ratio</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!empty($substituts)): ?>
        <?php foreach ($substituts as $sub): ?>
        <tr>
          <td style="font-size:12px;color:var(--texte-leger);"><?= $sub->id_substitut ?></td>
          <td>
            <strong><?= htmlspecialchars($sub->ingredient_source->nom) ?></strong>
            <?php if ($sub->ingredient_source->est_allergene): ?>
              <span class="badge badge-rouge" style="font-size:10px;margin-left:4px;">⚠️ Allergène</span>
            <?php elseif (!$sub->ingredient_source->est_disponible): ?>
              <span class="badge badge-orange" style="font-size:10px;margin-left:4px;">Indisponible</span>
            <?php endif; ?>
          </td>
          <td style="font-size:13px;color:var(--texte-leger);"><?= htmlspecialchars($sub->raison ?? '—') ?></td>
          <td>
            <strong style="color:var(--vert-fonce);">
              <?= htmlspecialchars($sub->ingredient_substitut->nom) ?>
            </strong>
          </td>
          <td>
            <?php if ($sub->ratio_conversion != 1): ?>
              <span class="badge badge-info">×<?= $sub->ratio_conversion ?></span>
            <?php else: ?>
              <span style="color:var(--texte-leger);font-size:13px;">1:1</span>
            <?php endif; ?>
          </td>
          <td>
            <div style="display:flex;gap:5px;">
              <button class="btn btn-outline btn-sm">✏️</button>
              <form method="POST" action="/admin/substituts/<?= $sub->id_substitut ?>/supprimer"
                    onsubmit="return confirm('Supprimer ce substitut ?');" style="display:inline;">
                <input type="hidden" name="_method" value="DELETE">
                <button type="submit" class="btn btn-danger btn-sm">🗑</button>
              </form>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr><td colspan="6" style="text-align:center;padding:30px;color:var(--texte-leger);">Aucun substitut configuré.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>
</div>

<!-- ══ MODAL : Ajouter/Modifier un ingrédient ══ -->
<div id="modal-ing" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.45);z-index:200;align-items:center;justify-content:center;">
  <div style="background:var(--blanc);border-radius:var(--radius);box-shadow:0 20px 60px rgba(0,0,0,0.3);padding:28px;width:540px;max-width:95vw;max-height:90vh;overflow-y:auto;">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
      <h2 style="font-family:'Playfair Display',serif;font-size:20px;color:var(--brun-fonce);" id="modal-ing-title">
        Nouvel ingrédient
      </h2>
      <button onclick="toggleModal('modal-ing')" style="background:none;border:none;font-size:22px;cursor:pointer;color:var(--texte-leger);">✕</button>
    </div>

    <form method="POST" action="/admin/ingredients" id="form-ingredient" novalidate>
      <input type="hidden" name="_method" id="ing-method" value="POST">
      <input type="hidden" name="id_ingredient" id="ing-id">

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
        <div class="form-group" style="grid-column:span 2;">
          <label class="form-label">Nom de l'ingrédient *</label>
          <input type="text" name="nom" id="ing-nom" class="form-control" placeholder="Ex : Lait d'amande">
        </div>
        <div class="form-group">
          <label class="form-label">Catégorie</label>
          <select name="categorie" id="ing-categorie" class="form-control">
            <option value="">-- Sélectionner --</option>
            <?php foreach (['Légume','Fruit','Céréale','Protéine','Produit laitier','Corps gras','Épice','Autre'] as $cat): ?>
              <option value="<?= $cat ?>"><?= $cat ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Unité par défaut</label>
          <select name="unite_defaut" id="ing-unite" class="form-control">
            <?php foreach (['g','kg','ml','L','unité'] as $u): ?>
              <option value="<?= $u ?>"><?= $u ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Calories / 100g</label>
          <input type="number" name="calories_100g" id="ing-cal" class="form-control" step="0.1" min="0" placeholder="0">
        </div>
        <div class="form-group">
          <label class="form-label">Protéines / 100g (g)</label>
          <input type="number" name="proteines_100g" id="ing-prot" class="form-control" step="0.1" min="0" placeholder="0">
        </div>
        <div class="form-group">
          <label class="form-label">Glucides / 100g (g)</label>
          <input type="number" name="glucides_100g" id="ing-gluc" class="form-control" step="0.1" min="0" placeholder="0">
        </div>
        <div class="form-group">
          <label class="form-label">Lipides / 100g (g)</label>
          <input type="number" name="lipides_100g" id="ing-lip" class="form-control" step="0.1" min="0" placeholder="0">
        </div>
      </div>

      <div style="display:flex;gap:20px;margin-bottom:18px;">
        <label class="form-check">
          <input type="checkbox" name="est_allergene" id="ing-allergene" value="1">
          <span>⚠️ Allergène</span>
        </label>
        <label class="form-check">
          <input type="checkbox" name="est_disponible" id="ing-disponible" value="1" checked>
          <span>✅ Disponible</span>
        </label>
      </div>

      <div style="display:flex;gap:10px;justify-content:flex-end;">
        <button type="button" onclick="toggleModal('modal-ing')" class="btn btn-outline">Annuler</button>
        <button type="submit" class="btn btn-primary">💾 Enregistrer</button>
      </div>
    </form>
  </div>
</div>

<!-- ══ MODAL : Ajouter un substitut ══ -->
<div id="modal-sub" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.45);z-index:200;align-items:center;justify-content:center;">
  <div style="background:var(--blanc);border-radius:var(--radius);box-shadow:0 20px 60px rgba(0,0,0,0.3);padding:28px;width:480px;max-width:95vw;">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
      <h2 style="font-family:'Playfair Display',serif;font-size:20px;color:var(--brun-fonce);">Nouveau substitut</h2>
      <button onclick="toggleModal('modal-sub')" style="background:none;border:none;font-size:22px;cursor:pointer;color:var(--texte-leger);">✕</button>
    </div>

    <form method="POST" action="/admin/substituts" novalidate>
      <div class="form-group">
        <label class="form-label">Ingrédient source (allergène / indisponible) *</label>
        <select name="id_ingredient_source" class="form-control">
          <option value="">-- Sélectionner --</option>
          <?php foreach ($ingredients ?? [] as $ing): ?>
            <option value="<?= $ing->id_ingredient ?>"><?= htmlspecialchars($ing->nom) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">Ingrédient de remplacement *</label>
        <select name="id_ingredient_sub" class="form-control">
          <option value="">-- Sélectionner --</option>
          <?php foreach ($ingredients ?? [] as $ing): ?>
            <option value="<?= $ing->id_ingredient ?>"><?= htmlspecialchars($ing->nom) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">Raison de la substitution</label>
        <input type="text" name="raison" class="form-control" placeholder="Ex : Alternative sans lactose">
      </div>
      <div class="form-group">
        <label class="form-label">Ratio de conversion (1 = même quantité)</label>
        <input type="number" name="ratio_conversion" class="form-control" value="1" step="0.01" min="0.01">
      </div>
      <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:10px;">
        <button type="button" onclick="toggleModal('modal-sub')" class="btn btn-outline">Annuler</button>
        <button type="submit" class="btn btn-primary">💾 Ajouter</button>
      </div>
    </form>
  </div>
</div>

<script>
function toggleModal(id) {
  const m = document.getElementById(id);
  if (m.style.display === 'none' || !m.style.display) {
    m.style.display = 'flex';
  } else {
    m.style.display = 'none';
  }
}

function switchTab(tab) {
  document.getElementById('tab-ing').style.display = tab === 'ing' ? 'block' : 'none';
  document.getElementById('tab-sub').style.display = tab === 'sub' ? 'block' : 'none';
  document.getElementById('tab-ing-btn').style.borderBottomColor = tab === 'ing' ? 'var(--brun-chaud)' : 'transparent';
  document.getElementById('tab-sub-btn').style.borderBottomColor = tab === 'sub' ? 'var(--brun-chaud)' : 'transparent';
  document.getElementById('tab-ing-btn').style.color = tab === 'ing' ? 'var(--brun-fonce)' : 'var(--texte-leger)';
  document.getElementById('tab-sub-btn').style.color = tab === 'sub' ? 'var(--brun-fonce)' : 'var(--texte-leger)';
}

function editIngredient(ing) {
  document.getElementById('modal-ing-title').textContent = 'Modifier : ' + ing.nom;
  document.getElementById('ing-method').value = 'PUT';
  document.getElementById('ing-id').value = ing.id_ingredient;
  document.getElementById('form-ingredient').action = '/admin/ingredients/' + ing.id_ingredient;
  document.getElementById('ing-nom').value = ing.nom || '';
  document.getElementById('ing-cal').value = ing.calories_100g || '';
  document.getElementById('ing-prot').value = ing.proteines_100g || '';
  document.getElementById('ing-gluc').value = ing.glucides_100g || '';
  document.getElementById('ing-lip').value = ing.lipides_100g || '';
  document.getElementById('ing-allergene').checked = !!ing.est_allergene;
  document.getElementById('ing-disponible').checked = !!ing.est_disponible;
  toggleModal('modal-ing');
}

// Fermer modales en cliquant à l'extérieur
document.querySelectorAll('[id^="modal-"]').forEach(m => {
  m.addEventListener('click', function(e) {
    if (e.target === this) toggleModal(this.id);
  });
});
</script>

<?php include __DIR__ . '/../../layout/footer.php'; ?>
