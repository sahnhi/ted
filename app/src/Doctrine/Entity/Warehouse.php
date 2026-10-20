<?php

namespace App\Doctrine\Entity;

use App\Doctrine\Repository\WarehouseRepository;
use App\Doctrine\Trait\FtpConfigurationAwareTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Resource\Model\ResourceInterface;

#[ORM\Table(name: 'erp_warehouse')]
#[ORM\Entity(repositoryClass: WarehouseRepository::class)]
class Warehouse implements ResourceInterface, \Stringable, FtpConfigurationAwareInterface
{
    use FtpConfigurationAwareTrait;

    public const WAREHOUSE_SPIDY = 'spidy';
    public const WAREHOUSE_DEFAULT = 'Spidy';

    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\SequenceGenerator(sequenceName: 'tablename_seq', allocationSize: 100, initialValue: 1)]
    private ?int $id = null;

    #[ORM\Column(name: 'name', type: 'string', nullable: false, unique: true)]
    private ?string $name = null;

    #[ORM\Column(name: 'crmName', type: 'string', nullable: true, unique: true)]
    private ?string $crmName = null;

    #[ORM\OneToMany(targetEntity: ShippingStock::class, mappedBy: 'warehouse', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private ?Collection $shippingStock = null;

    #[ORM\Column(name: 'opening_time', type: 'time', nullable: true)]
    private ?\DateTime $openingTime = null;

    #[ORM\Column(name: 'closing_time', type: 'time', nullable: true)]
    private ?\DateTime $closingTime = null;

    #[ORM\Column(name: 'cut_off', type: 'time', nullable: true)]
    private ?\DateTime $cutOff = null;

    #[ORM\Column(name: 'opening_calendar_days', type: 'ordered_array', nullable: true)]
    private ?array $openingCalendarDays = null;

    #[ORM\OneToMany(targetEntity: ExceptionalSchedule::class, mappedBy: 'warehouse', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private ?Collection $exceptionalSchedule = null;

    public function __construct()
    {
        $this->shippingStock = new ArrayCollection();
        $this->exceptionalSchedule = new ArrayCollection();
        $this->openingCalendarDays = [];
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

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getShippingStock(): ?Collection
    {
        return $this->shippingStock;
    }

    public function addShippingStock(ShippingStock $shippingStock): void
    {
        if (!$this->shippingStock->contains($shippingStock)) {
            $this->shippingStock[] = $shippingStock;
        }
    }

    public function removeShippingStock(ShippingStock $shippingStock): void
    {
        if ($this->shippingStock->contains($shippingStock)) {
            $this->shippingStock->removeElement($shippingStock);
        }
    }

    public function getOpeningTime(): ?\DateTime
    {
        return $this->openingTime ?: new \DateTime('00:00:00');
    }

    public function setOpeningTime(?\DateTime $openingTime): void
    {
        $this->openingTime = $openingTime;
    }

    public function getClosingTime(): ?\DateTime
    {
        return $this->closingTime ?: new \DateTime('23:59:59');
    }

    public function setClosingTime(?\DateTime $closingTime): void
    {
        $this->closingTime = $closingTime;
    }

    public function getCutOff(): ?\DateTime
    {
        return $this->cutOff;
    }

    public function setCutOff(?\DateTime $cutOff): void
    {
        $this->cutOff = $cutOff;
    }

    public function getOpeningCalendarDays(): ?array
    {
        return $this->openingCalendarDays;
    }

    public function setOpeningCalendarDays(?array $openingCalendarDays): void
    {
        $this->openingCalendarDays = $openingCalendarDays;
    }

    public function getExceptionalSchedule(): ?Collection
    {
        return $this->exceptionalSchedule;
    }

    public function addExceptionalSchedule(ExceptionalSchedule $exceptionalSchedule): void
    {
        if (!$this->exceptionalSchedule->contains($exceptionalSchedule)) {
            $exceptionalSchedule->setWarehouse($this);
            $this->exceptionalSchedule[] = $exceptionalSchedule;
        }
    }

    public function removeExceptionalSchedule(ExceptionalSchedule $exceptionalSchedule): void
    {
        if ($this->exceptionalSchedule->contains($exceptionalSchedule)) {
            $this->exceptionalSchedule->removeElement($exceptionalSchedule);
        }
    }

    public function getCrmName(): ?string
    {
        return $this->crmName;
    }

    public function setCrmName(string $crmName): void
    {
        $this->crmName = $crmName;
    }
}
