<?php

namespace App\Provider\ReturnProduct;

use App\Doctrine\Entity\ProductVariantEcotax;
use App\Factory\ReturnProduct\ReturnProductVariantEcotaxFactory;
use Sylius\Component\Resource\Repository\RepositoryInterface;

class ReturnProductVariantEcotaxProvider implements ReturnProductVariantEcotaxProviderInterface
{
    public function __construct(
        private readonly RepositoryInterface $productVariantEcotaxRepository,
        private readonly ReturnProductVariantEcotaxFactory $returnProductVariantEcotaxFactory
    ) {
    }

    public function provide($originalProductVariantEcotax, $variant): ?ProductVariantEcotax
    {
        $productVariantEcotax = $this->productVariantEcotaxRepository->findBy(['ecotax' => $originalProductVariantEcotax->getEcotax(), 'variant' => $variant]);

        if (empty($productVariantEcotax)) {
            $productVariantEcotax = $this->provideNew($originalProductVariantEcotax, $variant);
        } else {
            $productVariantEcotax = $productVariantEcotax[0];
        }

        return $productVariantEcotax;
    }

    public function provideNew($originalProductVariantEcotax, $variant): ?ProductVariantEcotax
    {
        /** @var ProductVariantEcotax $productVariantEcotax */
        $productVariantEcotax = $this->returnProductVariantEcotaxFactory->createNewFromExisting($originalProductVariantEcotax);
        $productVariantEcotax->setVariant($variant);

        $this->productVariantEcotaxRepository->add($productVariantEcotax);

        return $productVariantEcotax;
    }
}
