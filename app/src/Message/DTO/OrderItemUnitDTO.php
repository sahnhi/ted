<?php

namespace App\Message\DTO;

use App\Doctrine\Entity\Sylius\OrderItemUnit;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Setono\SyliusGiftCardPlugin\Model\GiftCardInterface;
use Sylius\Component\Core\Model\AdjustmentInterface;
use Sylius\Component\Core\Model\ShipmentInterface;
use Sylius\Component\Order\Model\OrderItemInterface;

final class OrderItemUnitDTO extends ArrayCollection
{
    /** @var int */
    public $id;

    /** @var OrderItemInterface */
    public $orderItem;

    /** @var Collection|AdjustmentInterface[] */
    public $adjustments;

    /** @var int */
    public $adjustmentsTotal = 0;

    /** @var ShipmentInterface|null */
    public $shipment;

    /** @var GiftCardInterface */
    public $giftCard;

    public function __construct(OrderItemUnit $orderItemUnit)
    {
        $this->id = $orderItemUnit->getId();
        $this->orderItem = new OrderItemDTO($orderItemUnit->getOrderItem());

        $this->adjustments = new ArrayCollection();
        foreach ($orderItemUnit->getAdjustments() as $adjustment) {
            $this->adjustments->add($adjustment);
        }

        $this->adjustmentsTotal = $orderItemUnit->getAdjustmentsTotal();

        $this->shipment = null;
        if ($orderItemUnit->getShipment()) {
            $this->shipment = new ShipmentDTO($orderItemUnit->getShipment());
        }

        /*if ($orderItemUnit->getGiftCard()) {
            $this->giftCard = new GiftCardDTO($orderItemUnit->getGiftCard());
        }*/
    }

    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return OrderItemInterface
     */
    public function getOrderItem()
    {
        return $this->orderItem;
    }

    /**
     * @return Collection|AdjustmentInterface[]
     */
    public function getAdjustments()
    {
        return $this->adjustments;
    }

    public function getAdjustmentsTotal(): int
    {
        return $this->adjustmentsTotal;
    }

    /**
     * @return ShipmentInterface|null
     */
    public function getShipment()
    {
        return $this->shipment;
    }

    /**
     * @return GiftCardInterface
     */
    public function getGiftCard()
    {
        return $this->giftCard;
    }
}
