<?php

namespace App\Services\ShippingMethod\Methods;

use App\Doctrine\Entity\Sylius\Order;
use App\Doctrine\Entity\Sylius\OrderItem;
use App\Doctrine\Entity\Sylius\ProductVariant;
use App\Doctrine\Entity\Sylius\Shipment;
use App\Doctrine\Entity\Sylius\ShippingMethod;
use App\Services\Date\DateHelper;
use App\Services\ShippingMethod\AbstractShippingMethod;
use Sylius\Component\Resource\Factory\FactoryInterface;

class Chronopost extends AbstractShippingMethod
{
    public const NAME = 'Chronopost';

    public function __construct(
        private readonly DateHelper $dateHelper,
        FactoryInterface $addressFactory,
    ) {
        parent::__construct($addressFactory);
    }

    /**
     * @throws \Exception
     */
    protected function handleShippingStockDate(
        Order $order,
        Shipment $shipment,
        ShippingMethod $shippingMethod
    ): void {
        $startDeliverySlot = new \DateTime();

        foreach ($order->getItems() as $item) {
            /** @var OrderItem $item */
            /** @var ProductVariant $variant */
            $variant = $item->getVariant();
            if (null !== ($date = $variant->getFirstShippingStockDate()) && $date > $startDeliverySlot) {
                $startDeliverySlot = $date;
            }
        }
        for ($i = 0; $i < (int) $shippingMethod->getTransportTime(); ++$i) {
            /* Add 1 day if the shippingMethod is closed */
            do {
                $startDeliverySlot = $startDeliverySlot->add(new \DateInterval('P1D'));
                $finalDate = $this->dateHelper->shippingMethodDateHelper->isOpen($startDeliverySlot, $shipment->getMethod());
            } while (!$finalDate);
        }
        $endDeliverySlot = clone $startDeliverySlot;
        $startDeliverySlot->add(new \DateInterval('PT' . $shippingMethod->getOpeningTime()->format('H') . 'H'));
        $endDeliverySlot->add(new \DateInterval('PT' . $shippingMethod->getClosingTime()->format('H') . 'H'));
        $shipment->setStartDeliverySlot($startDeliverySlot);
        $shipment->setEndDeliverySlot($endDeliverySlot);
    }
}
