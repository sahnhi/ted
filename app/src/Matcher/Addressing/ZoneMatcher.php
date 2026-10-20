<?php

declare(strict_types=1);

namespace App\Matcher\Addressing;

use App\Doctrine\Entity\Sylius\Zone;
use App\Doctrine\Entity\ZipCode;
use App\Doctrine\Repository\ZoneRepository;
use Sylius\Component\Addressing\Matcher\ZoneMatcherInterface;
use Sylius\Component\Addressing\Model\AddressInterface;
use Sylius\Component\Addressing\Model\Scope;
use Sylius\Component\Addressing\Model\ZoneInterface;
use Sylius\Component\Addressing\Model\ZoneMemberInterface;

class ZoneMatcher implements ZoneMatcherInterface
{
    private const PRIORITIES = [
        ZoneInterface::TYPE_PROVINCE,
        ZoneInterface::TYPE_COUNTRY,
        ZoneInterface::TYPE_ZONE,
    ];

    public function __construct(
        private readonly ZoneRepository $zoneRepository,
    ) {
    }

    public function match(AddressInterface $address, ?string $scope = null): ?ZoneInterface
    {
        $zones = [];

        /** @var ZoneInterface $zone */
        foreach ($this->getZones($scope) as $zone) {
            if ($this->addressBelongsToZone($address, $zone)) {
                $zones[$zone->getType()] = $zone;
            }
        }

        foreach (self::PRIORITIES as $priority) {
            if (isset($zones[$priority])) {
                return $zones[$priority];
            }
        }

        return null;
    }

    public function matchAll(AddressInterface $address, ?string $scope = null): array
    {
        $zones = [];

        foreach ($this->getZones($scope) as $zone) {
            if ($this->addressBelongsToZone($address, $zone)) {
                $zones[] = $zone;
            }
        }

        return $zones;
    }

    /**
     * @param Zone $zone
     */
    private function addressBelongsToZone(AddressInterface $address, ZoneInterface $zone): bool
    {
        if ($this->zoneHasZipCode($zone, $address)) {
            return true;
        }

        foreach ($zone->getMembers() as $member) {
            if ($this->addressBelongsToZoneMember($address, $member)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @throws \InvalidArgumentException
     */
    private function addressBelongsToZoneMember(AddressInterface $address, ZoneMemberInterface $member): bool
    {
        switch ($type = $member->getBelongsTo()->getType()) {
            case ZoneInterface::TYPE_PROVINCE:
                return null !== $address->getProvinceCode() && $address->getProvinceCode() === $member->getCode();
            case ZoneInterface::TYPE_COUNTRY:
                return null !== $address->getCountryCode() && $address->getCountryCode() === $member->getCode();
            case ZoneInterface::TYPE_ZONE:
                $zone = $this->getZoneByCode($member->getCode());

                return null !== $zone && $this->addressBelongsToZone($address, $zone);
            default:
                throw new \InvalidArgumentException(sprintf('Unexpected zone type "%s".', $type));
        }
    }

    private function getZones(?string $scope = null): array
    {
        if (null === $scope) {
            return $this->zoneRepository->findAll();
        }

        return $this->zoneRepository->findBy(['scope' => [$scope, Scope::ALL]]);
    }

    private function getZoneByCode(string $code): ?ZoneInterface
    {
        return $this->zoneRepository->findOneBy(['code' => $code]);
    }

    private function zoneHasZipCode(Zone $zone, AddressInterface $address): bool
    {
        $postCode = $address->getPostcode();
        $countryCode = $address->getCountryCode();

        return $zone->getZipCode()->exists(
            static fn (int $key, ZipCode $zipCode): bool => $zipCode->getCode() === $postCode && $zipCode->getCountry() === $countryCode
        );
    }
}
