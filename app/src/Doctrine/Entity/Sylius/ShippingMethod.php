<?php

namespace App\Doctrine\Entity\Sylius;

use App\Doctrine\Entity\ExceptionalSchedule;
use App\Doctrine\Entity\ShippingMethodCategory;
use App\Doctrine\Entity\ShippingMethodGroup;
use App\Doctrine\Entity\Warehouse;
use App\Doctrine\Repository\Sylius\ShippingMethodRepository;
use App\Doctrine\Trait\FtpConfigurationAwareTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Core\Model\ShippingMethod as BaseShippingMethod;

#[ORM\Table(name: 'sylius_shipping_method')]
#[ORM\Entity(repositoryClass: ShippingMethodRepository::class)]
class ShippingMethod extends BaseShippingMethod implements ShippingMethodInterface
{
    use FtpConfigurationAwareTrait;

    #[ORM\Column(name: 'id_shipping_method_balou', type: 'integer', unique: true, nullable: true)]
    private ?int $idShippingMethodBalou = null;

    #[ORM\JoinColumn(name: 'warehouse_id', referencedColumnName: 'id', nullable: false)]
    #[ORM\ManyToOne(targetEntity: Warehouse::class)]
    private ?Warehouse $warehouse = null;

    #[ORM\Column(name: 'opening_time', type: 'time', nullable: true)]
    private ?\DateTime $openingTime = null;

    #[ORM\Column(name: 'closing_time', type: 'time', nullable: true)]
    private ?\DateTime $closingTime = null;

    #[ORM\Column(name: 'opening_calendar_days', type: 'ordered_array', nullable: true)]
    private ?array $openingCalendarDays;

    #[ORM\Column(name: 'max_per_day', type: 'integer', nullable: true)]
    private ?int $maxPerDay = null;

    #[ORM\Column(name: 'max_per_slot', type: 'integer', nullable: true)]
    private ?int $maxPerSlot = null;

    #[ORM\Column(name: 'time_slot', type: 'time', nullable: true)]
    private ?\DateTime $timeSlot = null;

    #[ORM\Column(name: 'max_articles', type: 'float', nullable: true)]
    private ?float $maxArticles = null;

    #[ORM\OneToMany(targetEntity: ExceptionalSchedule::class, mappedBy: 'shippingMethod', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private ?Collection $exceptionalSchedule;

    #[ORM\Column(name: 'transport_time', type: 'float', nullable: true)]
    private ?float $transportTime = null;

    #[ORM\Column(name: 'shipping_time_date', type: 'float', nullable: true)]
    private ?float $shippingTimeDate = null;

    #[ORM\Column(name: 'delivery_message', type: 'string', nullable: true)]
    private ?string $deliveryMessage = null;

    #[ORM\Column(name: 'floor', type: 'boolean')]
    private bool $floor = false;

    #[ORM\Column(name: 'tuned', type: 'boolean')]
    private bool $tuned = false;

    #[ORM\Column(name: 'has_tracking', type: 'boolean')]
    private bool $hasTracking = false;

    #[ORM\Column(name: 'shipup_carrier_code', type: 'string', nullable: true)]
    private ?string $shipupCarrierCode = null;

    #[ORM\Column(name: 'calendar', type: 'boolean', options: ['default' => '1'])]
    private bool $calendar = true;

    #[ORM\Column(name: 'pick_up_point', type: 'boolean')]
    private bool $pickUpPoint = false;

    #[ORM\ManyToMany(targetEntity: ShippingCategory::class, mappedBy: 'shippingMethod', fetch: 'EXTRA_LAZY')]
    private ?Collection $shippingCategory;

    #[ORM\ManyToOne(targetEntity: ShippingMethodCategory::class, inversedBy: 'shippingMethods')]
    #[ORM\JoinColumn(name: 'shipping_method_category_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    private ShippingMethodCategory|null $shippingMethodCategory = null;

    #[ORM\ManyToOne(targetEntity: ShippingMethodGroup::class, inversedBy: 'id')]
    #[ORM\JoinColumn(name: 'shipping_method_group_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    #[ORM\OrderBy(['position' => 'ASC'])]
    private ShippingMethodGroup|null $shippingMethodGroup = null;

    public function __construct()
    {
        parent::__construct();
        $this->openingCalendarDays = [];
        $this->shippingCategory = new ArrayCollection();
        $this->exceptionalSchedule = new ArrayCollection();
    }

    public function __toString(): string
    {
        return (string) $this->getName();
    }

    public function getWarehouse(): ?Warehouse
    {
        return $this->warehouse;
    }

    public function setWarehouse(?Warehouse $warehouse): void
    {
        $this->warehouse = $warehouse;
    }

    public function getIdShippingMethodBalou(): ?int
    {
        return $this->idShippingMethodBalou;
    }

