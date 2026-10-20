<?php

namespace App\Factory;

use App\Doctrine\Entity\BundleGeneratorInterface;
use App\Doctrine\Entity\Sylius\ProductInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;

interface BundleGeneratorFactoryInterface extends FactoryInterface
{
    public function createNew(): BundleGeneratorInterface;

    public function createNewForProduct(ProductInterface $product): BundleGeneratorInterface;
}
