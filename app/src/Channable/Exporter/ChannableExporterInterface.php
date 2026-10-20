<?php

declare(strict_types=1);

namespace App\Channable\Exporter;

interface ChannableExporterInterface
{
    public function export(): void;
}
