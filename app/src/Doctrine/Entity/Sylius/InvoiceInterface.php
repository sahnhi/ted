<?php

namespace App\Doctrine\Entity\Sylius;

use Sylius\InvoicingPlugin\Entity\InvoiceInterface as BaseInvoiceInterface;

interface InvoiceInterface extends BaseInvoiceInterface
{
    public const INVOICE_TYPE_CARRIER = 'carrier';

    public function getType(): ?string;

    public function setType(?string $type): void;
}
