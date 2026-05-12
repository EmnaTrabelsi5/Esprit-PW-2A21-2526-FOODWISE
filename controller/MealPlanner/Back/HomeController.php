<?php

declare(strict_types=1);

namespace Controller\Back;

use Controller\Controller;
use Controller\Url;
use Model\Objectif;
use Model\PlanAlimentaire;
use Model\PlanRecette;

final class HomeController extends Controller
{
    public function index(): void
    {
        try {
            $planCount = count((new PlanAlimentaire())->all());
            $recetteCount = count((new PlanRecette())->all());
            $objectifCount = count((new Objectif())->all());
        } catch (\Throwable) {
            $planCount = 0;
            $recetteCount = 0;
            $objectifCount = 0;
        }

        $this->view('back/home', [
            'planCount' => $planCount,
            'recetteCount' => $recetteCount,
            'objectifCount' => $objectifCount,
            'url' => Url::class,
        ]);
    }
}

