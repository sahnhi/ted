<?php

namespace App\Doctrine\Entity;

use App\Doctrine\Entity\Sylius\Zone;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Resource\Model\ResourceInterface;

#[ORM\Table(name: 'sylius_zip_code')]
#[ORM\Entity]
#[ORM\Index(columns: ['code', 'country'])]
class ZipCode implements ResourceInterface, \Stringable
{
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private ?int $id = null;

    #[ORM\Column(name: 'code', type: 'string', nullable: false)]
    private ?string $code = null;

    #[ORM\JoinTable(name: 'sylius_zip_code_zone')]
    #[ORM\JoinColumn(name: 'zip_code_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'zone_id', referencedColumnName: 'id')]
    #[ORM\ManyToMany(targetEntity: Zone::class, inversedBy: 'zipCode')]
    private ?Collection $zone;

    #[ORM\Column(name: 'country', type: 'string', nullable: false)]
    private ?string $country = null;

    public function __construct()
    {
        $this->zone = new ArrayCollection();
    }

    public function __toString(): string
    {
        return (string) $this->getCode();
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): void
    {
        $this->code = $code;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getZone(): ?Collection
    {
        return $this->zone;
    }

    public function addZone(Zone $zone): void
    {
        if (!$this->zone->contains($zone)) {
            $this->zone[] = $zone;
            $zone->addZipCode($this);
        }
    }

    public function removeZone(Zone $zone): void
    {
        $this->zone->removeElement($zone);
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(string $country): void
    {
        $this->country = $country;
    }
}
