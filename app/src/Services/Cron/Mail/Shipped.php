<?php

namespace App\Services\Cron\Mail;

use App\Doctrine\Entity\Sylius\Order;
use App\Doctrine\Entity\Sylius\ShippingMethodInterface;
use App\Doctrine\Repository\Sylius\OrderRepository;
use App\Message\DTO\OrderDTO;
use App\Message\Mail;
use App\Services\Mail\MailerEmailInterface;
use App\Services\Sentry\ReportingInterface;
use Doctrine\ORM\EntityManagerInterface;
use Sentry;
use SM\Factory\Factory;
use Sylius\Component\Core\OrderShippingTransitions;
use Sylius\Component\Shipping\ShipmentTransitions;
use Symfony\Component\Messenger\MessageBusInterface;

class Shipped
{
    public function __construct(
        private readonly OrderRepository $orderRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly Factory $stateMachine,
        private readonly MessageBusInterface $bus,
        private readonly ReportingInterface $reporting
    ) {
    }

    /**
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function process(): void
    {
        $orders = $this->orderRepository->getListQueryShipped(ShippingMethodInterface::BIKE_DELIVERY);

        foreach ($orders as $order) {
            /* @var Order $order */
            try {
                $stateMachine = $this->stateMachine->get($order, OrderShippingTransitions::GRAPH);
                $stateMachine->apply(OrderShippingTransitions::TRANSITION_SHIP);

                $shipment = $order->getShipments()->first();

                $stateMachine = $this->stateMachine->get($shipment, ShipmentTransitions::GRAPH);
                $stateMachine->apply(ShipmentTransitions::TRANSITION_SHIP);

                $this->bus->dispatch(
                    new Mail(
                        MailerEmailInterface::TYPE_TRACKING_EMAIL,
                        new OrderDTO($order),
                        [
                            'recipients' => [
                                [
                                    'email' => $order->getCustomer()->getEmail(),
                                    'firstname' => $order->getCustomer()->getFirstName(),
                                    'lastname' => $order->getCustomer()->getLastName(),
                                ],
                            ],
                        ]
                    )
                );
            } catch (\Exception $e) {
                $this->reporting->reportIssue(
                    $e,
                    Sentry\Severity::error(),
                    ['Order' => $order->getNumber()]
                );
            }
        }

        $this->entityManager->flush();
    }
}
