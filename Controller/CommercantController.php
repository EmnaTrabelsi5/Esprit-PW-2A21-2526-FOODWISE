<?php

require_once __DIR__ . '/../Model/CommercantModel.php';
require_once __DIR__ . '/../Model/OffreModel.php';

class CommercantController {

    private CommercantModel $model;
    private OffreModel      $offreModel;

    public function __construct() {
        $this->model      = new CommercantModel();
        $this->offreModel = new OffreModel();
    }

    // ── Routeur principal ────────────────────────────────────
    public function handleRequest(): void {
        $action = $_GET['action'] ?? 'index';

        match ($action) {
            'index'   => $this->index(),
            'show'    => $this->show(),
            'create'  => $this->create(),
            'store'   => $this->store(),
            'edit'    => $this->edit(),
            'update'  => $this->update(),
            'delete'  => $this->delete(),
            default   => $this->index(),
        };
    }

    // ── Liste des commerçants ────────────────────────────────
    private function index(): void {
        $ville  = trim($_GET['ville']  ?? '');
        $statut = trim($_GET['statut'] ?? '');
        $villes = $this->model->getVilles();

        $commercants = $this->model->findAll($ville, $statut);

        require __DIR__ . '/../views/commercant/index.php';
    }

    // ── Détail d'un commerçant ───────────────────────────────
public function show() {
    if (!isset($_GET['id'])) {
        die("ID manquant");
    }

    $id = $_GET['id'];

    // 🔴 THIS is the important part
    $commercant = $this->model->getById($id);

    require __DIR__ . '/../views/commercant/show.php';
}

    // ── Formulaire d'inscription ─────────────────────────────
    private function create(): void {
        $data   = [];
        $errors = [];
        require __DIR__ . '/../views/commercant/form.php';
    }

    // ── Enregistrer un nouveau commerçant ────────────────────
    private function store(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: commercant.php?action=create');
            exit;
        }

        $data   = $_POST;
        $errors = $this->model->validate($data);

        // Vérifier email unique
        if (empty($errors['email']) && $this->model->findByEmail($data['email'])) {
            $errors['email'] = "Cet email est déjà utilisé par un autre commerçant.";
        }

        if (!empty($errors)) {
            require __DIR__ . '/../views/commercant/form.php';
            return;
        }

        $id = $this->model->create($data);
        if ($id) {
            $_SESSION['flash_success'] = "Commerçant <strong>{$data['nom']}</strong> inscrit avec succès !";
            header("Location: commercant.php?action=show&id={$id}");
        } else {
            $_SESSION['flash_error'] = "Une erreur est survenue lors de l'enregistrement.";
            require __DIR__ . '/../views/commercant/form.php';
        }
        exit;
    }

    // ── Formulaire de modification ───────────────────────────
    private function edit(): void {
        $id         = (int)($_GET['id'] ?? 0);
        $data       = $this->model->findById($id);
        $errors     = [];

        if (!$data) {
            $_SESSION['flash_error'] = "Commerçant introuvable.";
            header('Location: commercant.php?action=index');
            exit;
        }

        require __DIR__ . '/../views/commercant/form.php';
    }

    // ── Mettre à jour un commerçant ──────────────────────────
    private function update(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: commercant.php?action=index');
            exit;
        }

        $id     = (int)($_POST['id'] ?? 0);
        $data   = $_POST;
        $errors = $this->model->validate($data);

        // Email unique sauf pour le même commerçant
        if (empty($errors['email'])) {
            $existing = $this->model->findByEmail($data['email']);
            if ($existing && (int)$existing['id'] !== $id) {
                $errors['email'] = "Cet email est déjà utilisé.";
            }
        }

        if (!empty($errors)) {
            require __DIR__ . '/../views/commercant/form.php';
            return;
        }

        if ($this->model->update($id, $data)) {
            $_SESSION['flash_success'] = "Commerçant mis à jour avec succès.";
            header("Location: commercant.php?action=show&id={$id}");
        } else {
            $_SESSION['flash_error'] = "Erreur lors de la mise à jour.";
            header("Location: commercant.php?action=edit&id={$id}");
        }
        exit;
    }

    // ── Supprimer un commerçant ──────────────────────────────
    private function delete(): void {
        $id = (int)($_GET['id'] ?? 0);

        if ($this->model->delete($id)) {
            $_SESSION['flash_success'] = "Commerçant supprimé.";
        } else {
            $_SESSION['flash_error'] = "Impossible de supprimer ce commerçant.";
        }

        header('Location: commercant.php?action=index');
        exit;
    }
}
