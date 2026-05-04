<?php
require_once __DIR__ . '/../Model/CommandeModel.php';
require_once __DIR__ . '/../Model/OffreModel.php';
require_once __DIR__ . '/../Config.php';

class CommandeController {
    private CommandeModel $model;
    private OffreModel $offreModel;

    /*public function __construct() {
        // Auto-fix: Ensure all offers are available (handle expired offers)
        try {
            $db = config::getConnexion();
            $db->query("UPDATE `Offre` SET statut = 'disponible' WHERE statut = 'expire'");
        } catch (Exception $e) {
            error_log("Warning: Could not update Offre statuses: " . $e->getMessage());
        }
        $offreModel = new OffreModel();
        $offreModel->updateExpiredOffers();
        $this->model->cancelExpiredOrders();
        
    }*/
        public function __construct() {
    try {
        $db = config::getConnexion();
        $db->query("UPDATE `Offre` SET statut = 'disponible' WHERE statut = 'expire'");
    } catch (Exception $e) {
        error_log("Warning: Could not update Offre statuses: " . $e->getMessage());
    }

    // ✅ INITIALISATION CORRECTE
    $this->model = new CommandeModel();
    $this->offreModel = new OffreModel();

    // ✅ Tes règles métier
    $this->offreModel->updateExpiredOffers();
    $this->model->cancelExpiredOrders(); // 🔥 ton système intelligent
}
        public function passerCommande($data) {

        // 🔥 1. Vérifier disponibilité
        if (!$this->offre->isAvailable($data['offre'], $data['qte'])) {
            die("Offre non disponible !");
        }

        // 🔥 2. Créer commande
        $this->commande->create($data);

        // 🔥 3. Décrémenter stock
        $this->offre->decrementStock($data['offre'], $data['qte']);

        echo "Commande créée avec succès";
    }
    

    public function payer($id_commande) {
        $this->commande->simulatePayment($id_commande);
        echo "Paiement effectué";
    }

    // ── Routeur ──────────────────────────────────────────────
    public function handleRequest(): void {
        $action = $_GET['action'] ?? 'index';

        match ($action) {
            'index'      => $this->index(),
            'create'     => $this->create(),
            'store'      => $this->store(),
            'edit'       => $this->edit(),
            'update'     => $this->update(),
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
foreach ($commandes as &$commande) {
    $prix = CommandeModel::calculateFinalPrice(
        $commande['prix_unitaire'],
        $commande['quantite'],
        $commande['adresse_livraison'] // ⚠️ adapte ici
    );

    $commande['total'] = $prix['total'];

    $commande['total'] = $prix['total'];
    require __DIR__ . '/../View/Commande/front/mes_commandes.php';
}


    }

    // ── Formulaire de création de commande ───────────────────
    private function create(): void {
        $id_offre = (int)($_GET['id_offre'] ?? 0);

        if (!$id_offre) {
            $_SESSION['flash_error'] = "Offre non spécifiée.";
            header('Location: /FOODWISE1/router/offreRouter.php?action=index');
            exit;
        }

        $offreModel = new OffreModel();
        $offre = $offreModel->findById($id_offre);

        if (!$offre) {
            $_SESSION['flash_error'] = "Offre introuvable.";
            header('Location: /FOODWISE1/router/offreRouter.php?action=index');
            exit;
        }

        require __DIR__ . '/../View/Commande/front/form_commande.php';
    }

    // ── Enregistrer une commande ─────────────────────────────
    private function store(): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /FOODWISE1/router/commandeRouter.php?action=index');
        exit;
    }

    $id_offre  = (int)($_POST['id_offre'] ?? 0);
    $quantite  = (int)($_POST['quantite'] ?? 0);
    $id_client = $_SESSION['user_id'] ?? 1;
    $adresse = trim($_POST['adresse'] ?? '');
    $telephone = trim($_POST['telephone'] ?? '');
    $mode_paiement = $_POST['mode_paiement'] ?? '';
    $note = trim($_POST['note'] ?? '');

