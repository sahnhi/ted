<?php

namespace App\Services\Transmission\Tracking;

use App\Api\Shipup\ShipupApiInterface;
use App\Doctrine\Entity\Sylius\ShipmentInterface;
use App\Message\DTO\OrderDTO;
use App\Message\Mail;
use App\Services\Mail\MailerEmailInterface;
use Doctrine\Persistence\ObjectManager;
use SM\Factory\Factory as StateFactory;
use Sylius\Component\Core\OrderShippingTransitions;
use Sylius\Component\Shipping\ShipmentTransitions;
use Symfony\Component\Messenger\MessageBusInterface;
use Webmozart\Assert\Assert;

class TrackingService
{
    public const VIR_TRACKING_PREFIX = '0000070806';

    public function __construct(
        private readonly StateFactory $stateMachine,
        private readonly MessageBusInterface $bus,
        private readonly ObjectManager $orderManager,
        private readonly ObjectManager $shipmentManager,
        private readonly ShipupApiInterface $shipupApi
    ) {
    }

    /**
     * @throws \Exception
     */
    public function setTracking($trackingUrl, $order)
    {
        $stateMachine = $this->stateMachine->get($order, OrderShippingTransitions::GRAPH);
        $stateMachine->apply(OrderShippingTransitions::TRANSITION_SHIP);

        Assert::notNull($order->getShipments(), 'Shipment with given order does not exists');
        $shipment = $order->getShipments()[0];
        if (null !== $trackingUrl) {
            foreach ($trackingUrl as $key => $url) {
                if (0 !== $key) {
                    $shipment = clone $shipment;
                }
                $shipment->setTracking(is_array($url) && array_key_exists('link', $url) ? $url['link'] : $url);
                if (is_array($url) && array_key_exists('number', $url)) {
                    $shipment->setTrackingNumber($url['number']);
                }
                $order->addShipment($shipment);
            }
        } else {
            $shipment->setTracking(null);
        }
        $shipments = $order->getShipments();
        foreach ($shipments as $shipment) {
            $stateMachine = $this->stateMachine->get($shipment, ShipmentTransitions::GRAPH);
            $stateMachine->apply(ShipmentTransitions::TRANSITION_SHIP);

            $this->shipmentManager->persist($shipment);
        }

        $now = new \DateTime();
        if ($now < $shipment->getEndDeliverySlot()) {
            $this->bus->dispatch(new Mail(MailerEmailInterface::TYPE_TRACKING_EMAIL, new OrderDTO($order), [
                'recipients' => [
                    [
                        'email' => $order->getCustomer()->getEmail(),
                        'firstname' => $order->getCustomer()->getFirstName(),
                        'lastname' => $order->getCustomer()->getLastName(),
                    ],
                ],
            ]));
        }

        $this->orderManager->persist($order);
    }

    /**
     * @throws \Exception
     */
    public function setTrackingVir($trackingUrl, $order)
    {
        /** @var ShipmentInterface $shipment */
        $shipment = $order->getShipments()[0];
        $shipment->setTracking($trackingUrl);
        $shipment->setTrackingNumber(self::VIR_TRACKING_PREFIX . ltrim((string) $order->getNumber(), '0'));
        $this->shipmentManager->persist($shipment);
        $this->shipupApi->tracker($shipment);

        $now = new \DateTime();
        if ($now < $shipment->getEndDeliverySlot()) {
            $this->bus->dispatch(new Mail(MailerEmailInterface::TYPE_TRACKING_EMAIL, new OrderDTO($order), [
                'recipients' => [
                    [
                        'email' => $order->getCustomer()->getEmail(),
                        'firstname' => $order->getCustomer()->getFirstName(),
                        'lastname' => $order->getCustomer()->getLastName(),
                    ],
                ],
            ]));
        }

        $this->orderManager->persist($order);
    }
}
