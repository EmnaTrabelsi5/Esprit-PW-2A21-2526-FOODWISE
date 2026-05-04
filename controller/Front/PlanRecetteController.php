<?php

declare(strict_types=1);

namespace Controller\Front;

use Controller\Shared\PlanRecetteCrud;
use Controller\Controller;

final class PlanRecetteController extends Controller
{
    use PlanRecetteCrud;

    protected function layoutArea(): string
    {
        return 'front';
    }
}
