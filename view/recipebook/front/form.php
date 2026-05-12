<?php
/**
 * FoodWise — Module RecipeBook
 * Vue : Formulaire Ajouter / Modifier une recette
 */

// --- 1. FONCTION HELPER POUR LES ERREURS ---
function erreur(array $erreurs, string $champ): void {
    if (!empty($erreurs[$champ])) {
        echo '<p style="color:#C0392B; font-size:12px; margin-top:4px; font-weight:bold;">⚠ '
             . htmlspecialchars($erreurs[$champ]) . '</p>';
    }
}

$is_backoffice = $is_backoffice ?? false;
$estModif      = !empty($recette->id_recette ?? null);
$pageTitle     = $estModif ? 'Modifier ' . ($recette->nom ?? '') : 'Nouvelle recette';
$activeNav     = $is_backoffice ? 'gestion_recettes' : 'recettes';
$backoffice    = $is_backoffice;
$erreurs       = $erreurs ?? []; // Sécurité si la variable n'existe pas

include __DIR__ . '/layout/header.php';

$action = $estModif
  ? ($is_backoffice ? '?route=admin/recettes/' . $recette->id_recette . '/modifier' : '?route=recettes/' . $recette->id_recette . '/modifier')
  : ($is_backoffice ? '?route=admin/recettes/ajouter' : '?route=recettes/ajouter');
?>

<style>
    .input-erreur {
        border-color: #C0392B !important;
        background: #FEF0EE !important;
    }
    .input-erreur:focus {
        border-color: #C0392B !important;
        box-shadow: 0 0 0 3px rgba(192,57,43,0.15) !important;
    }
</style>

<nav style="font-size:13px;color:var(--texte-leger);margin-bottom:16px;">
  <a href="<?= $backoffice ? '?route=admin/recettes' : '?route=recettes' ?>" style="color:var(--brun-chaud);text-decoration:none;">
    <?= $backoffice ? 'Gestion des recettes' : 'Mes Recettes' ?>
  </a>
  <span style="margin:0 6px;">›</span>
  <span><?= $pageTitle ?></span>
</nav>

<h1 class="page-title"><?= $estModif ? '✏️ Modifier la recette' : '➕ Nouvelle recette' ?></h1>
<p class="page-subtitle"><?= $estModif ? 'Mettez à jour les informations de la recette.' : 'Remplissez le formulaire pour ajouter une nouvelle recette.' ?></p>

<form method="POST" action="<?= $action ?>" id="recipe-form" enctype="multipart/form-data" novalidate>
  <?php if ($estModif): ?>
    <input type="hidden" name="_method" value="PUT">
  <?php endif; ?>

<div style="display:grid;grid-template-columns:1fr 360px;gap:28px;align-items:start;">

  <div>
    <div class="card">
      <div class="card-header"><h2 class="card-title"> Informations générales</h2></div>

      <div class="form-group">
        <label class="form-label" for="nom">Nom de la recette *</label>
        <input type="text" id="nom" name="nom" 
               class="form-control <?= !empty($erreurs['nom']) ? 'input-erreur' : '' ?>"
               value="<?= htmlspecialchars($recette->nom ?? '') ?>"
               placeholder="Ex : Pâtes au Pesto d'Avocat">
        <?php erreur($erreurs, 'nom'); ?>
      </div>

      <div class="form-group">
        <label class="form-label" for="description">Description</label>
        <textarea id="description" name="description" class="form-control"
                  placeholder="Décrivez brièvement cette recette..."><?= htmlspecialchars($recette->description ?? '') ?></textarea>
      </div>

      <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;">
        <div class="form-group">
          <label class="form-label" for="temps_prep">Temps de prépa. (min) *</label>
          <input type="number" id="temps_prep" name="temps_prep" 
                 class="form-control <?= !empty($erreurs['temps_prep']) ? 'input-erreur' : '' ?>"
                 value="<?= $recette->temps_prep ?? '' ?>" placeholder="15">
          <?php erreur($erreurs, 'temps_prep'); ?>
        </div>
        
        <div class="form-group">
          <label class="form-label" for="temps_cuisson">Temps de cuisson (min)</label>
          <input type="number" id="temps_cuisson" name="temps_cuisson" class="form-control"
                 value="<?= $recette->temps_cuisson ?? '' ?>" placeholder="0">
        </div>

        <div class="form-group">
          <label class="form-label" for="portions">Nombre de portions *</label>
          <input type="number" id="portions" name="portions" 
                 class="form-control <?= !empty($erreurs['portions']) ? 'input-erreur' : '' ?>"
                 value="<?= $recette->portions ?? '' ?>" placeholder="4">
          <?php erreur($erreurs, 'portions'); ?>
        </div>
      </div>

      <div class="form-group">
        <label class="form-label" for="niveau_difficulte">Niveau de difficulté *</label>
        <select id="niveau_difficulte" name="niveau_difficulte" 
                class="form-control <?= !empty($erreurs['niveau_difficulte']) ? 'input-erreur' : '' ?>">
          <option value="">-- Sélectionner --</option>
          <?php foreach (['facile' => '⭐ Facile', 'moyen' => '⭐⭐ Moyen', 'difficile' => '⭐⭐⭐ Difficile'] as $val => $label): ?>
            <option value="<?= $val ?>" <?= ($recette->niveau_difficulte ?? '') === $val ? 'selected' : '' ?>>
              <?= $label ?>
            </option>
          <?php endforeach; ?>
        </select>
        <?php erreur($erreurs, 'niveau_difficulte'); ?>
      </div>

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
        <div class="ingredient-row" style="display:grid;grid-template-columns:2fr 1fr 1fr auto;gap:10px;align-items:end;margin-bottom:12px;" id="ing-row-<?= $i ?>">
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
                  <?= $ing->est_allergene ? ' ⚠' : '' ?>
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
        ⚠ Certains ingrédients sélectionnés sont des allergènes ou indisponibles. Des substituts seront proposés automatiquement.
      </div>
    </div>
  </div>

  <div>
    <div class="card" style="position:sticky;top:80px;">
      <div class="card-header"><h2 class="card-title">💾 Enregistrement</h2></div>
      <p style="font-size:13px;color:var(--texte-leger);margin-bottom:18px;">
        Les valeurs nutritionnelles seront <strong>calculées automatiquement</strong> à partir des ingrédients renseignés.
      </p>
      <div style="display:flex;flex-direction:column;gap:10px;">
        <button type="submit" name="action" value="publier" class="btn btn-primary" style="justify-content:center;">
           <?= $estModif ? 'Enregistrer les modifications' : 'Publier la recette' ?>
        </button>
        
        <a href="<?= $backoffice ? '?route=admin/recettes' : '?route=recettes' ?>" class="btn" style="justify-content:center;background:none;color:var(--texte-leger);">
          ✕ Annuler
        </a>
      </div>
    </div>
  </div>
