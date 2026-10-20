<?php

declare(strict_types=1);

namespace App\Generator\Renderer;

use App\Doctrine\Entity\Sylius\InvoiceInterface;
use App\Generator\Renderer\Exception\InvoiceRendererNotFound;
use Sylius\Component\Addressing\Model\ZoneInterface;
use Sylius\Component\Customer\Model\CustomerInterface;
use Sylius\Component\Order\Model\OrderInterface;

final class InvoiceRendererComponent implements InvoiceRendererInterface
{
    public function __construct(
        /** @param InvoiceRendererInterface[] $invoiceRenderers */
        private readonly iterable $invoiceRenderers
    ) {
    }

    public function renderTemplate(InvoiceInterface $invoice, OrderInterface $order, CustomerInterface $customer, ZoneInterface $taxZone, string $channelCode, ?string $type = null): string
    {
        /** @var InvoiceRendererInterface $invoiceRenderer */
        foreach ($this->invoiceRenderers as $invoiceRenderer) {
            if ($invoiceRenderer->support($taxZone, $channelCode, $type)) {
                return $invoiceRenderer->renderTemplate($invoice, $order, $customer, $taxZone, $channelCode, $type);
            }
        }

        throw new InvoiceRendererNotFound();
    }

    public function support(ZoneInterface $taxZone, string $channelCode, ?string $type = null): bool
    {
        return true;
    }
}
