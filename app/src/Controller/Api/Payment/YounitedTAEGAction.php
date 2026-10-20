<?php

declare(strict_types=1);

namespace App\Controller\Api\Payment;

use Sokeo\SyliusYounitedpayPlugin\Api\YounitedpayBestPrice;
use Symfony\Component\HttpFoundation\JsonResponse;

class YounitedTAEGAction
{
    public function __construct(
        private readonly YounitedpayBestPrice $younitedPayBestPrice
    ) {
    }

    public function __invoke(float $amount): JsonResponse
    {
        $bestPrice = $this->younitedPayBestPrice->getTwelveMonthsBestPrice($amount);

        return new JsonResponse(['taeg' => $bestPrice->getAnnualPercentageRate(), 'annual_debit_rate' => $bestPrice->getAnnualDebitRate()]);
    }
}
