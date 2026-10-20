<?php

namespace App\Doctrine\Repository;

use App\Doctrine\Entity\Sylius\ProductInterface;

interface ProductGoogleRssRepositoryInterface
{
    public function getForProductVariants(ProductInterface $product): array;
}
