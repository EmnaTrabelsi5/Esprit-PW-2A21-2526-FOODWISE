<?php

declare(strict_types=1);

namespace Controller\Shared;

use Controller\Controller;
use Controller\Url;
use Controller\Validation;
use Model\PlanAlimentaire;
use Model\PlanRecette;

/**
 * @mixin Controller
 */
trait PlanRecetteCrud
{
    abstract protected function layoutArea(): string;

    public function index(): void
    {
        $planFilter = $this->intFromGet('plan_id');
        try {
            $model = new PlanRecette();
            if ($planFilter !== null) {
                $recettes = $model->forPlan($planFilter);
                $filteredPlan = (new PlanAlimentaire())->find($planFilter);
            } else {
                $recettes = $model->all();
                $filteredPlan = null;
            }
        } catch (\Throwable) {
            $recettes = [];
            $filteredPlan = null;
            $_SESSION['flash_error'] = 'Impossible de lire les recettes (PDO ou schéma — voir database/schema.sql).';
        }
        $flashSuccess = $_SESSION['flash_success'] ?? null;
        unset($_SESSION['flash_success']);
        $flashError = $_SESSION['flash_error'] ?? null;
        unset($_SESSION['flash_error']);
        $this->view($this->layoutArea() . '/plan_recette/index', [
            'recettes' => $recettes,
            'filteredPlan' => $filteredPlan,
            'planFilterId' => $planFilter,
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
            $this->redirect(Url::to($this->layoutArea(), 'plan_recette', 'index'));
        }
        $recette = (new PlanRecette())->find($id);
        if ($recette === null) {
            $_SESSION['flash_error'] = 'Entrée introuvable.';
            $this->redirect(Url::to($this->layoutArea(), 'plan_recette', 'index'));
        }
        $this->view($this->layoutArea() . '/plan_recette/show', [
            'recette' => $recette,
            'url' => Url::class,
        ]);
    }

    public function create(): void
    {
        $planId = $this->intFromGet('plan_id');
        $plan = null;
        if ($planId !== null) {
            $plan = (new PlanAlimentaire())->find($planId);
        }
        $plans = (new PlanAlimentaire())->all();
        $this->view($this->layoutArea() . '/plan_recette/form', [
            'recette' => null,
            'plan' => $plan,
            'plans' => $plans,
            'errors' => [],
            'old' => [],
            'url' => Url::class,
            'csrf' => $this->csrfToken(),
        ]);
    }

    public function store(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(Url::to($this->layoutArea(), 'plan_recette', 'index'));
        }
        if (!$this->validateCsrf($_POST['_csrf'] ?? null)) {
            $_SESSION['flash_error'] = 'Jeton de sécurité invalide.';
            $this->redirect(Url::to($this->layoutArea(), 'plan_recette', 'create'));
        }

        [$errors, $payload] = $this->validateRecettePayload($_POST, true);
        if ($errors !== []) {
            $planId = $this->intFromPost('plan_alimentaire_id');
            $plan = $planId !== null ? (new PlanAlimentaire())->find($planId) : null;
            $this->view($this->layoutArea() . '/plan_recette/form', [
                'recette' => null,
                'plan' => $plan,
                'plans' => (new PlanAlimentaire())->all(),
                'errors' => $errors,
                'old' => $_POST,
                'url' => Url::class,
                'csrf' => $this->csrfToken(),
            ]);
            return;
        }

        try {
            (new PlanRecette())->insert($payload);
            $_SESSION['flash_success'] = 'Recette de plan enregistrée.';
            $this->redirect(Url::to($this->layoutArea(), 'plan_recette', 'index', ['plan_id' => (string) $payload['plan_alimentaire_id']]));
        } catch (\Throwable $e) {
            $_SESSION['flash_error'] = 'Erreur lors de l\'enregistrement : ' . $e->getMessage();
            $planId = $this->intFromPost('plan_alimentaire_id');
            $plan = $planId !== null ? (new PlanAlimentaire())->find($planId) : null;
            $this->view($this->layoutArea() . '/plan_recette/form', [
                'recette' => null,
                'plan' => $plan,
                'plans' => (new PlanAlimentaire())->all(),
                'errors' => [$e->getMessage()],
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
            $this->redirect(Url::to($this->layoutArea(), 'plan_recette', 'index'));
        }
        $recette = (new PlanRecette())->find($id);
        if ($recette === null) {
            $_SESSION['flash_error'] = 'Entrée introuvable.';
            $this->redirect(Url::to($this->layoutArea(), 'plan_recette', 'index'));
        }
        $plan = (new PlanAlimentaire())->find((int) $recette['plan_alimentaire_id']);
        $this->view($this->layoutArea() . '/plan_recette/form', [
            'recette' => $recette,
            'plan' => $plan,
            'plans' => (new PlanAlimentaire())->all(),
            'errors' => [],
            'old' => [],
            'url' => Url::class,
            'csrf' => $this->csrfToken(),
        ]);
    }

    public function update(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(Url::to($this->layoutArea(), 'plan_recette', 'index'));
        }
        if (!$this->validateCsrf($_POST['_csrf'] ?? null)) {
            $_SESSION['flash_error'] = 'Jeton de sécurité invalide.';
            $this->redirect(Url::to($this->layoutArea(), 'plan_recette', 'index'));
        }

        $id = $this->intFromPost('id');
        if ($id === null) {
            $this->redirect(Url::to($this->layoutArea(), 'plan_recette', 'index'));
        }
        $existing = (new PlanRecette())->find($id);
        if ($existing === null) {
            $_SESSION['flash_error'] = 'Entrée introuvable.';
            $this->redirect(Url::to($this->layoutArea(), 'plan_recette', 'index'));
        }

        [$errors, $payload] = $this->validateRecettePayload($_POST, false);
        if ($errors !== []) {
            $plan = (new PlanAlimentaire())->find((int) $existing['plan_alimentaire_id']);
            $this->view($this->layoutArea() . '/plan_recette/form', [
                'recette' => $existing,
                'plan' => $plan,
                'plans' => (new PlanAlimentaire())->all(),
                'errors' => $errors,
                'old' => $_POST,
                'url' => Url::class,
                'csrf' => $this->csrfToken(),
            ]);
            return;
        }

        try {
            (new PlanRecette())->update($id, $payload);
            $_SESSION['flash_success'] = 'Recette mise à jour.';
            $this->redirect(Url::to($this->layoutArea(), 'plan_recette', 'index', ['plan_id' => (string) $existing['plan_alimentaire_id']]));
        } catch (\Throwable $e) {
            $_SESSION['flash_error'] = 'Erreur lors de la mise à jour : ' . $e->getMessage();
            $plan = (new PlanAlimentaire())->find((int) $existing['plan_alimentaire_id']);
            $this->view($this->layoutArea() . '/plan_recette/form', [
                'recette' => $existing,
                'plan' => $plan,
                'plans' => (new PlanAlimentaire())->all(),
                'errors' => [$e->getMessage()],
                'old' => $_POST,
                'url' => Url::class,
                'csrf' => $this->csrfToken(),
            ]);
        }
    }

    public function destroy(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(Url::to($this->layoutArea(), 'plan_recette', 'index'));
        }
        if (!$this->validateCsrf($_POST['_csrf'] ?? null)) {
            $_SESSION['flash_error'] = 'Jeton de sécurité invalide.';
            $this->redirect(Url::to($this->layoutArea(), 'plan_recette', 'index'));
        }
        $id = $this->intFromPost('id');
        if ($id === null) {
            $this->redirect(Url::to($this->layoutArea(), 'plan_recette', 'index'));
        }
        $row = (new PlanRecette())->find($id);
        $planId = $row ? (int) $row['plan_alimentaire_id'] : null;
        try {
            (new PlanRecette())->delete($id);
            $_SESSION['flash_success'] = 'Recette supprimée du plan.';
        } catch (\Throwable $e) {
            $_SESSION['flash_error'] = 'Erreur lors de la suppression : ' . $e->getMessage();
        }
        $q = $planId !== null ? ['plan_id' => (string) $planId] : [];
        $this->redirect(Url::to($this->layoutArea(), 'plan_recette', 'index', $q));
    }

