<?php

declare(strict_types=1);

namespace Controller\Back;

use Controller\Shared\PlanAlimentaireCrud;
use Controller\Controller;

final class PlanAlimentaireController extends Controller
{
    use PlanAlimentaireCrud;

    protected function layoutArea(): string
    {
        return 'back';
    }
}

