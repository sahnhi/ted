<?php

namespace App\Factory\View;

use App\View\PageView;
use Pagerfanta\Pagerfanta;

interface PageViewFactoryInterface
{
    public function create(Pagerfanta $pagerfanta, string $route, array $parameters): PageView;
}