    /**
     * @return array{0: list<string>, 1: array<string, mixed>}
     */
    private function validateRecettePayload(array $post, bool $withPlanId): array
    {
        $errors = [];
        $jours = ['lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi', 'dimanche'];
        $creneaux = ['dejeuner', 'diner', 'collation'];

        $planId = null;
        if ($withPlanId) {
            $planId = $this->intFromPost('plan_alimentaire_id');
            if ($planId === null) {
                $errors[] = 'Vous devez sélectionner un plan alimentaire.';
            } elseif ((new PlanAlimentaire())->find($planId) === null) {
                $errors[] = 'Le plan alimentaire choisi n\'existe pas.';
            }
        }

        $jour = Validation::trim($post['jour_semaine'] ?? '');
        Validation::requireNonEmpty($jour, 'Jour de la semaine', $errors);
        Validation::requireOneOf($jour, $jours, 'Jour de la semaine', $errors);

        $creneau = Validation::trim($post['creneau'] ?? '');
        Validation::requireNonEmpty($creneau, 'Créneau', $errors);
        Validation::requireOneOf($creneau, $creneaux, 'Créneau', $errors);

        $nom = Validation::trim($post['nom_recette'] ?? '');
        Validation::requireNonEmpty($nom, 'Nom de la recette', $errors);
        Validation::maxLength($nom, 200, 'Nom de la recette', $errors);

        $duree = Validation::optionalInt($post['duree_minutes'] ?? '', 'Durée (minutes)', 1, 600, $errors);
        $notes = Validation::trim($post['notes'] ?? '');
        $notesNull = $notes === '' ? null : $notes;
        if ($notesNull !== null) {
            Validation::maxLength($notes, 2000, 'Notes', $errors);
        }

        $base = [
            'jour_semaine' => $jour,
            'creneau' => $creneau,
            'nom_recette' => $nom,
            'duree_minutes' => $duree,
            'notes' => $notesNull,
        ];

        if ($errors !== []) {
            return [$errors, []];
        }

        if ($withPlanId) {
            $planId = $this->intFromPost('plan_alimentaire_id');
            if ($planId === null || (new PlanAlimentaire())->find($planId) === null) {
                return [['Le plan alimentaire est invalide.'], []];
            }
            $payload = array_merge(['plan_alimentaire_id' => $planId], $base);
        } else {
            $payload = $base;
        }

        return [[], $payload];
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
