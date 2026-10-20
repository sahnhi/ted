<?php

declare(strict_types=1);

namespace App\Importer\ProductFeed;

interface ProductFeedImporterInterface
{
    public function import(): void;
}
