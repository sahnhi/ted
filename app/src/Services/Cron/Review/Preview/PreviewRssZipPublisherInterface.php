<?php

namespace App\Services\Cron\Review\Preview;

interface PreviewRssZipPublisherInterface
{
    public function publish(): void;
}
