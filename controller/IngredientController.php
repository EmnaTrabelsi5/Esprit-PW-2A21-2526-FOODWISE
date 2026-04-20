<?php
/**
 * FoodWise — Controller : IngredientController
 * controller/IngredientController.php
 */

require_once __DIR__ . '/../model/Ingredient.php';

class IngredientController
{
    /* ════════════════════════════════════════════════
       READ — Liste (back-office uniquement)
       ════════════════════════════════════════════════ */

    public function adminIndex(): void
    {
        $filtres = [
            'q'             => trim($_GET['q']             ?? ''),
            'categorie'     => $_GET['categorie']           ?? '',
            'est_allergene' => $_GET['est_allergene']       ?? '',
            'est_disponible'=> $_GET['est_disponible']      ?? '',
            'trier_par'     => $_GET['trier_par']           ?? 'nom_asc',
        ];
        $page = max(1, (int)($_GET['page'] ?? 1));

        $result      = Ingredient::getAll($filtres, $page);
        $ingredients = $result['ingredients'];
        $pagination  = [
            'page'        => $result['page'],
            'total_pages' => $result['total_pages'],
            'total'       => $result['total'],
        ];
        $stats      = Ingredient::getStats();
        $categories = Ingredient::CATEGORIES;

        $pageTitle  = 'Base Ingrédients';
        $activeNav  = 'gestion_ingredients';
        $backoffice = true;

        include 'view/recipebook/back/ingredient_list.php';
    }

    /* ════════════════════════════════════════════════
       CREATE — Formulaire + traitement POST
       ════════════════════════════════════════════════ */

    public function create(): void
    {
        $erreurs    = [];
        $ingredient = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $data    = $this->nettoyerPost($_POST);
            $erreurs = Ingredient::valider($data);

            /* Vérifier unicité du nom */
            if (empty($erreurs['nom']) && Ingredient::nomExiste($data['nom'])) {
                $erreurs['nom'] = 'Un ingrédient avec ce nom existe déjà.';
            }

            if (empty($erreurs)) {
                try {
                    $newId = Ingredient::add($data);
                    $this->setFlash('success', 'Ingrédient "' . htmlspecialchars($data['nom']) . '" ajouté avec succès !');
                    header('Location: /FOODWISE/index.php?route=admin_ingredients');
                    exit;
                } catch (Exception $e) {
                    $erreurs['global'] = 'Une erreur est survenue. Veuillez réessayer.';
                }
            }

            $ingredient = (object)$data;
        }

        $categories = Ingredient::CATEGORIES;
        $unites     = Ingredient::UNITES;
        $pageTitle  = 'Nouvel ingrédient';
        $activeNav  = 'gestion_ingredients';
        $backoffice = true;

        $categories = ['Légumes', 'Fruits', 'Céréales', 'Viandes', 'Produits laitiers']; // Ou requête BDD
        $unites = ['g', 'kg', 'ml', 'L', 'unité', 'pincée'];

        include 'view/recipebook/back/ingredient_form.php';
    }

    /* ════════════════════════════════════════════════
       UPDATE — Formulaire + traitement POST
       ════════════════════════════════════════════════ */

    public function edit(int $id): void
    {
        $ingredient = Ingredient::getById($id);

        if (!$ingredient) {
            $this->setFlash('error', 'Ingrédient introuvable.');
            header('Location: /FOODWISE/index.php?route=admin_ingredients');
            exit;
        }

        $erreurs = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $data    = $this->nettoyerPost($_POST);
            $erreurs = Ingredient::valider($data);

            /* Unicité en excluant l'ingrédient courant */
            if (empty($erreurs['nom']) && Ingredient::nomExiste($data['nom'], $id)) {
                $erreurs['nom'] = 'Un autre ingrédient porte déjà ce nom.';
            }

            if (empty($erreurs)) {
                try {
                    Ingredient::update($id, $data);
                    $this->setFlash('success', 'Ingrédient modifié avec succès !');
                    header('Location: /FOODWISE/index.php?route=admin_ingredients');
                    exit;
                } catch (Exception $e) {
                    $erreurs['global'] = 'Une erreur est survenue. Veuillez réessayer.';
                }
            }

            /* Repeupler l'objet avec les données saisies */
            $ingredient = (object)array_merge((array)$ingredient, $data);
        }

        $categories = Ingredient::CATEGORIES;
        $unites     = Ingredient::UNITES;
        $pageTitle  = 'Modifier : ' . $ingredient->nom;
        $activeNav  = 'gestion_ingredients';
        $backoffice = true;

        $categories = ['Légumes', 'Fruits', 'Céréales', 'Viandes', 'Produits laitiers']; // Ou requête BDD
        $unites = ['g', 'kg', 'ml', 'L', 'unité', 'pincée'];

        include 'view/recipebook/back/ingredient_form.php';
    }

    /* ════════════════════════════════════════════════
       DELETE
       ════════════════════════════════════════════════ */

    public function delete(int $id): void
    {
        $ingredient = Ingredient::getById($id);

        if (!$ingredient) {
            $this->setFlash('error', 'Ingrédient introuvable.');
            header('Location: /FOODWISE/index.php?route=admin_ingredients');
            exit;
        }

        try {
            Ingredient::delete($id);
            $this->setFlash('success', 'Ingrédient "' . htmlspecialchars($ingredient->nom) . '" supprimé.');
        } catch (Exception $e) {
            /* delete() lève une Exception si l'ingrédient est utilisé dans des recettes */
            $this->setFlash('error', $e->getMessage());
        }

        header('Location: /FOODWISE/index.php?route=admin_ingredients');
        exit;
    }

    /* ════════════════════════════════════════════════
       HELPERS PRIVÉS
       ════════════════════════════════════════════════ */

    private function nettoyerPost(array $post): array
    {
        return [
            'nom'            => trim($post['nom']            ?? ''),
            'categorie'      => trim($post['categorie']      ?? ''),
            'calories_100g'  => $post['calories_100g']       ?? '',
            'proteines_100g' => $post['proteines_100g']      ?? '',
            'glucides_100g'  => $post['glucides_100g']       ?? '',
            'lipides_100g'   => $post['lipides_100g']        ?? '',
            'est_allergene'  => isset($post['est_allergene']),
            'est_disponible' => isset($post['est_disponible']),
            'unite_defaut'   => $post['unite_defaut']        ?? 'g',
        ];
    }

    private function setFlash(string $type, string $message): void
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $_SESSION['flash'] = ['type' => $type, 'message' => $message];
    }
}
