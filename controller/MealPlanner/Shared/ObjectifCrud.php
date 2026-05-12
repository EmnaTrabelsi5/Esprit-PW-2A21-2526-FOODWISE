<?php

declare(strict_types=1);

namespace Controller\Shared;

use Controller\Controller;
use Controller\Url;
use Controller\Validation;
use Model\Objectif;

/**
 * @mixin Controller
 */
trait ObjectifCrud
{
    abstract protected function layoutArea(): string;

    public function index(): void
    {
        try {
            $objectifs = (new Objectif())->all();
        } catch (\Throwable) {
            $objectifs = [];
            $_SESSION['flash_error'] = 'Impossible de lire les objectifs.';
        }

        $flashSuccess = $_SESSION['flash_success'] ?? null;
        unset($_SESSION['flash_success']);
        $flashError = $_SESSION['flash_error'] ?? null;
        unset($_SESSION['flash_error']);

        $this->view($this->layoutArea() . '/objectif/index', [
            'objectifs' => $objectifs,
            'flashSuccess' => $flashSuccess,
            'flashError' => $flashError,
            'url' => Url::class,
            'csrf' => $this->csrfToken(),
        ]);
    }

    public function show(): void
    {
        $id = $this->intFromGet('id');
        if ($id === null) {
            $this->redirect(Url::to($this->layoutArea(), 'objectif', 'index'));
        }

        $model = new Objectif();
        $objectif = $model->find($id);
        if ($objectif === null) {
            $_SESSION['flash_error'] = 'Objectif introuvable.';
            $this->redirect(Url::to($this->layoutArea(), 'objectif', 'index'));
        }

        $plans = $model->linkedPlans($id);
        $comparaison = $model->compareNutritionFromRecetteTable($id);

        $this->view($this->layoutArea() . '/objectif/show', [
            'objectif' => $objectif,
            'plans' => $plans,
            'comparaison' => $comparaison,
            'url' => Url::class,
        ]);
    }

    public function create(): void
    {
        $this->view($this->layoutArea() . '/objectif/form', [
            'objectif' => null,
            'errors' => [],
            'old' => [],
            'url' => Url::class,
            'csrf' => $this->csrfToken(),
        ]);
    }

    public function store(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(Url::to($this->layoutArea(), 'objectif', 'index'));
        }
        if (!$this->validateCsrf($_POST['_csrf'] ?? null)) {
            $_SESSION['flash_error'] = 'Jeton de sécurité invalide.';
            $this->redirect(Url::to($this->layoutArea(), 'objectif', 'create'));
        }

        [$errors, $payload] = $this->validateObjectifPayload($_POST);
        if ($errors !== []) {
            $this->view($this->layoutArea() . '/objectif/form', [
                'objectif' => null,
                'errors' => $errors,
                'old' => $_POST,
                'url' => Url::class,
                'csrf' => $this->csrfToken(),
            ]);
            return;
        }

