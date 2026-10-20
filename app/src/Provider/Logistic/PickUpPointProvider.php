<?php

declare(strict_types=1);

namespace App\Provider\Logistic;

use App\Exception\Logistic\PickUpPoints\CarrierNotImplementedException;
use App\Middleware\Logistic\PickUpPoint\PickUpPointMiddlewareInterface;
use App\Model\Logistic\PickUp\PickUpPoints;
use App\Model\Logistic\PickUp\SearchableAddress;
use App\Validator\Logistic\PickUpPoints\SearchableAddressValidator;
use Webmozart\Assert\Assert;

final class PickUpPointProvider
{
    public function __construct(
        private readonly iterable $carriers,
        private readonly SearchableAddressValidator $searchableAddressValidator
    ) {
    }

    public function fetch(SearchableAddress $searchableAddress, string $method, $format = 'json'): PickUpPoints
    {
        $this->searchableAddressValidator->validate($searchableAddress);

        $carrier = $this->findCarrier($method);

        return $carrier->fetchPoints($searchableAddress);
    }

    private function findCarrier(string $method): PickUpPointMiddlewareInterface
    {
        $stdMethod = mb_strtolower($method);

        foreach ($this->carriers as $carrier) {
            Assert::isInstanceOf($carrier, PickUpPointMiddlewareInterface::class);

            if ($carrier->isEnabled() && $stdMethod === mb_strtolower($carrier->getCarrier())) {
                return $carrier;
            }
        }

        throw new CarrierNotImplementedException(sprintf('The carrier %s is not implemented.', $method));
    }
}
