<?php

declare(strict_types=1);

namespace App\Tests\Unit\Resolver\Order;

use App\Exception\Order\UnresolvableOrderNumberException;
use App\Resolver\Order\CompositeOrderNumberResolver;
use App\Resolver\Order\PackingOrderNumberResolver;
use PHPUnit\Framework\TestCase;

class CompositeOrderNumberResolverTest extends TestCase
{
    public function testItCallsTheCorrectResolver(): void
    {
        $context = [
            [PackingOrderNumberResolver::PACKING_DATA_ORDER_NUMBER_INDEX => 'TED12345'],
        ];

        $correctOrderNumberResolver = $this->createMock(PackingOrderNumberResolver::class);
        $correctOrderNumberResolver
            ->expects($this->once())
            ->method('resolve')
            ->with($context)
            ->willReturn('12345');

        $wrongOrderNumberResolverOne = $this->createMock(PackingOrderNumberResolver::class);
        $wrongOrderNumberResolverOne
            ->expects($this->once())
            ->method('resolve')
            ->willThrowException(new UnresolvableOrderNumberException());

        $wrongOrderNumberResolverTwo = $this->createMock(PackingOrderNumberResolver::class);
        $wrongOrderNumberResolverTwo
            ->expects($this->once())
            ->method('resolve')
            ->willThrowException(new UnresolvableOrderNumberException());

        $notCalledResolver = $this->createMock(PackingOrderNumberResolver::class);
        $notCalledResolver->expects($this->never())->method('resolve');

        $compositeOrderNumberResolver = new CompositeOrderNumberResolver([
            $wrongOrderNumberResolverOne,
            $wrongOrderNumberResolverTwo,
            $correctOrderNumberResolver,
            $notCalledResolver,
        ]);

        self::assertSame('12345', $compositeOrderNumberResolver->resolve($context));
    }
}
