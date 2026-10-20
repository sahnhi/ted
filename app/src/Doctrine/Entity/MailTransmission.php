<?php

namespace App\Doctrine\Entity;

use App\Doctrine\Entity\Sylius\ShippingMethod;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * MailTransmission.
 */
#[ORM\Table(name: 'erp_mail_transmission')]
#[ORM\Entity]
class MailTransmission
{
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\SequenceGenerator(sequenceName: 'tablename_seq', allocationSize: 100, initialValue: 1)]
    private ?int $id = null;

    #[ORM\Column(name: 'mail', type: 'string', nullable: false)]
    private ?string $mail = null;

    #[ORM\JoinTable(name: 'sylius_mailtransmissions_shippingmethods')]
    #[ORM\JoinColumn(name: 'erp_mail_transmission_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'shipping_method_id', referencedColumnName: 'id')]
    #[ORM\ManyToMany(targetEntity: ShippingMethod::class)]
    private ?Collection $shippingMethods;

    #[ORM\JoinTable(name: 'sylius_mailtransmissions_warehouses')]
    #[ORM\JoinColumn(name: 'erp_mail_transmission_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'warehouse_id', referencedColumnName: 'id')]
    #[ORM\ManyToMany(targetEntity: Warehouse::class)]
    private ?Collection $warehouses;

    public function __construct()
    {
        $this->shippingMethods = new ArrayCollection();
        $this->warehouses = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getMail(): string
    {
        return $this->mail;
    }

    public function setMail(string $mail): void
    {
        $this->mail = $mail;
    }

    public function getShippingMethods(): ?Collection
    {
        return $this->shippingMethods;
    }

    public function setShippingMethods(array $shippingMethods): MailTransmission
    {
        $this->shippingMethods[] = $shippingMethods;

        return $this;
    }

    public function addShippingMethod(ShippingMethod $shippingMethod): self
    {
        if (!$this->shippingMethods->contains($shippingMethod)) {
            $this->shippingMethods[] = $shippingMethod;
        }

        return $this;
    }

    public function removeShippingMethod(ShippingMethod $shippingMethod): self
    {
        if ($this->shippingMethods->contains($shippingMethod)) {
            $this->shippingMethods->removeElement($shippingMethod);
        }

        return $this;
    }

    public function getWarehouses(): ?Collection
    {
        return $this->warehouses;
    }

    public function addwarehouse(Warehouse $warehouse): self
    {
        if (!$this->warehouses->contains($warehouse)) {
            $this->warehouses[] = $warehouse;
        }

        return $this;
    }

    public function removewarehouse(Warehouse $warehouse): self
    {
        if ($this->warehouses->contains($warehouse)) {
            $this->warehouses->removeElement($warehouse);
        }

        return $this;
    }
}
