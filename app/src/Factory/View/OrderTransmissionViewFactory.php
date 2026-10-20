<?php

namespace App\Factory\View;

use App\Doctrine\Entity\BundleAssociationInterface;
use App\Doctrine\Entity\ShippingMethodCategory;
use App\Doctrine\Entity\Sylius\Order;
use App\Doctrine\Entity\Sylius\ProductVariantInterface;
use App\Doctrine\Entity\Sylius\Shipment;
use App\Doctrine\Entity\Sylius\ShippingMethod;
use App\Doctrine\Entity\Sylius\ShippingMethodInterface;
use App\Doctrine\Repository\Sylius\OrderRepositoryInterface;
use App\Generator\Renderer\SwissInvoiceRenderer;
use App\Model\ProductReferenceInterface;
use App\Services\Channel\ChannelPricingService;
use App\Services\OrderItem\OrderItemService;
use App\View\OrderTransmissionView;
use Sylius\Component\Core\Model\OrderItemInterface;
use Sylius\Component\Taxation\Calculator\CalculatorInterface;
use Sylius\Component\Taxation\Resolver\TaxRateResolverInterface;

final class OrderTransmissionViewFactory implements OrderTransmissionViewFactoryInterface
{
    public function __construct(
        private readonly CartItemViewFactoryInterface $cartItemFactory,
        private readonly AddressViewFactoryInterface $addressViewFactory,
        private readonly ShipmentViewFactoryInterface $shipmentViewFactory,
        private readonly CustomerViewFactoryInterface $customerViewFactory,
        private readonly OrderReasonTransmissionViewFactoryInterface $orderReasonTransmissionViewFactory,
        private readonly OrderItemService $orderItemService,
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly ChannelPricingService $channelPricingService,
        private readonly TaxRateResolverInterface $taxRateResolver,
        private readonly CalculatorInterface $taxCalculator,
    ) {
    }

    public function create(Order $order, string $localeCode): OrderTransmissionView
    {
        $orderTransmissionView = new OrderTransmissionView();
        $orderTransmissionView->number = $order->getNumber();
        $orderTransmissionView->checkoutCompletedAt = $order->getCheckoutCompletedAt();

        $orderTransmissionView->customer = $this->customerViewFactory->createTransmission($order->getCustomer());
        $logisticItemArray = [];
        $positivePricesOnly = $this->pricesHaveToBePositive($order);
        $quantities = $this->handleAssemblyQuantities($order);

        /** @var OrderItemInterface $item */
        foreach ($order->getItems() as $item) {
            /** @var ProductVariantInterface $variant */
            $variant = $item->getVariant();

            if (!$item->getVariant()->isShippingRequired()) {
                continue;
            }

            if ($variant->isBundle()) {
                /** @var BundleAssociationInterface $bundleAssociation */
                foreach ($variant->getBundleAssociations() as $bundleAssociation) {
                    $tax = $this->getTaxForBundleComponent($bundleAssociation->getVariant(), $bundleAssociation->getPrice());
                    $total = $bundleAssociation->getQuantity() * ($bundleAssociation->getPrice() - $tax);
                    $quantity = $item->getQuantity() * $bundleAssociation->getQuantity();

                    $this->orderItemService->handleLogisticItemArray(
                        $bundleAssociation->getVariant(),
                        $logisticItemArray,
                        $quantity,
                        $positivePricesOnly && 0 === $total ? $this->calculateTotalForLine($item, $quantity, $bundleAssociation) : $total
                    );
                }
            } else {
                $quantity = (!empty($quantities) && isset($quantities[$item->getVariant()->getCode()])) ?
                    $quantities[$item->getVariant()->getCode()] : $item->getQuantity();
                $this->orderItemService->handleLogisticItemArray(
                    $item->getVariant(),
                    $logisticItemArray,
                    $quantity,
                    $positivePricesOnly && 0 === $item->getTotal() ? $this->calculateTotalForLine($item, $item->getQuantity()) : $item->getTotal() - $item->getTaxTotal()
                );
            }
        }

        foreach ($logisticItemArray as $item) {
            $orderTransmissionView->items[] = $this->cartItemFactory->createTransmission($order->getChannel(), $localeCode, $item);
        }

        foreach ($order->getShipments() as $shipment) {
            $orderTransmissionView->shipments[] = $this->shipmentViewFactory->createTransmission($shipment, $localeCode);
        }

        if (null !== $order->getShippingAddress()) {
            if (null !== $order->getBillingAddress()
                && in_array($order->getShipments()->first()->getMethod()->getCode(), ShippingMethodInterface::METHOD_RELAY)
            ) {
                $orderTransmissionView->shippingAddress = $this->addressViewFactory->createTransmission($order->getBillingAddress());
            } else {
                $orderTransmissionView->shippingAddress = $this->addressViewFactory->createTransmission($order->getShippingAddress());
            }
        }

        foreach ($order->getOrderReason() as $reason) {
            $orderTransmissionView->orderReason[] = $this->orderReasonTransmissionViewFactory->createTransmission($reason, $order->getChannel(), $localeCode);
        }

        $shippingMethodCategory = $this->getShippingMethodCategory($order);
        $orderTransmissionView->shippingMethodCategory = $shippingMethodCategory?->getCode() ?? '';

        return $orderTransmissionView;
    }

