<?php

namespace App\Services\Review\Rss;

interface RssXmlFileGeneratorInterface
{
    public function generate(int $offset, int $total): void;
}
