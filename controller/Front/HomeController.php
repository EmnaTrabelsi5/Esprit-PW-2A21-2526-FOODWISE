<?php

declare(strict_types=1);

namespace Controller\Front;

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
            $plans = (new PlanAlimentaire())->all();
            $recetteCount = count((new PlanRecette())->all());
            $objectifCount = count((new Objectif())->all());
        } catch (\Throwable) {
            $plans = [];
            $recetteCount = 0;
            $objectifCount = 0;
        }

        $this->view('front/home', [
            'plans' => $plans,
            'recetteCount' => $recetteCount,
            'objectifCount' => $objectifCount,
            'semaineLabel' => 'Semaine en cours',
            'url' => Url::class,
        ]);
    }
}
