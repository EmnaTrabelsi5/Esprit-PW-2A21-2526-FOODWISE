<?php
 /* controller : OffreController.php */

require_once __DIR__ . '/../model/OffreModel.php';
require_once __DIR__ . '/../model/CommandeModel.php';
require_once __DIR__ . '/../config/config.php';



class OffreController {

    private OffreModel      $model;
    private CommandeModel $commandeModel;

    public function __construct() {
        // Auto-fix: Ensure all offers are available
        try {
            $db = config::getConnexion();
            $db->query("UPDATE `Offre` SET statut = 'disponible' WHERE statut = 'expire'");
        } catch (Exception $e) {
            error_log("Warning: Could not update Offre statuses: " . $e->getMessage());
        }
        
        $this->model           = new OffreModel();
        $this->commandeModel = new CommandeModel();
        $this->model->updateExpiredOffers();
    }

    // ── Routeur ──────────────────────────────────────────────
    public function handleRequest(): void {
        $action = $_GET['action'] ?? 'index';

        match ($action) {
            'index'   => $this->index(),
            'show'    => $this->show(),
            'create'  => $this->create(),
            'store'   => $this->store(),
            'edit'    => $this->edit(),
            'update'  => $this->update(),
            'cloture' => $this->cloture(),
            'delete'  => $this->delete(),
            default   => $this->index(),
        };
    }
    public function handleAdminRequest(): void {
    $action = $_GET['action'] ?? 'indexAdmin';

    match ($action) {
        'indexAdmin' => $this->indexAdmin(),
        'show'       => $this->showAdmin(),
        'edit'       => $this->editAdmin(),
        'update'     => $this->updateAdmin(),
        'delete'     => $this->deleteAdmin(),
        'create'     => $this->createAdmin(),
        'store'     => $this->storeAdmin(),
        default      => $this->indexAdmin(),
    };
}

    // ── Liste des offres (front) ─────────────────────────────
    private function index(): void {
        $filtres = [
            'ville'     => trim($_GET['ville']     ?? ''),
            'statut' => trim($_GET['statut'] ?? ''),
            'categorie' => trim($_GET['categorie'] ?? ''),
            'search'    => trim($_GET['search']    ?? ''),
            'tri'       => trim($_GET['tri']       ?? 'recent'),
        ];

        $offres = $this->model->findAll($filtres);
        $offres = $this->model->enrichWithStockStatus($offres);
        $stats       = $this->model->getStats();


        $villes      = CommandeModel::getVilles();
        $commercants = CommandeModel::findAll();

        $stats['critical'] = 0;

        foreach ($offres as $o) {
            if ($o['is_critical']) {
            $stats['critical']++;
            }
        }

    require __DIR__ . '/../view/Offre/front/liste_offre.php';
    }

    // ── Détail d'une offre ───────────────────────────────────
    /*private function show(): void {
        $id    = (int)($_GET['id'] ?? 0);


        $offre = $this->model->findById($id);
        //include 'View/Offre/front/show.php';
        require __DIR__ . '/../view/Offre/front/show.php';

        if (!$offre) {
            $_SESSION['flash_error'] = "Offre introuvable.";
            header('Location: ?route=offres/index');
            exit;
        }

    require __DIR__ . '/../view/Offre/front/show.php';
    }*/
    private function show(): void {
    $id = (int)($_GET['id'] ?? 0);

    $offre = $this->model->findById($id);

    if (!$offre) {
        $_SESSION['flash_error'] = "Offre introuvable.";
        header('Location: ?route=offres/index');
        exit;
    }

    require __DIR__ . '/../view/Offre/front/show.php';
}

    // ── Formulaire de création ───────────────────────────────
    private function create(): void {
        $data        = [];
        $errors      = [];
        $commercants = CommandeModel::findAll('', 'actif');
    require __DIR__ . '/../view/Offre/front/form_offre.php';
    }

    // ── Enregistrer une nouvelle offre ───────────────────────
    private function store(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ?route=offres/index');
            exit;
        }

        $data        = $_POST;
        $errors      = $this->model->validate($data);
        $commercants = CommandeModel::findAll('', 'actif');

        if (!empty($errors)) {
    require __DIR__ . '/../view/Offre/front/form_offre.php';
            return;
        }

