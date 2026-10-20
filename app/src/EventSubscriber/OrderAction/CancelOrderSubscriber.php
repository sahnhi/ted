<?php

declare(strict_types=1);

namespace App\EventSubscriber\OrderAction;

use App\Event\CancelOrderEvent;
use App\StateMachine\StateMachineApplicatorInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CancelOrderSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly StateMachineApplicatorInterface $stateMachineApplicator
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CancelOrderEvent::NAME => ['cancel'],
        ];
    }

    public function cancel(CancelOrderEvent $event): void
    {
        $this->stateMachineApplicator->cancelOrder($event->getOrder());
    }
}
