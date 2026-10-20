<?php

namespace Sokeo\SyliusYounitedpayPlugin\Api;

use Sokeo\SyliusYounitedpayPlugin\Api\BestPrice\BestPrice;
use Sokeo\SyliusYounitedpayPlugin\Api\Exception\TwelveMonthsBestPriceException;

class YounitedpayBestPrice implements YounitedpayBestPriceInterface
{
    /** @var YounitedpayApi */
    private $younitedpayApi;

    public function __construct(YounitedpayApi $younitedpayApi)
    {
        $this->younitedpayApi = $younitedpayApi;
    }

    public function getBestPrice(float $amount): array
    {
        return $this->younitedpayApi->executeRequest(
            $this->younitedpayApi->getYounitedpayApiBaseUrl(),
            self::BEST_PRICE_METHOD,
            self::BEST_PRICE_URI,
            [
                'json' => [
                    'borrowedAmount' => $amount,
                ],
            ]
        );
    }

    public function getTwelveMonthsBestPrice(float $amount): BestPrice
    {
        $response = $this->younitedpayApi->executeRequest(
            $this->younitedpayApi->getYounitedpayApiBaseUrl(),
            self::BEST_PRICE_METHOD,
            self::BEST_PRICE_URI,
            [
                'json' => [
                    'borrowedAmount' => $amount,
                ],
            ]
        );

        if (empty($response['offers'])) {
            throw new TwelveMonthsBestPriceException('offers not found');
        }

        foreach ($response['offers'] as $offer) {
            if (!empty($offer['maturityInMonths']) && !empty($offer['annualPercentageRate']) && 12 === $offer['maturityInMonths']) {
                return new BestPrice($offer['annualPercentageRate'], $offer['annualDebitRate']);
            }
        }

        throw new TwelveMonthsBestPriceException('12 months TAEG not found');
    }
}
