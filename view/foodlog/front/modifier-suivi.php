<?php
/**
 * FoodWise — Module FoodLog
 * Vue : Modifier une activité santé (Front)
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
$suiviId    = (int) ($_GET['id'] ?? 0);

if ($suiviId === 0 || !$controller->userHasAccessToSuivi($userId, $suiviId)) {
    header('Location: /FOODWISE/?route=foodlog/suivi');
    exit;
}

$suivi = $controller->getSuiviById($suiviId);
if (!$suivi) {
    header('Location: /FOODWISE/?route=foodlog/suivi');
    exit;
}

$today  = date('Y-m-d');
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'user_id'         => $userId,
        'date'            => $_POST['date'] ?? $suivi['date'],
        'type_activite'   => trim($_POST['type_activite'] ?? $suivi['type_activite']),
        'duree'           => $_POST['duree'] ?? $suivi['duree'],
        'calories_brulees'=> $_POST['calories_brulees'] ?? $suivi['calories_brulees'],
        'intensite'       => $_POST['intensite'] ?? $suivi['intensite'],
        'quantite_eau'    => $_POST['quantite_eau'] ?? $suivi['quantite_eau'],
        'note'            => trim($_POST['note'] ?? $suivi['note']),
    ];

    $errors = $controller->validateSuiviData($data);

    if (empty($errors)) {
        try {
            if ($controller->updateSuivi($suiviId, $data)) {
                header('Location: /FOODWISE/?route=foodlog/suivi&updated=1');
                exit;
            } else {
                $errors[] = 'Erreur lors de la modification du suivi.';
            }
        } catch (Exception $e) {
            if (str_contains($e->getMessage(), 'Duplicate entry') || str_contains($e->getMessage(), 'unique_user_date_activity')) {
                $errors[] = 'Une activité "' . htmlspecialchars($data['type_activite'], ENT_QUOTES, 'UTF-8') . '" existe déjà pour cette date.';
            } else {
                $errors[] = 'Une erreur est survenue lors de la modification. Veuillez réessayer.';
            }
        }
    }
}

$pageTitle  = 'Modifier une activité';
$activeNav  = 'foodlog_suivi';
$backoffice = false;
include __DIR__ . '/../../layouts/front/header.php';
?>

<h1 class="page-title">✏️ Modifier une activité</h1>
<p class="page-subtitle">Édition du suivi santé existant.</p>

<section class="card">
  <div class="card-header">
    <h2 class="card-title">Édition — <?= htmlspecialchars($suivi['type_activite'], ENT_QUOTES, 'UTF-8') ?></h2>
    <span class="card-meta"><?= htmlspecialchars($suivi['date'], ENT_QUOTES, 'UTF-8') ?>, <?= (int) $suivi['duree'] ?> min</span>
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
               value="<?= htmlspecialchars($_POST['date'] ?? $suivi['date'], ENT_QUOTES, 'UTF-8') ?>"
               max="<?= $today ?>" required>
      </div>
      <div class="form-group mb-0">
        <label class="form-label" for="type_activite">Type d'activité</label>
        <input type="text" id="type_activite" name="type_activite" class="form-control"
               list="activities" placeholder="Ex: Marche, Course..."
               value="<?= htmlspecialchars($_POST['type_activite'] ?? $suivi['type_activite'], ENT_QUOTES, 'UTF-8') ?>" required>
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
               value="<?= htmlspecialchars((string) ($_POST['duree'] ?? $suivi['duree']), ENT_QUOTES, 'UTF-8') ?>" required>
      </div>
      <div class="form-group mb-0">
        <label class="form-label" for="calories_brulees">Calories brûlées</label>
        <input type="number" id="calories_brulees" name="calories_brulees" class="form-control"
               min="0" step="0.01" placeholder="0"
               value="<?= htmlspecialchars((string) ($_POST['calories_brulees'] ?? $suivi['calories_brulees']), ENT_QUOTES, 'UTF-8') ?>" required>
      </div>
    </div>

    <div class="form-row">
      <div class="form-group mb-0">
        <label class="form-label" for="intensite">Intensité</label>
        <select class="form-control" id="intensite" name="intensite" required>
          <option value="faible" <?= ($_POST['intensite'] ?? $suivi['intensite']) === 'faible' ? 'selected' : '' ?>>Faible</option>
          <option value="moyen"  <?= ($_POST['intensite'] ?? $suivi['intensite']) === 'moyen'  ? 'selected' : '' ?>>Moyen</option>
          <option value="élevé"  <?= ($_POST['intensite'] ?? $suivi['intensite']) === 'élevé'  ? 'selected' : '' ?>>Élevé</option>
        </select>
      </div>
      <div class="form-group mb-0">
        <label class="form-label" for="quantite_eau">Quantité d'eau (mL)</label>
        <input type="number" id="quantite_eau" name="quantite_eau" class="form-control"
               min="0" step="50" placeholder="500"
               value="<?= htmlspecialchars((string) ($_POST['quantite_eau'] ?? $suivi['quantite_eau']), ENT_QUOTES, 'UTF-8') ?>" required>
      </div>
    </div>

    <div class="form-group">
      <label class="form-label" for="note">Note</label>
      <textarea id="note" name="note" class="form-control" rows="4" maxlength="500"><?= htmlspecialchars((string) ($_POST['note'] ?? $suivi['note']), ENT_QUOTES, 'UTF-8') ?></textarea>
      <small class="text-muted"><span id="noteCount"><?= strlen((string) ($_POST['note'] ?? $suivi['note'])) ?></span>/500 caractères</small>
    </div>

    <div class="flex-between mt-1 mb-0">
      <a href="?route=foodlog/suivi" class="btn btn-outline">Retour au suivi</a>
      <div style="display:flex;gap:8px;">
        <a href="/FOODWISE/?route=foodlog/supprimer&id=<?= $suiviId ?>&source=suivi&origin=front"
           class="btn btn-danger btn-sm"
           onclick="return confirm('Supprimer cette activité ?')">Supprimer</a>
        <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
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
