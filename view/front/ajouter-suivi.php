<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/auth.php';
requireClient();

// Inclure la configuration et les classes
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../controller/SuiviSanteController.php';

$pdo = require __DIR__ . '/../../config/config.php';
$controller = new SuiviSanteController($pdo);

// ID utilisateur fictif (à adapter avec votre système d'authentification)
$userId = $_SESSION['user_id'] ?? 1;
$today = date('Y-m-d');
$errors = [];
$success = false;

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'user_id' => $userId,
        'date' => $_POST['date'] ?? $today,
        'type_activite' => trim($_POST['type_activite'] ?? ''),
        'duree' => $_POST['duree'] ?? '',
        'calories_brulees' => $_POST['calories_brulees'] ?? '',
        'intensite' => $_POST['intensite'] ?? 'moyen',
        'quantite_eau' => $_POST['quantite_eau'] ?? '0',
        'note' => trim($_POST['note'] ?? ''),
    ];

    // Valider les données
    $errors = $controller->validateSuiviData($data);

    if (empty($errors)) {
        try {
            if ($controller->addSuivi($data)) {
                $success = true;
                $_POST = [];
            } else {
                $errors[] = 'Erreur lors de l\'ajout du suivi.';
            }
        } catch (Exception $e) {
            $errors[] = 'Erreur système: ' . $e->getMessage();
        }
    }
}
?>


<?php
$oldInput = $_POST;
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
                <a href="journal-alimentaire.php" class="nav-item"><span class="nav-ico" aria-hidden="true">📋</span> Journal alimentaire</a>
                <a href="ajouter-entree.php" class="nav-item"><span class="nav-ico" aria-hidden="true">➕</span> Ajouter une entrée</a>
                <a href="resume.php" class="nav-item"><span class="nav-ico" aria-hidden="true">📊</span> Résumé du jour</a>
                <p class="fw-sidebar-section">Santé</p>
                <a href="suivi-sante-unifie.php" class="nav-item"><span class="nav-ico" aria-hidden="true">🏃</span> Suivi Santé</a>
                <a href="ajouter-suivi.php" class="nav-item active"><span class="nav-ico" aria-hidden="true">➕</span> Ajouter activité</a>
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
                    <a href="resume.php">Résumé</a>
                </nav>
                <div class="topbar-user">
                    <span class="user-avatar-initials" aria-hidden="true">MD</span>
                    <span>Marie Dupont</span>
                </div>
            </header>
            <main class="page-body">
                <h1 class="page-title">Ajouter une activité</h1>
                <p class="page-subtitle">Formulaire connecté au contrôleur MVC.</p>
                <section class="card">
                    <div class="card-header">
                        <h2 class="card-title">Nouvelle activité physique ou hydratation</h2>
                        <span class="card-meta">Renseignez tous les champs nécessaires.</span>
                    </div>
                    <form id="suiviForm" action="" method="post">
                        <div id="form-errors" class="notif-panel<?= empty($errors) ? '' : ' error' ?>" style="display: <?= empty($errors) ? 'none' : 'block' ?>;margin-bottom:18px;">
                            <?php if (!empty($errors)): ?>
                                <ul>
                                    <?php foreach ($errors as $error): ?>
                                        <li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                            <?php if ($success): ?>
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    <strong>Succès!</strong> Votre suivi santé a été enregistré.
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="form-row">
                            <div class="form-group mb-0">
                                <label class="form-label" for="date">Date</label>
                                <input type="date" id="date" name="date" class="form-control" value="<?= htmlspecialchars($oldInput['date'] ?? date('Y-m-d'), ENT_QUOTES, 'UTF-8') ?>" max="<?= date('Y-m-d') ?>" required>
                            </div>
                            <div class="form-group mb-0">
                                <label class="form-label" for="type_activite">Type d'activité</label>
                                <input type="text" id="type_activite" name="type_activite" class="form-control" placeholder="Ex. Course, Fitness, Natation" list="activities" value="<?= htmlspecialchars($oldInput['type_activite'] ?? '', ENT_QUOTES, 'UTF-8') ?>" maxlength="100" required>
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
                                <input type="number" id="duree" name="duree" class="form-control" min="1" max="1440" placeholder="30" value="<?= htmlspecialchars($oldInput['duree'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
                            </div>
                            <div class="form-group mb-0">
                                <label class="form-label" for="calories_brulees">Calories brûlées (kcal)</label>
                                <input type="number" id="calories_brulees" name="calories_brulees" class="form-control" min="0" step="0.01" placeholder="0" value="<?= htmlspecialchars($oldInput['calories_brulees'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group mb-0">
                                <label class="form-label" for="intensite">Intensité</label>
                                <select id="intensite" name="intensite" class="form-control" required>
                                    <option value="">-- Sélectionnez --</option>
                                    <option value="faible" <?= ($oldInput['intensite'] ?? '') === 'faible' ? 'selected' : '' ?>>Faible</option>
                                    <option value="moyen" <?= ($oldInput['intensite'] ?? 'moyen') === 'moyen' ? 'selected' : '' ?>>Moyen</option>
                                    <option value="élevé" <?= ($oldInput['intensite'] ?? '') === 'élevé' ? 'selected' : '' ?>>Élevé</option>
                                </select>
                            </div>
                            <div class="form-group mb-0">
                                <label class="form-label" for="quantite_eau">Quantité d'eau (mL)</label>
                                <input type="number" id="quantite_eau" name="quantite_eau" class="form-control" min="0" step="50" placeholder="500" value="<?= htmlspecialchars($oldInput['quantite_eau'] ?? '0', ENT_QUOTES, 'UTF-8') ?>" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="note">Note (optionnelle)</label>
                            <textarea class="form-control" id="note" name="note" rows="3" maxlength="500" placeholder="Remarques supplémentaires..."><?= htmlspecialchars($oldInput['note'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                            <div class="form-text"><small id="noteCount">0</small>/500 caractères</div>
                        </div>
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="./journal-alimentaire.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Retour</a>
                            <button type="reset" class="btn btn-outline-secondary"><i class="fas fa-times"></i> Réinitialiser</button>
                            <button type="submit" class="btn btn-success"><i class="fas fa-check"></i> Enregistrer le Suivi</button>
                        </div>
                    </form>
                </section>
            </main>
        </div>
    </div>
</body>
<script>
// Compteur de caractères pour la note
document.getElementById('note').addEventListener('input', function() {
    document.getElementById('noteCount').textContent = this.value.length;
});
// Validation du formulaire
document.getElementById('suiviForm').addEventListener('submit', function(e) {
    const duree = parseInt(document.getElementById('duree').value);
    const caloriesBrulees = parseFloat(document.getElementById('calories_brulees').value);
    const quantiteEau = parseFloat(document.getElementById('quantite_eau').value);
    if (duree <= 0) {
        e.preventDefault();
        alert('La durée doit être positive.');
        return false;
    }
    if (caloriesBrulees < 0) {
        e.preventDefault();
        alert('Les calories brûlées ne peuvent pas être négatives.');
        return false;
    }
    if (quantiteEau < 0) {
        e.preventDefault();
        alert('La quantité d\'eau ne peut pas être négative.');
        return false;
    }
    return true;
});
</script>
<?php include_once __DIR__ . '/../../view/template/footer.php'; ?>
