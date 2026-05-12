<?php
/**
 * FoodWise — Vue : Formulaire Ingrédient (Back Office)
 * view/recipebook/back/ingredient_form.php
 *
 * Variables transmises par IngredientController :
 *   $ingredient   object|null
 *   $categories   array
 *   $unites       array
 *   $erreurs      array  ['champ' => 'message']  (erreurs PHP côté serveur)
 *   $backoffice   bool   true
 */

$estModif   = !empty($ingredient->id_ingredient ?? null);
$pageTitle  = $estModif ? 'Modifier : ' . ($ingredient->nom ?? '') : 'Nouvel ingrédient';
$activeNav  = 'gestion_ingredients';
$backoffice = true;
$erreurs    = $erreurs ?? [];

/*$categories = ['Légumes', 'Fruits', 'Céréales', 'Viandes', 'Produits laitiers']; // Ou requête BDD
$unites = ['g', 'kg', 'ml', 'L', 'unité', 'pincée'];*/

include __DIR__ . '/layout/header.php';

$action = $estModif
    ? 'index.php?route=admin/ingredients/' . $ingredient->id_ingredient . '/modifier'
    : 'index.php?route=admin/ingredients/ajouter';

/* ── Helpers ── */
function erreurChamp(array $e, string $c): void {
    if (!empty($e[$c])) {
        echo '<p class="err-msg" style="color:#C0392B;font-size:12px;margin:4px 0 0;display:flex;align-items:center;gap:4px;">'
           . '<span>⚠</span>' . htmlspecialchars($e[$c]) . '</p>';
    }
}
function classeErreur(array $e, string $c): string {
    return !empty($e[$c]) ? 'form-control input-erreur' : 'form-control';
}
?>

