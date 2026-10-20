<?php

declare(strict_types=1);

namespace App\Factory\View;

use App\Doctrine\Entity\ProductGoogleRssInterface;
use App\Doctrine\Entity\Sylius\Product;
use App\Doctrine\Entity\Sylius\ProductVariantInterface;
use App\Doctrine\Repository\Sylius\ProductVariantRepositoryInterface;
use App\View\RelatedProductView;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\ChannelPricingInterface;

class RelatedProductViewFactory implements RelatedProductViewFactoryInterface
{
    public function __construct(
        private readonly ImageViewFactoryInterface $imageViewFactory,
        private readonly ProductVariantRepositoryInterface $productVariantRepository,
    ) {
    }

    public function create(Product $product, ChannelInterface $channel, string $locale): RelatedProductView
    {
        $translation = $product->getTranslation($locale);
        $gtin = ($productRssGoogle = $product->getProductGoogleRss()) instanceof ProductGoogleRssInterface ? $productRssGoogle->getGtin() : null;

        $images = [];
        foreach ($product->getImages() as $image) {
            $images[] = $this->imageViewFactory->create($image);
        }

        $originalLowestPrice = null;
        $lowestPrice = null;
        $cheapestProductVariant = $this->productVariantRepository->findCheapestPrice($product, $channel);
        if ($cheapestProductVariant instanceof ProductVariantInterface) {
            $channelPricing = $cheapestProductVariant->getChannelPricingForChannel($channel);
            $originalLowestPrice = $channelPricing instanceof ChannelPricingInterface ? $channelPricing->getOriginalPrice() : null;
            $lowestPrice = $channelPricing instanceof ChannelPricingInterface ? $channelPricing->getPrice() : null;
        }

        return new RelatedProductView(
            $product->getCode(),
            $translation->getName(),
            $translation->getSlug(),
            $originalLowestPrice,
            $lowestPrice,
            $translation->getDescription(),
            $translation->getShortDescription(),
            $gtin,
            (string) $product->getAverageRating(),
            $images
        );
    }
}
