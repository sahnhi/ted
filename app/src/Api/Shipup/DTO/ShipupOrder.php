<?php

namespace App\Api\Shipup\DTO;

use App\Component\Core\OrderShippingStates;
use App\Doctrine\Entity\Sylius\ShipmentInterface;
use App\Doctrine\Entity\Sylius\ShippingMethodInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use JMS\Serializer\Annotation as Serializer;
use Sylius\Component\Core\Model\OrderInterface;

class ShipupOrder implements ShipupMessageInterface
{
    /**
     * @Serializer\Type("string")
     *
     * @Serializer\SerializedName("merchant_id")
     */
    public string $merchantId;

    /**
     * @Serializer\Type("string")
     *
     * @Serializer\SerializedName("order_number")
     */
    public string $orderNumber;

    /**
     * @Serializer\Type("ArrayCollection<App\Api\Shipup\DTO\ShipupFulfillment>")
     */
    public Collection $fulfillments;

    /**
     * @Serializer\Type("DateTime<'c'>")
     *
     * @Serializer\SerializedName("ordered_at")
     */
    public \DateTimeInterface $orderedAt;

    /**
     * @Serializer\Type("string")
     */
    public string $email;

    /**
     * @Serializer\Type("string")
     *
     * @Serializer\SerializedName("first_name")
     */
    public string $firstName;

    /**
     * @Serializer\Type("string")
     *
     * @Serializer\SerializedName("last_name")
     */
    public string $lastName;

    /**
     * @Serializer\Type("bool")
     */
    public bool $canceled = false;

    /**
     * @Serializer\Type("ArrayCollection<App\Api\Shipup\DTO\ShipupLineItem>")
     *
     * @Serializer\SerializedName("line_items")
     */
    public Collection $lineItems;

    /**
     * @Serializer\Type("App\Api\Shipup\DTO\ShipupShippingAddress")
     *
     * @Serializer\SerializedName("shipping_address")
     */
    public ShipupShippingAddress $shippingAddress;

    public function __construct(
        OrderInterface $order, /**
     * @Serializer\Type("bool")
     *
     * @Serializer\SerializedName("mute_notifications")
     */
        public bool $muteNotifications = true
    ) {
        $this->fulfillments = new ArrayCollection();
        $this->lineItems = new ArrayCollection();

        $this->merchantId = $order->getId();
        $this->orderNumber = ltrim((string) $order->getNumber(), '0');
        $this->orderedAt = $order->getCheckoutCompletedAt();
        $this->email = $order->getCustomer()->getEmail();
        $this->firstName = $order->getCustomer()->getFirstName();
        $this->lastName = $order->getCustomer()->getLastName();
        $this->canceled = OrderShippingStates::STATE_CANCELLED === $order->getState();
        $this->shippingAddress = new ShipupShippingAddress($order->getShippingAddress());

        foreach ($order->getItems() as $item) {
            $this->lineItems->add(new ShipupLineItem($item));
        }

        /** @var ShipmentInterface $shipment */
        foreach ($order->getShipments() as $key => $shipment) {
            if (ShippingMethodInterface::METHOD_STORE !== $shipment->getMethod()->getCode()) {
                $shipupTracker = null;

                if (null !== $shipment->getTrackingNumber()) {
                    $shipupTracker = new ShipupTracker($shipment, $this->muteNotifications);
                }

                $this->fulfillments->add(new ShipupFulfillment($shipment, $key, $shipupTracker));
            }
        }

        $this->cleanItems();
    }

    private function cleanItems(): void
    {
        /** @var ShipupLineItem $lineItem */
        foreach ($this->lineItems as $lineItem) {
            /** @var ShipupFulfillment $fulfillment */
            foreach ($this->fulfillments as $fulfillment) {
                /** @var ShipupLineItem $fulfillmentLineItem */
                foreach ($fulfillment->lineItems as $fulfillmentLineItem) {
                    if ($fulfillmentLineItem->sku === $lineItem->sku) {
                        $this->lineItems->removeElement($lineItem);
                    }

                    /** @var ShipupTracker $tracker */
                    foreach ($fulfillment->trackers as $tracker) {
                        /** @var ShipupLineItem $trackerLineItem */
                        foreach ($tracker->lineItems as $trackerLineItem) {
                            if ($lineItem->sku === $trackerLineItem->sku) {
                                $this->lineItems->removeElement($lineItem);
                            }

                            if ($fulfillmentLineItem->sku === $trackerLineItem->sku) {
                                $fulfillment->lineItems->removeElement($fulfillmentLineItem);
                            }
                        }
                    }
                }
            }
        }
    }
}
