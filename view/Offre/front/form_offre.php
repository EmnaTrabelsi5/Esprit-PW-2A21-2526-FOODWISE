<?php

$isEdit       = !empty($data['id']);
$pageTitle    = $isEdit ? 'Modifier l\'offre' : 'Publier une offre';
$activeModule = 'offre';
require __DIR__ . '/../../layouts/front/header.php';
 
$categories = [
  'fruits'   => '🍎 Fruits',
  'legumes'  => '🥦 Légumes',
  'viandes'  => '🥩 Viandes & Charcuterie',
  'poissons' => '🐟 Poissons & Fruits de mer',
  'laitiers' => '🥛 Produits laitiers & Œufs',
  'cereales' => '🌾 Céréales & Légumineuses',
  'epicerie' => '🫙 Épicerie fine',
  'autre'    => '📦 Autre',
];
$unites = ['kg','g','L','ml','pièce','boîte','bouteille','paquet','sachet','lot'];
?>
 
<div class="page-header-bar">
  <div>
    <h1 class="page-title"><?= $isEdit ? '✏️ Modifier l\'offre' : '🌿 Publier une offre' ?></h1>
    <p class="page-subtitle">
      <?= $isEdit ? 'Mettez à jour les détails de cette offre' : 'Partagez vos produits locaux avec la communauté FoodWise' ?>
    </p>
  </div>
  <a href="?route=offres/index" class="btn btn-outline">← Retour aux offres</a>
</div>
 
<div style="max-width:780px">
  <div class="card">
    <div class="card-header">
      <span class="card-title"><?= $isEdit ? 'Modifier l\'offre #' . (int)$data['id'] : 'Nouvelle offre' ?></span>
    </div>
 
    <form method="post"
