<?php

namespace App\Provider;

use Sylius\Component\Addressing\Matcher\ZoneMatcherInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\Scope;
use Sylius\Component\Core\Model\TaxRateInterface;
use Sylius\Component\Core\Provider\ZoneProviderInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;

class TaxRateProvider implements TaxRateProviderInterface
{
    public function __construct(
        private readonly RepositoryInterface $taxRateRepository,
        private readonly ZoneMatcherInterface $zoneMatcher,
        private readonly ZoneProviderInterface $defaultTaxZoneProvider
    ) {
    }

    public function provideTaxRateFromZone(OrderInterface $order): ?TaxRateInterface
    {
        $address = $order->getShippingAddress();

        $taxZone = $this->zoneMatcher->match($address, Scope::TAX);
        $taxZone = $taxZone ?: $this->defaultTaxZoneProvider->getZone($order);

        return $this->taxRateRepository->findOneBy(['zone' => $taxZone]);
    }
}
