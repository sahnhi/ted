<?php

namespace App\Services\Calendar;

interface ShippingEnabledCountryInterface
{
    public function isEnable(string $countryCode, string $productVariantCode): bool;
}
