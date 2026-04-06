<?php
/**
 * FoodWise — Module RecipeBook
 * Vue : Formulaire Ajouter / Modifier une recette
 * views/recipebook/front/form.php  (partagé front + back)
 *
 * Variables transmises par RecipeController::create() / edit($id) :
 *   $recette          object|null   null pour création, objet pour modification
 *   $ingredients_dispo array        Liste de tous les ingrédients disponibles
 *   $erreurs          array         Erreurs de validation JavaScript/PHP
 *   $is_backoffice    bool          true si accès via /admin
 */

$is_backoffice = $is_backoffice ?? false;
$estModif      = !empty($recette->id_recette ?? null);
$pageTitle     = $estModif ? 'Modifier ' . ($recette->nom ?? '') : 'Nouvelle recette';
$activeNav     = $is_backoffice ? 'gestion_recettes' : 'recettes';
$backoffice    = $is_backoffice;
include __DIR__ . '/../../layout/header.php';


$action = $estModif
  ? ($is_backoffice ? '/admin/recettes/' . $recette->id_recette . '/modifier' : '/recettes/' . $recette->id_recette . '/modifier')
  : ($is_backoffice ? '/admin/recettes/ajouter' : '/recettes/ajouter');
?>

<!-- Fil d'Ariane -->
<nav style="font-size:13px;color:var(--texte-leger);margin-bottom:16px;">
  <a href="<?= $backoffice ? '/admin/recettes' : '/recettes' ?>" style="color:var(--brun-chaud);text-decoration:none;">
    <?= $backoffice ? 'Gestion des recettes' : 'Mes Recettes' ?>
  </a>
  <span style="margin:0 6px;">›</span>
  <span><?= $pageTitle ?></span>
</nav>

<h1 class="page-title"><?= $estModif ? '✏️ Modifier la recette' : '➕ Nouvelle recette' ?></h1>
<p class="page-subtitle"><?= $estModif ? 'Mettez à jour les informations de la recette.' : 'Remplissez le formulaire pour ajouter une nouvelle recette.' ?></p>

<!-- Affichage des erreurs de validation -->
<?php if (!empty($erreurs)): ?>
<div style="background:#FADBD8;border:1.5px solid var(--alerte-rouge);border-radius:var(--radius);padding:14px 18px;margin-bottom:20px;">
  <strong style="color:var(--alerte-rouge);">⚠️ Erreurs de validation :</strong>
  <ul style="margin:6px 0 0 18px;font-size:14px;color:#922B21;">
    <?php foreach ($erreurs as $err): ?>
      <li><?= htmlspecialchars($err) ?></li>
    <?php endforeach; ?>
  </ul>
</div>
<?php endif; ?>

<form method="POST" action="<?= $action ?>" id="recipe-form" enctype="multipart/form-data" novalidate>
  <?php if ($estModif): ?>
    <input type="hidden" name="_method" value="PUT">
  <?php endif; ?>

