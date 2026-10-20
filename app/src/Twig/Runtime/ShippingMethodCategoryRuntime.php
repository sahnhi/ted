<?php

declare(strict_types=1);

namespace App\Twig\Runtime;

use App\Doctrine\Entity\ShippingMethodCategory;
use App\Doctrine\Entity\Sylius\Order;
use App\Provider\Shipping\ShippingMethodCategoryProvider;
use Twig\Extension\RuntimeExtensionInterface;

class ShippingMethodCategoryRuntime implements RuntimeExtensionInterface
{
    public function __construct(
        private readonly ShippingMethodCategoryProvider $shippingMethodCategoryProvider
    ) {
    }

    /**
     * @return array<ShippingMethodCategory>
     */
    public function provideEligibleForOrder(Order $order): array
    {
        return $this->shippingMethodCategoryProvider->provideEligibleForOrder($order);
    }
}
