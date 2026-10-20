<?php

namespace App\EventListener\OrderReturn;

use App\Doctrine\Entity\OrderReturn;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class OrderReturnUpdateListener
{
    public function __construct(
        private readonly TokenStorageInterface $tokenStorage
    ) {
    }

    public function preUpdate(OrderReturn $orderReturn, PreUpdateEventArgs $args)
    {
        $date = new \DateTime();
        if ($args->hasChangedField('savStatus') || $args->hasChangedField('warehouseStatus')) {
            foreach ($args->getEntityChangeSet() as $key => $changes) {
                $history = $orderReturn->getHistory();
                if (in_array($key, ['savStatus', 'warehouseStatus'])) {
                    $from = ('savStatus' === $key ? 'SAV' : 'Entrepôt') . ' : ';
                    $history .= $date->format('d/m/Y H:i:s') . '||';

                    $history .= $from . $changes[0] . ' => ' . $changes[1] . '||' .
                        $this->tokenStorage->getToken()->getUsername() .
                        ';';
                    $orderReturn->setHistory($history);
                }
            }
        }
        if ($args->hasChangedField('variants')) {
            $history = $orderReturn->getHistory();
            $history .= $date->format('d/m/Y H:i:s') . '||';
            $history .= 'Produit(s) reçu(s) :  [' . $args->getEntityChangeSet()['variants'][1] . ']||';

            $history .= $this->tokenStorage->getToken()->getUsername() .
                ';';
            $orderReturn->setHistory($history);
            $orderReturn->setVariants(null);
        }
    }

    public function preFlush(OrderReturn $orderReturn)
    {
        $date = new \DateTime();
        $history = $orderReturn->getHistory();
        if (empty($history)) {
            if (null !== $orderReturn->getSavStatus()) {
                $from = 'SAV : ';
                $history .= $date->format('d/m/Y H:i:s') . '||';

                $history .= $from . $orderReturn->getSavStatus()->getSavStatus() . '||' .
                    $this->tokenStorage->getToken()->getUsername() .
                    ';';
                $orderReturn->setHistory($history);
            }
            if (null !== $orderReturn->getWarehouseStatus()) {
                $from = 'Entrepôt : ';
                $history .= $date->format('d/m/Y H:i:s') . '||';

                $history .= $from . $orderReturn->getWarehouseStatus()->getWarehouseStatus() . '||' .
                    $this->tokenStorage->getToken()->getUsername() .
                    ';';
                $orderReturn->setHistory($history);
            }

            if (null !== $orderReturn->getVariants()) {
                $history = $orderReturn->getHistory();
                $history .= $date->format('d/m/Y H:i:s') . '||';
                $history .= 'Produit(s) reçu(s) :  [' . $orderReturn->getVariants() . ']||';

                $history .= $this->tokenStorage->getToken()->getUsername() .
                    ';';
                $orderReturn->setHistory($history);
                $orderReturn->setVariants(null);
            }
        }
    }
}
