<?php

namespace App\Doctrine\Enum;

enum ProductBundleType: string
{
    case Promotion = 'promotion';
    case FreeProduct = 'free_product';
}
