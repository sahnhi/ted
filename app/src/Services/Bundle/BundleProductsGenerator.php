<?php

namespace App\Services\Bundle;

use App\Doctrine\Entity\BundleGeneratorCombinationInterface;
use App\Doctrine\Entity\BundleGeneratorInterface;
use App\Doctrine\Entity\BundleGeneratorProduct;
use App\Doctrine\Entity\BundleGeneratorProductInterface;
use App\Doctrine\Entity\BundleGeneratorVariant;
use App\Doctrine\Entity\BundleGeneratorVariantInterface;
use Sylius\Bundle\ResourceBundle\Event\ResourceControllerEvent;

class BundleProductsGenerator implements BundleProductsGeneratorInterface
{
    public function generateProducts(ResourceControllerEvent $event): void
    {
        /** @var BundleGeneratorInterface $generator */
        $generator = $event->getSubject();

        if (($count = $generator->getProducts()->count()) < $generator->getNbProducts()) {
            while ($count < $generator->getNbProducts()) {
                $product = new BundleGeneratorProduct();
                $product->setBundleGenerator($generator);
                $generator->addProduct($product);
                ++$count;
            }
        }
    }

    public function generateVariants(ResourceControllerEvent $event): void
    {
        /** @var BundleGeneratorInterface $generator */
        $generator = $event->getSubject();

        $variantsToKeep = [];

        /** @var BundleGeneratorCombinationInterface $combination */
        foreach ($generator->getCombinationsToGenerate() as $combination) {
            $variantsArray = [];

            /** @var BundleGeneratorProductInterface $product */
            foreach ($generator->getProducts() as $product) {
                if (!($variant = $combination->getVariantFromProduct($product)) instanceof BundleGeneratorVariantInterface) {
                    $variant = new BundleGeneratorVariant();
                    $variant->setProduct($product);
                    $variant->setCombination($combination);
                    $combination->addVariant($variant);
                }

                $variantsArray[] = $variant;
            }

            $variantsToKeep[$combination->getId()] = $variantsArray;
        }

        $this->cleanVariants($generator, $variantsToKeep);
    }

    private function cleanVariants(BundleGeneratorInterface $generator, array $variantsToKeep): void
    {
        /** @var BundleGeneratorCombinationInterface $combination */
        foreach ($generator->getCombinations() as $combination) {
            /** @var BundleGeneratorVariantInterface $variant */
            foreach ($combination->getVariants() as $variant) {
                if (!in_array($variant, $variantsToKeep[$combination->getId()])) {
                    $combination->removeVariant($variant);
                }
            }
        }
    }
}
