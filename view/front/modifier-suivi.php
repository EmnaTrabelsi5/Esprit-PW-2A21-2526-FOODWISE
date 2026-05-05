<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/auth.php';
requireClient();

// Inclure la configuration et les classes
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../controller/SuiviSanteController.php';

$pdo = require __DIR__ . '/../../config/config.php';
$controller = new SuiviSanteController($pdo);

// ID utilisateur et suivi
$userId = $_SESSION['user_id'] ?? 1;
$suiviId = (int)($_GET['id'] ?? 0);

if ($suiviId === 0) {
    header('Location: ./journal-alimentaire.php');
    exit;
}

// Vérifier l'accès
if (!$controller->userHasAccessToSuivi($userId, $suiviId)) {
    header('Location: ./journal-alimentaire.php');
    exit;
}

// Récupérer les données du suivi santé
$suivi = $controller->getSuiviById($suiviId);
if (!$suivi) {
    header('Location: ./journal-alimentaire.php');
    exit;
}

$today = date('Y-m-d');
$errors = [];
$success = false;

// Convertir les valeurs en chaînes avant d'utiliser htmlspecialchars
$date = htmlspecialchars((string) $suivi['date']);
$typeActivite = htmlspecialchars((string) $suivi['type_activite']);
$duree = htmlspecialchars((string) $suivi['duree']);

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'user_id' => $userId,
        'date' => $_POST['date'] ?? $suivi['date'],
        'type_activite' => trim($_POST['type_activite'] ?? $suivi['type_activite']),
        'duree' => $_POST['duree'] ?? $suivi['duree'],
        'calories_brulees' => $_POST['calories_brulees'] ?? $suivi['calories_brulees'],
        'intensite' => $_POST['intensite'] ?? $suivi['intensite'],
        'quantite_eau' => $_POST['quantite_eau'] ?? $suivi['quantite_eau'],
        'note' => trim($_POST['note'] ?? $suivi['note']),
    ];

    // Valider les données
    $errors = $controller->validateSuiviData($data);

    if (empty($errors)) {
        try {
            if ($controller->updateSuivi($suiviId, $data)) {
                header('Location: ./journal-alimentaire.php');
                exit;
            } else {
                $errors[] = 'Erreur lors de la modification du suivi.';
            }
        } catch (Exception $e) {
            $errors[] = 'Erreur système: ' . $e->getMessage();
        }
    }
}

$pageTitle = 'Modifier une activité — FoodWise';
?>

