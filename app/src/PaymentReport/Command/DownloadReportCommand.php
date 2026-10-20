<?php

declare(strict_types=1);

namespace App\PaymentReport\Command;

use App\Utils\Url;

final class DownloadReportCommand
{
    public function __construct(
        private readonly Url $reportToDownload
    ) {
    }

    public function getReportToDownload(): Url
    {
        return $this->reportToDownload;
    }
}
