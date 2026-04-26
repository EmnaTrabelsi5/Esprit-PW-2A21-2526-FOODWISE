<?php

declare(strict_types=1);

namespace Controller\Shared;

use Controller\Controller;
use Controller\Url;
use Controller\Validation;
use Model\Objectif;
use Model\PlanAlimentaire;

/**
 * Logique CRUD partagée front / back (validation PHP dans les actions).
 *
 * @mixin Controller
 */
trait PlanAlimentaireCrud
{
    abstract protected function layoutArea(): string;

    private ?array $objectifsCache = null;

    public function index(): void
    {
        try {
            $plans = (new PlanAlimentaire())->all();
        } catch (\Throwable) {
            $plans = [];
            $_SESSION['flash_error'] = 'Impossible de lire les plans (connexion PDO ou tables manquantes — importez database/schema.sql).';
        }
        $flashSuccess = $_SESSION['flash_success'] ?? null;
        unset($_SESSION['flash_success']);
        $flashError = $_SESSION['flash_error'] ?? null;
        unset($_SESSION['flash_error']);
        $this->view($this->layoutArea() . '/plan_alimentaire/index', [
            'plans' => $plans,
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
            $this->redirect(Url::to($this->layoutArea(), 'plan_alimentaire', 'index'));
        }
        $plan = (new PlanAlimentaire())->find($id);
        if ($plan === null) {
            $_SESSION['flash_error'] = 'Plan introuvable.';
            $this->redirect(Url::to($this->layoutArea(), 'plan_alimentaire', 'index'));
        }
        $flashError = $_SESSION['flash_error'] ?? null;
        unset($_SESSION['flash_error']);
        $this->view($this->layoutArea() . '/plan_alimentaire/show', [
            'plan' => $plan,
            'flashError' => $flashError,
            'url' => Url::class,
        ]);
    }

    public function create(): void
    {
        $this->view($this->layoutArea() . '/plan_alimentaire/form', [
            'plan' => null,
            'objectifs' => $this->loadObjectifsSafe(),
            'errors' => [],
            'old' => [],
            'url' => Url::class,
            'csrf' => $this->csrfToken(),
        ]);
    }

    public function store(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(Url::to($this->layoutArea(), 'plan_alimentaire', 'index'));
        }
        if (!$this->validateCsrf($_POST['_csrf'] ?? null)) {
            $_SESSION['flash_error'] = 'Jeton de sécurité invalide. Réessayez.';
            $this->redirect(Url::to($this->layoutArea(), 'plan_alimentaire', 'create'));
        }

        [$errors, $payload] = $this->validatePlanPayload($_POST);
        if ($errors !== []) {
            $this->view($this->layoutArea() . '/plan_alimentaire/form', [
                'plan' => null,
                'objectifs' => $this->loadObjectifsSafe(),
                'errors' => $errors,
                'old' => $_POST,
                'url' => Url::class,
                'csrf' => $this->csrfToken(),
            ]);
            return;
        }

        try {
            (new PlanAlimentaire())->insert($payload);
            $_SESSION['flash_success'] = 'Plan alimentaire créé avec succès.';
            $this->redirect(Url::to($this->layoutArea(), 'plan_alimentaire', 'index'));
        } catch (\Throwable $e) {
            $_SESSION['flash_error'] = 'Erreur lors de la création : ' . $e->getMessage();
            $this->view($this->layoutArea() . '/plan_alimentaire/form', [
                'plan' => null,
                'objectifs' => $this->loadObjectifsSafe(),
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
            $this->redirect(Url::to($this->layoutArea(), 'plan_alimentaire', 'index'));
        }
        $plan = (new PlanAlimentaire())->find($id);
        if ($plan === null) {
            $_SESSION['flash_error'] = 'Plan introuvable.';
            $this->redirect(Url::to($this->layoutArea(), 'plan_alimentaire', 'index'));
        }
        $this->view($this->layoutArea() . '/plan_alimentaire/form', [
            'plan' => $plan,
            'objectifs' => $this->loadObjectifsSafe(),
            'errors' => [],
            'old' => [],
            'url' => Url::class,
            'csrf' => $this->csrfToken(),
        ]);
    }

    public function update(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(Url::to($this->layoutArea(), 'plan_alimentaire', 'index'));
        }
        if (!$this->validateCsrf($_POST['_csrf'] ?? null)) {
            $_SESSION['flash_error'] = 'Jeton de sécurité invalide.';
            $this->redirect(Url::to($this->layoutArea(), 'plan_alimentaire', 'index'));
        }

        $id = $this->intFromPost('id');
        if ($id === null) {
            $this->redirect(Url::to($this->layoutArea(), 'plan_alimentaire', 'index'));
        }
        $existing = (new PlanAlimentaire())->find($id);
        if ($existing === null) {
            $_SESSION['flash_error'] = 'Plan introuvable.';
            $this->redirect(Url::to($this->layoutArea(), 'plan_alimentaire', 'index'));
        }

        [$errors, $payload] = $this->validatePlanPayload($_POST);
        if ($errors !== []) {
            $this->view($this->layoutArea() . '/plan_alimentaire/form', [
                'plan' => $existing,
                'objectifs' => $this->loadObjectifsSafe(),
                'errors' => $errors,
                'old' => $_POST,
                'url' => Url::class,
                'csrf' => $this->csrfToken(),
            ]);
            return;
        }

        try {
            (new PlanAlimentaire())->update($id, $payload);
            $_SESSION['flash_success'] = 'Plan alimentaire mis à jour.';
            $this->redirect(Url::to($this->layoutArea(), 'plan_alimentaire', 'index'));
        } catch (\Throwable $e) {
            $_SESSION['flash_error'] = 'Erreur lors de la mise à jour : ' . $e->getMessage();
            $this->view($this->layoutArea() . '/plan_alimentaire/form', [
                'plan' => $existing,
                'objectifs' => $this->loadObjectifsSafe(),
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
            $this->redirect(Url::to($this->layoutArea(), 'plan_alimentaire', 'index'));
        }
        if (!$this->validateCsrf($_POST['_csrf'] ?? null)) {
            $_SESSION['flash_error'] = 'Jeton de sécurité invalide.';
            $this->redirect(Url::to($this->layoutArea(), 'plan_alimentaire', 'index'));
        }
        $id = $this->intFromPost('id');
        if ($id === null) {
            $this->redirect(Url::to($this->layoutArea(), 'plan_alimentaire', 'index'));
        }
        try {
            (new PlanAlimentaire())->delete($id);
            $_SESSION['flash_success'] = 'Plan alimentaire supprimé.';
        } catch (\Throwable $e) {
            $_SESSION['flash_error'] = 'Erreur lors de la suppression : ' . $e->getMessage();
        }
        $this->redirect(Url::to($this->layoutArea(), 'plan_alimentaire', 'index'));
    }

    /** @return array{0: list<string>, 1: array{titre: string, description: ?string, date_debut: string, date_fin: string, calories_cible: ?int, statut: string, id_obj: ?int}} */
    private function validatePlanPayload(array $post): array
    {
        $errors = [];
        $titre = Validation::trim($post['titre'] ?? '');
        $description = Validation::trim($post['description'] ?? '');
        $descNull = $description === '' ? null : $description;
        $dateDebut = Validation::requireDate($post['date_debut'] ?? null, 'Date de début', $errors);
        $dateFin = Validation::requireDate($post['date_fin'] ?? null, 'Date de fin', $errors);
        Validation::dateOrder($dateDebut, $dateFin, $errors);
        $calories = Validation::optionalInt($post['calories_cible'] ?? '', 'Calories cible', 800, 8000, $errors);
        $objectifId = Validation::optionalInt($post['id_obj'] ?? '', 'Objectif lié', 1, 1000000000, $errors);
        if ($objectifId !== null && !$this->objectifExistsSafe($objectifId)) {
            $errors[] = 'L\'objectif sélectionné est introuvable.';
        }
        $statut = Validation::trim($post['statut'] ?? '');
        $allowedStatuts = ['brouillon', 'actif', 'archive'];
        Validation::requireNonEmpty($statut, 'Statut', $errors);
        Validation::requireOneOf($statut, $allowedStatuts, 'Statut', $errors);
        Validation::requireNonEmpty($titre, 'Titre', $errors);
        Validation::maxLength($titre, 200, 'Titre', $errors);
        if ($descNull !== null) {
            Validation::maxLength($description, 5000, 'Description', $errors);
        }

        $payload = [
            'titre' => $titre,
            'description' => $descNull,
            'date_debut' => $dateDebut ?? '',
            'date_fin' => $dateFin ?? '',
            'calories_cible' => $calories,
            'statut' => $statut,
            'id_obj' => $objectifId,
        ];

        return [$errors, $payload];
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

    private function loadObjectifsSafe(): array
    {
        if (is_array($this->objectifsCache)) {
            return $this->objectifsCache;
        }
        try {
            $this->objectifsCache = (new Objectif())->all();
        } catch (\Throwable) {
            $this->objectifsCache = [];
        }
        return $this->objectifsCache;
    }

    private function objectifExistsSafe(int $id): bool
    {
        try {
            return (new Objectif())->find($id) !== null;
        } catch (\Throwable) {
            return false;
        }
    }
}
