<?php

declare(strict_types=1);

namespace App\Tests\Functional\Resolver\Order;

use App\Resolver\Order\OrderNumberResolverInterface;
use App\Resolver\Order\PackingOrderNumberResolver;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class CompositeOrderNumberResolverTest extends KernelTestCase
{
    private readonly OrderNumberResolverInterface $compositeOrderNumberResolver;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->compositeOrderNumberResolver = self::$container->get(OrderNumberResolverInterface::class);
    }

    /**
     * @dataProvider provideData
     */
    public function testItCorrectlyResolvesFromContext(array $context, string $expected): void
    {
        self::assertEquals($expected, $this->compositeOrderNumberResolver->resolve($context));
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
