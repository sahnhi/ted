<?php

declare(strict_types=1);

namespace App\Resolver\Shipping;

use Sylius\Component\Core\Model\AdminUserInterface;
use Sylius\Component\Core\Model\ShipmentInterface;
use Sylius\Component\Shipping\Exception\UnresolvedDefaultShippingMethodException;
use Sylius\Component\Shipping\Model\ShipmentInterface as BaseShipmentInterface;
use Sylius\Component\Shipping\Model\ShippingMethodInterface;
use Sylius\Component\Shipping\Resolver\DefaultShippingMethodResolverInterface;
use Sylius\Component\Shipping\Resolver\ShippingMethodsResolverInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ShippingMethodCategoryBasedDefaultShippingMethodsResolver implements DefaultShippingMethodResolverInterface
{
    public function __construct(
        private readonly DefaultShippingMethodResolverInterface $decoratedDefaultShippingMethodResolver,
        private readonly ShippingMethodsResolverInterface $shippingMethodCategoryBasedShippingMethodsResolver,
        private readonly TokenStorageInterface $tokenStorage,
    ) {
    }

    /**
     * @param ShipmentInterface $shipment
     *
     * @throws UnresolvedDefaultShippingMethodException
     */
    public function getDefaultShippingMethod(BaseShipmentInterface $shipment): ShippingMethodInterface
    {
        $user = $this->tokenStorage->getToken()?->getUser();

        // We must never override the shipping method of an order made by an admin since the shipping methods
        // are manually set. We therefore consider that we cannot resolve a default shipping method to prevent
        // Sylius from overriding it with the first eligible one.
        if ($user instanceof AdminUserInterface && null !== $shipment->getMethod()) {
            throw new UnresolvedDefaultShippingMethodException();
        }

        $shippingMethods = $this->shippingMethodCategoryBasedShippingMethodsResolver->getSupportedMethods($shipment);

        return $shippingMethods[0]
            ?? $this->decoratedDefaultShippingMethodResolver->getDefaultShippingMethod($shipment);
    }
}
