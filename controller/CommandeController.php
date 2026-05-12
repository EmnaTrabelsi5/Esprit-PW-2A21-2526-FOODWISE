<?php
/* controller :CommandeController.php*/
require_once __DIR__ . '/../model/CommandeModel.php';
require_once __DIR__ . '/../model/OffreModel.php';
require_once __DIR__ . '/../model/Recette.php';
require_once __DIR__ . '/../config/config.php';

class CommandeController {

    public function __construct() {
        // Auto-fix: Ensure all offers are available (handle expired offers)
        try {
            $db = config::getConnexion();
            $db->query("UPDATE `Offre` SET statut = 'disponible' WHERE statut = 'expire'");
        } catch (Exception $e) {
            error_log("Warning: Could not update Offre statuses: " . $e->getMessage());
        }
        $offreModel = new OffreModel();
        $offreModel->updateExpiredOffers();
    }
        public function passerCommande($data) {

        // ?? 1. V�rifier disponibilit�
        if (!$this->offre->isAvailable($data['offre'], $data['qte'])) {
            die("Offre non disponible !");
        }

        // ?? 2. Cr�er commande
        $this->commande->create($data);

        // ?? 3. D�cr�menter stock
        $this->offre->decrementStock($data['offre'], $data['qte']);

        echo "Commande cr��e avec succ�s";
    }

    public function payer($id_commande) {
        $this->commande->simulatePayment($id_commande);
        echo "Paiement effectu�";
    }

    // -- Routeur ----------------------------------------------
    public function handleRequest(): void {
        $action = $_GET['action'] ?? 'index';

        match ($action) {
            'index'       => $this->index(),
            'create'      => $this->create(),
            'createBulk'  => $this->createBulk(),
            'store'       => $this->store(),
            'storeBulk'   => $this->storeBulk(),
            'edit'        => $this->edit(),
            'update'      => $this->update(),
            'cancel'      => $this->cancel(),
            'pay'         => $this->pay(),
            'adminIndex'  => $this->adminIndex(),
            'adminUpdate' => $this->adminUpdate(),
            default       => $this->index(),
        };
    }

    public function handleAdminRequest(): void {
        $action = $_GET['action'] ?? 'indexAdmin';

        match ($action) {
            'indexAdmin' => $this->adminIndex(),
            'show'       => $this->adminShow(),  // Assuming it exists or add if needed
            default      => $this->adminIndex(),
        };
    }

    // -- Liste des commandes (front) --------------------------
    private function index(): void {
        // TEMP: Remplacer par session utilisateur
        $id_client = $_SESSION['user_id'] ?? 1;

        $commandes = CommandeModel::getByClient($id_client);

        require __DIR__ . '/../view/Commande/front/mes_commandes.php';
    }

    // -- Formulaire de cr�ation de commande -------------------
    private function create(): void {
        $id_offre = (int)($_GET['id_offre'] ?? 0);

        if (!$id_offre) {
            $_SESSION['flash_error'] = "Offre non sp�cifi�e.";
            header('Location: ?route=offreRouter.php?action=index');
            exit;
        }

        $offreModel = new OffreModel();
        $offre = $offreModel->findById($id_offre);

        if (!$offre) {
            $_SESSION['flash_error'] = "Offre introuvable.";
            header('Location: ?route=offreRouter.php?action=index');
            exit;
        }

        require __DIR__ . '/../view/Commande/front/form_commande.php';
    }

    private function createBulk(): void {
        $id_recette = (int)($_GET['id_recette'] ?? 0);

        if (!$id_recette) {
            $_SESSION['flash_error'] = "Recette non spécifiée.";
            header('Location: ?route=recettes');
            exit;
        }

        $recette = Recette::getById($id_recette);
        if (!$recette) {
            $_SESSION['flash_error'] = 'Recette introuvable.';
            header('Location: ?route=recettes');
            exit;
        }

        $courses = Recette::getOffresParRecette($id_recette);
        if (empty($courses['avec_offre'])) {
            $_SESSION['flash_error'] = "Aucune offre disponible pour cette recette.";
            header("Location: index.php?url=recettes/{$id_recette}/courses");
            exit;
        }

        $pageTitle  = 'Commander toutes les offres';
        $activeNav  = 'recettes';
        $backoffice = false;
        $avecOffre  = $courses['avec_offre'];
        $coutTotal  = $courses['cout_total'];

        require __DIR__ . '/../view/Commande/front/form_commande_bulk.php';
    }

    private function storeBulk(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?route=commandes/index');
            exit;
        }

