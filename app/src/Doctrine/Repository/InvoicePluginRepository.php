<?php

declare(strict_types=1);

namespace App\Doctrine\Repository;

use App\Doctrine\Entity\Sylius\InvoiceInterface;
use App\Identifier\OrderNumber;
use Sylius\InvoicingPlugin\Doctrine\ORM\InvoiceRepository;

final class InvoicePluginRepository extends InvoiceRepository implements InvoicePluginRepositoryInterface
{
    public function findCarrierInvoiceByOrderNumber(OrderNumber $orderNumber): ?InvoiceInterface
    {
        return $this->findOneBy(
            [
                'orderNumber' => $orderNumber->getOrderNumber(),
                'type' => InvoiceInterface::INVOICE_TYPE_CARRIER,
            ]
        );
    }

    public function findInvoiceByOrderNumber(OrderNumber $orderNumber): ?InvoiceInterface
    {
        return $this->findOneBy(
            [
                'orderNumber' => $orderNumber->getOrderNumber(),
                'type' => null,
            ]
        );
    }

    public function countCarrierInvoiceByOrderNumber(OrderNumber $orderNumber): int
    {
        return $this->count(
            [
                'orderNumber' => $orderNumber->getOrderNumber(),
                'type' => InvoiceInterface::INVOICE_TYPE_CARRIER,
            ]
        );
    }

    public function findOneByOrderNumber(string $orderNumber): ?InvoiceInterface
    {
        /** @var InvoiceInterface|null $invoice */
        $invoice = $this->findOneBy(['orderNumber' => $orderNumber, 'type' => null]);

        return $invoice;
    }
}
