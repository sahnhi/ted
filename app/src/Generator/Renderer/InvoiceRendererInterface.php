<?php

namespace App\Generator\Renderer;

use App\Doctrine\Entity\Sylius\InvoiceInterface;
use Sylius\Component\Addressing\Model\ZoneInterface;
use Sylius\Component\Customer\Model\CustomerInterface;
use Sylius\Component\Order\Model\OrderInterface;

interface InvoiceRendererInterface
{
    public function support(ZoneInterface $taxZone, string $channelCode, ?string $type = null): bool;

    public function renderTemplate(InvoiceInterface $invoice, OrderInterface $order, CustomerInterface $customer, ZoneInterface $taxZone, string $channelCode, ?string $type = null): string;
}
