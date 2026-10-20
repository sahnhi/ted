<?php

namespace App\Factory\ReturnProduct;

use App\Doctrine\Entity\ProductVariantEcotax;

class ReturnProductVariantEcotaxFactory implements ReturnFactoryInterface
{
    /**
     * @param ProductVariantEcotax $originalProductVariantEcotax
     */
    public function createNewFromExisting($originalProductVariantEcotax): ProductVariantEcotax
    {
        $productVariantEcotax = $this->createNew();
        $productVariantEcotax->setEcotax($originalProductVariantEcotax->getEcotax());
        $productVariantEcotax->setManufacturerPaid(ProductVariantEcotax::MANUFACTURER_RETURN);
        $productVariantEcotax->setQuantity($originalProductVariantEcotax->getQuantity());
        $productVariantEcotax->setVariant($originalProductVariantEcotax->getVariant());

        return $productVariantEcotax;
    }

    public function createNew(): ProductVariantEcotax
    {
        return new ProductVariantEcotax();
    }
}