action="<?= $isEdit 
? '?route=offres/update' 
: '?route=offres/store' ?>"
          novalidate>
 
      <?php if ($isEdit): ?>
        <input type="hidden" name="id" value="<?= (int)$data['id'] ?>">
      <?php endif; ?>
 
      <!-- Commerçant -->
      <div class="form-group">
        <label class="form-label" for="commercant_id">Commerçant *</label>
        <select class="form-control <?= !empty($errors['commercant_id']) ? 'is-invalid' : '' ?>"
                id="commercant_id" name="commercant_id">
          <option value="">— Sélectionnez un commerçant —</option>
          <?php foreach ($commercants as $c): ?>
            <option value="<?= $c['id'] ?>"
              <?= (int)($data['commercant_id'] ?? $_GET['commercant_id'] ?? 0) === (int)$c['id'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($c['nom']) ?> — <?= htmlspecialchars($c['ville']) ?>
            </option>
          <?php endforeach; ?>
        </select>
        <?php if (!empty($errors['commercant_id'])): ?>
          <span class="form-error"><?= $errors['commercant_id'] ?></span>
        <?php endif; ?>
      </div>
 
      <!-- Titre + Catégorie -->
      <div style="display:grid;grid-template-columns:2fr 1fr;gap:16px">
        <div class="form-group">
          <label class="form-label" for="titre">Titre de l'offre *</label>
          <input class="form-control <?= !empty($errors['titre']) ? 'is-invalid' : '' ?>"
                 type="text" id="titre" name="titre"
                 value="<?= htmlspecialchars($data['titre'] ?? '') ?>"
                 placeholder="Ex: Tomates bio cerises 1kg">
          <?php if (!empty($errors['titre'])): ?>
            <span class="form-error"><?= $errors['titre'] ?></span>
          <?php endif; ?>
        </div>
 
        <div class="form-group">
          <label class="form-label" for="categorie">Catégorie</label>
          <select class="form-control" id="categorie" name="categorie">
            <?php foreach ($categories as $k => $lbl): ?>
              <option value="<?= $k ?>" <?= ($data['categorie'] ?? 'autre') === $k ? 'selected' : '' ?>>
                <?= $lbl ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
 
      <!-- Description -->
      <div class="form-group">
        <label class="form-label" for="description">Description</label>
        <textarea class="form-control" id="description" name="description" rows="3"
                  placeholder="Décrivez le produit : origine, qualité, particularités..."><?= htmlspecialchars($data['description'] ?? '') ?></textarea>
      </div>
 
      <!-- Prix + Unité + Stock -->
      <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px">
        <div class="form-group">
          <label class="form-label" for="prix_unitaire">Prix unitaire (TND) *</label>
          <input class="form-control <?= !empty($errors['prix_unitaire']) ? 'is-invalid' : '' ?>"
                 type="number" id="prix_unitaire" name="prix_unitaire"
                 step="0.01" min="0"
                 value="<?= htmlspecialchars($data['prix_unitaire'] ?? '') ?>"
                 placeholder="0.00">
          <?php if (!empty($errors['prix_unitaire'])): ?>
            <span class="form-error"><?= $errors['prix_unitaire'] ?></span>
          <?php endif; ?>
        </div>
 
        <div class="form-group">
          <label class="form-label" for="unite">Unité</label>
          <select class="form-control" id="unite" name="unite">
            <?php foreach ($unites as $u): ?>
              <option value="<?= $u ?>" <?= ($data['unite'] ?? 'kg') === $u ? 'selected' : '' ?>><?= $u ?></option>
            <?php endforeach; ?>
          </select>
        </div>
 
        <div class="form-group">
          <label class="form-label" for="stock">Quantité en stock *</label>
          <input class="form-control <?= !empty($errors['stock']) ? 'is-invalid' : '' ?>"
                 type="number" id="stock" name="stock"
                 min="0"
                 value="<?= htmlspecialchars($data['stock'] ?? '0') ?>"
                 placeholder="0">
          <?php if (!empty($errors['stock'])): ?>
            <span class="form-error"><?= $errors['stock'] ?></span>
          <?php endif; ?>
        </div>
      </div>
 
      <!-- Statut (visible à la modification) -->
      <?php if ($isEdit): ?>
      <div class="form-group">
        <label class="form-label" for="statut">Statut</label>
        <select class="form-control" id="statut" name="statut">
          <option value="disponible" <?= ($data['statut'] ?? '') === 'disponible' ? 'selected' : '' ?>>✅ Disponible</option>
          <option value="epuise"     <?= ($data['statut'] ?? '') === 'epuise'     ? 'selected' : '' ?>>📭 Épuisé</option>
          <option value="suspendu"   <?= ($data['statut'] ?? '') === 'suspendu'   ? 'selected' : '' ?>>⏸ Suspendu</option>
        </select>
      </div>
      <?php endif; ?>
 
      <!-- Dates -->
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
        <div class="form-group">
          <label class="form-label" for="date_debut">Date de début</label>
          <input class="form-control" type="datetime-local" id="date_debut" name="date_debut"
                 value="<?= htmlspecialchars(
                   !empty($data['date_debut'])
                     ? date('Y-m-d\TH:i', strtotime($data['date_debut']))
                     : date('Y-m-d\TH:i')
                 ) ?>">
        </div>
 
        <div class="form-group">
          <label class="form-label" for="date_expiration">Date d'expiration *</label>
          <input class="form-control <?= !empty($errors['date_expiration']) ? 'is-invalid' : '' ?>"
                 type="datetime-local" id="date_expiration" name="date_expiration"
                 value="<?= htmlspecialchars(
                   !empty($data['date_expiration'])
                     ? date('Y-m-d\TH:i', strtotime($data['date_expiration']))
                     : ''
                 ) ?>">
          <?php if (!empty($errors['date_expiration'])): ?>
            <span class="form-error"><?= $errors['date_expiration'] ?></span>
          <?php endif; ?>
        </div>
      </div>
 
      <div class="substitut-alert" style="margin-bottom:16px">
        <span>💡</span>
        <span>
          Le statut de l'offre (disponible / épuisé / expiré) est géré <strong>automatiquement</strong>
          selon la quantité en stock et la date d'expiration.
        </span>
      </div>
 
      <div style="display:flex;gap:12px">
        <button type="submit" class="btn btn-primary">
          <?= $isEdit ? '💾 Enregistrer les modifications' : '🌿 Publier l\'offre' ?>
        </button>
        <a href="/FOODWISE1/router/offreRouter.php?action=index" class="btn btn-outline">Annuler</a>
      </div>
    </form>
  </div>
</div>
 
<?php require __DIR__ . '/../../layouts/front/footer.php'; ?>