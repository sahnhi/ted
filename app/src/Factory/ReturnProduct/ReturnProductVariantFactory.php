<?php

namespace App\Factory\ReturnProduct;

use App\Doctrine\Entity\Sylius\ProductVariant;
use App\Doctrine\Entity\Sylius\ProductVariantInterface;
use App\Provider\ReturnProduct\ReturnProductVariantProvider;
use Sylius\Component\Product\Factory\ProductVariantFactory;
use Sylius\Component\Product\Factory\ProductVariantFactoryInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Sylius\Component\Shipping\Model\ShippingCategoryInterface;

class ReturnProductVariantFactory extends ProductVariantFactory implements ReturnFactoryInterface, ProductVariantFactoryInterface
{
    public const SMALL_SHIPPING_CATEGORIES = ['pillow-best', 'small'];
    public const RETURN_SMALL_SHIPPING_CATEGORY = 'return-small';
    public const RETURN_BIG_SHIPPING_CATEGORY = 'return-big';

    public function __construct(
        ProductVariantFactoryInterface $factory,
        private readonly RepositoryInterface $shippingCategoryRepository
    ) {
        parent::__construct($factory);
    }

    public function createNewFromExisting($original): ProductVariantInterface
    {
        /** @var ProductVariantInterface $variant */
        $variant = $this->createNew();
        $variant->setCode(ReturnProductVariantProvider::PRODUCT_VARIANT_RETURN_PREFIX . $original->getCode());
        $variant->setTaxCategory($original->getTaxCategory());
        $variant->setEnabled(true);
        $variant->setTracked(false);
        $variant->setWidth($original->getWidth());
        $variant->setHeight($original->getHeight());
        $variant->setDepth($original->getDepth());
        $variant->setWeight($original->getWeight());
        $variant->setShippingRequired($original->isShippingRequired());
        $variant->setEcotax($original->getEcotax());
        $variant->setShippingCategory($this->getShippingCategory($original));

        return $variant;
    }

    private function getShippingCategory(ProductVariant $originalVariant): ShippingCategoryInterface
    {
        if (in_array($originalVariant->getShippingCategory()->getCode(), self::SMALL_SHIPPING_CATEGORIES)) {
            return $this->shippingCategoryRepository->findOneByCode(self::RETURN_SMALL_SHIPPING_CATEGORY);
        } else {
            return $this->shippingCategoryRepository->findOneByCode(self::RETURN_BIG_SHIPPING_CATEGORY);
        }
    }
}