<div style="display:grid;grid-template-columns:1fr 360px;gap:28px;align-items:start;">

  <!-- ══ Colonne principale ══ -->
  <div>

    <!-- Informations de base -->
    <div class="card">
      <div class="card-header"><h2 class="card-title">📋 Informations générales</h2></div>

      <div class="form-group">
        <label class="form-label" for="nom">Nom de la recette *</label>
        <input type="text" id="nom" name="nom" class="form-control"
               value="<?= htmlspecialchars($recette->nom ?? '') ?>"
               placeholder="Ex : Pâtes au Pesto d'Avocat" required>
      </div>

      <div class="form-group">
        <label class="form-label" for="description">Description</label>
        <textarea id="description" name="description" class="form-control"
                  placeholder="Décrivez brièvement cette recette..."><?= htmlspecialchars($recette->description ?? '') ?></textarea>
      </div>

      <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;">
        <div class="form-group">
          <label class="form-label" for="temps_prep">Temps de prépa. (min) *</label>
          <input type="number" id="temps_prep" name="temps_prep" class="form-control"
                 value="<?= $recette->temps_prep ?? '' ?>" min="1" max="600" placeholder="15">
        </div>
        <div class="form-group">
          <label class="form-label" for="temps_cuisson">Temps de cuisson (min)</label>
          <input type="number" id="temps_cuisson" name="temps_cuisson" class="form-control"
                 value="<?= $recette->temps_cuisson ?? '' ?>" min="0" max="600" placeholder="0">
        </div>
        <div class="form-group">
          <label class="form-label" for="portions">Nombre de portions *</label>
          <input type="number" id="portions" name="portions" class="form-control"
                 value="<?= $recette->portions ?? '' ?>" min="1" max="50" placeholder="4">
        </div>
      </div>

      <div class="form-group">
        <label class="form-label" for="niveau_difficulte">Niveau de difficulté *</label>
        <select id="niveau_difficulte" name="niveau_difficulte" class="form-control">
          <option value="">-- Sélectionner --</option>
          <?php foreach (['facile' => '⭐ Facile', 'moyen' => '⭐⭐ Moyen', 'difficile' => '⭐⭐⭐ Difficile'] as $val => $label): ?>
            <option value="<?= $val ?>" <?= ($recette->niveau_difficulte ?? '') === $val ? 'selected' : '' ?>>
              <?= $label ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- Image -->
      <div class="form-group">
        <label class="form-label" for="image">Image de la recette</label>
        <?php if (!empty($recette->image_url)): ?>
          <div style="margin-bottom:10px;">
            <img src="<?= htmlspecialchars($recette->image_url) ?>" alt="Image actuelle"
                 style="width:140px;height:90px;object-fit:cover;border-radius:8px;">
          </div>
        <?php endif; ?>
        <input type="file" id="image" name="image" class="form-control"
               accept="image/jpeg,image/png,image/webp">
        <div style="font-size:12px;color:var(--texte-leger);margin-top:4px;">JPG, PNG ou WebP — max 2 Mo</div>
      </div>
    </div>

    <!-- Régimes alimentaires -->
    <div class="card">
      <div class="card-header"><h2 class="card-title">🥗 Régimes alimentaires</h2></div>
      <div style="display:flex;flex-wrap:wrap;gap:20px;">
        <label class="form-check">
          <input type="checkbox" name="est_vegetarien" value="1"
                 <?= !empty($recette->est_vegetarien) ? 'checked' : '' ?>>
          <span>🥦 Végétarien</span>
        </label>
        <label class="form-check">
          <input type="checkbox" name="est_vegan" value="1"
                 <?= !empty($recette->est_vegan) ? 'checked' : '' ?>>
          <span>🌱 Vegan</span>
        </label>
        <label class="form-check">
          <input type="checkbox" name="est_sans_gluten" value="1"
                 <?= !empty($recette->est_sans_gluten) ? 'checked' : '' ?>>
          <span>🌾 Sans gluten</span>
        </label>
      </div>
    </div>

    <!-- Ingrédients dynamiques -->
    <div class="card">
      <div class="card-header">
        <h2 class="card-title">🧺 Ingrédients</h2>
        <button type="button" class="btn btn-secondary btn-sm" onclick="ajouterIngredient()">
          + Ajouter un ingrédient
        </button>
      </div>

      <div id="ingredients-container">
        <?php
          $lignes = !empty($recette_ingredients) ? $recette_ingredients : [null];
          foreach ($lignes as $i => $ri):
        ?>
        <div class="ingredient-row" style="display:grid;grid-template-columns:2fr 1fr 1fr auto;gap:10px;align-items:end;margin-bottom:12px;"
             id="ing-row-<?= $i ?>">
          <div class="form-group" style="margin-bottom:0;">
            <label class="form-label" style="font-size:11px;">Ingrédient</label>
            <select name="ingredients[<?= $i ?>][id_ingredient]" class="form-control ingredient-select">
              <option value="">-- Choisir --</option>
              <?php foreach ($ingredients_dispo as $ing): ?>
                <option value="<?= $ing->id_ingredient ?>"
                  <?= ($ri->id_ingredient ?? null) == $ing->id_ingredient ? 'selected' : '' ?>
                  data-allergene="<?= $ing->est_allergene ? '1' : '0' ?>"
                  data-disponible="<?= $ing->est_disponible ? '1' : '0' ?>">
                  <?= htmlspecialchars($ing->nom) ?>
                  <?= $ing->est_allergene ? ' ⚠️' : '' ?>
                  <?= !$ing->est_disponible ? ' ❌' : '' ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group" style="margin-bottom:0;">
            <label class="form-label" style="font-size:11px;">Quantité</label>
            <input type="number" name="ingredients[<?= $i ?>][quantite]" class="form-control"
                   value="<?= $ri->quantite ?? '' ?>" step="0.01" min="0.01" placeholder="100">
          </div>
          <div class="form-group" style="margin-bottom:0;">
            <label class="form-label" style="font-size:11px;">Unité</label>
            <select name="ingredients[<?= $i ?>][unite]" class="form-control">
              <?php foreach (['g','kg','ml','L','tsp','tbsp','unité','pincée','feuille','tranche'] as $u): ?>
                <option value="<?= $u ?>" <?= ($ri->unite ?? '') === $u ? 'selected' : '' ?>><?= $u ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <button type="button" onclick="supprimerIngredient(<?= $i ?>)"
                  style="background:none;border:1.5px solid var(--alerte-rouge);color:var(--alerte-rouge);border-radius:50%;width:32px;height:32px;cursor:pointer;font-size:14px;line-height:1;margin-bottom:0;">
            ✕
          </button>
        </div>
        <?php endforeach; ?>
      </div>

      <div id="allergene-warning" style="display:none;" class="substitut-alert" style="margin-top:10px;">
        ⚠️ Certains ingrédients sélectionnés sont des allergènes ou indisponibles. Des substituts seront proposés automatiquement.
      </div>
    </div>

  </div><!-- /colonne principale -->

  <!-- ══ Colonne latérale ══ -->
  <div>
    <div class="card" style="position:sticky;top:80px;">
      <div class="card-header"><h2 class="card-title">💾 Enregistrement</h2></div>

      <p style="font-size:13px;color:var(--texte-leger);margin-bottom:18px;">
        Les valeurs nutritionnelles seront <strong>calculées automatiquement</strong> à partir des ingrédients renseignés.
      </p>

      <div style="display:flex;flex-direction:column;gap:10px;">
        <button type="submit" name="action" value="publier" class="btn btn-primary" style="justify-content:center;">
          ✅ <?= $estModif ? 'Enregistrer les modifications' : 'Publier la recette' ?>
        </button>
        <button type="submit" name="action" value="brouillon" class="btn btn-outline" style="justify-content:center;">
          📄 Sauvegarder en brouillon
        </button>
        <a href="<?= $backoffice ? '/admin/recettes' : '/recettes' ?>" class="btn" style="justify-content:center;background:none;color:var(--texte-leger);">
          ✕ Annuler
        </a>
      </div>

      <?php if ($estModif): ?>
      <div style="margin-top:20px;padding-top:16px;border-top:1px solid var(--creme-fonce);">
        <p style="font-size:12px;color:var(--texte-leger);margin-bottom:8px;">
          Recette créée le <?= date('d/m/Y', strtotime($recette->date_creation)) ?>
        </p>
      </div>
      <?php endif; ?>
    </div>
  </div>