    public function setIdShippingMethodBalou(int $idShippingMethodBalou): static
    {
        $this->idShippingMethodBalou = $idShippingMethodBalou;

        return $this;
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

    public function getOpeningCalendarDays(): ?array
    {
        return $this->openingCalendarDays;
    }

    public function setOpeningCalendarDays(?array $openingCalendarDays): void
    {
        $this->openingCalendarDays = $openingCalendarDays;
    }

    public function getShippingCategory(): Collection
    {
        return $this->shippingCategory;
    }

    public function addShippingCategory(ShippingCategory $shippingCategory): void
    {
        if (!$this->shippingCategory->contains($shippingCategory)) {
            $this->shippingCategory->add($shippingCategory);
            $shippingCategory->addShippingMethod($this);
        }
    }

    public function removeShippingCategory(ShippingCategory $shippingCategory): void
    {
        if ($this->shippingCategory->contains($shippingCategory)) {
            $this->shippingCategory->removeElement($shippingCategory);
            $shippingCategory->removeShippingMethod($this);
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

    public function getMaxPerDay(): ?int
    {
        return $this->shippingMethodGroup?->getMaxPerDay() ?? $this->maxPerDay;
    }

    public function setMaxPerDay(?int $maxPerDay): void
    {
        $this->maxPerDay = $maxPerDay;
    }

    public function getMaxPerSlot(): ?int
    {
        return $this->maxPerSlot;
    }

    public function setMaxPerSlot(?int $maxPerSlot): void
    {
        $this->maxPerSlot = $maxPerSlot;
    }

    public function getTimeSlot(): ?\DateTime
    {
        return $this->timeSlot;
    }

    public function setTimeSlot(?\DateTime $timeSlot): void
    {
        $this->timeSlot = $timeSlot;
    }

    public function getMaxArticles(): ?float
    {
        return $this->maxArticles;
    }

    public function setMaxArticles(?float $maxArticles): void
    {
        $this->maxArticles = $maxArticles;
    }

    public function getExceptionalSchedule(): ?Collection
    {
        return $this->exceptionalSchedule;
    }

    public function addExceptionalSchedule(ExceptionalSchedule $exceptionalSchedule): void
    {
        if (!$this->exceptionalSchedule->contains($exceptionalSchedule)) {
            $exceptionalSchedule->setShippingMethod($this);
            $this->exceptionalSchedule[] = $exceptionalSchedule;
        }
    }

    public function removeExceptionalSchedule(ExceptionalSchedule $exceptionalSchedule): void
    {
        if ($this->exceptionalSchedule->contains($exceptionalSchedule)) {
            $this->exceptionalSchedule->removeElement($exceptionalSchedule);
        }
    }

    public function getShippingTimeDate(): ?float
    {
        return $this->shippingTimeDate;
    }

    public function setShippingTimeDate(float $shippingTimeDate): void
    {
        $this->shippingTimeDate = $shippingTimeDate;
    }

    public function getDeliveryMessage(): ?string
    {
        return $this->deliveryMessage;
    }

    public function setDeliveryMessage(?string $deliveryMessage): void
    {
        $this->deliveryMessage = $deliveryMessage;
    }

    public function getFloor(): bool
    {
        return $this->floor;
    }

    public function setFloor(bool $floor): void
    {
        $this->floor = $floor;
    }

    public function isTuned(): bool
    {
        return $this->tuned;
    }

    public function setTuned(bool $tuned): void
    {
        $this->tuned = $tuned;
    }

    public function getHasTracking(): bool
    {
        return $this->hasTracking;
    }

    public function setHasTracking(bool $hasTracking): void
    {
        $this->hasTracking = $hasTracking;
    }

    public function getDeliveryConditions(): ?string
    {
        return $this->getTranslation()->getDeliveryConditions();
    }

    public function setDeliveryConditions(?string $deliveryConditions): void
    {
        $this->getTranslation()->setDeliveryConditions($deliveryConditions);
    }

    public function isBikeDelivery(): bool
    {
        return in_array($this->code, self::BIKE_DELIVERY);
    }

    public function getShipupCarrierCode(): ?string
    {
        return $this->shipupCarrierCode;
    }

    public function setShipupCarrierCode(?string $shipupCarrierCode): void
    {
        $this->shipupCarrierCode = $shipupCarrierCode;
    }

    public function isCalendar(): bool
    {
        return $this->calendar;
    }

    public function setCalendar(bool $calendar): void
    {
        $this->calendar = $calendar;
    }

    public function setPickUpPoint(bool $pickUpPoint): self
    {
        $this->pickUpPoint = $pickUpPoint;

        return $this;
    }

    public function isPickUpPoint(): bool
    {
        return $this->pickUpPoint;
    }

    public function getShippingMethodCategory(): ShippingMethodCategory|null
    {
        return $this->shippingMethodCategory;
    }

    public function setShippingMethodCategory(ShippingMethodCategory|null $shippingMethodCategory): self
    {
        $this->shippingMethodCategory = $shippingMethodCategory;

        return $this;
    }

    public function getShippingMethodGroup(): ?ShippingMethodGroup
    {
        return $this->shippingMethodGroup;
    }

    public function setShippingMethodGroup(?ShippingMethodGroup $shippingMethodGroup): void
    {
        $this->shippingMethodGroup = $shippingMethodGroup;
    }
}
