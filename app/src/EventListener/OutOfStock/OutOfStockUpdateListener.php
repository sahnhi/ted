<?php

namespace App\EventListener\OutOfStock;

use App\Doctrine\Entity\OrderOutOfStock;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class OutOfStockUpdateListener
{
    public function __construct(
        private readonly TokenStorageInterface $tokenStorage
    ) {
    }

    public function preUpdate(OrderOutOfStock $orderOutOfStock, PreUpdateEventArgs $args)
    {
        $date = new \DateTime();

        if ($args->hasChangedField('status') || $args->hasChangedField('customerState')) {
            foreach ($args->getEntityChangeSet() as $key => $changes) {
                if (in_array($key, ['status', 'customerState'])) {
                    $history = $orderOutOfStock->getHistory();
                    $history .= $key . '/';
                    $history .= $changes[0] . '/';
                    $history .= $changes[1] . '/';
                    $history .= 'le ' . $date->format('d.m.Y à H:i') . '/';
                    $history .= $this->tokenStorage->getToken()->getUsername() . ';';
                    $orderOutOfStock->setHistory($history);
                }
            }
        }
    }
}