        $id = $this->model->create($data);
        if ($id) {
            $_SESSION['flash_success'] = "Offre <strong>{$data['titre']}</strong> publiée avec succès !";
            header('Location: ?route=offres/index');
        } else {
            $_SESSION['flash_error'] = "Erreur lors de la publication.";
    require __DIR__ . '/../view/Offre/front/form_offre.php';
        }
        exit;
    }

    // ── Formulaire de modification ───────────────────────────
    private function edit(): void {
        $id          = (int)($_GET['id'] ?? 0);
        $data        = $this->model->findById($id);
        $errors      = [];
        $commercants = CommandeModel::findAll('', 'actif');

        if (!$data) {
            $_SESSION['flash_error'] = "Offre introuvable.";
            header('Location: ?route=offres/index');
            exit;
        }

    require __DIR__ . '/../view/Offre/front/form_offre.php';
    }

    // ── Mettre à jour une offre ──────────────────────────────
    private function update(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ?route=offres/index');
            exit;
        }

        $id          = (int)($_POST['id'] ?? 0);
        $data        = $_POST;
        $errors      = $this->model->validate($data);
        $commercants = CommandeModel::findAll('', 'actif');

        if (!empty($errors)) {
            $data['id'] = $id;
    require __DIR__ . '/../view/Offre/front/form_offre.php';
            return;
        }

        if ($this->model->update($id, $data)) {
            $_SESSION['flash_success'] = "Offre mise à jour avec succès.";
            header("Location: ?route=offres/show&id={$id}");
        } else {
            $_SESSION['flash_error'] = "Erreur lors de la mise à jour.";
            header("Location: ?route=offres/edit&id={$id}");
        exit;
    }
    }

    // ── Clôturer une offre ───────────────────────────────────
    private function cloture(): void {
        $id = (int)($_GET['id'] ?? 0);

        if ($this->model->cloture($id)) {
            $_SESSION['flash_success'] = "Offre clôturée avec succès.";
        } else {
            $_SESSION['flash_error'] = "Impossible de clôturer cette offre.";
        }

        $ref = $_SERVER['HTTP_REFERER'] ?? 'offreRouter.php?action=index';
        header("Location: $ref");
        exit;
    }

    // ── Supprimer une offre ──────────────────────────────────
    private function delete(): void {
        $id = (int)($_GET['id'] ?? 0);

        if ($this->model->delete($id)) {
            $_SESSION['flash_success'] = "Offre supprimée.";
        } else {
            $_SESSION['flash_error'] = "Impossible de supprimer cette offre.";
        }

        header('Location: ?route=offres/index');
        exit;
    }
    private function indexAdmin(): void {

    $search = trim($_GET['search'] ?? '');

    $offres = $this->model->findAll([
        'search' => $search
    ]);

    $offres = $this->model->enrichWithStockStatus($offres);

    require __DIR__ . '/../view/Offre/back/detail_offre.php';
}
private function deleteAdmin(): void {

    $id = (int)(($_POST['id'] ?? $_GET['id']) ?? 0);

    if ($this->model->delete($id)) {
        $_SESSION['flash_success'] = "Offre supprimée avec succès.";
    } else {
        $_SESSION['flash_error'] = "Erreur lors de la suppression.";
    }

    header('Location: ?route=admin/offres/indexAdmin');
    exit;
}
private function showAdmin(): void {
    $id = (int)($_GET['id'] ?? 0);

    $offre = $this->model->findById($id);

    if (!$offre) {
        $_SESSION['flash_error'] = "Offre introuvable.";
        header('Location: ?route=admin/offres/indexAdmin');
        exit;
    }

    require __DIR__ . '/../view/Offre/back/show_admin.php';
}
private function editAdmin(): void {
    $id = (int)($_GET['id'] ?? 0);

    $data = $this->model->findById($id);
    $errors = [];
    $commercants = CommandeModel::findAll('', 'actif');

    if (!$data) {
        $_SESSION['flash_error'] = "Offre introuvable.";
        header('Location: ?route=admin/offres/indexAdmin');
        exit;
    }

    require __DIR__ . '/../view/Offre/back/form_offre.php';
}

private function updateAdmin(): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: ?route=admin/offres/indexAdmin');
        exit;
    }

    $id = (int)($_POST['id'] ?? 0);
    $data = $_POST;
    $errors = $this->model->validate($data);
    $commercants = CommandeModel::findAll('', 'actif');

    if (!empty($errors)) {
        $data['id'] = $id;
        require __DIR__ . '/../view/Offre/back/form_offre.php';
        return;
    }

    if ($this->model->update($id, $data)) {
        $_SESSION['flash_success'] = "Offre mise à jour.";
    } else {
        $_SESSION['flash_error'] = "Erreur lors de la mise à jour.";
    }

    header('Location: ?route=admin/offres/indexAdmin');
    exit;
}

private function createAdmin(): void {
    $data        = [];
    $errors      = [];
    $commercants = CommandeModel::findAll('', 'actif');
    require __DIR__ . '/../view/Offre/back/form_offre.php';
}

private function storeAdmin(): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: ?route=admin/offres/indexAdmin');
        exit;
    }

    $data        = $_POST;
    $errors      = $this->model->validate($data);
    $commercants = CommandeModel::findAll('', 'actif');

    if (!empty($errors)) {
        require __DIR__ . '/../view/Offre/back/form_offre.php';
        return;
    }

    $id = $this->model->create($data);
    if ($id) {
        $_SESSION['flash_success'] = "Offre <strong>{$data['titre']}</strong> publiée avec succès !";
        header('Location: ?route=admin/offres/indexAdmin');
    } else {
        $_SESSION['flash_error'] = "Erreur lors de la publication.";
        require __DIR__ . '/../view/Offre/back/form_offre.php';
    }
    exit;
}
}

