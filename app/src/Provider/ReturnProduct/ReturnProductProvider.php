<?php

namespace App\Provider\ReturnProduct;

use App\Doctrine\Entity\Sylius\ProductInterface;
use App\Factory\ReturnProduct\ReturnProductFactory;
use Sylius\Component\Resource\Repository\RepositoryInterface;

class ReturnProductProvider implements ReturnProductProviderInterface
{
    public const PRODUCT_RETURN_PREFIX = 'return-';

    public function __construct(
        private readonly RepositoryInterface $productRepository,
        private readonly ReturnProductFactory $returnProductFactory
    ) {
        $this->returnProductFactory->productReturnProvider = $this;
    }

    public function provide($originalProduct): ProductInterface
    {
        $product = $this->productRepository->findOneByCode(self::PRODUCT_RETURN_PREFIX . $originalProduct->getCode());

        if (null === $product) {
            $product = $this->provideNew($originalProduct);
        }

        return $product;
    }

    public function provideNew($originalProduct): ProductInterface
    {
        $product = $this->returnProductFactory->createNewFromExisting($originalProduct);
        $this->productRepository->add($product);

        return $product;
    }

    public function provideUpdatedOrNew(ProductInterface $originalProduct): ProductInterface
    {
        $product = $this->provide($originalProduct);
        $this->returnProductFactory->updateFromExisting($originalProduct, $product);

        return $product;
    }
}
