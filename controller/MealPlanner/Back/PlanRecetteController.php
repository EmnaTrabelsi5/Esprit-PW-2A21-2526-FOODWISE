<?php

declare(strict_types=1);

namespace Controller\Back;

use Controller\Shared\PlanRecetteCrud;
use Controller\Controller;

final class PlanRecetteController extends Controller
{
    use PlanRecetteCrud;

    protected function layoutArea(): string
    {
        return 'back';
    }
}

