<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/auth.php';
requireAdmin();

// Inclure la configuration et les classes
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../controller/SuiviSanteController.php';

$pdo = require __DIR__ . '/../../config/config.php';
$controller = new SuiviSanteController($pdo);

// ID du suivi
$suiviId = (int)($_GET['id'] ?? 0);

if ($suiviId === 0) {
    header('Location: ./manage-suivi.php');
    exit;
}

$suivi = $controller->getSuivi($suiviId);
if ($suivi === null) {
    header('Location: ./manage-suivi.php');
    exit;
}

$today = date('Y-m-d');
$errors = [];
$success = false;

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'user_id' => (int)$_POST['user_id'] ?? $suivi['user_id'],
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
                $success = true;
                // Rafraîchir les données
                $suivi = $controller->getSuivi($suiviId);
            } else {
                $errors[] = 'Erreur lors de la modification du suivi.';
            }
        } catch (Exception $e) {
            $errors[] = 'Erreur système: ' . $e->getMessage();
        }
    }
}
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
        <p class="fw-sidebar-section">Administration</p>
        <a href="dashboard-admin.php" class="nav-item"><span class="nav-ico" aria-hidden="true">🏠</span> Dashboard admin</a>
        <a href="dashboard-suivi.php" class="nav-item"><span class="nav-ico" aria-hidden="true">📊</span> Dashboard suivi</a>
        <a href="manage-suivi.php" class="nav-item active"><span class="nav-ico" aria-hidden="true">📝</span> Gérer les suivis</a>
        <a href="liste-entries.php" class="nav-item"><span class="nav-ico" aria-hidden="true">📁</span> Liste entrées</a>
        <a href="statistiques-globales.php" class="nav-item"><span class="nav-ico" aria-hidden="true">📈</span> Statistiques</a>
      </nav>
    </aside>

    <div class="main-content">
      <header class="topbar">
        <label for="fw-nav-toggle" class="topbar-menu-btn" aria-label="Ouvrir le menu">☰</label>
        <form class="topbar-search" action="#" method="get" role="search">
          <input type="search" name="q" placeholder="Rechercher un suivi…" aria-label="Recherche" value="">
          <button type="button">OK</button>
        </form>
        <nav class="topbar-nav" aria-label="Raccourcis">
          <a href="dashboard-admin.php">Accueil</a>
        </nav>
        <div class="topbar-user">
          <span class="user-avatar-initials" aria-hidden="true">AD</span>
          <span>Admin</span>
        </div>
      </header>

      <main class="page-body">
        <h1 class="page-title">Modifier le Suivi Santé</h1>
        <p class="page-subtitle">Édition du suivi de l’utilisateur.</p>

        <section class="card">
          <div class="card-header">
            <h2 class="card-title">Modifier le Suivi Santé [ID: <?= (int)$suivi['id']; ?>]</h2>
            <span class="card-meta">Utilisateur <?= (int)$suivi['user_id']; ?> · <?= htmlspecialchars($suivi['date'], ENT_QUOTES, 'UTF-8') ?></span>
          </div>

          <div class="card-body">
            <?php if ($success): ?>
              <div class="notif-panel success" style="margin-bottom:18px;">
                <strong>Succès!</strong> Le suivi santé a été modifié.
              </div>
            <?php endif; ?>

            <?php if (!empty($errors)): ?>
              <div class="notif-panel error" style="margin-bottom:18px;">
                <ul>
                  <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></li>
                  <?php endforeach; ?>
                </ul>
              </div>
            <?php endif; ?>

            <div class="alert alert-info small">
              <strong>Créé:</strong> <?= date('d/m/Y H:i:s', strtotime($suivi['created_at'])); ?><br>
              <strong>Modifié:</strong> <?= date('d/m/Y H:i:s', strtotime($suivi['updated_at'])); ?>
            </div>

            <form action="" method="POST" id="suiviForm" novalidate>
              <div class="form-group mb-3">
                <label for="user_id" class="form-label"><i class="fas fa-user"></i> ID Utilisateur</label>
                <input type="number" class="form-control" id="user_id" name="user_id" value="<?= htmlspecialchars($_POST['user_id'] ?? $suivi['user_id'], ENT_QUOTES, 'UTF-8') ?>" min="1" required>
              </div>

              <div class="form-group mb-3">
                <label for="date" class="form-label"><i class="fas fa-calendar"></i> Date</label>
                <input type="date" class="form-control" id="date" name="date" value="<?= htmlspecialchars($_POST['date'] ?? $suivi['date'], ENT_QUOTES, 'UTF-8') ?>" max="<?= $today ?>" required>
              </div>

              <div class="form-row">
                <div class="form-group mb-0">
                  <label for="type_activite" class="form-label"><i class="fas fa-running"></i> Type d'Activité</label>
                  <input type="text" class="form-control datalist-input" id="type_activite" name="type_activite" placeholder="Ex: Marche, Course..." list="activities" value="<?= htmlspecialchars($_POST['type_activite'] ?? $suivi['type_activite'], ENT_QUOTES, 'UTF-8') ?>" maxlength="100" required>
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

                <div class="form-group mb-0">
                  <label for="duree" class="form-label"><i class="fas fa-hourglass-half"></i> Durée (minutes)</label>
                  <input type="number" class="form-control" id="duree" name="duree" min="1" max="1440" value="<?= htmlspecialchars($_POST['duree'] ?? $suivi['duree'], ENT_QUOTES, 'UTF-8') ?>" required>
                </div>
              </div>

              <div class="form-row">
                <div class="form-group mb-0">
                  <label for="calories_brulees" class="form-label"><i class="fas fa-fire"></i> Calories Brûlées</label>
                  <input type="number" class="form-control" id="calories_brulees" name="calories_brulees" min="0" step="0.01" value="<?= htmlspecialchars($_POST['calories_brulees'] ?? $suivi['calories_brulees'], ENT_QUOTES, 'UTF-8') ?>" required>
                </div>
                <div class="form-group mb-0">
                  <label for="intensite" class="form-label"><i class="fas fa-bolt"></i> Intensité</label>
                  <select class="form-control" id="intensite" name="intensite" required>
                    <option value="faible" <?= ($_POST['intensite'] ?? $suivi['intensite']) === 'faible' ? 'selected' : '' ?>>Faible</option>
                    <option value="moyen" <?= ($_POST['intensite'] ?? $suivi['intensite']) === 'moyen' ? 'selected' : '' ?>>Moyen</option>
                    <option value="élevé" <?= ($_POST['intensite'] ?? $suivi['intensite']) === 'élevé' ? 'selected' : '' ?>>Élevé</option>
                  </select>
                </div>
              </div>

              <div class="form-group mb-3">
                <label for="quantite_eau" class="form-label"><i class="fas fa-droplet"></i> Quantité d'Eau (mL)</label>
                <input type="number" class="form-control" id="quantite_eau" name="quantite_eau" min="0" step="50" value="<?= htmlspecialchars($_POST['quantite_eau'] ?? $suivi['quantite_eau'], ENT_QUOTES, 'UTF-8') ?>" required>
              </div>

              <div class="form-group mb-3">
                <label for="note" class="form-label"><i class="fas fa-sticky-note"></i> Note (optionnelle)</label>
                <textarea class="form-control" id="note" name="note" rows="3" maxlength="500" placeholder="Remarques..."><?= htmlspecialchars($_POST['note'] ?? $suivi['note'], ENT_QUOTES, 'UTF-8') ?></textarea>
                <div class="form-text"><small id="noteCount"><?= strlen($_POST['note'] ?? $suivi['note']) ?></small>/500 caractères</small></div>
              </div>

              <div class="flex-between mt-1 mb-0">
                <a href="./manage-suivi.php" class="btn btn-outline">Retour au tableau</a>
                <button type="submit" class="btn btn-primary">Enregistrer</button>
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
  </script>
</body>
<?php include_once __DIR__ . '/../../view/template/footer.php'; ?>
