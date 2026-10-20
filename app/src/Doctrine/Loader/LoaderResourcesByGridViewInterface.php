<?php

declare(strict_types=1);

namespace App\Doctrine\Loader;

use Sylius\Bundle\ResourceBundle\Grid\View\ResourceGridView;

interface LoaderResourcesByGridViewInterface
{
    public function load(ResourceGridView $resourceGridView, int $offset): array;
}
