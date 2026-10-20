<?php

declare(strict_types=1);

namespace App\Generator\Renderer;

use App\Doctrine\Entity\Sylius\InvoiceInterface;
use App\Doctrine\Entity\Sylius\Order;
use App\Doctrine\Entity\Sylius\OrderItem;
use App\Doctrine\Repository\Sylius\OrderRepositoryInterface;
use App\Generator\Renderer\Model\InvoiceProduct;
use App\Generator\Renderer\Model\InvoiceProductList;
use App\Provider\TaxRateProviderInterface;
use App\Services\Channel\ChannelCode;
use App\Services\Shipment\ProductHtc;
use Sylius\Component\Addressing\Model\ZoneInterface;
use Sylius\Component\Customer\Model\CustomerInterface;
use Sylius\Component\Order\Model\OrderInterface;
use Symfony\Component\Config\FileLocatorInterface;
use Twig\Environment;

final class SwissInvoiceRenderer implements InvoiceRendererInterface
{
    public const SWISS_TAX_CODE = 'CH';

    public function __construct(
        private readonly Environment $engine,
        private readonly string $template,
        private readonly FileLocatorInterface $fileLocator,
        private readonly string $invoiceLogoPath,
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly TaxRateProviderInterface $taxRateProvider,
        private readonly ProductHtc $productHtcService
    ) {
    }

    public function support(ZoneInterface $taxZone, string $channelCode, ?string $type = null): bool
    {
        return InvoiceInterface::INVOICE_TYPE_CARRIER === $type
            && self::SWISS_TAX_CODE === $taxZone->getCode()
            && ChannelCode::CHANNEL_BIS !== $channelCode;
    }

    public function renderTemplate(InvoiceInterface $invoice, OrderInterface $order, CustomerInterface $customer, ZoneInterface $taxZone, string $channelCode, ?string $type = null): string
    {
        $invoiceProductList = InvoiceProductList::empty();

        /** @var Order $order */
        $orderItems = $order->getItems();
        $taxRate = $this->taxRateProvider->provideTaxRateFromZone($order);

        /** @var OrderItem $orderItem */
        foreach ($orderItems as $orderItem) {
            $invoiceProductList = $invoiceProductList->addList($this->findInvoiceProductForItem($orderItem->getId(), $order->getLocaleCode(), $taxRate->getAmountAsPercentage()));
        }

        return $this->engine->render($this->template, [
            'invoice' => $invoice,
            'invoiceLogoPath' => $this->fileLocator->locate($this->invoiceLogoPath),
            'order' => $order,
            'customer' => $customer,
            'productList' => $invoiceProductList,
        ]);
    }

    private function findInvoiceProductForItem(int $orderItemId, string $locale, float $tva): InvoiceProductList
    {
        $invoiceProductList = InvoiceProductList::empty();

        $logisticVariants = $this->orderRepository->findLogisticCodeAndNameForInvoiceByOrderItem($orderItemId, $locale);

        foreach ($logisticVariants as $logisticVariant) {
            $invoiceProductList = $invoiceProductList->addAndAggregateByCode(
                InvoiceProduct::createWithVariantPriceDividedByNumberOfLogisticVariants(
                    $logisticVariant['variantName'],
                    $logisticVariant['name'],
                    $logisticVariant['code'],
                    (int) $logisticVariant['total'],
                    $logisticVariant['quantity'],
                    $logisticVariant['variantQuantity'],
                    $tva,
                    (float) $logisticVariant['weight'],
                    $this->productHtcService->getProductVariantHtc($logisticVariant['code'])
                )
            );
        }

        return $invoiceProductList;
    }
}