<?php include_once __DIR__ . '/../../view/template/header.php'; ?>
<body class="theme-front">
  <input type="checkbox" id="fw-nav-toggle" class="fw-nav-toggle" hidden>

  <div class="fw-layout">
    <label for="fw-nav-toggle" class="sidebar-backdrop" aria-label="Fermer le menu"></label>

    <aside class="sidebar" aria-label="Navigation principale">
      <div class="sidebar-logo">
        <img src="../../public/images/foodwise-logo.png" alt="FoodWise">
      </div>
      <nav class="sidebar-nav">
        <p class="fw-sidebar-section">FoodLog</p>
        <a href="journal-alimentaire.php" class="nav-item active"><span class="nav-ico" aria-hidden="true">📋</span> Journal alimentaire</a>
        <a href="ajouter-entree.php" class="nav-item"><span class="nav-ico" aria-hidden="true">➕</span> Ajouter une entrée</a>
        <a href="resume-jour.php" class="nav-item"><span class="nav-ico" aria-hidden="true">📊</span> Résumé du jour</a>
        <p class="fw-sidebar-section">Compte</p>
        <a href="#" class="nav-item"><span class="nav-ico" aria-hidden="true">👤</span> Profil</a>
        <a href="#" class="nav-item"><span class="nav-ico" aria-hidden="true">⎋</span> Déconnexion</a>
      </nav>
    </aside>

    <div class="main-content">
      <header class="topbar">
        <label for="fw-nav-toggle" class="topbar-menu-btn" aria-label="Ouvrir le menu">☰</label>
        <form class="topbar-search" action="#" method="get" role="search">
          <input type="search" name="q" placeholder="Rechercher une activité…" aria-label="Recherche" value="">
          <button type="button">OK</button>
        </form>
        <nav class="topbar-nav" aria-label="Raccourcis">
          <a href="journal-alimentaire.php">Journal</a>
        </nav>
        <div class="topbar-user">
          <span class="user-avatar-initials" aria-hidden="true">MD</span>
          <span>Marie Dupont</span>
        </div>
      </header>

      <main class="page-body">
        <h1 class="page-title">Modifier une activité</h1>
        <p class="page-subtitle">Édition du suivi santé existant.</p>

        <section class="card">
          <div class="card-header">
            <h2 class="card-title">Édition — <?= htmlspecialchars($suivi['type_activite'], ENT_QUOTES, 'UTF-8') ?></h2>
            <span class="card-meta"><?= htmlspecialchars($suivi['date'], ENT_QUOTES, 'UTF-8') ?>, <?= htmlspecialchars((string) ($suivi['duree'] ?: '0'), ENT_QUOTES, 'UTF-8') ?> min</span>
          </div>

          <div class="card-body">
            <div id="form-errors" class="notif-panel<?= empty($errors) ? '' : ' error' ?>" style="display: <?= empty($errors) ? 'none' : 'block' ?>;margin-bottom:18px;">
              <?php if (!empty($errors)): ?>
                <ul>
                  <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></li>
                  <?php endforeach; ?>
                </ul>
              <?php endif; ?>
            </div>

            <?php if ($success): ?>
              <div class="alert alert-success alert-dismissible fade show" role="alert" style="margin-bottom:18px;">
                <strong>Succès!</strong> Votre suivi santé a été modifié.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
              </div>
            <?php endif; ?>

            <form action="" method="POST" id="suiviForm" novalidate>
              <div class="form-row">
                <div class="form-group mb-0">
                  <label class="form-label" for="date">Date</label>
                  <input type="date" id="date" name="date" class="form-control" value="<?= htmlspecialchars($_POST['date'] ?? $suivi['date'], ENT_QUOTES, 'UTF-8') ?>" max="<?= $today ?>" required>
                </div>
                <div class="form-group mb-0">
                  <label class="form-label" for="type_activite">Type d'activité</label>
                  <input type="text" id="type_activite" name="type_activite" class="form-control" list="activities" placeholder="Ex: Marche, Course..." value="<?= htmlspecialchars($_POST['type_activite'] ?? $suivi['type_activite'], ENT_QUOTES, 'UTF-8') ?>" required>
                  <datalist id="activities">
                    <option value="Marche">
                    <option value="Course">
                    <option value="Fitness">
                    <option value="Natation">
                    <option value="Cyclisme">
                    <option value="Yoga">
                    <option value="Musculation">
                    <option value="Danse">
                    <option value="Sports collectifs">
                    <option value="Escalade">
                  </datalist>
                </div>
              </div>

              <div class="form-row">
                <div class="form-group mb-0">
                  <label class="form-label" for="duree">Durée (minutes)</label>
                  <input type="number" id="duree" name="duree" class="form-control" min="1" max="1440" placeholder="30" value="<?= htmlspecialchars((string) ($_POST['duree'] ?? $suivi['duree']), ENT_QUOTES, 'UTF-8') ?>" required>
                </div>
                <div class="form-group mb-0">
                  <label class="form-label" for="calories_brulees">Calories brûlées</label>
                  <input type="number" id="calories_brulees" name="calories_brulees" class="form-control" min="0" step="0.01" placeholder="0" value="<?= htmlspecialchars((string) ($_POST['calories_brulees'] ?? $suivi['calories_brulees']), ENT_QUOTES, 'UTF-8') ?>" required>
                </div>
              </div>

              <div class="form-row">
                <div class="form-group mb-0">
                  <label class="form-label" for="intensite">Intensité</label>
                  <select class="form-control" id="intensite" name="intensite" required>
                    <option value="faible" <?= ($_POST['intensite'] ?? $suivi['intensite']) === 'faible' ? 'selected' : '' ?>>Faible</option>
                    <option value="moyen" <?= ($_POST['intensite'] ?? $suivi['intensite']) === 'moyen' ? 'selected' : '' ?>>Moyen</option>
                    <option value="élevé" <?= ($_POST['intensite'] ?? $suivi['intensite']) === 'élevé' ? 'selected' : '' ?>>Élevé</option>
                  </select>
                </div>
                <div class="form-group mb-0">
                  <label class="form-label" for="quantite_eau">Quantité d'eau (mL)</label>
                  <input type="number" id="quantite_eau" name="quantite_eau" class="form-control" min="0" step="50" placeholder="500" value="<?= htmlspecialchars((string) ($_POST['quantite_eau'] ?? $suivi['quantite_eau']), ENT_QUOTES, 'UTF-8') ?>" required>
                </div>
              </div>

              <div class="form-group">
                <label class="form-label" for="note">Note</label>
                <textarea id="note" name="note" class="form-control" rows="4" maxlength="500"><?= htmlspecialchars((string) ($_POST['note'] ?? $suivi['note']), ENT_QUOTES, 'UTF-8') ?></textarea>
                <p class="form-text"><small><span id="noteCount"><?= strlen((string) ($_POST['note'] ?? $suivi['note'])) ?></span>/500 caractères</small></p>
              </div>

              <div class="form-group">
                <p><small><strong>Créé:</strong> <?= date('d/m/Y H:i', strtotime($suivi['created_at'])) ?><br>
                <strong>Modifié:</strong> <?= date('d/m/Y H:i', strtotime($suivi['updated_at'])) ?></small></p>
              </div>

              <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                <a href="./suivi-sante-unifie.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Retour au suivi</a>
                <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> Enregistrer les modifications</button>
              </div>
            </form>
          </div>
        </section>
      </main>
    </div>
  </div>

  <script>
  document.getElementById('note').addEventListener('input', function() {
    document.getElementById('noteCount').textContent = this.value.length;
  });

  document.getElementById('suiviForm').addEventListener('submit', function(e) {
    const duree = parseInt(document.getElementById('duree').value, 10);
    const caloriesBrulees = parseFloat(document.getElementById('calories_brulees').value);
    const quantiteEau = parseFloat(document.getElementById('quantite_eau').value);

    if (duree <= 0 || Number.isNaN(duree)) {
      e.preventDefault();
      alert('La durée doit être positive.');
      return false;
    }

    if (caloriesBrulees < 0 || Number.isNaN(caloriesBrulees)) {
      e.preventDefault();
      alert('Les calories brûlées ne peuvent pas être négatives.');
      return false;
    }

    if (quantiteEau < 0 || Number.isNaN(quantiteEau)) {
      e.preventDefault();
      alert('La quantité d\'eau ne peut pas être négative.');
      return false;
    }

    return true;
  });
  </script>
</body>
<?php include_once __DIR__ . '/../../view/template/footer.php'; ?>
