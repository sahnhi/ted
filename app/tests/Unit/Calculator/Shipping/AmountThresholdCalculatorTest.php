<?php

declare(strict_types=1);

namespace App\Tests\Unit\Calculator\Shipping;

use App\Calculator\Shipping\AmountThresholdCalculator;
use App\Doctrine\Entity\Sylius\Order;
use App\Doctrine\Entity\Sylius\Shipment;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Core\Model\Channel;
use Sylius\Component\Shipping\Model\ShipmentInterface as BaseShipmentInterface;

class AmountThresholdCalculatorTest extends TestCase
{
    private AmountThresholdCalculator $calculator;

    protected function setUp(): void
    {
        $this->calculator = new AmountThresholdCalculator();
    }

    public function testItThrowsAndExceptionIfTheShipmentIsNotAnInstanceOfCoreInterface(): void
    {
        $shipment = $this->createMock(BaseShipmentInterface::class);

        $this->expectException(\InvalidArgumentException::class);

        $this->calculator->calculate($shipment, []);
    }

    public function testItReturnsTheConfiguredAmountForTheChannel(): void
    {
        $configuration = [
            'default' => [
                'price' => 1000,
            ],
            'web' => [
                'price' => 5000,
            ],
        ];

        $channel = new Channel();
        $channel->setCode('default');

        $order = new Order();
        $order->setChannel($channel);

        $shipment = new Shipment();
        $shipment->setOrder($order);

        self::assertEquals(1000, $this->calculator->calculate($shipment, $configuration));
    }
}
