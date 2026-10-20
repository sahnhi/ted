<?php

declare(strict_types=1);

namespace App\Generator\Renderer;

use App\Doctrine\Entity\Sylius\InvoiceInterface;
use App\Services\Channel\ChannelCode;
use Sylius\Component\Addressing\Model\ZoneInterface;
use Sylius\Component\Customer\Model\CustomerInterface;
use Sylius\Component\Order\Model\OrderInterface;
use Symfony\Component\Config\FileLocatorInterface;
use Twig\Environment;

final class DefaultInvoiceRenderer implements InvoiceRendererInterface
{
    public function __construct(
        private readonly Environment $engine,
        private readonly string $template,
        private readonly FileLocatorInterface $fileLocator,
        private readonly string $invoiceLogoPath
    ) {
    }

    public function support(ZoneInterface $taxZone, string $channelCode, ?string $type = null): bool
    {
        return null === $type && ChannelCode::CHANNEL_BIS !== $channelCode;
    }

    public function renderTemplate(InvoiceInterface $invoice, OrderInterface $order, CustomerInterface $customer, ZoneInterface $taxZone, string $channelCode, ?string $type = null): string
    {
        return $this->engine->render($this->template, [
            'invoice' => $invoice,
            'invoiceLogoPath' => $this->fileLocator->locate($this->invoiceLogoPath),
            'order' => $order,
            'customer' => $customer,
            'taxZone' => $taxZone,
        ]);
    }
}
