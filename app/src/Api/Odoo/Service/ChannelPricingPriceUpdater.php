<?php

declare(strict_types=1);

namespace App\Api\Odoo\Service;

use App\Doctrine\Entity\Sylius\ChannelPricing;

class ChannelPricingPriceUpdater
{
    public function updateChannelPricing(ChannelPricing $channelPricing, int $price): void
    {
        // If the price is reduced, it means that there is currently a discount on this channel pricing.
        // Since we do NOT want to stop the discount, we save the price in the originalPrice field.
        // Therefore, when the discount ends, the price will be set to the Odoo $price
        if ($channelPricing->hasDiscount()) {
            $channelPricing->setOriginalPrice($price);

            return;
        }

        $channelPricing->setPrice($price);
    }
}
