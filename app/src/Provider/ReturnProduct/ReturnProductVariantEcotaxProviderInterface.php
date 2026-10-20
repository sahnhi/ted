<?php

namespace App\Provider\ReturnProduct;

use App\Doctrine\Entity\ProductVariantEcotax;

interface ReturnProductVariantEcotaxProviderInterface
{
    public function provide($originalProductVariantEcotax, $variant): ?ProductVariantEcotax;

    public function provideNew($originalProductVariantEcotax, $variant): ?ProductVariantEcotax;
}
