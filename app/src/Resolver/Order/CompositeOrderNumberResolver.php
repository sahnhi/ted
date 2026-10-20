<?php

declare(strict_types=1);

namespace App\Resolver\Order;

use App\Exception\Order\UnresolvableOrderNumberException;

class CompositeOrderNumberResolver implements OrderNumberResolverInterface
{
    public function __construct(
        /** @var iterable<OrderNumberResolverInterface> */
        private readonly iterable $resolvers
    ) {
    }

    public function resolve(array $context): string
    {
        foreach ($this->resolvers as $resolver) {
            try {
                return $resolver->resolve($context);
            } catch (UnresolvableOrderNumberException) {
                continue;
            }
        }

        throw new UnresolvableOrderNumberException('No suitable order number resolver');
    }
}
