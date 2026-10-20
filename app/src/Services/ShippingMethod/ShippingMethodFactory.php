<?php

namespace App\Services\ShippingMethod;

use App\Doctrine\Entity\Sylius\ShippingMethod;
use App\Services\ShippingMethod\Methods\Chronopost;
use App\Services\ShippingMethod\Methods\DefaultShippingMethod;
use App\Services\ShippingMethod\Methods\DPDRelay;

class ShippingMethodFactory
{
    /** @var AbstractShippingMethod[] */
    private array $shippingMethods = [];

    public function __construct(
        DefaultShippingMethod $defaultShippingMethod,
        Chronopost $chronopost,
        DPDRelay $dpdRelay
    ) {
        $this->shippingMethods = [
            DefaultShippingMethod::NAME => $defaultShippingMethod,
            Chronopost::NAME => $chronopost,
            DPDRelay::NAME => $dpdRelay,
        ];
    }

    public function get($shippingMethod)
    {
        if (!array_key_exists($shippingMethod, $this->shippingMethods)) {
            return $this->shippingMethods[DefaultShippingMethod::NAME];
        }

        return $this->shippingMethods[$shippingMethod];
    }

    public function getFromShippingMethod(ShippingMethod $shippingMethod)
    {
        $code = ucfirst(strtolower((string) $shippingMethod->getCode()));

        return $this->get($code);
    }
}
