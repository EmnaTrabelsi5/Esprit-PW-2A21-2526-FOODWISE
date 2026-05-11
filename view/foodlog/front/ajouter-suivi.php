<?php
/**
 * FoodWise — Module FoodLog
 * Vue : Ajouter une activité santé (Front)
 */

require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../controller/SuiviSanteController.php';

if (session_status() === PHP_SESSION_NONE) session_start();
if (empty($_SESSION['user_id'])) {
    header('Location: /FOODWISE/?route=module2/front/connexion');
    exit;
}

$pdo        = config::getConnexion();
$controller = new SuiviSanteController($pdo);
$userId     = (int) $_SESSION['user_id'];
$today      = date('Y-m-d');
$errors     = [];
$success    = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'user_id'         => $userId,
        'date'            => $_POST['date'] ?? $today,
        'type_activite'   => trim($_POST['type_activite'] ?? ''),
        'duree'           => $_POST['duree'] ?? '',
        'calories_brulees'=> $_POST['calories_brulees'] ?? '',
        'intensite'       => $_POST['intensite'] ?? 'moyen',
        'quantite_eau'    => $_POST['quantite_eau'] ?? '0',
        'note'            => trim($_POST['note'] ?? ''),
    ];

    $errors = $controller->validateSuiviData($data);

    if (empty($errors)) {
        try {
            if ($controller->addSuivi($data)) {
                header('Location: /FOODWISE/?route=foodlog/suivi&created=1');
                exit;
            } else {
                $errors[] = "Erreur lors de l'ajout du suivi.";
            }
        } catch (Exception $e) {
            if (str_contains($e->getMessage(), 'Duplicate entry') || str_contains($e->getMessage(), 'unique_user_date_activity')) {
                $errors[] = 'Vous avez déjà enregistré une activité "' . htmlspecialchars($data['type_activite'], ENT_QUOTES, 'UTF-8') . '" pour cette date. Choisissez un autre type d\'activité ou modifiez l\'entrée existante.';
            } else {
                $errors[] = 'Une erreur est survenue lors de l\'enregistrement. Veuillez réessayer.';
            }
        }
    }
}

$oldInput = $_POST;

$pageTitle  = 'Ajouter une activité';
$activeNav  = 'foodlog_suivi';
$backoffice = false;
include __DIR__ . '/../../layouts/front/header.php';
?>

<h1 class="page-title">➕ Ajouter une activité</h1>
<p class="page-subtitle">Enregistrez une activité physique ou votre hydratation.</p>

<section class="card">
  <div class="card-header">
    <h2 class="card-title">Nouvelle activité physique ou hydratation</h2>
    <span class="card-meta">Renseignez tous les champs nécessaires.</span>
  </div>

  <form id="suiviForm" action="" method="post">

    <?php if (!empty($errors)): ?>
      <div class="flash flash-error" style="margin-bottom:18px;">
        <ul style="margin:0;padding-left:18px;">
          <?php foreach ($errors as $error): ?>
            <li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <div class="form-row">
      <div class="form-group mb-0">
        <label class="form-label" for="date">Date</label>
        <input type="date" id="date" name="date" class="form-control"
               value="<?= htmlspecialchars($oldInput['date'] ?? $today, ENT_QUOTES, 'UTF-8') ?>"
               max="<?= $today ?>" required>
      </div>
      <div class="form-group mb-0">
        <label class="form-label" for="type_activite">Type d'activité</label>
        <input type="text" id="type_activite" name="type_activite" class="form-control"
               placeholder="Ex. Course, Fitness, Natation" list="activities"
               value="<?= htmlspecialchars($oldInput['type_activite'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
               maxlength="100" required>
        <datalist id="activities">
          <option value="Marche"><option value="Course"><option value="Fitness">
          <option value="Natation"><option value="Cyclisme"><option value="Yoga">
          <option value="Musculation"><option value="Danse"><option value="Sports collectifs">
          <option value="Escalade">
        </datalist>
      </div>
    </div>

    <div class="form-row">
      <div class="form-group mb-0">
        <label class="form-label" for="duree">Durée (minutes)</label>
        <input type="number" id="duree" name="duree" class="form-control"
               min="1" max="1440" placeholder="30"
               value="<?= htmlspecialchars($oldInput['duree'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
      </div>
      <div class="form-group mb-0">
        <label class="form-label" for="calories_brulees">Calories brûlées (kcal)</label>
        <input type="number" id="calories_brulees" name="calories_brulees" class="form-control"
               min="0" step="0.01" placeholder="0"
               value="<?= htmlspecialchars($oldInput['calories_brulees'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
      </div>
    </div>

    <div class="form-row">
      <div class="form-group mb-0">
        <label class="form-label" for="intensite">Intensité</label>
        <select id="intensite" name="intensite" class="form-control" required>
          <option value="">-- Sélectionnez --</option>
          <option value="faible" <?= ($oldInput['intensite'] ?? '') === 'faible' ? 'selected' : '' ?>>Faible</option>
          <option value="moyen"  <?= ($oldInput['intensite'] ?? 'moyen') === 'moyen' ? 'selected' : '' ?>>Moyen</option>
          <option value="élevé"  <?= ($oldInput['intensite'] ?? '') === 'élevé' ? 'selected' : '' ?>>Élevé</option>
        </select>
      </div>
      <div class="form-group mb-0">
        <label class="form-label" for="quantite_eau">Quantité d'eau (mL)</label>
        <input type="number" id="quantite_eau" name="quantite_eau" class="form-control"
               min="0" step="50" placeholder="500"
               value="<?= htmlspecialchars($oldInput['quantite_eau'] ?? '0', ENT_QUOTES, 'UTF-8') ?>" required>
      </div>
    </div>

    <div class="form-group">
      <label class="form-label" for="note">Note (optionnelle)</label>
      <textarea class="form-control" id="note" name="note" rows="3" maxlength="500"
                placeholder="Remarques supplémentaires..."><?= htmlspecialchars($oldInput['note'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
      <small class="text-muted"><span id="noteCount">0</span>/500 caractères</small>
    </div>

    <div class="flex-between mt-1 mb-0">
      <a href="?route=foodlog/suivi" class="btn btn-outline">Annuler</a>
      <div style="display:flex;gap:8px;">
        <button type="reset" class="btn btn-secondary">Réinitialiser</button>
        <button type="submit" class="btn btn-primary">Enregistrer</button>
      </div>
    </div>
  </form>
</section>

<script>
document.getElementById('note').addEventListener('input', function() {
  document.getElementById('noteCount').textContent = this.value.length;
});
</script>

<?php include __DIR__ . '/../../layouts/front/footer.php'; ?>
