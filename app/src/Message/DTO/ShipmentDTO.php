<?php

namespace App\Message\DTO;

use App\Doctrine\Entity\Sylius\Shipment;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Sylius\Component\Shipping\Model\ShipmentInterface;
use Sylius\Component\Shipping\Model\ShipmentUnitInterface;

final class ShipmentDTO extends ArrayCollection
{
    public $id;
    /** @var string */
    public $state = ShipmentInterface::STATE_CART;
    /** @var ShippingMethodDTO */
    public $method;
    /** @var Collection|ShipmentUnitInterface[] */
    public $units;
    /** @var string */
    public $tracking;
    /** @var \DateTime */
    public $startDeliverySlot;
    /** @var \DateTime */
    public $endDeliverySlot;
    /** @var \DateTime */
    public $shippingDate;
    /** @var int */
    public $idShippingMethodBalou;

    public function __construct(Shipment $shipment)
    {
        $this->id = $shipment->getId();
        $this->state = $shipment->getState();
        $this->method = new ShippingMethodDTO($shipment->getMethod());
        $this->units = null;
        $this->tracking = $shipment->getTracking();
        $this->startDeliverySlot = $shipment->getStartDeliverySlot();
        $this->endDeliverySlot = $shipment->getEndDeliverySlot();
        $this->shippingDate = $shipment->getShippingDate();
        $this->idShippingMethodBalou = $shipment->getIdShippingMethodBalou();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getState(): ?string
    {
        return $this->state;
    }

    public function getMethod(): ?ShippingMethodDTO
    {
        return $this->method;
    }

    /**
     * @return Collection|ShipmentUnitInterface[]
     */
    public function getUnits()
    {
        return $this->units;
    }

    public function getTracking(): ?string
    {
        return $this->tracking;
    }

    public function getStartDeliverySlot(): ?\DateTime
    {
        return $this->startDeliverySlot;
    }

    public function getEndDeliverySlot(): ?\DateTime
    {
        return $this->endDeliverySlot;
    }

    public function getShippingDate(): ?\DateTime
    {
        return $this->shippingDate;
    }

    public function getIdShippingMethodBalou(): int
    {
        return $this->idShippingMethodBalou;
    }
}
