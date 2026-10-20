<?php

namespace App\Generator;

use App\Doctrine\Entity\Sylius\InvoiceInterface as AppInvoiceInterface;
use App\Doctrine\Entity\Sylius\Order;
use App\Generator\Renderer\InvoiceRendererInterface;
use Knp\Snappy\GeneratorInterface;
use Sylius\Component\Addressing\Matcher\ZoneMatcherInterface;
use Sylius\Component\Core\Model\Scope;
use Sylius\Component\Core\Provider\ZoneProviderInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Sylius\InvoicingPlugin\Entity\InvoiceInterface;
use Sylius\InvoicingPlugin\Generator\InvoicePdfFileGeneratorInterface;
use Sylius\InvoicingPlugin\Model\InvoicePdf;

final class InvoicePdfFileGenerator implements InvoicePdfFileGeneratorInterface
{
    public const FILE_EXTENSION = '.pdf';

    public function __construct(
        private readonly GeneratorInterface $pdfGenerator,
        private readonly RepositoryInterface $orderRepository,
        private readonly ZoneMatcherInterface $zoneMatcher,
        private readonly ZoneProviderInterface $defaultTaxZoneProvider,
        private readonly InvoiceRendererInterface $invoiceRenderer
    ) {
    }

    /**
     * @param AppInvoiceInterface $invoice
     */
    public function generate(InvoiceInterface $invoice): InvoicePdf
    {
        $filename = str_replace('/', '_', $invoice->number()) . '_' . $invoice->orderNumber();

        if ($invoice instanceof AppInvoiceInterface && null !== $invoice->getType()) {
            $filename .= '_' . AppInvoiceInterface::INVOICE_TYPE_CARRIER;
        }

        $filename .= self::FILE_EXTENSION;

        /** @var Order $order */
        $order = $this->orderRepository->findOneByNumber($invoice->orderNumber());

        $shippingAddress = $order->getShippingAddress();
        $taxZone = null;

        if (null !== $shippingAddress) {
            $taxZone = $this->zoneMatcher->match($shippingAddress, Scope::TAX);
        }

        $taxZone = $taxZone ?: $this->defaultTaxZoneProvider->getZone($order);

        $pdf = $this->pdfGenerator->getOutputFromHtml(
            $this->invoiceRenderer->renderTemplate($invoice, $order, $order->getCustomer(), $taxZone, $order->getChannel()->getCode(), $invoice->getType()),
            [
                'page-size' => 'A4',
            ]
        );

        return new InvoicePdf($filename, $pdf);
    }
}
