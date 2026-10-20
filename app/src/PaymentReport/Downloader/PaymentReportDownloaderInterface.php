<?php

namespace App\PaymentReport\Downloader;

use App\PaymentReport\Model\PaymentList;
use App\Utils\Url;

interface PaymentReportDownloaderInterface
{
    public function download(Url $url): PaymentList;
}
