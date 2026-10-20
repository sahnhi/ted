<?php

declare(strict_types=1);

namespace App\ERP\Application\Query\Order;

use App\Doctrine\Repository\Sylius\OrderRepository;
use App\ERP\Infrastructure\Shared\Bus\Query\QueryHandlerInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Sylius\Component\Resource\Repository\RepositoryInterface;

final class GetOrdersToShipQueryHandler implements QueryHandlerInterface
{
    public function __construct(
        /** @var RepositoryInterface&OrderRepository $orderRepository */
        private readonly RepositoryInterface $orderRepository,
    ) {
    }

    public function __invoke(GetOrdersToShipQuery $query): Paginator
    {
        return new Paginator($this->orderRepository->getErpShipped());
    }
}
