<?php

declare(strict_types=1);

namespace App\Calculator\Shipping;

use Sylius\Component\Core\Model\ShipmentInterface;
use Sylius\Component\Shipping\Calculator\CalculatorInterface;
use Sylius\Component\Shipping\Model\ShipmentInterface as BaseShipmentInterface;
use Webmozart\Assert\Assert;

class AmountThresholdCalculator implements CalculatorInterface
{
    public const TYPE = 'amount_threshold';

    /**
     * @param ShipmentInterface $subject
     */
    public function calculate(BaseShipmentInterface $subject, array $configuration): int
    {
        Assert::isInstanceOf($subject, ShipmentInterface::class);

        $channelCode = $subject->getOrder()->getChannel()->getCode();

        return $configuration[$channelCode]['price'];
    }

    public function getType(): string
    {
        return self::TYPE;
    }
}
