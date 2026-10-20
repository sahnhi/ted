<?php

namespace App\Services\Cron\Review\Preview;

interface PreviewRssXmlPublisherInterface
{
    public function publish(int $offset, int $total): void;

    public function publishFirst(): void;
}
