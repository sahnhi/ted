<?php

namespace App\Doctrine\Entity;

use App\Doctrine\Entity\Sylius\ShippingCategory;
use App\Doctrine\Entity\Sylius\ShippingMethod;
use App\Doctrine\Entity\Sylius\Zone;
use App\Doctrine\Repository\ServiceRulesRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Resource\Model\ResourceInterface;

#[ORM\Table(name: 'erp_service_rules')]
#[ORM\Entity(repositoryClass: ServiceRulesRepository::class)]
class ServiceRules implements ResourceInterface, \Stringable
{
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\SequenceGenerator(sequenceName: 'tablename_seq', allocationSize: 100, initialValue: 1)]
    private ?int $id = null;

    #[ORM\Column(name: 'name', type: 'string', nullable: false)]
    private ?string $name = null;

    #[ORM\Column(name: 'priority', type: 'integer', nullable: false)]
    private ?int $priority = null;

    #[ORM\Column(name: 'delivery_slot', type: 'time', nullable: true)]
    private ?\DateTime $deliverySlot = null;

    #[ORM\Column(name: 'transport_time', type: 'float', nullable: true)]
    private ?float $transportTime = null;

    #[ORM\Column(name: 'erp_id', type: 'integer', nullable: true)]
    private ?int $idShippingMethodBalou = null;

    #[ORM\OneToMany(targetEntity: ExceptionalSchedule::class, mappedBy: 'serviceRules', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private ?Collection $exceptionalSchedule;

    #[ORM\JoinColumn(name: 'shipping_method_id', referencedColumnName: 'id')]
    #[ORM\ManyToOne(targetEntity: ShippingMethod::class)]
    private ?ShippingMethod $shippingMethod = null;

    #[ORM\JoinColumn(name: 'warehouse_id', referencedColumnName: 'id')]
    #[ORM\ManyToOne(targetEntity: Warehouse::class)]
    private ?Warehouse $warehouse = null;

    #[ORM\JoinColumn(name: 'zone_id', referencedColumnName: 'id')]
    #[ORM\ManyToOne(targetEntity: Zone::class)]
    private ?Zone $deliveryZone = null;

    #[ORM\ManyToMany(targetEntity: ShippingCategory::class, mappedBy: 'serviceRules', fetch: 'EXTRA_LAZY')]
    private ?Collection $shippingCategory;

    public function __construct()
    {
        $this->exceptionalSchedule = new ArrayCollection();
        $this->shippingCategory = new ArrayCollection();
    }

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

    public function getPriority(): ?int
    {
        return $this->priority;
    }

    public function setPriority(int $priority): void
    {
        $this->priority = $priority;
    }

    public function getShippingMethod(): ?ShippingMethod
    {
        return $this->shippingMethod;
    }

    public function setShippingMethod(ShippingMethod $shippingMethod): void
    {
        $this->shippingMethod = $shippingMethod;
    }

    public function getWarehouse(): ?Warehouse
    {
        return $this->warehouse;
    }

    public function setWarehouse(Warehouse $warehouse): void
    {
        $this->warehouse = $warehouse;
    }

    public function getDeliveryZone(): ?Zone
    {
        return $this->deliveryZone;
    }

    public function setDeliveryZone(Zone $deliveryZone): void
    {
        $this->deliveryZone = $deliveryZone;
    }

    public function getDeliverySlot(): ?\DateTime
    {
        return $this->deliverySlot;
    }

    public function setDeliverySlot(?\DateTime $deliverySlot): void
    {
        $this->deliverySlot = $deliverySlot;
    }

    public function getShippingCategory(): ?Collection
    {
        return $this->shippingCategory;
    }

    public function addShippingCategory(ShippingCategory $shippingCategory): void
    {
        if (!$this->shippingCategory->contains($shippingCategory)) {
            $this->shippingCategory[] = $shippingCategory;
            $shippingCategory->addServiceRules($this);
        }
    }

    public function removeShippingCategory(ShippingCategory $shippingCategory): void
    {
        if ($this->shippingCategory->contains($shippingCategory)) {
            $this->shippingCategory->removeElement($shippingCategory);
            $shippingCategory->removeServiceRules($this);
        }
    }

    public function getExceptionalSchedule(): ?Collection
    {
        return $this->exceptionalSchedule;
    }

    public function addExceptionalSchedule(ExceptionalSchedule $exceptionalSchedule): void
    {
        if (!$this->exceptionalSchedule->contains($exceptionalSchedule)) {
            $exceptionalSchedule->setServiceRules($this);
            $this->exceptionalSchedule[] = $exceptionalSchedule;
        }
    }

    public function removeExceptionalSchedule(ExceptionalSchedule $exceptionalSchedule): void
    {
        if ($this->exceptionalSchedule->contains($exceptionalSchedule)) {
            $this->exceptionalSchedule->removeElement($exceptionalSchedule);
        }
    }

    public function getTransportTime(): ?float
    {
        return $this->transportTime;
    }

    public function setTransportTime(?float $transportTime): void
    {
        $this->transportTime = $transportTime;
    }

    public function getIdShippingMethodBalou(): ?int
    {
        return $this->idShippingMethodBalou;
    }

    public function setIdShippingMethodBalou(?int $idShippingMethodBalou): void
    {
        $this->idShippingMethodBalou = $idShippingMethodBalou;
    }
}
