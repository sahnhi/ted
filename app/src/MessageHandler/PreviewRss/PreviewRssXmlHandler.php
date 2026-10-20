<?php

namespace App\MessageHandler\PreviewRss;

use App\Message\PreviewRss\PreviewRssXml;
use App\Services\Review\Rss\RssXmlFileGenerator;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class PreviewRssXmlHandler implements MessageHandlerInterface
{
    public function __construct(
        private readonly RssXmlFileGenerator $rssXmlFileGenerator
    ) {
    }

    public function __invoke(PreviewRssXml $message): void
    {
        $this->rssXmlFileGenerator->generate($message->getOffset(), $message->getRssTotal());
    }
}