</div>
</form>

<script>
let rowCount = document.querySelectorAll('.ingredient-row').length;
function ajouterIngredient() {
    const i = document.querySelectorAll('.ingredient-row').length; 
    
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

document.getElementById('recipe-form').addEventListener('submit', function(e) {

  document.querySelectorAll('.input-erreur').forEach(el => el.classList.remove('input-erreur'));
  document.querySelectorAll('.erreur-msg').forEach(el => el.remove());

  const erreurs = [];
  const nom = document.getElementById('nom').value.trim();
  const temps = document.getElementById('temps_prep').value;
  const portions = document.getElementById('portions').value;
  const diff = document.getElementById('niveau_difficulte').value;


  function afficherErreur(idChamp, message) {
    if (idChamp === 'ingredients') {
      const select = document.querySelector('.ingredient-row .ingredient-select');
      if (select) {
        select.classList.add('input-erreur');
        const p = document.createElement('p');
        p.className = 'erreur-msg';
        p.style.cssText = 'color:#C0392B; font-size:12px; margin-top:4px; font-weight:bold;';
        p.innerHTML = '⚠ ' + message;
        const wrapper = select.closest('.form-group') || select.parentNode;
        wrapper.appendChild(p);
      }
      return;
    }

    const input = document.getElementById(idChamp);
    input.classList.add('input-erreur');
    const p = document.createElement('p');
    p.className = 'erreur-msg';
    p.style.cssText = 'color:#C0392B; font-size:12px; margin-top:4px; font-weight:bold;';
    p.innerHTML = '⚠ ' + message;
    input.parentNode.appendChild(p);
  }

  function afficherErreurInput(input, message) {
    input.classList.add('input-erreur');
    if (input.name && input.name.endsWith('[quantite]')) {
      input.setAttribute('title', message);
    } else {
      const p = document.createElement('p');
      p.className = 'erreur-msg';
      p.style.cssText = 'color:#C0392B; font-size:12px; margin-top:4px; font-weight:bold;';
      p.innerHTML = '⚠' + message;
      const wrapper = input.closest('.form-group') || input.parentNode;
      wrapper.appendChild(p);
    }
  }

  function validerIngredients() {
    const ingredientRows = document.querySelectorAll('.ingredient-row');
    let hasIngredient = false;
    ingredientRows.forEach(row => {
      const ingredientSelect = row.querySelector('.ingredient-select');
      const quantiteInput = row.querySelector('input[name$="[quantite]"]');
      if (!ingredientSelect || !quantiteInput) {
        return;
      }

      const selectedIngredient = ingredientSelect.value.trim();
      const quantiteValeur = quantiteInput.value.trim();
      if (selectedIngredient) {
        hasIngredient = true;
      }
      if (selectedIngredient && (!quantiteValeur || Number(quantiteValeur) <= 0)) {
        erreurs.push({champ: quantiteInput, msg: 'La quantité de l\'ingrédient est requise et doit être supérieure à 0.'});
      } else {
        quantiteInput.removeAttribute('title');
      }
    });

    if (!hasIngredient) {
      erreurs.push({champ: 'ingredients', msg: 'Ajoutez au moins un ingrédient à la recette.'});
    }
  }

  if (!nom) {
    erreurs.push({champ: 'nom', msg: 'Le nom est obligatoire.'});
  }
  if (!temps || temps < 1) {
    erreurs.push({champ: 'temps_prep', msg: 'Le temps doit être > 0.'});
  }
  if (!portions || portions < 1) {
    erreurs.push({champ: 'portions', msg: 'Au moins 1 portion.'});
  }
  if (!diff) {
    erreurs.push({champ: 'niveau_difficulte', msg: 'Niveau requis.'});
  }

  validerIngredients();

  if (erreurs.length > 0) {
    e.preventDefault();
    erreurs.forEach(err => {
      if (typeof err.champ === 'string') {
        afficherErreur(err.champ, err.msg);
      } else {
        afficherErreurInput(err.champ, err.msg);
      }
    });
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }
});

// Initialisation
document.querySelectorAll('.ingredient-select').forEach(s => s.addEventListener('change', checkAllergenes));
checkAllergenes();
</script>
