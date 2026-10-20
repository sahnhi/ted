<?php

namespace App\Doctrine\Entity;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Core\Model\Address;

#[ORM\Table(name: 'sylius_osteopath')]
#[ORM\Entity]
class Osteopath implements \Stringable
{
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\SequenceGenerator(sequenceName: 'tablename_seq', allocationSize: 100, initialValue: 1)]
    private ?int $id = null;

    #[ORM\Column(name: 'name', type: 'string', nullable: false)]
    private ?string $name = null;

    #[ORM\JoinColumn(name: 'sylius_address_id', referencedColumnName: 'id', unique: true)]
    #[ORM\OneToOne(targetEntity: Address::class)]
    private ?Address $address = null;

    public function __toString(): string
    {
        return (string) $this->getName();
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAddress(): ?Address
    {
        return $this->address;
    }

    public function setAddress(Address $address): void
    {
        $this->address = $address;
    }
}
