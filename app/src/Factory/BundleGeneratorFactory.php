<?php

namespace App\Factory;

use App\Doctrine\Entity\BundleGenerator;
use App\Doctrine\Entity\BundleGeneratorInterface;
use App\Doctrine\Entity\Sylius\ProductInterface;

class BundleGeneratorFactory implements BundleGeneratorFactoryInterface
{
    public function createNew(): BundleGeneratorInterface
    {
        return new BundleGenerator();
    }

    public function createNewForProduct(ProductInterface $product): BundleGeneratorInterface
    {
        $generator = $this->createNew();
        $generator->setProduct($product);

        return $generator;
    }
}