    // 🔹 Validation
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

    // 🔥 👉 ICI : Vérification métier
    $offreModel = new OffreModel();
    $offreModel->updateExpiredOffers(); // important

    $offre = $offreModel->findById($id_offre);

    if (!$offre) {
        $_SESSION['flash_error'] = "Offre introuvable.";
        header('Location: /FOODWISE1/router/offreRouter.php?action=index');
        exit;
    }

    if ($offre['statut'] !== 'disponible') {
        $_SESSION['flash_error'] = "Offre non disponible.";
        header('Location: /FOODWISE1/router/offreRouter.php?action=index'); 
        exit;
    }

    if ($offre['stock'] < $quantite) {
        $_SESSION['flash_error'] = "Stock insuffisant.";
        header("Location: commande.php?action=create&id_offre={$id_offre}");
        exit;
    }

    if (strtotime($offre['date_expiration']) <= time()) {
        $_SESSION['flash_error'] = "Offre expirée.";
        header('Location: /FOODWISE1/router/offreRouter.php?action=index');
        exit;
    }

    // 🔥 Création commande
    $result = CommandeModel::create(
        $id_offre, 
        $id_client, 
        $quantite, 
        $adresse, 
        $telephone, 
        $mode_paiement, 
        $note
    );

    if ($result) {

        // 🔥 IMPORTANT : décrémenter le stock
        OffreModel::decrementStock($id_offre, $quantite);

        $_SESSION['flash_success'] = "Commande créée avec succès !";
        header('Location: /FOODWISE1/router/commandeRouter.php?action=index');

    } else {
        $_SESSION['flash_error'] = "Erreur lors de la création de la commande.";
        header("Location: /FOODWISE1/commande.php?action=create&id_offre={$id_offre}");
    }

    exit;
}

    // ── Formulaire de modification de commande ────────────────
    private function edit(): void {
        $id_commande = (int)($_GET['id'] ?? 0);
        $id_client = $_SESSION['user_id'] ?? 1;

        if (!$id_commande) {
            $_SESSION['flash_error'] = "Commande non spécifiée.";
            header('Location: /FOODWISE1/router/commandeRouter.php?action=index');
            exit;
        }

        // Récupérer la commande
        $commande = CommandeModel::getById($id_commande, $id_client);

        if (!$commande) {
            $_SESSION['flash_error'] = "Commande introuvable.";
            header('Location: /FOODWISE1/router/commandeRouter.php?action=index');
            exit;
        }

        if ($commande['statut'] !== 'en_attente') {
            $_SESSION['flash_error'] = "Cette commande ne peut plus être modifiée.";
            header('Location: /FOODWISE1/router/commandeRouter.php?action=index');
            exit;
        }

        // Récupérer l'offre associée
        $offreModel = new OffreModel();
        $offre = $offreModel->findById($commande['id_offre']);

        if (!$offre) {
            $_SESSION['flash_error'] = "Offre associée introuvable.";
            header('Location: /FOODWISE1/router/commandeRouter.php?action=index');
            exit;
        }

        require __DIR__ . '/../View/Commande/front/form_commande.php';
    }

    // ── Mettre à jour une commande ────────────────────────────
    private function update(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: /FOODWISE1/router/commandeRouter.php?action=index');
            exit;
        }

        $id_commande = (int)($_POST['id_commande'] ?? 0);
        $quantite = (int)($_POST['quantite'] ?? 0);
        $id_client = $_SESSION['user_id'] ?? 1;
        $adresse = trim($_POST['adresse'] ?? '');
        $telephone = trim($_POST['telephone'] ?? '');
        $mode_paiement = $_POST['mode_paiement'] ?? '';
        $note = trim($_POST['note'] ?? '');

        if (!$id_commande) {
            $_SESSION['flash_error'] = "Commande non spécifiée.";
            header('Location: /FOODWISE1/router/commandeRouter.php?action=index');
            exit;
        }

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
            header("Location: /FOODWISE1/commande.php?action=edit&id={$id_commande}");
            exit;
        }

        // Mettre à jour la commande
        $result = CommandeModel::update($id_commande, $id_client, $quantite, $adresse, $telephone, $mode_paiement, $note);

        if ($result) {
            $_SESSION['flash_success'] = "Commande modifiée avec succès !";
            header('Location: /FOODWISE1/router/commandeRouter.php?action=index');
        } else {
            $_SESSION['flash_error'] = "Erreur lors de la modification de la commande.";
            header("Location: /FOODWISE1/commande.php?action=edit&id={$id_commande}");
        }
        exit;
    }

    // ── Annuler une commande ──────────────────────────────────
    private function cancel(): void {
        $id = (int)($_GET['id'] ?? 0);
        $id_client = $_SESSION['user_id'] ?? 1; // TEMP

        if (!$id) {
            $_SESSION['flash_error'] = "Commande non spécifiée.";
            header('Location: /FOODWISE1/router/commandeRouter.php?action=index');
            exit;
        }

        $result = CommandeModel::cancel($id);

        if ($result) {
            $_SESSION['flash_success'] = "Commande annulée avec succès.";
        } else {
            $_SESSION['flash_error'] = "Impossible d'annuler cette commande.";
        }

        header('Location: /FOODWISE1/router/commandeRouter.php?action=index');
        exit;
    }

    // ── Payer une commande ────────────────────────────────────
