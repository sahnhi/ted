<?php

namespace App\EventListener;

use Sylius\InvoicingPlugin\Event\OrderPlaced;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class NoInvoiceOnOrderPlacedListener implements MessageHandlerInterface
{
    public function __invoke(OrderPlaced $event): void
    {
        // intentionally left blank
    }
}
