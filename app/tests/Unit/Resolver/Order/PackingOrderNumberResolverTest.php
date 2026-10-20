<?php

declare(strict_types=1);

namespace App\Tests\Unit\Resolver\Order;

use App\Exception\Order\UnresolvableOrderNumberException;
use App\Resolver\Order\PackingOrderNumberResolver;
use PHPUnit\Framework\TestCase;

class PackingOrderNumberResolverTest extends TestCase
{
    public function testItThrowsAnExceptionWhenThereIsTooManyOrderNumbers(): void
    {
        $resolver = new PackingOrderNumberResolver('TED');

        $packingData = [
            [PackingOrderNumberResolver::PACKING_DATA_ORDER_NUMBER_INDEX => 'TED12345'],
            [PackingOrderNumberResolver::PACKING_DATA_ORDER_NUMBER_INDEX => 'TED23456'],
        ];

        $this->expectException(UnresolvableOrderNumberException::class);
        $this->expectExceptionMessage('Too many order numbers in packing data: TED12345, TED23456');

        $resolver->resolve($packingData);
    }

    public function testItThrowsAnExceptionIfTheOrderNumberIsNotValid(): void
    {
        $resolver = new PackingOrderNumberResolver('TED');

        $packingData = [
            [PackingOrderNumberResolver::PACKING_DATA_ORDER_NUMBER_INDEX => 'FOOBAR'],
        ];

        $this->expectException(UnresolvableOrderNumberException::class);
        $this->expectExceptionMessage('Unable to resolve packing data for order number: FOOBAR');

        $resolver->resolve($packingData);
    }

    /**
     * @dataProvider providePattern
     */
    public function testItResolvesTheOrderNumberWithPattern(string $pattern, string $orderNumber, string $expected): void
    {
        $resolver = new PackingOrderNumberResolver($pattern);

        $packingData = [
            [PackingOrderNumberResolver::PACKING_DATA_ORDER_NUMBER_INDEX => $orderNumber],
        ];

        self::assertSame($expected, $resolver->resolve($packingData));
    }

    private function providePattern(): array
    {
        return [
            ['TED', 'TED12345', '12345'],
            ['TEDLYON', 'TEDLYON42345', '42345'],
            ['OLV', 'OLV03062024', '03062024'],
            ['BTQ', 'BTQ1204', '1204'],
            ['STE', 'STE04062024', '04062024'],
            ['TEDBUR', 'TEDBUR01072024', '01072024'],
            ['ESA', 'ESA03072024', '03072024'],
        ];
    }
}
