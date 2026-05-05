<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/auth.php';
requireAdmin();

// Inclure la configuration et les classes
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../controller/SuiviSanteController.php';

$pdo = require __DIR__ . '/../../config/config.php';
$controller = new SuiviSanteController($pdo);

// Variables
$page = (int)($_GET['page'] ?? 1);
$limit = 20;
$offset = ($page - 1) * $limit;
$errors = [];
$success = false;
$filter = $_GET['filter'] ?? 'all';
$search = trim($_GET['search'] ?? '');

// Récupérer les suivis (avec pagination simple)
$allSuivis = $controller->getAllSuivis();
$totalSuivis = count($allSuivis);
$totalPages = ceil($totalSuivis / $limit);

// Filtrer et paginer
$suivis = array_slice($allSuivis, $offset, $limit);

// Traitement de la suppression
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'delete' && isset($_POST['delete_id'])) {
        $deleteId = (int)$_POST['delete_id'];
        try {
            if ($controller->deleteSuivi($deleteId)) {
                $success = true;
                // Rafraîchir les données
                $allSuivis = $controller->getAllSuivis();
                $totalSuivis = count($allSuivis);
                $totalPages = ceil($totalSuivis / $limit);
                $suivis = array_slice($allSuivis, $offset, $limit);
            }
        } catch (Exception $e) {
            $errors[] = 'Erreur lors de la suppression: ' . $e->getMessage();
        }
    }
}
?>

<?php include_once __DIR__ . '/../../view/template/header.php'; ?>

<div class="container-fluid mt-4">
    <!-- En-tête -->
    <div class="row mb-4">
        <div class="col">
            <h2 class="mb-0">
                <i class="fas fa-heartbeat"></i> Gestion des Suivis Santé
            </h2>
        </div>
        <div class="col text-end">
            <a href="./dashboard-suivi.php" class="btn btn-info">
                <i class="fas fa-chart-line"></i> Voir Statistiques
            </a>
        </div>
    </div>

    <!-- Messages -->
    <?php if ($success): ?>
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

    <!-- Filtres et Recherche -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Filtres et Recherche</h5>
        </div>
        <div class="card-body">
            <form action="" method="GET" class="row g-3">
                <div class="col-md-6">
                    <label for="search" class="form-label">Rechercher par utilisateur ou activité</label>
                    <input
                        type="text"
                        class="form-control"
                        id="search"
                        name="search"
                        placeholder="Recherche..."
                        value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <div class="col-md-3">
                    <label for="filter" class="form-label">Type d'activité</label>
                    <select class="form-select" id="filter" name="filter">
                        <option value="all">Tous</option>
                        <?php
                        $activities = [];
                        foreach ($allSuivis as $suivi) {
                            $activities[] = $suivi['type_activite'];
                        }
                        $activities = array_unique($activities);
                        sort($activities);
                        foreach ($activities as $activity):
                        ?>
                            <option value="<?php echo htmlspecialchars($activity); ?>"
                                <?php echo $filter === $activity ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($activity); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label>&nbsp;</label>
                    <div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Rechercher
                        </button>
                        <a href="./manage-suivi.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Réinitialiser
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Tableau des suivis -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="fas fa-table"></i> Liste des Suivis
                <span class="badge bg-success"><?php echo $totalSuivis; ?> suivi<?php echo $totalSuivis > 1 ? 's' : ''; ?></span>
            </h5>
        </div>

        <?php if (empty($suivis)): ?>
            <div class="card-body">
                <div class="alert alert-info mb-0">
                    <i class="fas fa-info-circle"></i>
                    Aucun suivi à afficher.
                </div>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover table-sm mb-0 sortable-table">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>User ID</th>
                            <th>Date</th>
                            <th>Type d'Activité</th>
                            <th>Durée</th>
                            <th>Intensité</th>
                            <th>Calories</th>
                            <th>Eau</th>
                            <th>Créé</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($suivis as $suivi): ?>
                            <tr>
                                <td>
                                    <span class="badge bg-secondary">
                                        <?php echo (int)$suivi['id']; ?>
                                    </span>
                                </td>
                                <td>
                                    <i class="fas fa-user"></i> <?php echo (int)$suivi['user_id']; ?>
                                </td>
                                <td>
                                    <i class="fas fa-calendar"></i>
                                    <?php echo date('d/m/Y', strtotime($suivi['date'])); ?>
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($suivi['type_activite']); ?></strong>
                                    <?php if (!empty($suivi['note'])): ?>
                                        <br>
                                        <small class="text-muted d-block text-truncate" title="<?php echo htmlspecialchars($suivi['note']); ?>">
                                            note: <?php echo htmlspecialchars(substr($suivi['note'], 0, 30)); ?>...
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
                                    <?php echo number_format((float)$suivi['calories_brulees'], 1); ?> kcal
                                </td>
                                <td>
                                    <i class="fas fa-droplet text-primary"></i>
                                    <?php echo number_format((float)$suivi['quantite_eau'] / 1000, 2); ?> L
                                </td>
                                <td>
                                    <small class="text-muted">
                                        <?php echo date('d/m/y H:i', strtotime($suivi['created_at'])); ?>
                                    </small>
                                </td>
                                <td>
                                    <a href="./modifier-suivi.php?id=<?php echo (int)$suivi['id']; ?>" class="btn btn-sm btn-primary" title="Modifier">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="" method="POST" style="display: inline-block;" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce suivi?');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="delete_id" value="<?php echo (int)$suivi['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-danger" title="Supprimer">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <div class="card-footer">
                    <nav aria-label="Page navigation">
                        <ul class="pagination justify-content-center mb-0">
                            <?php if ($page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=1">Première</a>
                                </li>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $page - 1; ?>">Précédente</a>
                                </li>
                            <?php endif; ?>

                            <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                                <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                            <?php endfor; ?>

                            <?php if ($page < $totalPages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $page + 1; ?>">Suivante</a>
                                </li>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $totalPages; ?>">Dernière</a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                    <div class="text-center text-muted small">
                        Page <?php echo $page; ?> / <?php echo $totalPages; ?>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <!-- Retour -->
    <div class="mt-4 mb-4">
        <a href="./dashboard-admin.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Retour au Dashboard
        </a>
    </div>
</div>

<script src="../../public/js/table-interactions.js"></script>

<?php include_once __DIR__ . '/../../view/template/footer.php'; ?>
