<?php

declare(strict_types=1);

namespace Controller\Front;

use Controller\Controller;
use Controller\Url;
use Model\Recette;
use Model\Ingredient;
use Model\PlanAlimentaire;
use Model\PlanRecette;

final class MagicRecipeController extends Controller
{
    // Identifiants API (À remplacer par vos vrais accès Edamam)
    private const EDAMAM_APP_ID = 'YOUR_APP_ID';
    private const EDAMAM_APP_KEY = 'YOUR_APP_KEY';

    public function index(): void
    {
        $query = $_GET['q'] ?? '';
        $diet = $_GET['diet'] ?? '';
        $recipes = [];
        $error = null;

        if ($query !== '') {
            // Check if keys are configured
            if (self::EDAMAM_APP_ID === 'YOUR_APP_ID' || self::EDAMAM_APP_KEY === 'YOUR_APP_KEY') {
                $error = "Mode Démo activé : Veuillez configurer vos vraies clés API Edamam dans MagicRecipeController.php pour accéder à toutes les recettes.";
                $recipes = $this->getDemoRecipes($query);
            } else {
                $apiUrl = "https://api.edamam.com/search?q=" . urlencode($query) . "&app_id=" . self::EDAMAM_APP_ID . "&app_key=" . self::EDAMAM_APP_KEY . "&from=0&to=12";
                
                if ($diet !== '') {
                    $apiUrl .= "&diet=" . urlencode($diet);
                }

                try {
                    $response = @file_get_contents($apiUrl);
                    if ($response !== false) {
                        $data = json_decode($response, true);
                        $recipes = $data['hits'] ?? [];
                    } else {
                        $error = "Erreur de connexion à l'API Edamam. Vérifiez vos clés dans MagicRecipeController.php.";
                        $recipes = $this->getDemoRecipes($query);
                    }
                } catch (\Throwable $e) {
                    $error = "Erreur API : " . $e->getMessage();
                    $recipes = $this->getDemoRecipes($query);
                }
            }
        }

        $this->view('front/magic_recipe', [
            'query' => $query,
            'diet' => $diet,
            'recipes' => $recipes,
            'error' => $error,
            'url' => Url::class,
            'csrf' => $this->csrfToken(),
        ]);
    }

    /**
     * Importation "Magique" d'une recette dans la DB locale.
     */
    public function import(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$this->validateCsrf($_POST['_csrf'] ?? null)) {
            $this->redirect(Url::to('front', 'magic_recipe', 'index'));
        }

        $recipeData = json_decode($_POST['recipe_json'] ?? '{}', true);
        if (empty($recipeData)) {
            $_SESSION['flash_error'] = "Données de recette invalides.";
            $this->redirect(Url::to('front', 'magic_recipe', 'index'));
        }

        try {
            $recetteModel = new Recette();
            $ingredientModel = new Ingredient();

            // 1. Insertion de la recette
            $recetteId = $recetteModel->insert([
                'nom' => $recipeData['label'],
                'image_url' => $recipeData['image'],
                'calories' => (float) $recipeData['calories'],
                'proteines' => (float) ($recipeData['totalNutrients']['PROCNT']['quantity'] ?? 0),
                'glucides' => (float) ($recipeData['totalNutrients']['CHOCDF']['quantity'] ?? 0),
                'lipides' => (float) ($recipeData['totalNutrients']['FAT']['quantity'] ?? 0),
                'source_api_id' => $recipeData['uri'] ?? null
            ]);

            // 2. Insertion des ingrédients
            foreach ($recipeData['ingredients'] as $ing) {
                $ingredientModel->insert([
                    'recette_id' => $recetteId,
                    'nom' => $ing['food'],
                    'quantite' => (string) ($ing['quantity'] ?? '1'),
                    'unite' => $ing['measure'] ?? 'portion'
                ]);
            }

            $_SESSION['flash_success'] = "✨ Recette '{$recipeData['label']}' importée avec succès !";
        } catch (\Throwable $e) {
            $_SESSION['flash_error'] = "Erreur lors de l'importation : " . $e->getMessage();
        }

        $this->redirect(Url::to('front', 'magic_recipe', 'index', ['q' => $_POST['q'] ?? '']));
    }

    private function getDemoRecipes(string $query): array
    {
        $recipes = [];
        $types = [
            ['suffix' => 'Traditionnel', 'tag' => 'food'],
            ['suffix' => 'Gourmet', 'tag' => 'gourmet'],
            ['suffix' => 'Healthy', 'tag' => 'healthy'],
            ['suffix' => 'Express', 'tag' => 'fast'],
            ['suffix' => 'Maison', 'tag' => 'homemade'],
            ['suffix' => 'Chef Special', 'tag' => 'plate']
        ];

        // Seed randomization with query string
        mt_srand(crc32(strtolower($query)));

        foreach ($types as $i => $type) {
            $baseCal = mt_rand(350, 850);
            // Using a search-based image service to match the query keyword
            $imageUrl = "https://loremflickr.com/600/400/" . urlencode($query) . "," . $type['tag'] . "/all?lock=" . $i;
            
            $recipes[] = ['recipe' => [
                'label' => ucfirst($query) . " " . $type['suffix'],
                'image' => $imageUrl,
                'calories' => $baseCal,
                'uri' => 'demo_' . $i . '_' . md5($query),
                'totalNutrients' => [
                    'PROCNT' => ['quantity' => mt_rand(15, 40)],
                    'CHOCDF' => ['quantity' => mt_rand(20, 100)],
                    'FAT' => ['quantity' => mt_rand(10, 35)]
                ],
                'ingredients' => [
                    ['food' => ucfirst($query), 'quantity' => mt_rand(100, 300), 'measure' => 'g'],
                    ['food' => 'Ingrédients frais', 'quantity' => 1, 'measure' => 'portion']
                ]
            ]];
        }
        
        return $recipes;
    }
}

