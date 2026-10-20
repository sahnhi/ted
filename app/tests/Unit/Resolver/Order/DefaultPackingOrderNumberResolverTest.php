<?php

declare(strict_types=1);

namespace App\Tests\Unit\Resolver\Order;

use App\Resolver\Order\DefaultPackingOrderNumberResolver;
use App\Resolver\Order\OrderNumberResolverInterface;
use App\Resolver\Order\PackingOrderNumberResolver;
use PHPUnit\Framework\TestCase;

class DefaultPackingOrderNumberResolverTest extends TestCase
{
    private readonly OrderNumberResolverInterface $resolver;

    protected function setUp(): void
    {
        $this->resolver = new DefaultPackingOrderNumberResolver();
    }

    /**
     * @dataProvider provideData
     */
    public function testItExtractsTheOrderNumber(array $context, string $expectedOrderNumber): void
    {
        self::assertSame($expectedOrderNumber, $this->resolver->resolve($context));
    }

    private function provideData(): array
    {
        return [
            [[[PackingOrderNumberResolver::PACKING_DATA_ORDER_NUMBER_INDEX => 'TED12345']], '12345'],
            [[[PackingOrderNumberResolver::PACKING_DATA_ORDER_NUMBER_INDEX => 'TEDLYON42345']], '42345'],
            [[[PackingOrderNumberResolver::PACKING_DATA_ORDER_NUMBER_INDEX => 'OLV03062024']], '03062024'],
            [[[PackingOrderNumberResolver::PACKING_DATA_ORDER_NUMBER_INDEX => 'BTQ1204']], '1204'],
            [[[PackingOrderNumberResolver::PACKING_DATA_ORDER_NUMBER_INDEX => 'STE04062024']], '04062024'],
            [[[PackingOrderNumberResolver::PACKING_DATA_ORDER_NUMBER_INDEX => 'TEDBUR01072024']], '01072024'],
            [[[PackingOrderNumberResolver::PACKING_DATA_ORDER_NUMBER_INDEX => 'ESA03072024']], '03072024'],
            [[[PackingOrderNumberResolver::PACKING_DATA_ORDER_NUMBER_INDEX => 'CHA19052025']], '19052025'],
            [[[PackingOrderNumberResolver::PACKING_DATA_ORDER_NUMBER_INDEX => '0012345']], '0012345'],
        ];
    }
}
