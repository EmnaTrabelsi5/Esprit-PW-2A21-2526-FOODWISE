<?php

require_once __DIR__ . '/../Model/OffreModel.php';
require_once __DIR__ . '/../Model/CommandeModel.php';
/*require_once "../Config.php";*/



class OffreController {

    private OffreModel      $model;
    private CommandeModel $commandeModel;

    public function __construct() {
        $this->model           = new OffreModel();
        $this->commandeModel = new CommandeModel();
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

    // ── Liste des offres (front) ─────────────────────────────
    private function index(): void {
        $filtres = [
            'ville'     => trim($_GET['ville']     ?? ''),
            'statut' => trim($_GET['statut'] ?? ''),
            'categorie' => trim($_GET['categorie'] ?? ''),
            'search'    => trim($_GET['search']    ?? ''),
            'tri'       => trim($_GET['tri']       ?? 'recent'),
        ];

        $offres      = $this->model->findAll($filtres);
        $stats       = $this->model->getStats();
        $villes      = CommandeModel::getVilles();
        $commercants = CommandeModel::findAll();

    require __DIR__ . '/../View/Offre/front/liste_offre.php';
    }

    // ── Détail d'une offre ───────────────────────────────────
    private function show(): void {
        $id    = (int)($_GET['id'] ?? 0);


        $offre = $this->model->findById($id);
        //include 'View/Offre/front/show.php';
        require __DIR__ . '/../View/Offre/front/show.php';

        if (!$offre) {
            $_SESSION['flash_error'] = "Offre introuvable.";
            header('Location: offre.php?action=index');
            exit;
        }

    require __DIR__ . '/../View/Offre/front/show.php';
    }

    // ── Formulaire de création ───────────────────────────────
    private function create(): void {
        $data        = [];
        $errors      = [];
        $commercants = CommandeModel::findAll('', 'actif');
    require __DIR__ . '/../View/Offre/front/form_offre.php';
    }

    // ── Enregistrer une nouvelle offre ───────────────────────
    private function store(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: offre.php?action=create');
            exit;
        }

        $data        = $_POST;
        $errors      = $this->model->validate($data);
        $commercants = CommandeModel::findAll('', 'actif');

        if (!empty($errors)) {
    require __DIR__ . '/../View/Offre/front/form_offre.php';
            return;
        }

        $id = $this->model->create($data);
        if ($id) {
            $_SESSION['flash_success'] = "Offre <strong>{$data['titre']}</strong> publiée avec succès !";
            header("Location: offre.php?action=show&id={$id}");
        } else {
            $_SESSION['flash_error'] = "Erreur lors de la publication.";
    require __DIR__ . '/../View/Offre/front/form_offre.php';
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
            header('Location: offre.php?action=index');
            exit;
        }

    require __DIR__ . '/../View/Offre/front/form_offre.php';
    }

    // ── Mettre à jour une offre ──────────────────────────────
    private function update(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: offre.php?action=index');
            exit;
        }

        $id          = (int)($_POST['id'] ?? 0);
        $data        = $_POST;
        $errors      = $this->model->validate($data);
        $commercants = CommandeModel::findAll('', 'actif');

        if (!empty($errors)) {
            $data['id'] = $id;
    require __DIR__ . '/../View/Offre/front/form_offre.php';
            return;
        }

        if ($this->model->update($id, $data)) {
            $_SESSION['flash_success'] = "Offre mise à jour avec succès.";
            header("Location: offre.php?action=show&id={$id}");
        } else {
            $_SESSION['flash_error'] = "Erreur lors de la mise à jour.";
            header("Location: offre.php?action=edit&id={$id}");
        }
        exit;
    }

    // ── Clôturer une offre ───────────────────────────────────
    private function cloture(): void {
        $id = (int)($_GET['id'] ?? 0);

        if ($this->model->cloture($id)) {
            $_SESSION['flash_success'] = "Offre clôturée avec succès.";
        } else {
            $_SESSION['flash_error'] = "Impossible de clôturer cette offre.";
        }

        $ref = $_SERVER['HTTP_REFERER'] ?? 'offre.php?action=index';
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

        header('Location: offre.php?action=index');
        exit;
    }
}
