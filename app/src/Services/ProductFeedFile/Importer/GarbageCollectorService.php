<?php

declare(strict_types=1);

namespace App\Services\ProductFeedFile\Importer;

use Sylius\Component\Resource\Repository\RepositoryInterface;

final class GarbageCollectorService implements GarbageCollectorServiceInterface
{
    public function __construct(
        private readonly RepositoryInterface $productFeedFileImporterRepository
    ) {
    }

    public function clear(?int $toleranceTimeInSeconds = self::PROCESSING_TOLERANCE_TIME): void
    {
        $processingFiles = $this->productFeedFileImporterRepository->findBy(['state' => 'processing']);

        $toleranceDateTime = new \DateTimeImmutable(sprintf('-%d seconds', $toleranceTimeInSeconds));
        foreach ($processingFiles as $processingFile) {
            if ($processingFile->getCreatedAt() < $toleranceDateTime) {
                $processingFile->setState('failed');
                $this->productFeedFileImporterRepository->add($processingFile);
            }
        }
    }
}
