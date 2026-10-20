<?php

namespace App\Message\PreviewRss;

class PreviewRssXml
{
    public function __construct(
        private readonly int $offset,
        private readonly int $rssTotal
    ) {
    }

    public function getOffset(): int
    {
        return $this->offset;
    }

    public function getRssTotal(): int
    {
        return $this->rssTotal;
    }
}
