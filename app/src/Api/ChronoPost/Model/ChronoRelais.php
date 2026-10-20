<?php

declare(strict_types=1);

namespace App\Api\ChronoPost\Model;

final class ChronoRelais
{
    public function __construct(
        private readonly bool $accessToReducedMobilityPerson,
        private readonly bool $active,
        private readonly string $addressOne,
        private readonly string $addressTwo,
        private readonly string $addressThree,
        private readonly ?string $countryCode,
        private readonly ?string $postalCode,
        private readonly string $coordGeolocalisationLatitude,
        private readonly string $coordGeolocalisationLongitude,
        private readonly int $distanceInMeters,
        private readonly string $identifier,
        private readonly string $locationIndex,
        private readonly array $listScheduleOpening,
        private readonly string $city,
        private readonly string $name,
        private readonly int $maxWeight,
        private readonly string $pointType,
        private readonly string $urlGoogleMaps
    ) {
    }

    public static function fromChronoRelaisResponse(array $chronoRelais): self
    {
        return new self(
            (bool) $chronoRelais['accesPersonneMobiliteReduite'],
            (bool) $chronoRelais['actif'],
            $chronoRelais['adresse1'],
            $chronoRelais['adresse2'],
            $chronoRelais['adresse3'],
            $chronoRelais['codePays'],
            $chronoRelais['codePostal'],
            $chronoRelais['coordGeolocalisationLatitude'],
            $chronoRelais['coordGeolocalisationLongitude'],
            (int) $chronoRelais['distanceEnMetre'],
            $chronoRelais['identifiant'],
            $chronoRelais['indiceDeLocalisation'],
            $chronoRelais['listeHoraireOuverture'],
            $chronoRelais['localite'],
            $chronoRelais['nom'],
            (int) $chronoRelais['poidsMaxi'],
            $chronoRelais['typeDePoint'],
            $chronoRelais['urlGoogleMaps'],
        );
    }

    public function toArray(): array
    {
        return [
            'accesPersonneMobiliteReduite' => $this->accessToReducedMobilityPerson,
            'actif' => $this->active,
            'address_1' => $this->addressOne,
            'address_2' => $this->addressTwo,
            'address_3' => $this->addressThree,
            'countryCode' => $this->countryCode,
            'postalCode' => $this->postalCode,
            'latitude' => $this->coordGeolocalisationLatitude,
            'longitude' => $this->coordGeolocalisationLongitude,
            'distance' => $this->distanceInMeters,
            'identifier' => $this->identifier,
            'localisationNumber' => $this->locationIndex,
            'openingTime' => $this->listScheduleOpening,
            'city' => $this->city,
            'name' => $this->name,
            'weight' => $this->maxWeight,
            'type' => $this->pointType,
            'urlGoogleMaps' => $this->urlGoogleMaps,
        ];
    }
}
