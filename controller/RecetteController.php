<?php
require_once __DIR__ . '/../model/Recette.php';

class RecetteController
{
    /* ════════════════════════════════════════════════
       READ — Liste des recettes (avec filtres)
       ════════════════════════════════════════════════ */

    public function index(): void
    {
        $filtres = [
            'q'            => trim($_GET['q']            ?? ''),
            'regime'       => $_GET['regime']             ?? '',
            'difficulte'   => $_GET['difficulte']         ?? '',
            'temps_max'    => $_GET['temps_max']          ?? '',
            'calories_max' => $_GET['calories_max']       ?? '',
            'trier_par'    => $_GET['trier_par']          ?? 'date_desc',
        ];
        $page = max(1, (int)($_GET['page'] ?? 1));

        $result     = Recette::getAll($filtres, $page);
        $recettes   = $result['recettes'];
        $pagination = [
            'page'        => $result['page'],
            'total_pages' => $result['total_pages'],
            'total'       => $result['total'],
        ];

        $pageTitle  = 'Mes Recettes';
        $activeNav  = 'recettes';
        $backoffice = false;

        include 'view/recipebook/front/list.php';
    }

    /* ════════════════════════════════════════════════
       READ — Détail d'une recette
       ════════════════════════════════════════════════ */

    public function show(int $id): void
    {
        $recette = Recette::getById($id);

        if (!$recette) {
            http_response_code(404);
            $this->renderErreur('Recette introuvable', 'Cette recette n\'existe pas ou a été supprimée.');
            return;
        }

        Recette::incrementViews($id);

        $ingredients = $this->getIngredients($id);

        $nutrition = [
            'calories'  => $recette->calories_totales  ?? 0,
            'proteines' => $recette->proteines_totales ?? 0,
            'glucides'  => $recette->glucides_totales  ?? 0,
            'lipides'   => $recette->lipides_totales   ?? 0,
        ];

        $substituts = $this->getSubstituts($id);
        $score      = null;

        $pageTitle  = $recette->nom;
        $activeNav  = 'recettes';
        $backoffice = false;

        include 'view/recipebook/front/detail.php';
    }

public function showAdmin(int $id, bool $admin): void
{
    $recette = Recette::getById($id);

    if (!$recette) {
        http_response_code(404);
        $this->renderErreur('Recette introuvable', 'Cette recette n\'existe pas ou a été supprimée.');
        return;
    }

    if (!$admin) {
        Recette::incrementViews($id);
    }

    $ingredients = $this->getIngredients($id);

    $nutrition = [
        'calories'  => $recette->calories_totales  ?? 0,
        'proteines' => $recette->proteines_totales ?? 0,
        'glucides'  => $recette->glucides_totales  ?? 0,
        'lipides'   => $recette->lipides_totales   ?? 0,
    ];

    $substituts = $this->getSubstituts($id);
    $score      = null;

    $pageTitle  = $recette->nom;
    $activeNav  = $admin ? 'gestion_recettes' : 'recettes';
    $backoffice = true;


   include 'view/recipebook/back/detail.php';
}




    /* ════════════════════════════════════════════════
       CREATE — Formulaire + traitement POST
       ════════════════════════════════════════════════ */

    public function create(bool $admin = false): void
    {
        $erreurs = [];
        $recette = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $data    = $this->nettoyerPost($_POST);
            $erreurs = Recette::valider($data);

        if (empty($_POST['ingredients'])) {
            $erreurs[] = 'Au moins un ingrédient est requis.';
        } else {
            foreach ($_POST['ingredients'] as $i => $ing) {
                if (empty($ing['id_ingredient'])) {
                    $erreurs[] = "Ingrédient #" . ($i+1) . " invalide.";
                }
                if (!is_numeric($ing['quantite']) || $ing['quantite'] <= 0) {
                    $erreurs[] = "Quantité invalide pour ingrédient #" . ($i+1);
                }
            }
        }

            if (empty($erreurs)) {
                $data['image_url'] = $this->gererUploadImage();

                try {
                    $newId = Recette::add($data);

                    if (!empty($_POST['ingredients'])) {
                        $this->sauvegarderIngredients($newId, $_POST['ingredients']);
                    }

                    $this->setFlash('success', 'Recette "' . htmlspecialchars($data['nom']) . '" ajoutée avec succès !');

                    /* Redirection selon contexte front/back */
                    $redirect = $admin
                        ? '/FOODWISE/admin/recettes'
                        : '/FOODWISE/recettes/' . $newId;
                    header('Location: ' . $redirect);
                    exit;

                } catch (Exception $e) {
                    $erreurs[] = 'Une erreur est survenue lors de l\'enregistrement. Veuillez réessayer.';
                }
            }

            $recette = (object)$data;
        }

