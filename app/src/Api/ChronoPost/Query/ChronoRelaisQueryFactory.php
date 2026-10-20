<?php

declare(strict_types=1);

namespace App\Api\ChronoPost\Query;

use App\Api\ChronoPost\OrderWeightCalculator\OrderWeightCalculator;
use Symfony\Component\HttpFoundation\InputBag;
use Webmozart\Assert\Assert;

final class ChronoRelaisQueryFactory
{
    public function __construct(
        private readonly OrderWeightCalculator $orderWeightCalculator
    ) {
    }

    /**
     * @throws \Exception
     */
    public function fromQueryParams(InputBag $queryParameters): ChronoRelaisQuery
    {
        Assert::true(null !== $queryParameters->get('zipCode'), 'missing argument : zipCode');
        Assert::true(null !== $queryParameters->get('shippingDate'), 'missing argument : shippingDate');
        Assert::true(null !== $queryParameters->get('tokenOrder'), 'missing argument : tokenOrder');

        $tokenOrder = $queryParameters->get('tokenOrder');

        $address = $this->getAddress($queryParameters->has('address') ? $queryParameters->get('address') : null);

        $weight = $this->orderWeightCalculator->weightInGramsCalculateFromTokenOrder($tokenOrder);

        return new ChronoRelaisQuery(
            $address,
            $queryParameters->get('zipCode'),
            $queryParameters->has('city') ? $queryParameters->get('city') : null,
            $queryParameters->has('countryCode') ? $queryParameters->get('countryCode') : null,
            new \DateTime($queryParameters->get('shippingDate')),
            $weight,
        );
    }

    public function fromAdmin(InputBag $queryParameters): ChronoRelaisQuery
    {
        Assert::true(null !== $queryParameters->get('zipCode'), 'missing argument : zipCode');
        Assert::true(null !== $queryParameters->get('products'), 'missing argument : products');

        $address = $this->getAddress($queryParameters->has('address') ? $queryParameters->get('address') : null);

        $weight = $this->orderWeightCalculator->weightInGramsCalculateFromProducts($queryParameters->get('products'));

        return new ChronoRelaisQuery(
            $address,
            $queryParameters->get('zipCode'),
            $queryParameters->has('city') ? $queryParameters->get('city') : null,
            $queryParameters->has('countryCode') ? $queryParameters->get('countryCode') : null,
            new \DateTime(),
            $weight,
        );
    }

    private function getAddress(?string $address): ?string
    {
        if (is_string($address) && strlen($address) > 60) {
            throw new \InvalidArgumentException('address should be lesser than 60 characters');
        }

        return $address;
    }
}
