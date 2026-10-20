<?php

declare(strict_types=1);

namespace App\Api\ChronoPost\Query;

final class ChronoRelaisQuery
{
    public const MONACO_ZIPCODE = '98000';
    public const MONACO_CITY = 'monaco';
    public const BEAUSOLEIL_ZIPCODE = '06240';
    public const BEAUSOLEIL_CITY = 'BEAUSOLEIL';
    private string $type = 'T';
    private string $service = 'T';
    private int $maxPointChronoPost = 16;
    private int $maxDistanceSearch = 15;
    private int $holidayTolerant = 1;

    public function __construct(
        private readonly ?string $address,
        private string $zipCode,
        private ?string $city,
        private readonly ?string $countryCode,
        private readonly \DateTime $shippingDate,
        private readonly int $weight
    ) {
        $this->handleMonacoException();
    }

    public function toArray(): array
    {
        return [
            'address' => $this->address,
            'zipCode' => $this->zipCode,
            'city' => $this->city,
            'countryCode' => $this->countryCode,
            'type' => $this->type,
            'service' => $this->service,
            'shippingDate' => $this->shippingDate->format('d/m/Y'),
            'maxPointChronopost' => $this->maxPointChronoPost,
            'maxDistanceSearch' => $this->maxDistanceSearch,
            'holidayTolerant' => $this->holidayTolerant,
            'weight' => $this->weight,
        ];
    }

    private function handleMonacoException()
    {
        if (self::MONACO_ZIPCODE === $this->zipCode || (null !== $this->city && self::MONACO_CITY === strtolower($this->city))) {
            $this->zipCode = self::BEAUSOLEIL_ZIPCODE;
            $this->city = self::BEAUSOLEIL_CITY;
        }
    }
}
