<?php

namespace App\Services\Mail;

use Sylius\Component\Core\Model\OrderInterface;
use Sylius\InvoicingPlugin\Entity\InvoiceInterface;

interface InvoiceEmailSenderInterface
{
    public function sendInvoiceEmail(OrderInterface $order, InvoiceInterface $invoice): void;
}
