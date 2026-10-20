<?php

declare(strict_types=1);

namespace App\ApiPlatform\State\Order;

use ApiPlatform\Core\DataProvider\ContextAwareCollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\Doctrine\Entity\Sylius\Order;
use App\Doctrine\Repository\Sylius\OrderRepositoryInterface;

class EmailAndOrderNumberOrderCollectionProvider implements ContextAwareCollectionDataProviderInterface, RestrictedDataProviderInterface
{
    public function __construct(
        private readonly OrderRepositoryInterface $orderRepository,
    ) {
    }

    public function supports(string $resourceClass, ?string $operationName = null, array $context = []): bool
    {
        return Order::class === $resourceClass
            && 'shop_get_by_email_and_order_number' === $operationName
            && isset($context['filters']['email'], $context['filters']['orderNumber']);
    }

    /**
     * @return iterable<Order>
     */
    public function getCollection(string $resourceClass, ?string $operationName = null, array $context = []): iterable
    {
        /** @var Order|null $order */
        $order = $this->orderRepository->findOneByNumber($context['filters']['orderNumber']);

        if ($order?->getCustomer()?->getEmail() === $context['filters']['email']) {
            return [$order];
        }

        return [];
    }
}
