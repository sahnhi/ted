<?php

namespace App\Factory\View;

use App\Doctrine\Entity\Sylius\ProductInterface;
use App\Doctrine\Entity\Sylius\ProductVariantInterface;
use App\Exception\ViewCreationException;
use App\View\ProductVariantTransmissionView;
use App\View\ProductVariantView;
use Sylius\Component\Core\Model\ChannelInterface;

interface ProductVariantViewFactoryInterface
{
    /**
     * @throws ViewCreationException
     */
    public function create(ProductVariantInterface $productVariant, ChannelInterface $channel, string $locale): ProductVariantView;

    public function createForCategory(ProductVariantInterface $productVariant, ProductInterface $product, ChannelInterface $channel, string $locale): ProductVariantView;

    public function createForTaxon(ProductVariantInterface $productVariant, ?ProductInterface $productWithVariant, ChannelInterface $channel, string $locale): ProductVariantView;

    public function createTransmission(ProductVariantInterface $productVariant, ChannelInterface $channel, string $locale): ProductVariantTransmissionView;
}
