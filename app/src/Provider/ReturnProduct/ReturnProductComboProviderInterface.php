<?php

namespace App\Provider\ReturnProduct;

interface ReturnProductComboProviderInterface
{
    public function provide($originalVariant, $variant);

    public function provideNew($originalVariant, $variant);
}