        $recette_ingredients = [];
        $ingredients_dispo   = $this->getAllIngredients();
        $pageTitle           = 'Nouvelle recette';
        $activeNav           = $admin ? 'gestion_recettes' : 'recettes';
        $backoffice          = $admin;

        $vue = $admin
            ? 'view/recipebook/back/form.php'
            : 'view/recipebook/front/form.php';

        include $vue;
    }

    /* ════════════════════════════════════════════════
       UPDATE — Formulaire + traitement POST
       ════════════════════════════════════════════════ */

    public function edit(int $id, bool $admin = false): void
    {
        $recette = Recette::getById($id);

        if (!$recette) {
            http_response_code(404);
            $this->renderErreur('Recette introuvable', 'Cette recette n\'existe pas.');
            return;
        }

        $erreurs = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $data    = $this->nettoyerPost($_POST);

            $erreurs = Recette::valider($data);

            if (empty($erreurs)) {
                $nouvelleImage     = $this->gererUploadImage();
                $data['image_url'] = $nouvelleImage ?? $recette->image_url;

                try {
                    Recette::update($id, $data);

                    if (!empty($_POST['ingredients'])) {
                        $db = config::getConnexion();
                        $db->prepare('DELETE FROM recette_ingredient WHERE id_recette = ?')
                           ->execute([$id]);
                        $this->sauvegarderIngredients($id, $_POST['ingredients']);
                    }

                    $this->setFlash('success', 'Recette modifiée avec succès !');

                    /* Redirection selon contexte front/back */
                    $redirect = $admin
                        ? '/FOODWISE/admin/recettes'
                        : '/FOODWISE/recettes/' . $id;
                    header('Location: ' . $redirect);
                    exit;

                } catch (Exception $e) {
                    $erreurs[] = 'Erreur lors de la modification.';
                }
            }
        }

        $recette_ingredients = $this->getIngredients($id);
        $ingredients_dispo   = $this->getAllIngredients();
        $pageTitle           = 'Modifier ' . $recette->nom;
        $activeNav           = $admin ? 'gestion_recettes' : 'recettes';
        $backoffice          = $admin;

        $vue = $admin
            ? 'view/recipebook/back/form.php'
            : 'view/recipebook/front/form.php';

        include $vue;
    }

    /* ════════════════════════════════════════════════
       DELETE — Supprimer une recette
       ════════════════════════════════════════════════ */

    public function delete(int $id): void
    {
        $recette = Recette::getById($id);

        if (!$recette) {
            $this->setFlash('error', 'Recette introuvable.');
            header('Location: /FOODWISE/recettes');
            exit;
        }

        /* Détecter si la suppression vient du back-office */
        $referer  = $_SERVER['HTTP_REFERER'] ?? '';
        $fromAdmin = str_contains($referer, '/admin/');

        try {
            Recette::delete($id);
            $this->setFlash('success', 'Recette "' . htmlspecialchars($recette->nom) . '" supprimée.');
        } catch (Exception $e) {
            $this->setFlash('error', 'Impossible de supprimer cette recette.');
        }

        $redirect = $fromAdmin
            ? '/FOODWISE/admin/recettes'
            : '/FOODWISE/recettes';
        header('Location: ' . $redirect);
        exit;
    }

    /* ════════════════════════════════════════════════
       BACK-OFFICE — Liste admin
       ════════════════════════════════════════════════ */

    public function adminIndex(): void
    {
        $filtres = [
            'q'          => trim($_GET['q']          ?? ''),
            'regime'     => $_GET['regime']           ?? '',
            'difficulte' => $_GET['difficulte']       ?? '',
            'trier_par'  => $_GET['trier_par']        ?? 'date_desc',
        ];
        $page = max(1, (int)($_GET['page'] ?? 1));

        $result     = Recette::getAll($filtres, $page);
        $recettes   = $result['recettes'];
        $pagination = [
            'page'        => $result['page'],
            'total_pages' => $result['total_pages'],
            'total'       => $result['total'],
        ];

        $stats      = Recette::getStats();
        $pageTitle  = 'Gestion des recettes';
        $activeNav  = 'gestion_recettes';
        $backoffice = true;

        include 'view/recipebook/back/admin_list.php';
    }

    /* ════════════════════════════════════════════════
       MÉTHODES PRIVÉES — Helpers internes
       ════════════════════════════════════════════════ */