        $id_recette = (int)($_GET['id_recette'] ?? 0);
        $id_client  = $_SESSION['user_id'] ?? 1;
        $adresse    = trim($_POST['adresse'] ?? '');
        $telephone  = trim($_POST['telephone'] ?? '');
        $mode_paiement = $_POST['mode_paiement'] ?? '';
        $note = trim($_POST['note'] ?? '');
        $articles = $_POST['offers'] ?? [];

        $errors = [];
        if (empty($articles) || !is_array($articles)) {
            $errors[] = "Aucune offre sélectionnée.";
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

        $validArticles = [];
        foreach ($articles as $item) {
            $id_offre = (int)($item['id_offre'] ?? 0);
            $quantite = (int)($item['quantite'] ?? 0);
            if ($id_offre && $quantite > 0) {
                $validArticles[] = ['id_offre' => $id_offre, 'quantite' => $quantite];
            }
        }

        if (empty($validArticles)) {
            $errors[] = "Aucune offre valide à commander.";
        }

        if (!empty($errors)) {
            $_SESSION['flash_error'] = implode('<br>', $errors);
            header("Location: index.php?route=commandes/createBulk&id_recette={$id_recette}");
            exit;
        }

        $result = CommandeModel::createMultiple(
            $validArticles,
            $id_client,
            $adresse,
            $telephone,
            $mode_paiement,
            $note
        );

        if ($result) {
            $_SESSION['flash_success'] = "Commande groupée créée avec succès !";
            header('Location: ?route=commandes/index');
        } else {
            $_SESSION['flash_error'] = "Erreur lors de la création de la commande groupée.";
            header("Location: index.php?route=commandes/createBulk&id_recette={$id_recette}");
        }
        exit;
    }

    // -- Enregistrer une commande -----------------------------
    private function store(): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php?route=commandes/index');
        exit;
    }

    $id_offre  = (int)($_POST['id_offre'] ?? 0);
    $quantite  = (int)($_POST['quantite'] ?? 0);
    $id_client = $_SESSION['user_id'] ?? 1;
    $adresse = trim($_POST['adresse'] ?? '');
    $telephone = trim($_POST['telephone'] ?? '');
    $mode_paiement = $_POST['mode_paiement'] ?? '';
    $note = trim($_POST['note'] ?? '');

    // ?? Validation
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
        header("Location: index.php?route=commandes/create&id_offre={$id_offre}");
        exit;
    }

    // ?? ?? ICI : V�rification m�tier
    $offreModel = new OffreModel();
    $offreModel->updateExpiredOffers(); // important

    $offre = $offreModel->findById($id_offre);

    if (!$offre) {
        $_SESSION['flash_error'] = "Offre introuvable.";
        header('Location: ?route=offreRouter.php?action=index');
        exit;
    }

    if ($offre['statut'] !== 'disponible') {
        $_SESSION['flash_error'] = "Offre non disponible.";
        header('Location: ?route=offreRouter.php?action=index'); 
        exit;
    }

    if ($offre['stock'] < $quantite) {
        $_SESSION['flash_error'] = "Stock insuffisant.";
        header("Location: index.php?route=commandes/create&id_offre={$id_offre}");
        exit;
    }

    if (strtotime($offre['date_expiration']) <= time()) {
        $_SESSION['flash_error'] = "Offre expir�e.";
        header('Location: ?route=offreRouter.php?action=index');
        exit;
    }

    // ?? Cr�ation commande
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

        // ?? IMPORTANT : d�cr�menter le stock
        OffreModel::decrementStock($id_offre, $quantite);

        $_SESSION['flash_success'] = "Commande cr��e avec succ�s !";
        header('Location: index.php?route=commandes/index');

    } else {
        $_SESSION['flash_error'] = "Erreur lors de la cr�ation de la commande.";
        header("Location: ?route=commandes/create&id_offre={$id_offre}");
    }

    exit;
}

    // -- Formulaire de modification de commande ----------------
    private function edit(): void {
        $id_commande = (int)($_GET['id'] ?? 0);
        $id_client = $_SESSION['user_id'] ?? 1;

        if (!$id_commande) {
            $_SESSION['flash_error'] = "Commande non sp�cifi�e.";
            header('Location: index.php?route=commandes/index');
            exit;
        }

        // R�cup�rer la commande
        $commande = CommandeModel::getById($id_commande, $id_client);

        if (!$commande) {
            $_SESSION['flash_error'] = "Commande introuvable.";
            header('Location: index.php?route=commandes/index');
            exit;
        }

        if ($commande['statut'] !== 'en_attente') {
            $_SESSION['flash_error'] = "Cette commande ne peut plus �tre modifi�e.";
            header('Location: index.php?route=commandes/index');
            exit;
        }

        // R�cup�rer l'offre associ�e
        $offreModel = new OffreModel();
        $offre = $offreModel->findById($commande['id_offre']);

        if (!$offre) {
            $_SESSION['flash_error'] = "Offre associ�e introuvable.";
            header('Location: index.php?route=commandes/index');
            exit;
        }

        require __DIR__ . '/../view/Commande/front/form_commande.php';
    }

    // -- Mettre � jour une commande ----------------------------
    private function update(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: index.php?route=commandes/index');
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
            $_SESSION['flash_error'] = "Commande non sp�cifi�e.";
            header('Location: ?route=commandeRouter.php?action=index');
            exit;
        }

        // Validation
        $errors = [];
        if ($quantite <= 0) {
            $errors[] = "La quantit� doit �tre sup�rieure � 0.";
        }
        if (empty($adresse)) {
            $errors[] = "L'adresse de livraison est requise.";
        }
        if (empty($telephone) || !preg_match('/^[0-9]{8}$/', $telephone)) {
            $errors[] = "Le num�ro de t�l�phone doit contenir 8 chiffres.";
        }
        if (empty($mode_paiement)) {
            $errors[] = "Le mode de paiement est requis.";
        }

        if (!empty($errors)) {
            $_SESSION['flash_error'] = implode('<br>', $errors);
            header("Location: index.php?route=commandes/edit&id={$id_commande}");
            exit;
        }

        // Mettre � jour la commande
        $result = CommandeModel::update($id_commande, $id_client, $quantite, $adresse, $telephone, $mode_paiement, $note);

        if ($result) {
            $_SESSION['flash_success'] = "Commande modifiée avec succès !";
            header('Location: index.php?route=commandes/index');
        } else {
            $_SESSION['flash_error'] = "Erreur lors de la modification de la commande.";
            header("Location: index.php?route=commandes/edit&id={$id_commande}");
        }
        exit;
    }

    // -- Annuler une commande ----------------------------------
    private function cancel(): void {
        $id = (int)($_GET['id'] ?? 0);
        $id_client = $_SESSION['user_id'] ?? 1; // TEMP

        if (!$id) {
            $_SESSION['flash_error'] = "Commande non spécifiée.";
            header('Location: index.php?route=commandes/index');
            exit;
        }

        $result = CommandeModel::cancel($id);

        if ($result) {
            $_SESSION['flash_success'] = "Commande annulée avec succès.";
        } else {
            $_SESSION['flash_error'] = "Impossible d'annuler cette commande.";
        }

        header('Location: index.php?route=commandes/index');
        exit;
    }

    // -- Payer une commande ------------------------------------
