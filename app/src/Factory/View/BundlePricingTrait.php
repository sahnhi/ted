<?php

namespace App\Factory\View;

use App\Doctrine\Entity\Sylius\ProductInterface;
use App\Doctrine\Enum\ProductBundleType;
use Sylius\Component\Core\Model\ChannelPricing;

trait BundlePricingTrait
{
    public static function getPromotionBundlePrice($price): int
    {
        return (int) round($price * 0.9);
    }

    public static function getBundlePrice(ChannelPricing $pricing): int
    {
        /** @var ProductInterface $product */
        $product = $pricing->getProductVariant()?->getProduct();
        $bundleType = $product->getBundleType();

        return self::getBundlePriceFromValues($pricing->getPrice(), $bundleType);
    }

    public static function getBundlePriceFromValues(int $price, ?ProductBundleType $bundleType): int
    {
        return match ($bundleType) {
            ProductBundleType::FreeProduct, null => $price,
            ProductBundleType::Promotion => self::getPromotionBundlePrice($price),
        };
    }
}
