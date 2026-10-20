<?php

declare(strict_types=1);

namespace App\Validator\Logistic\PickUpPoints;

use App\Exception\Logistic\PickUpPoints\SearchableAddressObjectNotValidException;
use App\Model\Logistic\PickUp\SearchableAddress;
use Webmozart\Assert\Assert;

/**
 * For searching pickup point, we must:
 * - must have country code to know which country we are searching
 * - must have zip code or city to know where we are searching
 * - the address is not a problem since we can find pick up points in the city
 *   or even on a zip code area
 */
final class SearchableAddressValidator
{
    public function validate(SearchableAddress $searchableAddress): void
    {
        $this->mustHaveCountryCode($searchableAddress);
        $this->mustHaveZipCodeOrCity($searchableAddress);
    }

    private function mustHaveCountryCode(SearchableAddress $searchableAddress): void
    {
        try {
            Assert::notEmpty($searchableAddress->getCountryCode(), 'Country code must be set');
        } catch (\InvalidArgumentException $e) {
            throw new SearchableAddressObjectNotValidException('Country code must be set', 0, $e);
        }
    }

    private function mustHaveZipCodeOrCity(SearchableAddress $searchableAddress): void
    {
        try {
            Assert::notEmpty($searchableAddress->getZipCode() || $searchableAddress->getCity(), 'Zipcode or city must be set');
        } catch (\InvalidArgumentException $e) {
            throw new SearchableAddressObjectNotValidException('Country code must be set', 0, $e);
        }
    }
}