/*private function pay(): void {
    $id = (int)($_GET['id'] ?? 0);

    if (!$id) {
        $_SESSION['flash_error'] = "Commande non sp�cifi�e.";
        header('Location: ?route=commandeRouter.php?action=index');
        exit;
    }

    // ?? V�rification AVANT paiement
    $commande = CommandeModel::getByIdSimple($id);

    if (!$commande) {
        $_SESSION['flash_error'] = "Commande introuvable.";
        header('Location: ?route=commandeRouter.php?action=index');
        exit;
    }

    if ($commande['paiement_status'] === 'paye') {
        $_SESSION['flash_error'] = "D�j� pay�.";
        header('Location: ?route=commandeRouter.php?action=index');
        exit;
    }

    // ?? Paiement
    $result = CommandeModel::simulatePayment($id);

    if ($result) {
        $_SESSION['flash_success'] = "Paiement effectu� avec succ�s !";
    } else {
        $_SESSION['flash_error'] = "Erreur lors du paiement.";
    }

    header('Location: ?route=commandeRouter.php?action=index');
    exit;
}*/
private function pay(): void {

    $id = (int)($_GET['id'] ?? 0);

    if (!$id) {
        $_SESSION['flash_error'] = "Commande non spécifiée.";
        header('Location: index.php?route=commandes/index');
        exit;
    }

    $commande = CommandeModel::getByIdSimple($id);

    if (!$commande) {
        $_SESSION['flash_error'] = "Commande introuvable.";
        header('Location: index.php?route=commandes/index');
        exit;
    }

    if ($commande['paiement_status'] === 'paye') {
        $_SESSION['flash_error'] = "Déjà payé.";
        header('Location: index.php?route=commandes/index');
        exit;
    }

    $result = CommandeModel::simulatePayment($id, $commande['mode_paiement']);

    if ($result) {
        $_SESSION['flash_success'] = "Paiement simulé avec succès !";
    } else {
        $_SESSION['flash_error'] = "Erreur lors du paiement.";
    }

    header('Location: index.php?route=commandes/index');
    exit;
}

    // -- Liste des commandes (admin) --------------------------
    private function adminIndex(): void {
        $commandes = CommandeModel::getAll();

        require __DIR__ . '/../view/Commande/back/commande_admin.php';
    }

    // -- Mettre � jour le statut (admin) ----------------------
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

