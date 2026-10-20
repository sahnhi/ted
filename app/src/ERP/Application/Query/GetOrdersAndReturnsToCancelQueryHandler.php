<?php

declare(strict_types=1);

namespace App\ERP\Application\Query;

use App\Api\Odoo\Model\Order;
use App\Doctrine\Repository\Sylius\OrderRepository;
use App\ERP\Infrastructure\Shared\Bus\Query\QueryHandlerInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Sylius\Component\Core\OrderPaymentStates;
use Sylius\Component\Order\Model\OrderInterface as BaseOrderInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;

final class GetOrdersAndReturnsToCancelQueryHandler implements QueryHandlerInterface
{
    public function __construct(
        /** @var RepositoryInterface&OrderRepository $orderRepository */
        private readonly RepositoryInterface $orderRepository,
    ) {
    }

    public function __invoke(GetOrdersAndReturnsToCancelQuery $query): Paginator
    {
        $queryBuilder = $this->orderRepository->createQueryBuilder('o');

        $query = $queryBuilder
            ->leftJoin('o.channel', 'c')
            ->andWhere($queryBuilder->expr()->eq('o.state', ':state'))
            ->andWhere($queryBuilder->expr()->neq('o.odooState', ':odooState'))
            ->andWhere($queryBuilder->expr()->eq('o.paymentState', ':paymentState'))
            ->andWhere($queryBuilder->expr()->lt('o.updatedAt', ':nowMinusTenMinutes'))
            ->setParameter('state', BaseOrderInterface::STATE_CANCELLED)
            ->setParameter('odooState', Order::ODOO_STATE_CANCELLED)
            ->setParameter('paymentState', OrderPaymentStates::STATE_PAID)
            ->setParameter('nowMinusTenMinutes', new \DateTime('-10 minutes'))
            ->getQuery()
        ;

        return new Paginator($this->orderRepository->getErpToCancel());
    }
}
