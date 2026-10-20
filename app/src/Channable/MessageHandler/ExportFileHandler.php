<?php

declare(strict_types=1);

namespace App\Channable\MessageHandler;

use App\Channable\Message\ExportFile;
use App\Channable\Message\ExportProductRow;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

final class ExportFileHandler implements MessageHandlerInterface
{
    public function __construct(
        private readonly RepositoryInterface $channableFileLogRepository,
        private readonly RepositoryInterface $productFeedFileRepository,
        private readonly MessageBusInterface $bus,
    ) {
    }

    public function __invoke(ExportFile $message): void
    {
        $channableFileLog = $this->channableFileLogRepository->find($message->getChannableFileLogId());
        $products = $this->productFeedFileRepository->findBy(['enabled' => true]);
        foreach ($products as $product) {
            $this->bus->dispatch(new ExportProductRow($channableFileLog->getId(), $product->getId()));
        }
    }
}