/*private function pay(): void {
    $id = (int)($_GET['id'] ?? 0);

    if (!$id) {
        $_SESSION['flash_error'] = "Commande non spécifiée.";
        header('Location: /FOODWISE1/router/commandeRouter.php?action=index');
        exit;
    }

    // 🔥 Vérification AVANT paiement
    $commande = CommandeModel::getByIdSimple($id);

    if (!$commande) {
        $_SESSION['flash_error'] = "Commande introuvable.";
        header('Location: /FOODWISE1/router/commandeRouter.php?action=index');
        exit;
    }

    if ($commande['paiement_status'] === 'paye') {
        $_SESSION['flash_error'] = "Déjà payé.";
        header('Location: /FOODWISE1/router/commandeRouter.php?action=index');
        exit;
    }

    // 🔥 Paiement
    $result = CommandeModel::simulatePayment($id);

    if ($result) {
        $_SESSION['flash_success'] = "Paiement effectué avec succès !";
    } else {
        $_SESSION['flash_error'] = "Erreur lors du paiement.";
    }

    header('Location: /FOODWISE1/router/commandeRouter.php?action=index');
    exit;
}*/
private function pay(): void {

    $id = (int)($_GET['id'] ?? 0);

    if (!$id) {
        $_SESSION['flash_error'] = "Commande non spécifiée.";
        header('Location: /FOODWISE1/router/commandeRouter.php?action=index');
        exit;
    }

    $commande = CommandeModel::getByIdSimple($id);

    if (!$commande) {
        $_SESSION['flash_error'] = "Commande introuvable.";
        header('Location: /FOODWISE1/router/commandeRouter.php?action=index');
        exit;
    }

    if ($commande['paiement_status'] === 'paye') {
        $_SESSION['flash_error'] = "Déjà payé.";
        header('Location: /FOODWISE1/router/commandeRouter.php?action=index');
        exit;
    }

    $result = CommandeModel::simulatePayment($id, $commande['mode_paiement']);

    if ($result) {
        $_SESSION['flash_success'] = "Paiement simulé avec succès !";
    } else {
        $_SESSION['flash_error'] = "Erreur lors du paiement.";
    }

    header('Location: /FOODWISE1/router/commandeRouter.php?action=index');
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
            header('Location: commandeRouter.php?action=adminIndex');
            exit;
        }

        $result = CommandeModel::updateStatus($id, $status);

        if ($result) {
            $_SESSION['flash_success'] = "Statut mis à jour avec succès.";
        } else {
            $_SESSION['flash_error'] = "Erreur lors de la mise à jour.";
        }

        header('Location: commandeRouter.php?action=adminIndex');
        exit;
    }
    
}