<style>
.input-erreur {
    border-color: #C0392B !important;
    background:   #FEF0EE !important;
}
.input-erreur:focus {
    border-color: #C0392B !important;
    box-shadow: 0 0 0 3px rgba(192,57,43,0.10) !important;
}
/* Compteur de caractères */
.char-count { font-size:11px; color:var(--texte-leger); text-align:right; margin-top:3px; }
.char-count.warn { color:var(--alerte-orange); }
.char-count.over { color:#C0392B; font-weight:700; }
/* Barre macros live */
.macro-bar { height:8px; border-radius:4px; background:var(--creme-fonce); overflow:hidden; margin-top:8px; }
.macro-bar-fill { height:100%; border-radius:4px; background:var(--vert-moyen); transition:width 0.3s; }
.macro-bar-fill.warn { background:var(--alerte-orange); }
.macro-bar-fill.over { background:#C0392B; }
</style>

<!-- Fil d'Ariane -->
<nav style="font-size:13px;color:var(--texte-leger);margin-bottom:16px;">
  <a href="index.php?route=admin" style="color:var(--brun-chaud);text-decoration:none;">Tableau de bord</a>
  <span style="margin:0 6px;">›</span>
  <a href="index.php?route=admin_ingredients" style="color:var(--brun-chaud);text-decoration:none;">Base Ingrédients</a>
  <span style="margin:0 6px;">›</span>
  <span><?= htmlspecialchars($pageTitle) ?></span>
</nav>

<h1 class="page-title"><?= $estModif ? '✏️ Modifier l\'ingrédient' : '➕ Nouvel ingrédient' ?></h1>
<p class="page-subtitle">
  <?= $estModif ? 'Mise à jour des données nutritionnelles et métadonnées.' : 'Remplissez les informations de ce nouvel ingrédient.' ?>
</p>

<!-- Bandeau erreurs PHP -->
<?php if (!empty($erreurs)): ?>
<div style="background:#FEF0EE;border:1.5px solid #C0392B;border-radius:var(--radius);
            padding:12px 16px;margin-bottom:20px;display:flex;align-items:center;gap:10px;">
  <span style="font-size:18px;">⚠️</span>
  <span style="font-size:14px;color:#922B21;">
    <?= count($erreurs) ?> erreur<?= count($erreurs) > 1 ? 's' : '' ?> à corriger ci-dessous.
  </span>
</div>
<?php endif; ?>

<!-- Erreur globale (ex: erreur BDD) -->
<?php if (!empty($erreurs['global'])): ?>
<div style="background:#FADBD8;border:1.5px solid #C0392B;border-radius:var(--radius);padding:12px 16px;margin-bottom:16px;font-size:14px;color:#922B21;">
  <?= htmlspecialchars($erreurs['global']) ?>
</div>
<?php endif; ?>

<form method="POST" action="<?= $action ?>" id="ingredient-form" novalidate>
  <?php if ($estModif): ?>
    <input type="hidden" name="_method" value="PUT">
  <?php endif; ?>

<div style="display:grid;grid-template-columns:1fr 300px;gap:24px;align-items:start;">

  <!-- ══ Colonne principale ══ -->
  <div>

    <form method="POST" action="<?= $action ?>" id="ingredient-form" novalidate>
      <?php if ($estModif): ?>
        <input type="hidden" name="_method" value="PUT">
      <?php endif; ?>

    <!-- Identification -->
    <div class="card">
      <div class="card-header"><h2 class="card-title">🏷️ Identification</h2></div>

      <!-- Nom -->
      <div class="form-group">
        <label class="form-label" for="nom">Nom de l'ingrédient *</label>
        <input type="text"
               id="nom" name="nom"
               class="<?= classeErreur($erreurs, 'nom') ?>"
               value="<?= htmlspecialchars($ingredient->nom ?? '') ?>"
               placeholder="Ex : Lait d'amande"
               maxlength="100">
        <div class="char-count" id="nom-count">0 / 100</div>
        <?php erreurChamp($erreurs, 'nom'); ?>
      </div>

      <!-- Catégorie + Unité -->
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">

        <div class="form-group">
          <label class="form-label" for="categorie">Catégorie</label>
          <select id="categorie" name="categorie"
                  class="<?= classeErreur($erreurs, 'categorie') ?>">
            <option value="">-- Choisir --</option>
            <?php foreach ($categories as $cat): ?>
              <option value="<?= $cat ?>"
                <?= ($ingredient->categorie ?? '') === $cat ? 'selected' : '' ?>>
                <?= $cat ?>
              </option>
            <?php endforeach; ?>
          </select>
          <?php erreurChamp($erreurs, 'categorie'); ?>
        </div>

        <div class="form-group">
          <label class="form-label" for="unite_defaut">Unité par défaut *</label>
          <select id="unite_defaut" name="unite_defaut"
                  class="<?= classeErreur($erreurs, 'unite_defaut') ?>">
            <?php foreach ($unites as $u): ?>
              <option value="<?= $u ?>"
                <?= ($ingredient->unite_defaut ?? 'g') === $u ? 'selected' : '' ?>>
                <?= $u ?>
              </option>
            <?php endforeach; ?>
          </select>
          <?php erreurChamp($erreurs, 'unite_defaut'); ?>
        </div>

      </div>

      <!-- Statuts -->
      <div style="display:flex;gap:28px;flex-wrap:wrap;">
        <label class="form-check">
          <input type="checkbox" id="est_allergene" name="est_allergene" value="1"
                 <?= !empty($ingredient->est_allergene) ? 'checked' : '' ?>>
          <span>⚠️ Allergène connu</span>
        </label>
      </div>

    </div><!-- /card identification -->

    <!-- Valeurs nutritionnelles -->
    <div class="card">
      <div class="card-header">
        <h2 class="card-title">🔬 Valeurs nutritionnelles</h2>
        <span style="font-size:12px;color:var(--texte-leger);">Pour 100g / 100ml</span>
      </div>

      <!-- Barre macros dynamique -->
      <div style="background:var(--creme-fonce);border-radius:8px;padding:12px 14px;margin-bottom:18px;">
        <div style="display:flex;justify-content:space-between;font-size:12px;color:var(--texte-leger);margin-bottom:6px;">
          <span>Répartition des macronutriments</span>
          <span id="macro-total-label">0 / 100g</span>
        </div>
        <div class="macro-bar">
          <div class="macro-bar-fill" id="macro-bar-fill" style="width:0%"></div>
        </div>
        <div style="display:flex;gap:14px;font-size:11px;margin-top:6px;color:var(--texte-leger);">
          <span><span style="color:var(--vert-moyen);font-weight:700;">■</span> Prot. <span id="lb-prot">0</span>g</span>
          <span><span style="color:var(--brun-clair);font-weight:700;">■</span> Gluc. <span id="lb-gluc">0</span>g</span>
          <span><span style="color:#E67E22;font-weight:700;">■</span> Lip. <span id="lb-lip">0</span>g</span>
        </div>
        <div id="macro-warning" style="display:none;margin-top:8px;font-size:12px;color:#C0392B;font-weight:500;">
          ⚠ La somme dépasse 100g — vérifiez vos valeurs.
        </div>
      </div>

      <!-- Champs nutritionnels en grille -->
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">

        <!-- Calories -->
        <div class="form-group" style="grid-column:span 2;">
          <label class="form-label" for="calories_100g">Calories (kcal)</label>
          <input type="number"
                 id="calories_100g" name="calories_100g"
                 class="<?= classeErreur($erreurs, 'calories_100g') ?>"
                 value="<?= htmlspecialchars((string)($ingredient->calories_100g ?? '')) ?>"
                 step="0.1" min="0" placeholder="0">
          <div style="font-size:11px;color:var(--texte-leger);margin-top:3px;">
            Apport énergétique total pour 100g (peut dépasser 100)
          </div>
          <?php erreurChamp($erreurs, 'calories_100g'); ?>
        </div>

        <!-- Protéines -->
        <div class="form-group">
          <label class="form-label" for="proteines_100g">Protéines (g)</label>
          <input type="number"
                 id="proteines_100g" name="proteines_100g"
                 class="<?= classeErreur($erreurs, 'proteines_100g') ?>"
                 value="<?= htmlspecialchars((string)($ingredient->proteines_100g ?? '')) ?>"
                 step="0.1" min="0" max="100" placeholder="0"
                 oninput="updateMacroBar()">
          <?php erreurChamp($erreurs, 'proteines_100g'); ?>
        </div>

        <!-- Glucides -->
        <div class="form-group">
          <label class="form-label" for="glucides_100g">Glucides (g)</label>
          <input type="number"
                 id="glucides_100g" name="glucides_100g"
                 class="<?= classeErreur($erreurs, 'glucides_100g') ?>"
                 value="<?= htmlspecialchars((string)($ingredient->glucides_100g ?? '')) ?>"
                 step="0.1" min="0" max="100" placeholder="0"
                 oninput="updateMacroBar()">
          <?php erreurChamp($erreurs, 'glucides_100g'); ?>
        </div>

        <!-- Lipides -->
        <div class="form-group">
          <label class="form-label" for="lipides_100g">Lipides (g)</label>
          <input type="number"
                 id="lipides_100g" name="lipides_100g"
                 class="<?= classeErreur($erreurs, 'lipides_100g') ?>"
                 value="<?= htmlspecialchars((string)($ingredient->lipides_100g ?? '')) ?>"
                 step="0.1" min="0" max="100" placeholder="0"
                 oninput="updateMacroBar()">
          <?php erreurChamp($erreurs, 'lipides_100g'); ?>
        </div>

      </div>
    </div><!-- /card nutrition -->

    </form>

  </div><!-- /colonne principale -->

  <!-- ══ Colonne latérale ══ -->
  <div>
    <div class="card" style="position:sticky;top:80px;">
      <div class="card-header"><h2 class="card-title">⚙️ Actions</h2></div>

      <!-- Infos si modification -->
      <?php if ($estModif): ?>
      <div style="background:var(--creme-fonce);border-radius:8px;padding:11px 13px;margin-bottom:16px;font-size:13px;">
        <div style="display:flex;justify-content:space-between;margin-bottom:5px;">
          <span style="color:var(--texte-leger);">ID</span>
          <span style="font-weight:700;font-family:monospace;">#<?= $ingredient->id_ingredient ?></span>
        </div>
        <div style="display:flex;justify-content:space-between;">
          <span style="color:var(--texte-leger);">Statut</span>
          <span>
            <?= $ingredient->est_disponible
                ? '<span class="badge badge-vert">Disponible</span>'
                : '<span class="badge badge-rouge">Indisponible</span>' ?>
          </span>
        </div>
      </div>
      <?php endif; ?>

      <div style="display:flex;flex-direction:column;gap:10px;">
        <button type="submit" form="ingredient-form" class="btn btn-primary" style="justify-content:center;">
          💾 <?= $estModif ? 'Enregistrer' : 'Créer l\'ingrédient' ?>
        </button>
        <a href="index.php?route=admin_ingredients"
           class="btn btn-outline" style="justify-content:center;">
          ✕ Annuler
        </a>
      </div>

      <!-- Suppression -->
      <?php if ($estModif): ?>
      <div style="margin-top:20px;padding-top:16px;border-top:1px solid var(--creme-fonce);">
        <p style="font-size:12px;color:var(--texte-leger);margin-bottom:10px;">Zone dangereuse</p>
        <form method="POST"
              action="index.php?route=admin/ingredients/<?= $ingredient->id_ingredient ?>/supprimer"
              onsubmit="return confirm('Supprimer «<?= htmlspecialchars(addslashes($ingredient->nom)) ?>» ?\n\nImpossible si cet ingrédient est utilisé dans des recettes.');">
          <input type="hidden" name="_method" value="DELETE">
          <button type="submit" class="btn btn-danger btn-sm" style="width:100%;justify-content:center;">
            🗑 Supprimer
          </button>
        </form>
      </div>
      <?php endif; ?>
    </div>
  </div>

</div><!-- /grid -->
</form>

<script>
/* ──  Compteur de caractères sur le nom ── */
const nomInput  = document.getElementById('nom');
const nomCount  = document.getElementById('nom-count');

function majCompteurNom() {
  const len = nomInput.value.length;
  nomCount.textContent = len + ' / 100';
  nomCount.className = 'char-count' + (len > 90 ? (len >= 100 ? ' over' : ' warn') : '');
}
nomInput.addEventListener('input', majCompteurNom);
majCompteurNom(); // initialisation


/* ──  Barre macros dynamique ── */
function updateMacroBar() {
  const prot = parseFloat(document.getElementById('proteines_100g').value) || 0;
  const gluc = parseFloat(document.getElementById('glucides_100g').value)  || 0;
  const lip  = parseFloat(document.getElementById('lipides_100g').value)   || 0;
  const total = prot + gluc + lip;

  document.getElementById('lb-prot').textContent = prot.toFixed(1);
  document.getElementById('lb-gluc').textContent = gluc.toFixed(1);
  document.getElementById('lb-lip').textContent  = lip.toFixed(1);

  const pct  = Math.min(total, 100);
  const fill = document.getElementById('macro-bar-fill');
  fill.style.width = pct + '%';
  fill.className = 'macro-bar-fill' + (total > 100 ? ' over' : total > 85 ? ' warn' : '');

  document.getElementById('macro-total-label').textContent = total.toFixed(1) + ' / 100g';
  document.getElementById('macro-warning').style.display = total > 100 ? 'block' : 'none';
}
updateMacroBar(); // initialisation


/* ──  Helpers d'affichage d'erreur JS ── */
function afficherErreurJS(champId, message) {
  const field = document.getElementById(champId);
  if (!field) return;

  field.classList.add('input-erreur');

  /* Éviter les doublons */
  const existant = field.parentNode.querySelector('.js-err');
  if (existant) existant.remove();

  const p = document.createElement('p');
  p.className = 'js-err';
  p.style.cssText = 'color:#C0392B;font-size:12px;margin:4px 0 0;display:flex;align-items:center;gap:4px;';
  p.innerHTML = '<span>⚠</span>' + message;
  field.parentNode.insertBefore(p, field.nextSibling);
}

function effacerErreursJS() {
  document.querySelectorAll('.js-err').forEach(el => el.remove());
  document.querySelectorAll('.input-erreur').forEach(el => el.classList.remove('input-erreur'));
}


/* ──  Validation complète au submit ── */
document.getElementById('ingredient-form').addEventListener('submit', function(e) {

  effacerErreursJS();

  let estValide = true;
  let premierChampErreur = null;

  /* ─ Nom obligatoire ─ */
  const nom = document.getElementById('nom').value.trim();
  if (nom === '') {
    afficherErreurJS('nom', 'Le nom de l\'ingrédient est obligatoire.');
    premierChampErreur = premierChampErreur || 'nom';
    estValide = false;
  } else if (nom.length > 100) {
    afficherErreurJS('nom', 'Le nom ne doit pas dépasser 100 caractères.');
    premierChampErreur = premierChampErreur || 'nom';
    estValide = false;
  }


  /* ─ Catégorie obligatoire ─ */
  const cat = document.getElementById('categorie');
  if (cat && cat.value.trim() === '') {
    afficherErreurJS('categorie', 'La catégorie est obligatoire.');
    premierChampErreur = premierChampErreur || 'categorie';
    estValide = false;
  }

  /* ─ Unité de défaut obligatoire ─ */
  const unite = document.getElementById('unite_defaut');
  if (unite && unite.value.trim() === '') {
    afficherErreurJS('unite_defaut', 'Veuillez choisir une unité.');
    premierChampErreur = premierChampErreur || 'unite_defaut';
    estValide = false;
  }

  /* ─ Valeurs nutritionnelles : nombre positif ─ */
  const champsNutri = [
    { id: 'calories_100g',  label: 'Les calories' },
    { id: 'proteines_100g', label: 'Les protéines' },
    { id: 'glucides_100g',  label: 'Les glucides'  },
    { id: 'lipides_100g',   label: 'Les lipides'   },
  ];

  champsNutri.forEach(function(c) {
    const val = document.getElementById(c.id).value;
    // Si le champ est vide
  if (val === '') {
    afficherErreurJS(c.id, c.label + ' sont obligatoires.');
    if (!premierChampErreur) premierChampErreur = c.id;
    estValide = false;
  } 
  // S'il n'est pas vide, on vérifie que c'est un nombre positif
  else if (isNaN(parseFloat(val)) || parseFloat(val) < 0) {
    afficherErreurJS(c.id, c.label + ' doivent être un nombre positif.');
    if (!premierChampErreur) premierChampErreur = c.id;
    estValide = false;
  }
  });

  /* ─ Somme macros ≤ 100g ─ */
  const prot  = parseFloat(document.getElementById('proteines_100g').value) || 0;
  const gluc  = parseFloat(document.getElementById('glucides_100g').value)  || 0;
  const lip   = parseFloat(document.getElementById('lipides_100g').value)   || 0;
  const total = prot + gluc + lip;

  if (total > 100) {
    afficherErreurJS('proteines_100g',
      'La somme protéines + glucides + lipides (' + total.toFixed(1) + 'g) dépasse 100g.'
    );
    premierChampErreur = premierChampErreur || 'proteines_100g';
    estValide = false;
  }

  /* ─ Calories cohérentes (avertissement, pas bloquant) ─ */
  const cal = parseFloat(document.getElementById('calories_100g').value) || 0;
  if (cal > 900) {
    afficherErreurJS('calories_100g',
      'Valeur inhabituelle (' + cal + ' kcal). Vérifiez — aucun aliment ne dépasse ~900 kcal/100g.'
    );
    premierChampErreur = premierChampErreur || 'calories_100g';
    estValide = false;
  }

  /* ─ Blocage si erreurs ─ */
  if (!estValide) {
    e.preventDefault();

    /* Scroller vers la première erreur */
    if (premierChampErreur) {
      const el = document.getElementById(premierChampErreur);
      if (el) el.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
  }

});


/* ──  Validation à la saisie (feedback immédiat) ── */

/* Nom : vérif en temps réel */
nomInput.addEventListener('blur', function() {
  const n = this.value.trim();
  const errEl = this.parentNode.querySelector('.js-err');
  if (errEl) errEl.remove();
  this.classList.remove('input-erreur');

  if (n === '') {
    afficherErreurJS('nom', 'Le nom est obligatoire.');
  } else if (n.length > 100) {
    afficherErreurJS('nom', 'Le nom ne doit pas dépasser 100 caractères.');
  }
});

/* Catégorie : validation au blur */
const catInput = document.getElementById('categorie');
if (catInput) {
    catInput.addEventListener('blur', function() {
        if (this.value.trim() === '') {
            afficherErreurJS('categorie', 'La catégorie est obligatoire.');
        } else {
            this.classList.remove('input-erreur');
            const err = this.parentNode.querySelector('.js-err');
            if (err) err.remove();
        }
    });
}

/* Unité : validation au blur */
const uniteInput = document.getElementById('unite_defaut');
if (uniteInput) {
    uniteInput.addEventListener('blur', function() {
        if (this.value.trim() === '') {
            afficherErreurJS('unite_defaut', 'Veuillez choisir une unité.');
        } else {
            this.classList.remove('input-erreur');
            const err = this.parentNode.querySelector('.js-err');
            if (err) err.remove();
        }
    });
}



/* Valeurs nutritionnelles : vérif au blur */
['calories_100g', 'proteines_100g', 'glucides_100g', 'lipides_100g'].forEach(function(id) {
  document.getElementById(id).addEventListener('blur', function() {
    const errEl = this.parentNode.querySelector('.js-err');
    if (errEl) errEl.remove();
    this.classList.remove('input-erreur');

    const val = this.value;
    if (val !== '' && (isNaN(parseFloat(val)) || parseFloat(val) < 0)) {
      afficherErreurJS(id, 'Doit être un nombre positif.');
    }
  });
});

</script>

<?php include __DIR__ . '/layout/footer.php'; ?>

