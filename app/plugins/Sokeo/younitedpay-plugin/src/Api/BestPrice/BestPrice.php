<?php

declare(strict_types=1);

namespace Sokeo\SyliusYounitedpayPlugin\Api\BestPrice;

class BestPrice
{
    private $annualPercentageRate;

    private $annualDebitRate;

    public function __construct(float $annualPercentageRate, float $annualDebitRate)
    {
        $this->annualPercentageRate = $annualPercentageRate;
        $this->annualDebitRate = $annualDebitRate;
    }

    public function getAnnualPercentageRate(): float
    {
        return $this->annualPercentageRate;
    }

    public function getAnnualDebitRate(): float
    {
        return $this->annualDebitRate;
    }
}
