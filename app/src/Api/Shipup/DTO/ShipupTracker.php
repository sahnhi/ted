<?php

namespace App\Api\Shipup\DTO;

use App\Doctrine\Entity\Sylius\OrderItemUnit;
use App\Doctrine\Entity\Sylius\ShipmentInterface;
use App\Doctrine\Entity\Sylius\ShippingMethodInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use JMS\Serializer\Annotation as Serializer;

class ShipupTracker
{
    /**
     * @Serializer\Type("string")
     */
    public string $carrierCode;

    /**
     * @Serializer\Type("DateTime<'c'>")
     *
     * @Serializer\SerializedName("expected_delivery_at")
     */
    public \DateTimeInterface $expectedDeliveryAt;

    /**
     * @Serializer\Type("string")
     */
    public string $trackingNumber;

    /**
     * @Serializer\Type("string")
     */
    public string $untrackedCarrierName;

    /**
     * @Serializer\Type("ArrayCollection<App\Api\Shipup\DTO\ShipupLineItem>")
     *
     * @Serializer\SerializedName("line_items")
     */
    public Collection $lineItems;

    public function __construct(
        ShipmentInterface $shipment, /**
     * @Serializer\Type("bool")
     *
     * @Serializer\SerializedName("mute_notifications")
     */
        public bool $muteNotifications = true
    ) {
        /** @var ShippingMethodInterface $shippingMethod */
        $shippingMethod = $shipment->getMethod();

        $this->carrierCode = $shippingMethod->getShipupCarrierCode();
        $this->expectedDeliveryAt = $shipment->getEndDeliverySlot();
        $this->trackingNumber = $shipment->getTrackingNumber();
        $this->untrackedCarrierName = $shippingMethod->getName();
        $this->lineItems = new ArrayCollection();

        foreach ($shipment->getOrder()->getItems() as $item) {
            $count = 0;

            /** @var OrderItemUnit $unit */
            foreach ($item->getUnits() as $unit) {
                if ($unit->getShipment() === $shipment) {
                    ++$count;
                }
            }

            if ($count > 0) {
                $this->lineItems->add(new ShipupLineItem($item, $count));
            }
        }
    }
}
