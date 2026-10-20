<?php

namespace App\Provider;

use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\TaxRateInterface;

interface TaxRateProviderInterface
{
    public function provideTaxRateFromZone(OrderInterface $order): ?TaxRateInterface;
}
