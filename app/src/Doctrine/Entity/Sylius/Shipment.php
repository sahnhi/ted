<?php

namespace App\Doctrine\Entity\Sylius;

use App\Doctrine\Entity\ShippingMethodCategory;
use App\Doctrine\Entity\Warehouse;
use App\Doctrine\Repository\Sylius\ShipmentRepository;
use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Core\Model\AdjustmentInterface;
use Sylius\Component\Core\Model\Shipment as BaseShipment;

#[ORM\Table(name: 'sylius_shipment')]
#[ORM\Entity(repositoryClass: ShipmentRepository::class)]
class Shipment extends BaseShipment implements ShipmentInterface
{
    public const SUFFIX_BUILD_SERVICE = 'MONT';

    #[ORM\Column(name: 'start_delivery_slot', type: 'datetime', nullable: true)]
    private ?\DateTime $startDeliverySlot = null;

    #[ORM\Column(name: 'end_delivery_slot', type: 'datetime', nullable: true)]
    private ?\DateTime $endDeliverySlot = null;

    #[ORM\Column(name: 'id_shipping_method_balou', type: 'integer', nullable: true)]
    private ?int $idShippingMethodBalou = null;

    #[ORM\Column(name: 'shipping_date', type: 'datetime', nullable: true)]
    private ?\DateTime $shippingDate = null;

    #[ORM\Column(name: 'proof_of_delivery', type: 'string', nullable: true)]
    private ?string $proofOfDelivery = null;

    #[ORM\Column(name: 'notify_warehouse', type: 'boolean', nullable: true)]
    private ?bool $notifyWarehouse = true;

    #[ORM\Column(name: 'tracking_number', type: 'string', nullable: true)]
    private ?string $trackingNumber = null;

    #[ORM\Column(name: 'relay_id', type: 'string', nullable: true)]
    private ?string $relayId = null;

    #[ORM\JoinColumn(name: 'warehouse_id', referencedColumnName: 'id')]
    #[ORM\ManyToOne(targetEntity: Warehouse::class)]
    private ?Warehouse $warehouse = null;

    #[ORM\ManyToOne(targetEntity: ShippingMethodCategory::class)]
    #[ORM\JoinColumn(name: 'shipping_method_category_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    private ShippingMethodCategory|null $shippingMethodCategory = null;

    public function getEndDeliverySlot(): ?\DateTime
    {
        return $this->endDeliverySlot;
    }

    public function setEndDeliverySlot(?\DateTime $endDeliverySlot): void
    {
        $this->endDeliverySlot = $endDeliverySlot;
    }

    public function getStartDeliverySlot(): ?\DateTime
    {
        return $this->startDeliverySlot;
    }

    public function setStartDeliverySlot(?\DateTime $startDeliverySlot): void
    {
        $this->startDeliverySlot = $startDeliverySlot;
    }

    public function getIdShippingMethodBalou(): ?int
    {
        return $this->idShippingMethodBalou;
    }

    public function setIdShippingMethodBalou(?int $idShippingMethodBalou): void
    {
        $this->idShippingMethodBalou = $idShippingMethodBalou;
    }

    public function getShippingDate(): ?\DateTime
    {
        return $this->shippingDate;
    }

    public function setShippingDate(\DateTime|null $shippingDate): void
    {
        $this->shippingDate = $shippingDate;
    }

    public function getWarehouse(): ?Warehouse
    {
        return $this->warehouse;
    }

    public function setWarehouse(?Warehouse $warehouse): void
    {
        $this->warehouse = $warehouse;
    }

    public function getProofOfDelivery(): ?string
    {
        return $this->proofOfDelivery;
    }

    public function setProofOfDelivery(string $proofOfDelivery): Shipment
    {
        $this->proofOfDelivery = $proofOfDelivery;

        return $this;
    }

    public function notifyWarehouse(): bool
    {
        return $this->notifyWarehouse;
    }

    public function setNotifyWarehouse(bool $notifyWarehouse): ShipmentInterface
    {
        $this->notifyWarehouse = $notifyWarehouse;

        return $this;
    }

    public function getTrackingNumber(): ?string
    {
        return $this->trackingNumber;
    }

    public function setTrackingNumber(?string $trackingNumber): self
    {
        $this->trackingNumber = $trackingNumber;

        return $this;
    }

    public function getRelayId(): ?string
    {
        return $this->relayId;
    }

    public function setRelayId(?string $relayId): void
    {
        $this->relayId = $relayId;
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

    public function getShippingTotal(): int
    {
        return $this->getAdjustmentsTotal(AdjustmentInterface::SHIPPING_ADJUSTMENT)
            + $this->getAdjustmentsTotal(AdjustmentInterface::ORDER_SHIPPING_PROMOTION_ADJUSTMENT)
            + $this->getAdjustmentsTotal(AdjustmentInterface::TAX_ADJUSTMENT);
    }
}
