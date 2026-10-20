<?php

namespace App\Services\Date;

use App\Doctrine\Entity\Sylius\ShippingMethod;

interface ShippingMethodDateHelperInterface
{
    public function isOpen(\DateTime $dateTime, ShippingMethod $shippingMethod): bool;

    public function getFirstDateAvailable(\DateTime $date, ShippingMethod $shippingMethod): \DateTime;
}
