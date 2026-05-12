<?php
declare(strict_types=1);

/**
 * Mappings pour standardiser les allergies, régimes et intolérances
 * Évite les doublons dus à des variantes de saisie
 */

class AllergenMappings
{
    /**
     * Mappings des allergies
     * Clé = ce que l'utilisateur tape (insensible à la casse)
     * Valeur = la forme standardisée
     */
    private static array $allergenMap = [
        'lait' => 'Lactose',
        'lactose' => 'Lactose',
        'produits laitiers' => 'Lactose',
        'produit laitier' => 'Lactose',
        'gluten' => 'Gluten',
        'blé' => 'Gluten',
        'arachides' => 'Arachides',
        'arachide' => 'Arachides',
        'cacahuète' => 'Arachides',
        'cacahuètes' => 'Arachides',
        'noix' => 'Noix',
        'fruits secs' => 'Noix',
        'oeufs' => 'Œufs',
        'œuf' => 'Œufs',
        'oeuf' => 'Œufs',
        'poisson' => 'Poisson',
        'poissons' => 'Poisson',
        'crustacés' => 'Crustacés',
        'crustacé' => 'Crustacés',
        'crevettes' => 'Crustacés',
        'crevette' => 'Crustacés',
        'moules' => 'Crustacés',
        'moule' => 'Crustacés',
        'sesame' => 'Sésame',
        'sésame' => 'Sésame',
        'graines de sesame' => 'Sésame',
        'graines de sésame' => 'Sésame',
        'moutarde' => 'Moutarde',
        'anhydride sulfureux' => 'Sulfites',
        'sulfites' => 'Sulfites',
        'mollusques' => 'Mollusques',
        'mollusque' => 'Mollusques',
        'celeri' => 'Céleri',
        'céleri' => 'Céleri',
        'lupin' => 'Lupin',
        'lupins' => 'Lupin',
    ];

    /**
     * Mappings des régimes alimentaires
     */
    private static array $regimeMap = [
        'vegetarien' => 'Végétarien',
        'végétarien' => 'Végétarien',
        'vegetarienne' => 'Végétarien',
        'végétarienne' => 'Végétarien',
        'vegan' => 'Vegan',
        'végan' => 'Vegan',
        'vegane' => 'Vegan',
        'végane' => 'Vegan',
        'sans viande' => 'Végétarien',
        'sans poisson' => 'Végétarien',
        'halal' => 'Halal',
        'casher' => 'Casher',
        'kasher' => 'Casher',
        'paleo' => 'Paléo',
        'paléo' => 'Paléo',
        'paleolithique' => 'Paléo',
        'keto' => 'Cétogène',
        'cetogene' => 'Cétogène',
        'cétogène' => 'Cétogène',
        'atkins' => 'Cétogène',
        'sans gluten' => 'Sans gluten',
        'gluten free' => 'Sans gluten',
        'sans lactose' => 'Sans lactose',
        'lactose free' => 'Sans lactose',
        'fodmap' => 'Faible FODMAP',
        'faible fodmap' => 'Faible FODMAP',
    ];

    /**
     * Mappings des intolérances
     */
    private static array $intolereranceMap = [
        'lactose' => 'Lactose',
        'lait' => 'Lactose',
        'gluten' => 'Gluten',
        'fructose' => 'Fructose',
        'histamine' => 'Histamine',
        'sulfites' => 'Sulfites',
        'caféine' => 'Caféine',
        'caffeine' => 'Caféine',
        'salicylates' => 'Salicylates',
        'amines' => 'Amines biogènes',
        'amines biogenes' => 'Amines biogènes',
        'amines biogènes' => 'Amines biogènes',
    ];

    /**
     * Standardise une allergie
     */
    public static function standardizeAllergen(string $input): string
    {
        return self::standardize($input, self::$allergenMap);
    }

    /**
     * Standardise un régime
     */
    public static function standardizeRegime(string $input): string
    {
        return self::standardize($input, self::$regimeMap);
    }

    /**
     * Standardise une intolérances
     */
    public static function standardizeIntolerance(string $input): string
    {
        return self::standardize($input, self::$intolereranceMap);
    }

    /**
     * Fonction générique de standardisation
     */
    private static function standardize(string $input, array $map): string
    {
        $trimmed = trim($input);
        if ($trimmed === '') {
            return '';
        }

        $lower = mb_strtolower($trimmed, 'UTF-8');
        
        // Chercher une correspondance exacte
        if (isset($map[$lower])) {
            return $map[$lower];
        }

        // Chercher une correspondance partielle
        foreach ($map as $key => $value) {
            if (strpos($lower, $key) !== false || strpos($key, $lower) !== false) {
                return $value;
            }
        }

        // Si aucune correspondance, retourner la saisie originale avec première lettre majuscule
        return ucfirst($trimmed);
    }

    /**
     * Standardise une liste d'allergies (séparées par virgules)
     */
    public static function standardizeAllergenList(string $input): string
    {
        return self::standardizeList($input, 'standardizeAllergen');
    }

    /**
     * Standardise une liste de régimes (séparées par virgules)
     */
    public static function standardizeRegimeList(string $input): string
    {
        return self::standardizeList($input, 'standardizeRegime');
    }

    /**
     * Standardise une liste d'intolérances (séparées par virgules)
     */
    public static function standardizeIntoleranceList(string $input): string
    {
        return self::standardizeList($input, 'standardizeIntolerance');
    }

    /**
     * Traite une liste d'éléments séparés par virgules
     */
    private static function standardizeList(string $input, string $method): string
    {
        if (trim($input) === '') {
            return '';
        }

        $items = array_filter(
            array_map('trim', explode(',', $input)),
            fn($item) => $item !== ''
        );

        $standardized = array_map(
            fn($item) => self::$method($item),
            $items
        );

        // Supprimer les doublons
        $unique = array_unique($standardized, SORT_STRING);

        return implode(', ', $unique);
    }
}