private function nettoyerPost(array $post): array
{
    return [
        'nom'               => trim($post['nom'] ?? ''),
        'description'       => trim($post['description'] ?? ''),
        'temps_prep'        => $post['temps_prep'] ?? 0,
        'temps_cuisson'     => $post['temps_cuisson'] ?? 0,
        'portions'          => $post['portions'] ?? 1,
        'niveau_difficulte' => $post['niveau_difficulte'] ?? '',

        // FIX IMPORTANT
        'est_vegetarien'    => !empty($post['est_vegetarien']) ? 1 : 0,
        'est_sans_gluten'   => !empty($post['est_sans_gluten']) ? 1 : 0,
        'est_vegan'         => !empty($post['est_vegan']) ? 1 : 0,
    ];
}

    private function gererUploadImage(): ?string
    {
        if (empty($_FILES['image']['name'])) {
            return null;
        }

        $file      = $_FILES['image'];
        $typesOk   = ['image/jpeg', 'image/png', 'image/webp'];
        $maxTaille = 2 * 1024 * 1024;

        if (!in_array($file['type'], $typesOk) || $file['size'] > $maxTaille) {
            throw new Exception("Image invalide (type ou taille).");
        }

        $ext     = pathinfo($file['name'], PATHINFO_EXTENSION);
        $nomFich = time() . '_' . uniqid() . '.' . strtolower($ext);
        $dossier = __DIR__ . '/../assets/uploads/';

        if (!is_dir($dossier)) {
            mkdir($dossier, 0755, true);
        }

        if (move_uploaded_file($file['tmp_name'], $dossier . $nomFich)) {
            return '/FOODWISE/assets/uploads/' . $nomFich;
        }

        return null;
    }

    private function sauvegarderIngredients(int $idRecette, array $lignes): void
    {
        $db   = config::getConnexion();
        $sql  = "
            INSERT INTO recette_ingredient
                (id_recette, id_ingredient, quantite, unite, ordre_affichage, est_optionnel)
            VALUES (?, ?, ?, ?, ?, 0)
        ";
        $stmt  = $db->prepare($sql);
        $ordre = 1;

        foreach ($lignes as $ligne) {
            if (empty($ligne['id_ingredient']) || empty($ligne['quantite'])) {
                continue;
            }
            $stmt->execute([
                $idRecette,
                (int)$ligne['id_ingredient'],
                (float)$ligne['quantite'],
                $ligne['unite'] ?? 'g',
                $ordre++,
            ]);
        }
    }

    private function getIngredients(int $idRecette): array
    {
        $db  = config::getConnexion();
        $sql = "
            SELECT ri.*, i.nom AS ingredient_nom,
                   i.est_allergene, i.est_disponible,
                   i.calories_100g, i.proteines_100g, i.glucides_100g, i.lipides_100g
            FROM recette_ingredient ri
            JOIN ingredient i ON i.id_ingredient = ri.id_ingredient
            WHERE ri.id_recette = ?
            ORDER BY ri.ordre_affichage
        ";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idRecette]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    private function getSubstituts(int $idRecette): array
    {
        $db  = config::getConnexion();
        $sql = "
            SELECT s.*,
                   i_src.nom AS nom_source,
                   i_src.est_allergene,
                   i_src.est_disponible,
                   i_sub.nom AS nom_substitut
            FROM substitut s
            JOIN ingredient i_src ON i_src.id_ingredient = s.id_ingredient_source
            JOIN ingredient i_sub ON i_sub.id_ingredient = s.id_ingredient_sub
            WHERE s.id_ingredient_source IN (
                SELECT id_ingredient FROM recette_ingredient WHERE id_recette = ?
            )
            AND (i_src.est_allergene = 1 OR i_src.est_disponible = 0)
        ";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idRecette]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    private function getAllIngredients(): array
    {
        $db = config::getConnexion();
        return $db->query('SELECT * FROM ingredient ORDER BY nom ASC')
                  ->fetchAll(PDO::FETCH_OBJ);
    }

    private function setFlash(string $type, string $message): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['flash'] = ['type' => $type, 'message' => $message];
    }

    private function renderErreur(string $titre, string $message): void
    {
        $pageTitle  = $titre;
        $activeNav  = '';
        $backoffice = false;
        include __DIR__ . '/../view/layout/header.php';
        echo '<div style="text-align:center;padding:60px 20px;">';
        echo '<h2 style="font-family:\'Playfair Display\',serif;color:#4E2C0E;">' . htmlspecialchars($titre) . '</h2>';
        echo '<p style="color:#9B7355;margin:12px 0 24px;">' . htmlspecialchars($message) . '</p>';
        echo '<a href="/FOODWISE/recettes" style="background:#A0522D;color:#FDF6EC;padding:10px 24px;border-radius:25px;text-decoration:none;font-weight:700;">Retour aux recettes</a>';
        echo '</div>';
        include __DIR__ . '/../view/layout/footer.php';
    }
}