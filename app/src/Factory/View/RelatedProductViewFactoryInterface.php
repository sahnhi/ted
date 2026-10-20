<?php

declare(strict_types=1);

namespace App\Factory\View;

use App\Doctrine\Entity\Sylius\Product;
use App\View\RelatedProductView;
use Sylius\Component\Core\Model\ChannelInterface;

interface RelatedProductViewFactoryInterface
{
    public function create(Product $product, ChannelInterface $channel, string $locale): RelatedProductView;
}