</div><!-- /grid -->
</form>

<script>
let rowCount = <?= count($lignes ?? [1]) ?>;

function ajouterIngredient() {
  const i = rowCount++;
  const ingOptions = document.querySelector('.ingredient-select').innerHTML;
  const row = document.createElement('div');
  row.className = 'ingredient-row';
  row.id = 'ing-row-' + i;
  row.style.cssText = 'display:grid;grid-template-columns:2fr 1fr 1fr auto;gap:10px;align-items:end;margin-bottom:12px;';
  row.innerHTML = `
    <div class="form-group" style="margin-bottom:0;">
      <label class="form-label" style="font-size:11px;">Ingrédient</label>
      <select name="ingredients[${i}][id_ingredient]" class="form-control ingredient-select" onchange="checkAllergenes()">
        <option value="">-- Choisir --</option>${ingOptions}
      </select>
    </div>
    <div class="form-group" style="margin-bottom:0;">
      <label class="form-label" style="font-size:11px;">Quantité</label>
      <input type="number" name="ingredients[${i}][quantite]" class="form-control" step="0.01" min="0.01" placeholder="100">
    </div>
    <div class="form-group" style="margin-bottom:0;">
      <label class="form-label" style="font-size:11px;">Unité</label>
      <select name="ingredients[${i}][unite]" class="form-control">
        ${['g','kg','ml','L','tsp','tbsp','unité','pincée','feuille','tranche'].map(u=>`<option value="${u}">${u}</option>`).join('')}
      </select>
    </div>
    <button type="button" onclick="supprimerIngredient(${i})"
            style="background:none;border:1.5px solid var(--alerte-rouge);color:var(--alerte-rouge);border-radius:50%;width:32px;height:32px;cursor:pointer;font-size:14px;line-height:1;margin-bottom:0;">✕</button>
  `;
  document.getElementById('ingredients-container').appendChild(row);
}

