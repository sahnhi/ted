<?php

namespace App\Creator;

use Sylius\Component\Core\Model\OrderInterface;
use Sylius\InvoicingPlugin\Creator\InvoiceCreatorInterface;
use Sylius\InvoicingPlugin\Creator\MassInvoicesCreatorInterface;
use Sylius\InvoicingPlugin\Exception\InvoiceAlreadyGenerated;

final class MassInvoicesCreator implements MassInvoicesCreatorInterface
{
    public function __construct(
        private readonly InvoiceCreatorInterface $invoiceCreator
    ) {
    }

    public function __invoke(array $orders): void
    {
        /** @var OrderInterface $order */
        foreach ($orders as $order) {
            try {
                $this->invoiceCreator->__invoke($order->getNumber(), $order->getCheckoutCompletedAt());
            } catch (InvoiceAlreadyGenerated) {
                continue;
            }
        }
    }
}
