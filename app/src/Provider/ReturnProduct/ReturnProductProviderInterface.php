<?php

namespace App\Provider\ReturnProduct;

interface ReturnProductProviderInterface
{
    public function provide($originalProduct);

    public function provideNew($originalProduct);
}
