<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Doctrine\Entity\ExportAccountingData;
use App\Message\ExportAccountingData as MessageExportAccountingData;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\Messenger\MessageBusInterface;

class ExportAccountingDataNotifier
{
    public function __construct(
        private readonly MessageBusInterface $messageBus,
    ) {
    }

    public function postPersist(LifecycleEventArgs $event): void
    {
        $exportAccountingData = $event->getObject();
        if ($exportAccountingData instanceof ExportAccountingData) {
            $messageExportAccountingData = new MessageExportAccountingData($exportAccountingData->getId());
            $this->messageBus->dispatch($messageExportAccountingData);
        }
    }
}