function supprimerIngredient(i) {
  const row = document.getElementById('ing-row-' + i);
  if (row && document.querySelectorAll('.ingredient-row').length > 1) {
    row.remove();
    checkAllergenes();
  }
}

function checkAllergenes() {
  const selects = document.querySelectorAll('.ingredient-select');
  let hasWarning = false;
  selects.forEach(sel => {
    const opt = sel.options[sel.selectedIndex];
    if (opt && (opt.dataset.allergene === '1' || opt.dataset.disponible === '0')) {
      hasWarning = true;
    }
  });
  document.getElementById('allergene-warning').style.display = hasWarning ? 'flex' : 'none';
}

// Validation JS côté client (pas de validation HTML5 comme demandé)
document.getElementById('recipe-form').addEventListener('submit', function(e) {
  const erreurs = [];
  const nom = document.getElementById('nom').value.trim();
  const temps = document.getElementById('temps_prep').value;
  const portions = document.getElementById('portions').value;
  const diff = document.getElementById('niveau_difficulte').value;

  if (!nom) erreurs.push('Le nom de la recette est obligatoire.');
  if (!temps || temps < 1) erreurs.push('Le temps de préparation doit être supérieur à 0.');
  if (!portions || portions < 1) erreurs.push('Le nombre de portions doit être au moins 1.');
  if (!diff) erreurs.push('Veuillez choisir un niveau de difficulté.');

  const ingredients = document.querySelectorAll('.ingredient-select');
  let hasIng = false;
  ingredients.forEach(sel => { if (sel.value) hasIng = true; });
  if (!hasIng) erreurs.push('Veuillez ajouter au moins un ingrédient.');

  if (erreurs.length > 0) {
    e.preventDefault();
    let div = document.getElementById('form-errors');
    if (!div) {
      div = document.createElement('div');
      div.id = 'form-errors';
      div.style.cssText = 'background:#FADBD8;border:1.5px solid var(--alerte-rouge);border-radius:var(--radius);padding:14px 18px;margin-bottom:20px;';
      document.querySelector('.page-subtitle').after(div);
    }
    div.innerHTML = '<strong style="color:var(--alerte-rouge);">⚠️ Erreurs :</strong><ul style="margin:6px 0 0 18px;font-size:14px;color:#922B21;">'
      + erreurs.map(e => `<li>${e}</li>`).join('') + '</ul>';
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }
});

// Initialisation
document.querySelectorAll('.ingredient-select').forEach(s => s.addEventListener('change', checkAllergenes));
checkAllergenes();
</script>

<?php include __DIR__ . '/../../layout/footer.php'; ?>
