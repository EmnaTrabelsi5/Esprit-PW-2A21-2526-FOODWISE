<?php

declare(strict_types=1);

namespace Controller\Back;

use Controller\Shared\ObjectifCrud;
use Controller\Controller;

final class ObjectifController extends Controller
{
    use ObjectifCrud;

    protected function layoutArea(): string
    {
        return 'back';
    }
}

