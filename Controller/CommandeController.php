<?php
require_once __DIR__ . '/../Model/CommandeModel.php';
require_once __DIR__ . '/../Model/OffreModel.php';

class CommandeController {

    // ── Routeur ──────────────────────────────────────────────
    public function handleRequest(): void {
        $action = $_GET['action'] ?? 'index';

        match ($action) {
            'index'      => $this->index(),
            'create'     => $this->create(),
            'store'      => $this->store(),
            'cancel'     => $this->cancel(),
            'pay'        => $this->pay(),
            'adminIndex' => $this->adminIndex(),
            'adminUpdate' => $this->adminUpdate(),
            default      => $this->index(),
        };
    }

    // ── Liste des commandes (front) ──────────────────────────
    private function index(): void {
        // TEMP: Remplacer par session utilisateur
        $id_client = $_SESSION['user_id'] ?? 1;

        $commandes = CommandeModel::getByClient($id_client);

        require __DIR__ . '/../View/Commande/front/mes_commandes.php';
    }

    // ── Formulaire de création de commande ───────────────────
    private function create(): void {
        $id_offre = (int)($_GET['id_offre'] ?? 0);

        if (!$id_offre) {
            $_SESSION['flash_error'] = "Offre non spécifiée.";
            header('Location: offre.php?action=index');
            exit;
        }

        $offreModel = new OffreModel();
        $offre = $offreModel->findById($id_offre);

        if (!$offre) {
            $_SESSION['flash_error'] = "Offre introuvable.";
            header('Location: offre.php?action=index');
            exit;
        }

        require __DIR__ . '/../View/Commande/front/form_commande.php';
    }

    // ── Enregistrer une commande ─────────────────────────────
    private function store(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /FOODWISE1/commande.php?action=create');
            exit;
        }

        $id_offre  = (int)($_POST['id_offre'] ?? 0);
        $quantite  = (int)($_POST['quantite'] ?? 0);
        $id_client = $_SESSION['user_id'] ?? 1; // TEMP: Remplacer par session
        $adresse = trim($_POST['adresse'] ?? '');
        $telephone = trim($_POST['telephone'] ?? '');
        $mode_paiement = $_POST['mode_paiement'] ?? '';
        $note = trim($_POST['note'] ?? '');

        // Validation
        $errors = [];
        if ($quantite <= 0) {
            $errors[] = "La quantité doit être supérieure à 0.";
        }
        if (empty($adresse)) {
            $errors[] = "L'adresse de livraison est requise.";
        }
        if (empty($telephone) || !preg_match('/^[0-9]{8}$/', $telephone)) {
            $errors[] = "Le numéro de téléphone doit contenir 8 chiffres.";
        }
        if (empty($mode_paiement)) {
            $errors[] = "Le mode de paiement est requis.";
        }

        if (!empty($errors)) {
            $_SESSION['flash_error'] = implode('<br>', $errors);
            header("Location: /FOODWISE1/commande.php?action=create&id_offre={$id_offre}");
            exit;
        }

        // Créer la commande
        $result = CommandeModel::create($id_offre, $id_client, $quantite, $adresse, $telephone, $mode_paiement, $note);

        if ($result) {
            $_SESSION['flash_success'] = "Commande créée avec succès !";
            header('Location: /FOODWISE1/commande.php?action=index');
        } else {
            $_SESSION['flash_error'] = "Erreur lors de la création de la commande. Stock insuffisant ou offre indisponible.";
            header("Location: /FOODWISE1/commande.php?action=create&id_offre={$id_offre}");
        }
        exit;
    }

    // ── Annuler une commande ──────────────────────────────────
    private function cancel(): void {
        $id = (int)($_GET['id'] ?? 0);
        $id_client = $_SESSION['user_id'] ?? 1; // TEMP

        if (!$id) {
            $_SESSION['flash_error'] = "Commande non spécifiée.";
            header('Location: commande.php?action=index');
            exit;
        }

        $result = CommandeModel::cancel($id);

        if ($result) {
            $_SESSION['flash_success'] = "Commande annulée avec succès.";
        } else {
            $_SESSION['flash_error'] = "Impossible d'annuler cette commande.";
        }

        header('Location: commande.php?action=index');
        exit;
    }

    // ── Payer une commande ────────────────────────────────────
    private function pay(): void {
        $id = (int)($_GET['id'] ?? 0);

        if (!$id) {
            $_SESSION['flash_error'] = "Commande non spécifiée.";
            header('Location: commande.php?action=index');
            exit;
        }

        // Simulation de paiement
        $result = CommandeModel::pay($id);

        if ($result) {
            $_SESSION['flash_success'] = "Paiement effectué avec succès !";
        } else {
            $_SESSION['flash_error'] = "Erreur lors du paiement.";
        }

        header('Location: commande.php?action=index');
        exit;
    }

    // ── Liste des commandes (admin) ──────────────────────────
    private function adminIndex(): void {
        $commandes = CommandeModel::getAll();

        require __DIR__ . '/../View/Commande/back/commande_admin.php';
    }

    // ── Mettre à jour le statut (admin) ──────────────────────
    private function adminUpdate(): void {
        $id     = (int)($_GET['id'] ?? 0);
        $status = $_GET['status'] ?? '';

        if (!$id || !in_array($status, ['en_attente', 'confirme', 'annule'])) {
            $_SESSION['flash_error'] = "Paramètres invalides.";
            header('Location: commande.php?action=adminIndex');
            exit;
        }

        $result = CommandeModel::updateStatus($id, $status);

        if ($result) {
            $_SESSION['flash_success'] = "Statut mis à jour avec succès.";
        } else {
            $_SESSION['flash_error'] = "Erreur lors de la mise à jour.";
        }

        header('Location: commande.php?action=adminIndex');
        exit;
    }
}
