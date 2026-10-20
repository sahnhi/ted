<?php

declare(strict_types=1);

namespace App\Generator;

use Sylius\Component\Core\Model\OrderInterface;
use Sylius\InvoicingPlugin\Entity\InvoiceInterface;
use Sylius\InvoicingPlugin\Generator\InvoiceGeneratorInterface as BaseInvoiceGeneratorInterface;

interface InvoiceGeneratorInterface extends BaseInvoiceGeneratorInterface
{
    public function generateForOrder(OrderInterface $order, \DateTimeInterface $date): InvoiceInterface;

    public function generateForOrderWithType(OrderInterface $order, \DateTimeInterface $date, ?string $type = null): InvoiceInterface;
}
