<?php

namespace App\Factory\View;

use App\Doctrine\Entity\Sylius\ProductInterface;
use App\View\ProductTransmissionView;
use App\View\ProductView;
use Sylius\Component\Core\Model\ChannelInterface;

interface ProductViewFactoryInterface
{
    public function create(ProductInterface $product, ChannelInterface $channel, string $locale, $removeCartField = false, bool $withVariants = false): ProductView;

    public function createForCategory(ProductInterface $product, ChannelInterface $channel, string $locale, $removeCartField = false): ProductView;

    public function list(ProductInterface $product, ChannelInterface $channel, string $locale, bool $bounds = false): ProductView;

    public function listShopApi(ProductInterface $product, ChannelInterface $channel, string $locale, bool $bounds = false): ProductView;

    public function listForTaxon(ProductInterface $product, array $productTaxons, ChannelInterface $channel, string $locale): ProductView;

    public function createTransmission(ProductInterface $product, ChannelInterface $channel, string $locale): ProductTransmissionView;
}
