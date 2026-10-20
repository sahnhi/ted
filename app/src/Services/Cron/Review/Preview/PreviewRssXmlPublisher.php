<?php

declare(strict_types=1);

namespace App\Services\Cron\Review\Preview;

use App\Doctrine\Repository\Sylius\ProductReviewRepositoryInterface;
use App\Message\PreviewRss\PreviewRssXml;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

final class PreviewRssXmlPublisher implements PreviewRssXmlPublisherInterface
{
    public function __construct(
        private readonly MessageBusInterface $bus,
        private readonly ProductReviewRepositoryInterface $productReviewRepository,
        private readonly LoggerInterface $googleshoppingLogger
    ) {
    }

    public function publishFirst(): void
    {
        $previewRssCount = $this->productReviewRepository->countRssReview();

        if ($previewRssCount > 0) {
            $this->publish(0, $previewRssCount);
        }
    }

    public function publish(int $offset, int $total): void
    {
        $this->googleshoppingLogger->info('[Google Shopping] XML file published', [
            'offset' => $offset,
            'total' => $total,
        ]);

        $previewRss = new PreviewRssXml($offset, $total);
        $this->bus->dispatch($previewRss);
    }
}
