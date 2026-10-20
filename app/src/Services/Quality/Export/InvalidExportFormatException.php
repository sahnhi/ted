<?php

declare(strict_types=1);

namespace App\Services\Quality\Export;

class InvalidExportFormatException extends \Exception
{
    public function __construct(string $format = '', $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct(sprintf('Export format %s not valid', $format), $code, $previous);
    }
}
