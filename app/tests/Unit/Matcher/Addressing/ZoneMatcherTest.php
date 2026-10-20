<?php

declare(strict_types=1);

namespace App\Tests\Unit\Matcher\Addressing;

use App\Doctrine\Entity\Sylius\Address;
use App\Doctrine\Entity\Sylius\Zone;
use App\Doctrine\Entity\ZipCode;
use App\Doctrine\Repository\ZoneRepository;
use App\Matcher\Addressing\ZoneMatcher;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Addressing\Model\ZoneInterface;
use Sylius\Component\Addressing\Model\ZoneMember;
use Sylius\Component\Core\Model\Scope;
use Sylius\Component\Resource\Repository\RepositoryInterface;

class ZoneMatcherTest extends TestCase
{
    private readonly RepositoryInterface $zoneRepository;
    private readonly ZoneMatcher $zoneMatcher;

    protected function setUp(): void
    {
        $this->zoneRepository = $this->createMock(ZoneRepository::class);

        $this->zoneMatcher = new ZoneMatcher($this->zoneRepository);
    }

    /**
     * @dataProvider provideForMatch
     */
    public function testMatch(Address $address, Zone $parentZone, Zone $memberZone, Zone|null $expectedZone): void
    {
        $this->zoneRepository
            ->expects($this->once())
            ->method('findBy')
            ->with(['scope' => [Scope::SHIPPING, Scope::ALL]])
            ->willReturn([$parentZone]);

        $this->zoneRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['code' => $parentZone->getMembers()->first()->getCode()])
            ->willReturn($memberZone);

        self::assertSame($expectedZone, $this->zoneMatcher->match($address, Scope::SHIPPING));
    }

    private function provideForMatch(): iterable
    {
        $address = new Address();
        $address->setPostcode('69001');
        $address->setCountryCode('FR');

        $parentZone = new Zone();
        $parentZone->setCode('VIR_69');
        $parentZone->setType(ZoneInterface::TYPE_ZONE);

        $member = new ZoneMember();
        $member->setCode('69_RHONE');
        $parentZone->addMember($member);

        $memberZone = new Zone();
        $memberZone->setCode('69_RHONE');
        $memberZone->setType(ZoneInterface::TYPE_PROVINCE);

        $zipCode = new ZipCode();
        $zipCode->setCode('69001');
        $zipCode->setCountry('FR');
        $memberZone->addZipCode($zipCode);

        // Both the country and zip code match, we expect the parentZone
        yield [$address, $parentZone, $memberZone, $parentZone];

        $address = new Address();
        $address->setPostcode('69001');
        $address->setCountryCode('FR');

        $parentZone = new Zone();
        $parentZone->setCode('VIR_69');
        $parentZone->setType(ZoneInterface::TYPE_ZONE);

        $member = new ZoneMember();
        $member->setCode('69_RHONE');
        $parentZone->addMember($member);

        $memberZone = new Zone();
        $memberZone->setCode('69_RHONE');
        $memberZone->setType(ZoneInterface::TYPE_PROVINCE);

        $zipCode = new ZipCode();
        $zipCode->setCode('69002');
        $zipCode->setCountry('FR');
        $memberZone->addZipCode($zipCode);

        // The ZipCode code and Address postCode does not match, we expect null
        yield [$address, $parentZone, $memberZone, null];

        $address = new Address();
        $address->setPostcode('38200');
        $address->setCountryCode('FR');

        $parentZone = new Zone();
        $parentZone->setCode('VIR_69');
        $parentZone->setType(ZoneInterface::TYPE_ZONE);

        $member = new ZoneMember();
        $member->setCode('69_RHONE');
        $parentZone->addMember($member);

        $memberZone = new Zone();
        $memberZone->setCode('69_RHONE');
        $memberZone->setType(ZoneInterface::TYPE_PROVINCE);

        $zipCode = new ZipCode();
        $zipCode->setCode('38200');
        $zipCode->setCountry('ES');
        $memberZone->addZipCode($zipCode);

        // The ZipCode country and Address country does not match, we expect null
        yield [$address, $parentZone, $memberZone, null];
    }
}
