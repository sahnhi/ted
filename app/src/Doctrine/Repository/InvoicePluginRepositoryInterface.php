<?php

namespace App\Doctrine\Repository;

use App\Doctrine\Entity\Sylius\InvoiceInterface;
use App\Identifier\OrderNumber;
use Sylius\InvoicingPlugin\Doctrine\ORM\InvoiceRepositoryInterface;

interface InvoicePluginRepositoryInterface extends InvoiceRepositoryInterface
{
    public function findCarrierInvoiceByOrderNumber(OrderNumber $orderNumber): ?InvoiceInterface;

    public function findInvoiceByOrderNumber(OrderNumber $orderNumber): ?InvoiceInterface;

    public function countCarrierInvoiceByOrderNumber(OrderNumber $orderNumber): int;
}
