<?php

declare(strict_types=1);

namespace Controller;

final class Router
{
    public function dispatch(): void
    {
        $area = $_GET['area'] ?? 'front';
        if (!in_array($area, ['front', 'back'], true)) {
            $area = 'front';
        }

        $resource = preg_replace('/[^a-z_]/', '', (string) ($_GET['resource'] ?? 'home'));
        $action = preg_replace('/[^a-z_]/', '', (string) ($_GET['action'] ?? 'index'));

        if ($resource === '') {
            $resource = 'home';
        }
        if ($action === '') {
            $action = 'index';
        }

        $map = [
            'home' => [
                'front' => [\Controller\Front\HomeController::class, 'index'],
                'back' => [\Controller\Back\HomeController::class, 'index'],
            ],
            'plan_alimentaire' => [
                'front' => [\Controller\Front\PlanAlimentaireController::class, $this->resolveCrudAction($action)],
                'back' => [\Controller\Back\PlanAlimentaireController::class, $this->resolveCrudAction($action)],
            ],
            'plan_recette' => [
                'front' => [\Controller\Front\PlanRecetteController::class, $this->resolveCrudAction($action)],
                'back' => [\Controller\Back\PlanRecetteController::class, $this->resolveCrudAction($action)],
            ],
            'objectif' => [
                'front' => [\Controller\Front\ObjectifController::class, $this->resolveCrudAction($action)],
                'back' => [\Controller\Back\ObjectifController::class, $this->resolveCrudAction($action)],
            ],
        ];

        if (!isset($map[$resource][$area])) {
            http_response_code(404);
            echo 'Ressource inconnue.';
            return;
        }

        [$class, $method] = $map[$resource][$area];
        if (!class_exists($class) || !method_exists($class, $method)) {
            http_response_code(404);
            echo 'Action introuvable.';
            return;
        }

        $controller = new $class();
        $controller->{$method}();
    }

    private function resolveCrudAction(string $action): string
    {
        $allowed = ['index', 'show', 'create', 'store', 'edit', 'update', 'destroy'];
        return in_array($action, $allowed, true) ? $action : 'index';
    }
}
