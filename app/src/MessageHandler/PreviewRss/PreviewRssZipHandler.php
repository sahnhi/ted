<?php

namespace App\MessageHandler\PreviewRss;

use App\Message\PreviewRss\PreviewRssZip;
use App\Services\Review\Rss\RssZipFileGeneratorInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class PreviewRssZipHandler implements MessageHandlerInterface
{
    public function __construct(
        private readonly RssZipFileGeneratorInterface $rssZipFileGenerator
    ) {
    }

    public function __invoke(PreviewRssZip $message): void
    {
        $this->rssZipFileGenerator->generate();
    }
}
