<?php
// views/commercant/form.php  — Création & modification
$isEdit      = !empty($data['id']);
$pageTitle   = $isEdit ? 'Modifier le commerçant' : 'Inscrire un commerçant';
$activeModule = 'commercant';
require __DIR__ . '/../layouts/header.php';
?>
 
<div class="page-header-bar">
  <div>
    <h1 class="page-title"><?= $isEdit ? '✏️ Modifier le commerçant' : '🏪 Inscrire un commerçant' ?></h1>
    <p class="page-subtitle">
      <?= $isEdit
        ? 'Modifiez les informations du commerçant partenaire'
        : 'Ajoutez un nouveau commerçant à la plateforme FoodWise LocalMarket' ?>
    </p>
  </div>
  <a href="commercant.php?action=index" class="btn btn-outline">← Retour à la liste</a>
</div>
 
<div style="max-width:720px">
  <div class="card">
    <div class="card-header">
      <span class="card-title">
        <?= $isEdit ? 'Informations du commerçant' : 'Nouveau commerçant partenaire' ?>
      </span>
      <?php if ($isEdit): ?>
        <span class="badge badge-brun">#<?= $data['id'] ?></span>
      <?php endif; ?>
    </div>
 
    <!-- Formulaire — action selon create / update -->
    <form method="post"
          action="<?= $isEdit ? 'commercant.php?action=update' : 'commercant.php?action=store' ?>"
          novalidate>
 
      <?php if ($isEdit): ?>
        <input type="hidden" name="id" value="<?= (int)$data['id'] ?>">
      <?php endif; ?>
 
      <!-- Nom + Email -->
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
        <div class="form-group">
          <label class="form-label" for="nom">Nom du commerce *</label>
          <input class="form-control <?= !empty($errors['nom']) ? 'is-invalid' : '' ?>"
                 type="text" id="nom" name="nom"
                 value="<?= htmlspecialchars($data['nom'] ?? '') ?>"
                 placeholder="Ex: Bio du Terroir">
          <?php if (!empty($errors['nom'])): ?>
            <span class="form-error"><?= $errors['nom'] ?></span>
          <?php endif; ?>
        </div>
 
        <div class="form-group">
          <label class="form-label" for="email">Email *</label>
          <input class="form-control <?= !empty($errors['email']) ? 'is-invalid' : '' ?>"
                 type="text" id="email" name="email"
                 value="<?= htmlspecialchars($data['email'] ?? '') ?>"
                 placeholder="contact@exemple.tn">
          <?php if (!empty($errors['email'])): ?>
            <span class="form-error"><?= $errors['email'] ?></span>
          <?php endif; ?>
        </div>
      </div>
 
      <!-- Téléphone + Statut -->
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
        <div class="form-group">
          <label class="form-label" for="telephone">Téléphone</label>
          <input class="form-control" type="text" id="telephone" name="telephone"
                 value="<?= htmlspecialchars($data['telephone'] ?? '') ?>"
                 placeholder="Ex: 71 123 456">
        </div>
 
        <div class="form-group">
          <label class="form-label" for="statut">Statut</label>
          <select class="form-control" id="statut" name="statut">
            <option value="actif"    <?= ($data['statut'] ?? 'actif') === 'actif'    ? 'selected' : '' ?>>✅ Actif</option>
            <option value="inactif"  <?= ($data['statut'] ?? '')       === 'inactif'  ? 'selected' : '' ?>>⏸ Inactif</option>
            <option value="suspendu" <?= ($data['statut'] ?? '')       === 'suspendu' ? 'selected' : '' ?>>🚫 Suspendu</option>
          </select>
        </div>
      </div>
 
      <!-- Adresse -->
      <div class="form-group">
        <label class="form-label" for="adresse">Adresse *</label>
        <input class="form-control <?= !empty($errors['adresse']) ? 'is-invalid' : '' ?>"
               type="text" id="adresse" name="adresse"
               value="<?= htmlspecialchars($data['adresse'] ?? '') ?>"
               placeholder="Ex: 12 Rue de la République">
        <?php if (!empty($errors['adresse'])): ?>
          <span class="form-error"><?= $errors['adresse'] ?></span>
        <?php endif; ?>
      </div>
 
      <!-- Ville + Code postal -->
      <div style="display:grid;grid-template-columns:2fr 1fr;gap:16px">
        <div class="form-group">
          <label class="form-label" for="ville">Ville *</label>
          <input class="form-control <?= !empty($errors['ville']) ? 'is-invalid' : '' ?>"
                 type="text" id="ville" name="ville"
                 value="<?= htmlspecialchars($data['ville'] ?? '') ?>"
                 placeholder="Ex: Tunis, Sousse, Nabeul...">
          <?php if (!empty($errors['ville'])): ?>
            <span class="form-error"><?= $errors['ville'] ?></span>
          <?php endif; ?>
        </div>
 
        <div class="form-group">
          <label class="form-label" for="code_postal">Code postal</label>
          <input class="form-control" type="text" id="code_postal" name="code_postal"
                 value="<?= htmlspecialchars($data['code_postal'] ?? '') ?>"
                 placeholder="Ex: 1000">
        </div>
      </div>
 
      <!-- Description -->
      <div class="form-group">
        <label class="form-label" for="description">Description</label>
        <textarea class="form-control" id="description" name="description"
                  rows="3"
                  placeholder="Présentez votre commerce, vos produits, vos engagements..."><?= htmlspecialchars($data['description'] ?? '') ?></textarea>
      </div>
 
      <!-- Boutons -->
      <div style="display:flex;gap:12px;margin-top:8px">
        <button type="submit" class="btn btn-primary">
          <?= $isEdit ? '💾 Enregistrer les modifications' : '✅ Inscrire le commerçant' ?>
        </button>
        <a href="commercant.php?action=<?= $isEdit ? 'show&id=' . (int)$data['id'] : 'index' ?>"
           class="btn btn-outline">Annuler</a>
      </div>
    </form>
  </div>
</div>
 
<?php require __DIR__ . '/../layouts/footer.php'; ?>