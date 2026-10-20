<?php

declare(strict_types=1);

namespace Sokeo\SyliusYounitedpayPlugin\Api;

use Sokeo\SyliusYounitedpayPlugin\Api\BestPrice\BestPrice;

interface YounitedpayBestPriceInterface
{
    public const BEST_PRICE_URI = '/api/1.0/BestPrice';
    public const BEST_PRICE_METHOD = 'POST';

    public function getBestPrice(float $amount);

    public function getTwelveMonthsBestPrice(float $amount): BestPrice;
}
