<?php

declare(strict_types=1);

namespace App\Twig\Extension\Shipping;

use App\Twig\Runtime\ShippingMethodCategoryRuntime;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class ShippingMethodCategoryExtension extends AbstractExtension
{
    public function getFunctions(): iterable
    {
        return [
            new TwigFunction('app_get_eligible_shipping_method_category_for_order', [ShippingMethodCategoryRuntime::class, 'provideEligibleForOrder']),
        ];
    }
}
