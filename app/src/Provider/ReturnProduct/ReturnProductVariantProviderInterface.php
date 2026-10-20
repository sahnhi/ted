<?php

namespace App\Provider\ReturnProduct;

interface ReturnProductVariantProviderInterface
{
    public function provide($originalVariant, $product);

    public function provideNew($originalVariant, $product);
}
