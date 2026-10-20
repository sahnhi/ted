<?php

namespace App\Factory;

use App\Doctrine\Entity\Sylius\Order;
use App\Doctrine\Entity\Sylius\OrderItem;
use App\Doctrine\Entity\Sylius\ProductVariant;
use App\Doctrine\Entity\Sylius\ShipmentInterface;
use Sylius\AdminOrderCreationPlugin\Factory\OrderFactoryInterface as BaseOrderFactoryInterface;
use Sylius\AdminOrderCreationPlugin\ReorderProcessing\ReorderProcessor;
use Sylius\Component\Channel\Repository\ChannelRepositoryInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\TokenAssigner\OrderTokenAssignerInterface;
use Sylius\Component\Order\Modifier\OrderItemQuantityModifierInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Webmozart\Assert\Assert;

final class OrderFactory implements OrderFactoryInterface
{
    public function __construct(
        private readonly BaseOrderFactoryInterface $decoratedFactory,
        private readonly ChannelRepositoryInterface $channelRepository,
        private readonly ReorderProcessor $appReorderProcessor,
        private readonly ReorderProcessor $pluginReorderProcessor,
        private readonly OrderTokenAssignerInterface $tokenAssigner,
        private readonly ShipmentFactoryInterface $shipmentFactory,
        private readonly OrderItemQuantityModifierInterface $orderItemQuantityModifier,
        private readonly FactoryInterface $orderItemFactory,
    ) {
    }

    public function createNew(): OrderInterface
    {
        /** @var OrderInterface $newOrder */
        $newOrder = $this->decoratedFactory->createNew();

        $this->tokenAssigner->assignTokenValue($newOrder);

        return $newOrder;
    }

    public function createForCustomerAndChannel(string $customerId, string $channelCode): OrderInterface
    {
        $newOrder = $this->decoratedFactory->createForCustomerAndChannel($customerId, $channelCode);

        $this->tokenAssigner->assignTokenValue($newOrder);

        return $newOrder;
    }

    public function createFromExistingOrder(OrderInterface $order): OrderInterface
    {
        return $this->decoratedFactory->createFromExistingOrder($order);
    }

    public function createReorder(OrderInterface $order, string $channelCode, ?string $type = null): OrderInterface
    {
        /** @var Order $reorder */
        $reorder = $this->createNew();
        $reorder->setParentNumber($order->getNumber());

        $channel = $this->channelRepository->findOneByCode($channelCode);
        Assert::notNull($channel);

        $reorder->setChannel($channel);
        $reorder->setCurrencyCode($order->getCurrencyCode());

        $this->pluginReorderProcessor->process($order, $reorder);

        $this->appReorderProcessor->process($order, $reorder, $channel->getCode(), true);
        if (null !== $type) {
            $this->appReorderProcessor->process($order, $reorder, $type, true);
        }

        // Processors reset it, so we need to set channel again for form
        $reorder->setChannel($channel);

        return $reorder;
    }

    /**
     * @param array<ProductVariant> $variants
     */
    public function createNewForChargesEstimate(array $variants, ChannelInterface $channel): Order
    {
        /** @var Order $order */
        $order = $this->createNew();
        $order->setChannel($channel);

        /** @var ShipmentInterface $shipment */
        $shipment = $this->shipmentFactory->createNew();

        foreach ($variants as $variantData) {
            /** @var ProductVariant $variant */
            $variant = $variantData['variant'];
            $quantity = $variantData['quantity'];

            /** @var OrderItem $orderItem */
            $orderItem = $this->orderItemFactory->createNew();
            $orderItem->setVariant($variant);
            $orderItem->setUnitPrice($variant->getChannelPricingForChannel($channel)?->getPrice());

            $this->orderItemQuantityModifier->modify($orderItem, $quantity);

            $order->addItem($orderItem);
        }

        foreach ($order->getItemUnits() as $itemUnit) {
            if (null === $itemUnit->getShipment()) {
                $shipment->addUnit($itemUnit);
            }
        }

        $order->addShipment($shipment);

        return $order;
    }
}
