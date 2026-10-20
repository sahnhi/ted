<?php

declare(strict_types=1);

namespace App\EventSubscriber\OrderAction;

use App\Doctrine\Entity\Sylius\Shipment;
use App\Services\Mail\Order\UpdateOrderDeliverDateInterface;
use Sylius\Bundle\ResourceBundle\Event\ResourceControllerEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class UpdateOrderDeliveryDateSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly UpdateOrderDeliverDateInterface $updateOrderDeliverDate
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'sylius.shipment.post_update' => ['updateDateDelivery'],
        ];
    }

    public function updateDateDelivery(ResourceControllerEvent $event): void
    {
        /** @var Shipment $shipment */
        $shipment = $event->getSubject();

        if ($shipment->notifyWarehouse()) {
            $this->updateOrderDeliverDate->notifyNewDate($shipment->getOrder()->getNumber(), $shipment->getStartDeliverySlot(), $shipment->getEndDeliverySlot());
        }
    }
}
