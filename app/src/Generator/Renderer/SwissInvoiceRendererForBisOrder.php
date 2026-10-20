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
use App\Services\Channel\ChannelPricingService;
use App\Services\Shipment\ProductHtc;
use Sylius\Component\Addressing\Model\ZoneInterface;
use Sylius\Component\Customer\Model\CustomerInterface;
use Sylius\Component\Order\Model\OrderInterface;
use Symfony\Component\Config\FileLocatorInterface;
use Twig\Environment;

final class SwissInvoiceRendererForBisOrder implements InvoiceRendererInterface
{
    public function __construct(
        private readonly Environment $engine,
        private readonly string $template,
        private readonly FileLocatorInterface $fileLocator,
        private readonly string $invoiceLogoPath,
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly TaxRateProviderInterface $taxRateProvider,
        private readonly ProductHtc $productHtcService,
        private readonly ChannelPricingService $channelPricingService
    ) {
    }

    public function support(ZoneInterface $taxZone, string $channelCode, ?string $type = null): bool
    {
        return InvoiceInterface::INVOICE_TYPE_CARRIER === $type && SwissInvoiceRenderer::SWISS_TAX_CODE === $taxZone->getCode() && ChannelCode::CHANNEL_BIS === $channelCode;
    }

    public function renderTemplate(InvoiceInterface $invoice, OrderInterface $order, CustomerInterface $customer, ZoneInterface $taxZone, string $channelCode, ?string $type = null): string
    {
        $invoiceProductList = InvoiceProductList::empty();

        /** @var Order $order */
        $orderItems = $order->getItems();
        $parentOrder = $this->orderRepository->findInitialOrder($order->getNumber());

        $taxRate = $this->taxRateProvider->provideTaxRateFromZone($order);

        /** @var OrderItem $orderItem */
        foreach ($orderItems as $orderItem) {
            $invoiceProductList = $invoiceProductList->addList($this->findInvoiceProductForItem($orderItem->getId(), $order->getLocaleCode(), $taxRate->getAmountAsPercentage(), $parentOrder, $order->getId()));
        }

        return $this->engine->render($this->template, [
            'invoice' => $invoice,
            'invoiceLogoPath' => $this->fileLocator->locate($this->invoiceLogoPath),
            'order' => $order,
            'customer' => $customer,
            'productList' => $invoiceProductList,
            'taxRate' => $taxRate->getAmount() * 100,
        ]);
    }

    /**
     * @param Order $parentOrder
     */
    private function findInvoiceProductForItem(int $orderItemId, string $locale, float $tva, OrderInterface $parentOrder, int $orderId): InvoiceProductList
    {
        $invoiceProductList = InvoiceProductList::empty();

        $logisticVariants = $this->orderRepository->findLogisticCodeAndNameForInvoiceByOrderItem($orderItemId, $locale);
        $parentItems = $parentOrder->getItems();

        foreach ($logisticVariants as $logisticVariant) {
            if ('0' === $logisticVariant['total']) {
                $logisticVariant['total'] = $this->retrievePrice($logisticVariant, $parentOrder);
            }

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

    private function retrievePrice(array $logisticVariant, Order $parentOrder): int
    {
        $initialOrderItem = $parentOrder->findOrderItemByLogisticVariant($logisticVariant['code']);

        if ($initialOrderItem instanceof OrderItem) {
            return $initialOrderItem->getVariant()->getCode() === $logisticVariant['code'] ?
                $initialOrderItem->getUnitPrice() * $logisticVariant['quantity'] :
                $this->getTotalDividedByNumberOfLogisticVariants($initialOrderItem, (int) $logisticVariant['quantity']);
        } else {
            return $this->channelPricingService->getPriceByChannel($logisticVariant['code'], $parentOrder->getChannel()) * $logisticVariant['quantity'];
        }
    }

    private function getTotalDividedByNumberOfLogisticVariants(OrderItem $initialOrderItem, int $quantity): int
    {
        if ($initialOrderItem->getOrderItemLogistics()->isEmpty()) {
            return $initialOrderItem->getUnitPrice() * $quantity;
        }

        return (int) ceil($initialOrderItem->getUnitPrice() / $initialOrderItem->getOrderItemLogistics()->count() * $quantity);
    }
}
