<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/auth.php';
requireClient();

// Inclure la configuration et les classes
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../controller/SuiviSanteController.php';
require_once __DIR__ . '/../../controller/JournalController.php';

$pdo = require __DIR__ . '/../../config/config.php';
$suiviController = new SuiviSanteController($pdo);
$journalController = new JournalController($pdo);

// ID utilisateur
$userId = $_SESSION['user_id'] ?? 1;
$date = $_GET['date'] ?? date('Y-m-d');
$errors = [];
$deletionSuccess = false;

// Vérifier le format de la date
if (!\DateTime::createFromFormat('Y-m-d', $date)) {
    $date = date('Y-m-d');
}

// Limitation à aujourd'hui max
if ($date > date('Y-m-d')) {
    $date = date('Y-m-d');
}

// Récupérer les données
$suivis = $suiviController->getDailySuivis($userId, $date);
$report = $suiviController->getDailyHealthReport($userId, $date, $journalController);
$journalEntries = $journalController->findAllByUserAndDate($userId, $date);

// Traitement de la suppression
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $deleteId = (int)$_POST['delete_id'];
    
    // Vérifier que l'utilisateur a accès
    if ($suiviController->userHasAccessToSuivi($userId, $deleteId)) {
        try {
            if ($suiviController->deleteSuivi($deleteId)) {
                $deletionSuccess = true;
                // Rafraîchir les données
                $suivis = $suiviController->getDailySuivis($userId, $date);
                $report = $suiviController->getDailyHealthReport($userId, $date, $journalController);
            }
        } catch (Exception $e) {
            $errors[] = 'Erreur lors de la suppression: ' . $e->getMessage();
        }
    }
}
?>

<?php include_once __DIR__ . '/../../view/template/header.php'; ?>

