<?php

namespace App\Services\InvoiceGenerator;

use App\Generator\InvoicePdfFileGenerator;
use App\Generator\Renderer\SwissInvoiceRenderer;
use App\Message\Invoice;
use App\Services\Channel\ChannelCode;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\InvoicingPlugin\Doctrine\ORM\InvoiceRepositoryInterface;
use Sylius\InvoicingPlugin\Entity\InvoiceInterface;
use Symfony\Component\Messenger\MessageBusInterface;

final class InvoiceGenerator
{
    public function __construct(
        private readonly InvoiceRepositoryInterface $invoiceRepository,
        private readonly InvoicePdfFileGenerator $invoicePdfFileGenerator,
        private readonly MessageBusInterface $bus
    ) {
    }

    public function generate(OrderInterface $order): void
    {
        $channelCode = $order->getChannel()->getCode();

        if (!in_array($channelCode, [ChannelCode::CHANNEL_B2B, ChannelCode::CHANNEL_RETURN, ChannelCode::CHANNEL_OPERATION, 'choose'], true)
        ) {
            if (ChannelCode::CHANNEL_BIS === $channelCode && SwissInvoiceRenderer::SWISS_TAX_CODE !== $order->getShippingAddress()->getCountryCode()) {
                return;
            }

            $invoices = $this->invoiceRepository->findBy(['orderNumber' => $order->getNumber()]);

            if (!empty($invoices)) {
                foreach ($invoices as $invoice) {
                    $invoicePdf = $this->invoicePdfFileGenerator->generate($invoice);

                    $this->bus->dispatch(new Invoice($invoice->number(), $invoicePdf->filename(), $invoicePdf->content()));
                }
            }
        }
    }

    public function regenerate(InvoiceInterface $invoice)
    {
        $invoicePdf = $this->invoicePdfFileGenerator->generate($invoice);
        $this->bus->dispatch(new Invoice($invoice->number(), $invoicePdf->filename(), $invoicePdf->content()));
    }
}
