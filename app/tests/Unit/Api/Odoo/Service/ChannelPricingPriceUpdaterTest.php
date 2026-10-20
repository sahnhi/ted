<?php

declare(strict_types=1);

namespace App\Tests\Unit\Api\Odoo\Service;

use App\Api\Odoo\Service\ChannelPricingPriceUpdater;
use App\Doctrine\Entity\Sylius\ChannelPricing;
use PHPUnit\Framework\TestCase;

class ChannelPricingPriceUpdaterTest extends TestCase
{
    private readonly ChannelPricingPriceUpdater $channelPricingPriceUpdater;

    protected function setUp(): void
    {
        $this->channelPricingPriceUpdater = new ChannelPricingPriceUpdater();
    }

    public function testUpdateChannelPricingWithDiscount(): void
    {
        $channelPricing = new ChannelPricing();
        $channelPricing->setPrice(5000);
        $channelPricing->setOriginalPrice(10000);

        self::assertTrue($channelPricing->hasDiscount());

        $this->channelPricingPriceUpdater->updateChannelPricing($channelPricing, 11000);

        self::assertEquals(5000, $channelPricing->getPrice());
        self::assertEquals(11000, $channelPricing->getOriginalPrice());
    }

    public function testUpdateChannelPricingWithoutDiscount(): void
    {
        $channelPricing = new ChannelPricing();
        $channelPricing->setPrice(5000);

        self::assertFalse($channelPricing->hasDiscount());

        $this->channelPricingPriceUpdater->updateChannelPricing($channelPricing, 11000);

        self::assertEquals(11000, $channelPricing->getPrice());
        self::assertNull($channelPricing->getOriginalPrice());
    }
}
