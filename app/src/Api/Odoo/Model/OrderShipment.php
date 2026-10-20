<?php

namespace App\Api\Odoo\Model;

use App\Api\Odoo\Api\OdooApiInterface;
use App\Doctrine\Entity\Sylius\Order as BaseOrder;
use App\Doctrine\Entity\Sylius\Shipment;
use App\Doctrine\Entity\Sylius\ShipmentInterface;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation as Serializer;
use Sylius\Component\Core\Model\OrderInterface as BaseOrderInterface;
use Webmozart\Assert\Assert;

class OrderShipment extends AbstractOrder
{
    /**
     * @var \DateTime
     *
     * @Serializer\Type("DateTime<'c'>")
     *
     * @Serializer\Groups({OdooApiInterface::API_CALL_SHIPPED})
     *
     * @Serializer\SerializedName("date_done")
     */
    public $dateDone;

    /**
     * @var \DateTimeInterface|null
     *
     * @Serializer\Type("DateTime<'c'>")
     *
     * @Serializer\Groups({OdooApiInterface::API_CALL_SHIPPED})
     */
    public $odooDeliveryDate;

    public function __construct(
        BaseOrderInterface $order,
        ArrayCollection $items,
        ?AddressInterface $shippingAddress = null,
        ?AddressInterface $invoicingAddress = null,
        ?DeliveryDateInterface $deliveryDate = null,
        ?CustomerInterface $customer = null,
        private readonly ?string $paymentId = null,
        private readonly ?string $taxRateCode = null
    ) {
        /* @var BaseOrder $order */
        parent::__construct($order, $items, $shippingAddress, $invoicingAddress, $deliveryDate, $customer);

        $this->name = $order->getNumber();
        $this->items = $items;

        /** @var Shipment $shipment */
        $shipment = $order->getShipments()->last();
        Assert::isInstanceOf($shipment, ShipmentInterface::class, 'Shipment is needed for OrderShipment');

        $this->dateDone = $shipment->getShippingDate() ?? $order->getCheckoutCompletedAt();
        $this->odooDeliveryDate = $shipment->getUpdatedAt();
        // Todo: to remove when the php -2 hours will be fixed
        if ($this->dateDone) {
            $timeFix = getenv('TEMPORARY_PHP_TIME_FIX');
            $timeFix = !empty($timeFix) ? $timeFix : 1;
            $interval = new \DateInterval('PT' . $timeFix . 'H');
            $this->dateDone->sub($interval);
        }
    }
}
