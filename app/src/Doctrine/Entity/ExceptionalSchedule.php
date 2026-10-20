<?php

namespace App\Doctrine\Entity;

use App\Doctrine\Entity\Sylius\ShippingMethod;
use App\Doctrine\Repository\ExceptionalScheduleRepository;
use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Resource\Model\ResourceInterface;

#[ORM\Table(name: 'erp_exceptional_schedule')]
#[ORM\Entity(repositoryClass: ExceptionalScheduleRepository::class)]
class ExceptionalSchedule implements ResourceInterface
{
    public const EXCEPTIONAL_TYPE = [
        self::EXCEPTIONAL_TYPE_OPENING,
        self::EXCEPTIONAL_TYPE_CLOSING,
        self::EXCEPTIONAL_TYPE_OPENING_SLOT,
        self::EXCEPTIONAL_TYPE_CLOSING_SLOT,
    ];

    public const EXCEPTIONAL_TYPE_OPENING = 'opening';
    public const EXCEPTIONAL_TYPE_CLOSING = 'closing';
    public const EXCEPTIONAL_TYPE_OPENING_SLOT = 'opening_slot';
    public const EXCEPTIONAL_TYPE_CLOSING_SLOT = 'closing_slot';

    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private ?int $id = null;

    #[ORM\Column(name: 'type', type: 'string', nullable: false)]
    private ?string $type = null;

    #[ORM\Column(name: 'start', type: 'datetime')]
    private ?\DateTime $start = null;

    #[ORM\Column(name: 'end', type: 'datetime')]
    private ?\DateTime $end = null;

    #[ORM\JoinColumn(name: 'shipping_method_id', referencedColumnName: 'id')]
    #[ORM\ManyToOne(targetEntity: ShippingMethod::class, inversedBy: 'exceptionalSchedule')]
    private ?ShippingMethod $shippingMethod = null;

    #[ORM\JoinColumn(name: 'warehouse_id', referencedColumnName: 'id')]
    #[ORM\ManyToOne(targetEntity: Warehouse::class, inversedBy: 'exceptionalSchedule')]
    private ?Warehouse $warehouse = null;

    #[ORM\JoinColumn(name: 'service_rules_id', referencedColumnName: 'id')]
    #[ORM\ManyToOne(targetEntity: ServiceRules::class)]
    private ?ServiceRules $serviceRules = null;

    #[ORM\Column(name: 'calendar_days', type: 'array', nullable: true)]
    private ?array $calendarDays;

    public function __construct()
    {
        $this->calendarDays = [];
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getStart(): ?\DateTime
    {
        return $this->start;
    }

    public function setStart(?\DateTime $start): void
    {
        $this->start = $start;
    }

    public function getEnd(): ?\DateTime
    {
        return $this->end;
    }

    public function setEnd(?\DateTime $end): void
    {
        $this->end = $end;
    }

    public function getShippingMethod(): ?ShippingMethod
    {
        return $this->shippingMethod;
    }

    public function setShippingMethod(?ShippingMethod $shippingMethod): void
    {
        $this->shippingMethod = $shippingMethod;
    }

    public function getWarehouse(): ?Warehouse
    {
        return $this->warehouse;
    }

    public function setWarehouse(?Warehouse $warehouse): void
    {
        $this->warehouse = $warehouse;
    }

    public function getServiceRules(): ?ServiceRules
    {
        return $this->serviceRules;
    }

    public function setServiceRules(?ServiceRules $serviceRules): void
    {
        $this->serviceRules = $serviceRules;
    }

    public function getCalendarDays(): ?array
    {
        return $this->calendarDays;
    }

    public function setCalendarDays(?array $calendarDays): void
    {
        $this->calendarDays = $calendarDays;
    }
}
