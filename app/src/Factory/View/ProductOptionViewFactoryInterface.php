<?php

namespace App\Factory\View;

use App\Doctrine\Entity\Sylius\ProductInterface;
use App\Doctrine\Entity\Sylius\ProductOption;
use App\View\ProductOptionView;
use Sylius\Component\Core\Model\ChannelInterface;

interface ProductOptionViewFactoryInterface
{
    public function create(ProductOption $option, string $locale, ProductInterface $product): ProductOptionView;

    public function createForTaxon(ProductOption $option, string $locale, ProductInterface $product): ProductOptionView;

    public function createForBundle(ProductOption $option, string $locale, ProductInterface $product, ChannelInterface $channel): ProductOptionView;
}
