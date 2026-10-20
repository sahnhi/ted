<?php

declare(strict_types=1);

namespace App\EventListener\Odoo;

use App\Api\Odoo\Model\OrderInterface;
use App\Doctrine\Entity\Sylius\Order;
use App\ERP\Domain\StateMachine\Applicator\StateMachineApplicatorInterface;
use Doctrine\Bundle\DoctrineBundle\EventSubscriber\EventSubscriberInterface;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use SM\Factory\FactoryInterface;
use Sylius\Component\Core\Model\Payment;

class OrderPaymentListener implements EventSubscriberInterface
{
    public function __construct(
        private readonly FactoryInterface $stateMachineFactory,
        private readonly StateMachineApplicatorInterface $stateMachineApplicator,
    ) {
    }

    public function getSubscribedEvents(): array
    {
        return [
            Events::postPersist,
        ];
    }

    public function postPersist(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();

        if (!$entity instanceof Payment) {
            return;
        }

        /** @var Order $order */
        $order = $entity->getOrder();
        $odooOrderStateMachine = $this->stateMachineFactory->get($order, OrderInterface::GRAPH);

        if (false === $odooOrderStateMachine->can(OrderInterface::ODOO_TRANSITION_CONFIRM_RETURN)) {
            return;
        }

        $this->stateMachineApplicator->confirmReturn($order);
    }
}
