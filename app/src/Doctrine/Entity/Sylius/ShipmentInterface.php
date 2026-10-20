<?php

namespace App\Doctrine\Entity\Sylius;

use App\Doctrine\Entity\ShippingMethodCategory;
use Sylius\Component\Core\Model\ShipmentInterface as BaseShipmentInterface;

interface ShipmentInterface extends BaseShipmentInterface
{
    public function getEndDeliverySlot(): ?\DateTime;

    public function setEndDeliverySlot(?\DateTime $endDeliverySlot): void;

    public function getStartDeliverySlot(): ?\DateTime;

    public function setStartDeliverySlot(?\DateTime $startDeliverySlot): void;

    public function setIdShippingMethodBalou(?int $idShippingMethodBalou): void;

    public function getIdShippingMethodBalou(): ?int;

    public function getShippingDate(): ?\DateTime;

    public function setShippingDate(\DateTime $shippingDate): void;

    public function notifyWarehouse(): bool;

    public function setNotifyWarehouse(bool $notifyWarehouse): self;

    public function getTrackingNumber(): ?string;

    public function setTrackingNumber(?string $trackingNumber): self;

    public function getShippingMethodCategory(): ShippingMethodCategory|null;

    public function setShippingMethodCategory(ShippingMethodCategory|null $shippingMethodCategory): self;

    public function getShippingTotal(): int;
}
