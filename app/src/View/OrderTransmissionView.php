<?php

namespace App\View;

class OrderTransmissionView
{
    public const DELIVERY = 'delivery';
    public const PICKUP = 'pickup';
    public const ORDER_TYPE = 'TD';
    public const SENDER = 'Tediber';

    /**
     * @var string
     */
    public $number;

    /**
     * @var array|ItemTransmissionView[]
     */
    public $items = [];

    /**
     * @var AddressTransmissionView
     */
    public $shippingAddress;

    /**
     * @var CustomerTransmissionView
     */
    public $customer;

    /**
     * @var array|ShipmentTransmissionView[]
     */
    public $shipments = [];

    /**
     * @var \DateTime
     */
    public $checkoutCompletedAt;

    /**
     * @var string
     */
    public $orderType;

    /**
     * @var array|OrderReasonTransmissionView[]
     */
    public $orderReason = [];

    public string $shippingMethodCategory = '';

    /**
     * OrderTransmissionView constructor.
     */
    public function __construct()
    {
        $this->shippingAddress = new AddressTransmissionView();
        $this->customer = new CustomerTransmissionView();
    }

    public function getNumberTransmission(): string
    {
        return ltrim($this->number, 0); // integer
    }

    public function getTEDNumberTransmission(): string
    {
        return 'TED' . $this->getNumberTransmission(); // integer
    }

    public function getType(): string
    {
        return (str_contains($this->number, 'R')) ? self::PICKUP : self::DELIVERY;
    }

    public function getSender(): string
    {
        return self::SENDER;
    }

    /**
     * @return string
     */
    public function getInstructions()
    {
        return $this->shippingAddress->details;
    }

    /**
     * @return string
     */
    public function getDateShipment()
    {
        return $this->shipments[0]->startDeliverySlot->format('Y-m-d');
    }

    /**
     * @return string
     */
    public function getDateShipmentFr()
    {
        return $this->shipments[0]->startDeliverySlot->format('d/m/y');
    }

    public function getTimeWindows(): array
    {
        $timeWindows = [];

        foreach ($this->shipments as $shipment) {
            $timeWindows['start'] = $shipment->startDeliverySlot->format('H:i');
            $timeWindows['stop'] = $shipment->endDeliverySlot->format('H:i');

            return $timeWindows;
        }

        return $timeWindows;
    }

    /**
     * @return string
     */
    public function getDateOrder()
    {
        return $this->checkoutCompletedAt->format('d/m/Y');
    }

    public function getShipmentDeliveryHour(): string
    {
        return substr((string) $this->getTimeWindows()['start'], 0, 2) . '/' . substr((string) $this->getTimeWindows()['stop'], 0, 2);
    }

    public function getOrderType(): string
    {
        return self::ORDER_TYPE;
    }

    /**
     * @return array
     */
    public function getCustomer()
    {
        return [
            'phone' => $this->shippingAddress->getTelPortable(),
            'email' => $this->getEmail(),
            'person' => $this->shippingAddress->getPerson(),
        ];
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->customer->getEmail();
    }

    /**
     * @return string
     */
    public function getPhone()
    {
        return $this->shippingAddress->getTelPortable();
    }

    public function getMetadata(): array
    {
        $metaData = [];

        foreach ($this->items as $item) {
            if (null !== $item->parentProduct && null !== $item->parentProduct->getMetaData()) {
                foreach ($item->parentProduct->getMetaData() as $row) {
                    if (!empty($row['shippingMethod']) && str_contains($this->shipments[0]->method->code, (string) $row['shippingMethod'])) {
                        $metaData['TypeLivraison'] = $row['data'];
                    }
                }
            }
        }

        if (in_array($this->shippingMethodCategory, ['EXPRESS_PETIT_COLIS', 'EXPRESS_ENCOMBRANT'], true)) {
            $metaData['typlivtedi'] = 'en bas de l\'immeuble';
        }

        return $metaData;
    }

    public function getPickupItems(): string
    {
        $pickupItems = [];
        foreach ($this->orderReason as $reason) {
            $pickupItems[] = $reason->quantity . ' x ' . $reason->variants[0]->code;
        }

        return implode(', ', $pickupItems);
    }

    public function getReasons(): string
    {
        $orderReasons = [];
        foreach ($this->orderReason as $orderReason) {
            $orderReasons[] = $orderReason->name;
        }

        return implode(', ', $orderReasons);
    }
}