    private function pricesHaveToBePositive(Order $order): bool
    {
        if (SwissInvoiceRenderer::SWISS_TAX_CODE === $order->getShippingAddress()->getCountryCode()) {
            return true;
        }

        return false;
    }

    private function calculateTotalForLine(OrderItemInterface $item, int $quantity, ?BundleAssociationInterface $bundleAssociation = null): int
    {
        /** @var ProductVariantInterface $variant */
        $variant = $item->getVariant();

        if ($variant->isBundle() && $bundleAssociation instanceof BundleAssociationInterface) {
            $variant = $bundleAssociation->getVariant();
        }

        /** @var Order $order */
        $order = $item->getOrder();
        $parentOrder = $this->orderRepository->getOrderParent($order);

        $channel = $parentOrder instanceof Order ? $parentOrder->getChannel() : $order->getChannel();
        $unitPrice = $this->channelPricingService->getPriceByChannel($variant->getCode(), $channel);
        $tax = $this->getTaxForBundleComponent($variant, $unitPrice);

        return ($unitPrice - $tax) * $quantity;
    }

    private function handleAssemblyQuantities(Order $order): ?array
    {
        /** @var ShippingMethod $shippingMethod */
        $shippingMethod = $order->getShipments()->first()->getMethod();

        $quantities = [];
        if (str_contains((string) $shippingMethod->getCode(), ShippingMethodInterface::METHOD_EDELIV)) {
            foreach ($order->getItems() as $item) {
                $variantCode = $item->getVariant()->getCode();
                foreach (ProductReferenceInterface::ASSEMBLY_MATCHES as $codeAssembly => $valueAssembly) {
                    if (str_starts_with((string) $variantCode, $valueAssembly)) {
                        $quantities[$codeAssembly] = !isset($quantities[$codeAssembly]) ?
                            $item->getQuantity() :
                            $quantities[$codeAssembly] + $item->getQuantity();
                    }
                }
            }
        }

        return $quantities;
    }

    private function getTaxForBundleComponent(ProductVariantInterface $variant, int $price): int
    {
        $taxRate = $this->taxRateResolver->resolve($variant);

        return (int) $this->taxCalculator->calculate($price / 100, $taxRate) * 100;
    }

    private function getShippingMethodCategory(Order $order): ShippingMethodCategory|null
    {
        $shippingMethodCategories = $order->getShippingMethodCategories();

        if (!empty($shippingMethodCategories)) {
            return reset($shippingMethodCategories);
        }

        /** @var Shipment $shipment */
        $shipment = $order->getShipments()->first();
        /** @var ShippingMethod $shippingMethod */
        $shippingMethod = $shipment->getMethod();

        return $shippingMethod->getShippingMethodCategory();
    }
}