<div class="container mt-5">
    <!-- En-tête avec sélecteur de date -->
    <div class="row mb-4">
        <div class="col-md-8">
            <h2 class="mb-3">
                <i class="fas fa-heartbeat"></i> Mon Suivi Santé
            </h2>
        </div>
        <div class="col-md-4">
            <form action="" method="GET" class="d-flex gap-2">
                <input
                    type="date"
                    name="date"
                    value="<?php echo htmlspecialchars($date); ?>"
                    max="<?php echo date('Y-m-d'); ?>"
                    onchange="this.form.submit()"
                    class="form-control form-control-sm">
            </form>
        </div>
    </div>

    <!-- Messages -->
    <?php if ($deletionSuccess): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <strong>Succès!</strong> Le suivi a été supprimé.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Erreurs:</strong>
            <ul class="mb-0 mt-2">
                <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Statistiques du jour -->
    <div class="row mb-4">
        <!-- Calories Consommées -->
        <div class="col-md-3">
            <div class="card text-center bg-light border-danger">
                <div class="card-body">
                    <h6 class="card-title text-danger">Calories Consommées</h6>
                    <h3 class="text-danger">
                        <i class="fas fa-utensils"></i>
                        <?php echo number_format((int)$report['calories_consumed'], 0, ',', ' '); ?>
                    </h3>
                    <small class="text-muted">
                        <?php echo count($journalEntries); ?> entrée<?php echo count($journalEntries) > 1 ? 's' : ''; ?>
                    </small>
                </div>
            </div>
        </div>

        <!-- Calories Brûlées -->
        <div class="col-md-3">
            <div class="card text-center bg-light border-success">
                <div class="card-body">
                    <h6 class="card-title text-success">Calories Brûlées</h6>
                    <h3 class="text-success">
                        <i class="fas fa-fire"></i>
                        <?php echo number_format((int)$report['total_calories_burned'], 0, ',', ' '); ?>
                    </h3>
                    <small class="text-muted">
                        <?php echo $report['suivis_count']; ?> activité<?php echo $report['suivis_count'] > 1 ? 's' : ''; ?>
                    </small>
                </div>
            </div>
        </div>

        <!-- Bilan -->
        <div class="col-md-3">
            <div class="card text-center bg-light <?php echo $report['balance'] > 0 ? 'border-warning' : 'border-info'; ?>">
                <div class="card-body">
                    <h6 class="card-title <?php echo $report['balance'] > 0 ? 'text-warning' : 'text-info'; ?>">Bilan Calorique</h6>
                    <h3 class="<?php echo $report['balance'] > 0 ? 'text-warning' : 'text-info'; ?>">
                        <i class="fas fa-balance-scale"></i>
                        <?php echo $report['balance'] >= 0 ? '+' : ''; ?><?php echo number_format((int)$report['balance'], 0, ',', ' '); ?>
                    </h3>
                    <small class="text-muted">
                        <?php echo $report['balance'] > 0 ? 'Surplus' : 'Déficit'; ?>
                    </small>
                </div>
            </div>
        </div>

        <!-- Eau -->
        <div class="col-md-3">
            <div class="card text-center bg-light border-primary">
                <div class="card-body">
                    <h6 class="card-title text-primary">Eau Consommée</h6>
                    <h3 class="text-primary">
                        <i class="fas fa-droplet"></i>
                        <?php echo number_format($report['total_water'] / 1000, 2, ',', ' '); ?>L
                    </h3>
                    <small class="text-muted">
                        <?php
                        $targetWater = 2000; // 2L
                        $waterPercentage = ($report['total_water'] / $targetWater) * 100;
                        echo $waterPercentage >= 100 ? '✓ Objectif atteint' : number_format($waterPercentage, 0) . '% objectif';
                        ?>
                    </small>
                </div>
            </div>
        </div>
    </div>

    <!-- Durée d'activité -->
    <div class="alert alert-info">
        <i class="fas fa-hourglass-half"></i>
        <strong>Durée totale d'activité:</strong> <?php echo $report['total_activity_duration']; ?> minutes
        (<?php echo floor($report['total_activity_duration'] / 60); ?>h <?php echo $report['total_activity_duration'] % 60; ?>min)
    </div>

    <!-- Liste des suivis -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="fas fa-list"></i> Activités Physiques
            </h5>
            <a href="./ajouter-suivi.php" class="btn btn-sm btn-success">
                <i class="fas fa-plus"></i> Ajouter une activité
            </a>
        </div>

        <?php if (empty($suivis)): ?>
            <div class="card-body">
                <div class="alert alert-info mb-0">
                    <i class="fas fa-info-circle"></i>
                    Aucune activité enregistrée pour cette date.
                    <a href="./ajouter-suivi.php" class="alert-link">Ajouter une activité</a>
                </div>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Type d'Activité</th>
                            <th>Durée</th>
                            <th>Intensité</th>
                            <th>Calories Brûlées</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($suivis as $suivi): ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($suivi['type_activite']); ?></strong>
                                    <?php if (!empty($suivi['note'])): ?>
                                        <br>
                                        <small class="text-muted">
                                            <i class="fas fa-sticky-note"></i>
                                            <?php echo htmlspecialchars(substr($suivi['note'], 0, 50)); ?>
                                            <?php echo strlen($suivi['note']) > 50 ? '...' : ''; ?>
                                        </small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <i class="fas fa-hourglass-half"></i>
                                    <?php echo (int)$suivi['duree']; ?> min
                                </td>
                                <td>
                                    <span class="badge
                                        <?php
                                        if ($suivi['intensite'] === 'élevé') {
                                            echo 'bg-danger';
                                        } elseif ($suivi['intensite'] === 'moyen') {
                                            echo 'bg-warning text-dark';
                                        } else {
                                            echo 'bg-info';
                                        }
                                        ?>
                                    ">
                                        <?php echo ucfirst(htmlspecialchars($suivi['intensite'])); ?>
                                    </span>
                                </td>
                                <td>
                                    <i class="fas fa-fire text-danger"></i>
                                    <?php echo number_format((float)$suivi['calories_brulees'], 1, ',', ' '); ?> kcal
                                </td>
                                <td>
                                    <a href="./modifier-suivi.php?id=<?php echo (int)$suivi['id']; ?>" class="btn btn-sm btn-primary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="" method="POST" style="display: inline-block;" onsubmit="return confirm('Êtes-vous sûr?');">
                                        <input type="hidden" name="delete_id" value="<?php echo (int)$suivi['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <!-- Retour -->
    <div class="mt-4">
        <a href="./journal-alimentaire.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Retour au Journal
        </a>
    </div>
</div>

<?php include_once __DIR__ . '/../../view/template/footer.php'; ?>
