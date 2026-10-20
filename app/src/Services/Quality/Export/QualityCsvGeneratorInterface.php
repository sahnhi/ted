<?php

declare(strict_types=1);

namespace App\Services\Quality\Export;

use Sylius\Bundle\ResourceBundle\Grid\View\ResourceGridView;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\StreamedResponse;

interface QualityCsvGeneratorInterface
{
    public const FORMAT = 'csv';

    public const MAX_ITEM_EXPORT = 10000;

    public function generateCsvFromResourceGridView(ResourceGridView $resources, ?SessionInterface $session = null): StreamedResponse;
}