        try {
            (new Objectif())->insert($payload);
            $_SESSION['flash_success'] = 'Objectif créé avec calcul BMR/TDEE.';
            $this->redirect(Url::to($this->layoutArea(), 'objectif', 'index'));
        } catch (\Throwable $e) {
            $this->view($this->layoutArea() . '/objectif/form', [
                'objectif' => null,
                'errors' => ['Erreur lors de la création : ' . $e->getMessage()],
                'old' => $_POST,
                'url' => Url::class,
                'csrf' => $this->csrfToken(),
            ]);
        }
    }

    public function edit(): void
    {
        $id = $this->intFromGet('id');
        if ($id === null) {
            $this->redirect(Url::to($this->layoutArea(), 'objectif', 'index'));
        }
        $objectif = (new Objectif())->find($id);
        if ($objectif === null) {
            $_SESSION['flash_error'] = 'Objectif introuvable.';
            $this->redirect(Url::to($this->layoutArea(), 'objectif', 'index'));
        }

        $this->view($this->layoutArea() . '/objectif/form', [
            'objectif' => $objectif,
            'errors' => [],
            'old' => [],
            'url' => Url::class,
            'csrf' => $this->csrfToken(),
        ]);
    }

    public function update(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(Url::to($this->layoutArea(), 'objectif', 'index'));
        }
        if (!$this->validateCsrf($_POST['_csrf'] ?? null)) {
            $_SESSION['flash_error'] = 'Jeton de sécurité invalide.';
            $this->redirect(Url::to($this->layoutArea(), 'objectif', 'index'));
        }

        $id = $this->intFromPost('id');
        if ($id === null) {
            $this->redirect(Url::to($this->layoutArea(), 'objectif', 'index'));
        }
        $existing = (new Objectif())->find($id);
        if ($existing === null) {
            $_SESSION['flash_error'] = 'Objectif introuvable.';
            $this->redirect(Url::to($this->layoutArea(), 'objectif', 'index'));
        }

        [$errors, $payload] = $this->validateObjectifPayload($_POST);
        if ($errors !== []) {
            $this->view($this->layoutArea() . '/objectif/form', [
                'objectif' => $existing,
                'errors' => $errors,
                'old' => $_POST,
                'url' => Url::class,
                'csrf' => $this->csrfToken(),
            ]);
            return;
        }

        try {
            (new Objectif())->update($id, $payload);
            $_SESSION['flash_success'] = 'Objectif mis à jour et recalculé.';
            $this->redirect(Url::to($this->layoutArea(), 'objectif', 'index'));
        } catch (\Throwable $e) {
            $this->view($this->layoutArea() . '/objectif/form', [
                'objectif' => $existing,
                'errors' => ['Erreur lors de la mise à jour : ' . $e->getMessage()],
                'old' => $_POST,
                'url' => Url::class,
                'csrf' => $this->csrfToken(),
            ]);
        }
    }

    public function destroy(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(Url::to($this->layoutArea(), 'objectif', 'index'));
        }
        if (!$this->validateCsrf($_POST['_csrf'] ?? null)) {
            $_SESSION['flash_error'] = 'Jeton de sécurité invalide.';
            $this->redirect(Url::to($this->layoutArea(), 'objectif', 'index'));
        }
        $id = $this->intFromPost('id');
        if ($id === null) {
            $this->redirect(Url::to($this->layoutArea(), 'objectif', 'index'));
        }

        try {
            (new Objectif())->delete($id);
            $_SESSION['flash_success'] = 'Objectif supprimé.';
        } catch (\Throwable $e) {
            $_SESSION['flash_error'] = 'Erreur lors de la suppression : ' . $e->getMessage();
        }
        $this->redirect(Url::to($this->layoutArea(), 'objectif', 'index'));
    }

    /** @return array{0: list<string>, 1: array{type: string, calories_cible: int, macros: string}} */
    private function validateObjectifPayload(array $post): array
    {
        $errors = [];
        $types = ['maintien', 'perte', 'prise'];

        $type = Validation::trim($post['type'] ?? '');
        Validation::requireNonEmpty($type, 'Type d\'objectif', $errors);
        Validation::requireOneOf($type, $types, 'Type d\'objectif', $errors);

        $age = Validation::requireInt($post['age'] ?? null, 'Age', 15, 100, $errors);
        $poids = Validation::requireInt($post['poids_kg'] ?? null, 'Poids (kg)', 20, 500, $errors);
        $taille = Validation::requireInt($post['taille_cm'] ?? null, 'Taille (cm)', 100, 260, $errors);
        $sexe = Validation::trim($post['sexe'] ?? '');
        Validation::requireOneOf($sexe, ['homme', 'femme'], 'Sexe', $errors);
        $activite = Validation::trim($post['niveau_activite'] ?? '');
        Validation::requireOneOf($activite, ['sedentaire', 'leger', 'modere', 'intense', 'extreme'], 'Niveau d\'activité', $errors);

        if ($errors !== []) {
            return [$errors, []];
        }

        $bmr = $this->computeBmr((string) $sexe, (int) $poids, (int) $taille, (int) $age);
        $tdee = $this->computeTdee($bmr, (string) $activite);
        $caloriesCible = $this->applyTypeAdjustment($tdee, $type);
        $macros = $this->buildMacrosString($caloriesCible, $type);

        return [[], [
            'type' => $type,
            'calories_cible' => $caloriesCible,
            'macros' => $macros,
        ]];
    }

    private function computeBmr(string $sexe, int $poidsKg, int $tailleCm, int $age): float
    {
        $base = (10.0 * $poidsKg) + (6.25 * $tailleCm) - (5.0 * $age);
        return $sexe === 'homme' ? ($base + 5.0) : ($base - 161.0);
    }

    private function computeTdee(float $bmr, string $activite): float
    {
        $factors = [
            'sedentaire' => 1.2,
            'leger' => 1.375,
            'modere' => 1.55,
            'intense' => 1.725,
            'extreme' => 1.9,
        ];
        $factor = $factors[$activite] ?? 1.2;
        return $bmr * $factor;
    }

    private function applyTypeAdjustment(float $tdee, string $type): int
    {
        if ($type === 'perte') {
            return (int) round($tdee * 0.85);
        }
        if ($type === 'prise') {
            return (int) round($tdee * 1.10);
        }
        return (int) round($tdee);
    }

    private function buildMacrosString(int $calories, string $type): string
    {
        $ratios = [
            'maintien' => ['p' => 0.30, 'g' => 0.40, 'l' => 0.30],
            'perte' => ['p' => 0.35, 'g' => 0.35, 'l' => 0.30],
            'prise' => ['p' => 0.25, 'g' => 0.50, 'l' => 0.25],
        ];
        $ratio = $ratios[$type] ?? $ratios['maintien'];
        $proteines = (int) round(($calories * $ratio['p']) / 4);
        $glucides = (int) round(($calories * $ratio['g']) / 4);
        $lipides = (int) round(($calories * $ratio['l']) / 9);
        return sprintf('P:%dg|G:%dg|L:%dg', $proteines, $glucides, $lipides);
    }

    private function intFromGet(string $key): ?int
    {
        if (!isset($_GET[$key])) {
            return null;
        }
        $v = Validation::trim((string) $_GET[$key]);
        if ($v === '' || !ctype_digit($v)) {
            return null;
        }
        return (int) $v;
    }

    private function intFromPost(string $key): ?int
    {
        if (!isset($_POST[$key])) {
            return null;
        }
        $v = Validation::trim((string) $_POST[$key]);
        if ($v === '' || !ctype_digit($v)) {
            return null;
        }
        return (int) $v;
    }
}

