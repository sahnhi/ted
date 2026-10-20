<?php

declare(strict_types=1);

namespace App\Doctrine\Entity\Sylius;

use App\Doctrine\Trait\ChannelPricingTrait;
use Doctrine\ORM\Mapping as ORM;
use Setono\SyliusCatalogPromotionPlugin\Model\ChannelPricingInterface as CatalogPromotionChannelPricingInterface;
use Sylius\Component\Core\Model\ChannelPricing as BaseChannelPricing;

#[ORM\Entity]
#[ORM\Table(name: 'sylius_channel_pricing')]
class ChannelPricing extends BaseChannelPricing implements CatalogPromotionChannelPricingInterface
{
    use ChannelPricingTrait;
}
