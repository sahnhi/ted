<?php

namespace App\Message\DTO;

use App\Doctrine\Entity\Sylius\ShippingMethod;
use Doctrine\Common\Collections\ArrayCollection;
use Sylius\Component\Shipping\Model\ShippingMethodInterface;

final class ShippingMethodDTO extends ArrayCollection
{
    /** @var string */
    public $code;
    /** @var bool */
    public $hasTracking;
    /** @var bool */
    public $floor;
    /** @var bool */
    public $tuned;
    /** @var bool */
    public $bikeDelivery;
    /** @var ?string */
    public $deliveryConditions;

    public function __construct(ShippingMethodInterface $method)
    {
        /* @var ShippingMethod $method */
        $this->code = $method->getCode();
        $this->hasTracking = $method->getHasTracking();
        $this->floor = $method->getFloor();
        $this->tuned = $method->isTuned();
        $this->bikeDelivery = $method->isBikeDelivery();
        $this->deliveryConditions = $method->getDeliveryConditions();
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): ShippingMethodDTO
    {
        $this->code = $code;

        return $this;
    }

    public function hasTracking(): bool
    {
        return $this->hasTracking;
    }

    public function setHasTracking(bool $hasTracking): ShippingMethodDTO
    {
        $this->hasTracking = $hasTracking;

        return $this;
    }

    public function isFloor(): bool
    {
        return $this->floor;
    }

    public function setFloor(bool $floor): ShippingMethodDTO
    {
        $this->floor = $floor;

        return $this;
    }

    public function isTuned(): bool
    {
        return $this->tuned;
    }

    public function setTuned(bool $tuned): ShippingMethodDTO
    {
        $this->tuned = $tuned;

        return $this;
    }

    public function isBikeDelivery(): bool
    {
        return $this->bikeDelivery;
    }

    public function setBikeDelivery(bool $bikeDelivery): ShippingMethodDTO
    {
        $this->bikeDelivery = $bikeDelivery;

        return $this;
    }

    public function getDeliveryConditions(): ?string
    {
        return $this->deliveryConditions;
    }

    public function setDeliveryConditions(?string $deliveryConditions): ShippingMethodDTO
    {
        $this->deliveryConditions = $deliveryConditions;

        return $this;
    }
}
