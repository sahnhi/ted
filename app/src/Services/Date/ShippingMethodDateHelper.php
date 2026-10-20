<?php

namespace App\Services\Date;

use App\Doctrine\Entity\Sylius\ShippingMethod;

class ShippingMethodDateHelper implements ShippingMethodDateHelperInterface
{
    public function isOpen(\DateTime $date, ShippingMethod $shippingMethod): bool
    {
        return in_array($date->format('w'), $shippingMethod->getOpeningCalendarDays());
    }

    public function getFirstDateAvailable(\DateTime $date, ShippingMethod $shippingMethod): \DateTime
    {
        if ($this->isOpen($date, $shippingMethod)) {
            return $date;
        }

        $interval = new \DateInterval('P1D');
        $yesterday = clone $date;
        $yesterday->sub($interval);

        return $this->isOpen($yesterday, $shippingMethod) ? $yesterday : $this->getFirstDateAvailable($yesterday, $shippingMethod);
    }
}
