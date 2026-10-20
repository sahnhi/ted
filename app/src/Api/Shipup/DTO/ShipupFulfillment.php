<?php

namespace App\Api\Shipup\DTO;

use App\Component\Core\OrderShippingStates;
use App\Doctrine\Entity\Sylius\OrderItemUnit;
use App\Doctrine\Entity\Sylius\ShipmentInterface;
use App\Doctrine\Entity\Sylius\ShippingMethod;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use JMS\Serializer\Annotation as Serializer;

class ShipupFulfillment implements ShipupMessageInterface
{
    public const SHIPUP_STATE_PENDING = 'pending';
    public const SHIPUP_STATE_PREPARING_SHIPMENT = 'preparing_shipment';
    public const SHIPUP_STATE_SHIPPED = 'shipped';
    public const SHIPUP_STATE_PARTIALLY_SHIPPED = 'partially_shipped';
    public const SHIPUP_STATE_CANCELED = 'canceled';
    public const SHIPUP_STATE_BLOCKED = 'blocked';

    /**
     * @Serializer\Type("string")
     *
     * @Serializer\SerializedName("merchant_id")
     */
    public string $merchantId;

    /**
     * @Serializer\Type("string")
     *
     * @Serializer\SerializedName("fulfillment_number")
     */
    public string $fulfillmentNumber;

    /**
     * @Serializer\Type("DateTime<'c'>")
     *
     * @Serializer\SerializedName("estimated_delivery_range_start")
     */
    public \DateTimeInterface $estimatedDeliveryRangeStart;

    /**
     * @Serializer\Type("DateTime<'c'>")
     *
     * @Serializer\SerializedName("estimated_delivery_range_end")
     */
    public \DateTimeInterface $estimatedDeliveryRangeEnd;

    /**
     * @Serializer\Type("string")
     *
     * @Serializer\SerializedName("status_code")
     */
    public string $statusCode;

    /**
     * @Serializer\Type("string")
     *
     * @Serializer\SerializedName("warehouse_merchant_id")
     */
    public ?string $warehouseMerchantId;

    /**
     * @Serializer\Type("App\Api\Shipup\DTO\ShipupShippingAddress")
     *
     * @Serializer\SerializedName("shipping_address")
     */
    public ShipupShippingAddress $shippingAddress;

    /**
     * @Serializer\Type("ArrayCollection<App\Api\Shipup\DTO\ShipupLineItem>")
     *
     * @Serializer\SerializedName("line_items")
     */
    public Collection $lineItems;

    /**
     * @Serializer\Type("ArrayCollection<App\Api\Shipup\DTO\ShipupTracker>")
     */
    public Collection $trackers;

    public function __construct(ShipmentInterface $shipment, int $index = 0, ?ShipupTracker $shipupTracker = null)
    {
        $order = $shipment->getOrder();
        /** @var ShippingMethod $method */
        $method = $shipment->getMethod();

        $this->trackers = new ArrayCollection();
        $this->lineItems = new ArrayCollection();

        $this->merchantId = $shipment->getId();
        $this->fulfillmentNumber = ltrim((string) $order->getNumber(), '0') . '_' . ($index + 1);
        $this->estimatedDeliveryRangeStart = $shipment->getStartDeliverySlot();
        $this->estimatedDeliveryRangeEnd = $shipment->getEndDeliverySlot();
        $this->statusCode = $this->getStatusCode($shipment->getState());
        $this->warehouseMerchantId = $method->getWarehouse() ? (string) $method->getWarehouse()->getId() : null;
        $this->shippingAddress = new ShipupShippingAddress($order->getShippingAddress());

        foreach ($order->getItems() as $item) {
            $count = 0;

            /** @var OrderItemUnit $unit */
            foreach ($item->getUnits() as $unit) {
                if ($unit->getShipment() === $shipment) {
                    ++$count;
                }
            }

            if ($count > 0) {
                $this->lineItems->add(new ShipupLineItem($item));
            }
        }

        if ($shipupTracker instanceof ShipupTracker) {
            $this->trackers->add($shipupTracker);
        }
    }

    private function getStatusCode(string $status): string
    {
        switch ($status) {
            case OrderShippingStates::STATE_CART:
                return self::SHIPUP_STATE_PENDING;

            case OrderShippingStates::STATE_READY:
                return self::SHIPUP_STATE_PREPARING_SHIPMENT;

            case OrderShippingStates::STATE_PARTIALLY_SHIPPED:
                return self::SHIPUP_STATE_PARTIALLY_SHIPPED;

            case OrderShippingStates::STATE_SHIPPED:
            case OrderShippingStates::STATE_DELIVERED:
                return self::SHIPUP_STATE_SHIPPED;

            case OrderShippingStates::STATE_CANCELLED:
                return self::SHIPUP_STATE_CANCELED;

            default:
                return self::SHIPUP_STATE_BLOCKED;
        }
    }
}
