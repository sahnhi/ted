<?php

declare(strict_types=1);

namespace App\Tests\Unit\Validator\Logistic\PickUpPoints;

use App\Doctrine\Entity\Sylius\Order;
use App\Doctrine\Entity\Sylius\OrderInterface;
use App\Exception\Logistic\PickUpPoints\SearchableAddressObjectNotValidException;
use App\Model\Logistic\PickUp\SearchableAddress;
use App\Validator\Logistic\PickUpPoints\SearchableAddressValidator;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class SearchableAddressValidatorTest extends KernelTestCase
{
    private const ZIP_CODE = '75004';
    private const ADDRESS = '20 rue de la Sainte Croix Bretonnerie';
    private const CITY = 'Paris';
    private const COUNTRY_CODE = 'FR';

    private \DateTimeImmutable $shippingDate;

    private OrderInterface $order;

    private SearchableAddressValidator $searchableAddressValidator;

    public function setUp(): void
    {
        $this->shippingDate = new \DateTimeImmutable();
        $this->order = new Order();

        self::bootKernel();
        $this->searchableAddressValidator = self::$container->get(SearchableAddressValidator::class);
    }

    public function testValidSearchableAddress(): void
    {
        $searchableAddress = new SearchableAddress(
            self::ZIP_CODE,
            self::ADDRESS,
            self::CITY,
            self::COUNTRY_CODE,
            $this->shippingDate,
            $this->order,
            []
        );
        $this->assertNull(
            $this->searchableAddressValidator->validate($searchableAddress)
        );
    }

    public function testValidSearchableAddressWithoutZipCode(): void
    {
        $searchableAddress = new SearchableAddress(
            '',
            self::ADDRESS,
            self::CITY,
            self::COUNTRY_CODE,
            $this->shippingDate,
            $this->order,
            []
        );
        $this->assertNull(
            $this->searchableAddressValidator->validate($searchableAddress)
        );
    }

    public function testValidSearchableAddressWithoutAddress(): void
    {
        $searchableAddress = new SearchableAddress(
            self::ZIP_CODE,
            '',
            self::CITY,
            self::COUNTRY_CODE,
            $this->shippingDate,
            $this->order,
            []
        );
        $this->assertNull(
            $this->searchableAddressValidator->validate($searchableAddress)
        );
    }

    public function testValidSearchableAddressWithoutCity(): void
    {
        $searchableAddress = new SearchableAddress(
            self::ZIP_CODE,
            self::ADDRESS,
            '',
            self::COUNTRY_CODE,
            $this->shippingDate,
            $this->order,
            []
        );
        $this->assertNull(
            $this->searchableAddressValidator->validate($searchableAddress)
        );
    }

    public function testValidSearchableAddressWithoutCountryCode(): void
    {
        $this->expectException(SearchableAddressObjectNotValidException::class);

        $searchableAddress = new SearchableAddress(
            self::ZIP_CODE,
            self::ADDRESS,
            self::CITY,
            '',
            $this->shippingDate,
            $this->order,
            []
        );

        $this->searchableAddressValidator->validate($searchableAddress);
    }

    public function testNotValidSearchableAddress(): void
    {
        $this->expectException(SearchableAddressObjectNotValidException::class);

        $searchableAddress = new SearchableAddress(
            '',
            '',
            '',
            '',
            $this->shippingDate,
            $this->order,
            []
        );
        $this->searchableAddressValidator->validate($searchableAddress);
    }
}
