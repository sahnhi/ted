<?php

namespace App\Message\DTO;

use Doctrine\Common\Collections\ArrayCollection;
use Sylius\Component\Core\Model\Adjustment;
use Sylius\Component\Order\Model\OrderInterface;
use Sylius\Component\Order\Model\OrderItemInterface;

final class AdjustmentDTO extends ArrayCollection
{
    public $id;

    /** @var OrderInterface|null */
    public $order;

    /** @var OrderItemInterface|null */
    public $orderItem;

    /** @var string|null */
    public $type;

    /** @var string|null */
    public $label;

    /** @var int */
    public $amount = 0;

    /** @var bool */
    public $neutral = false;

    /** @var bool */
    public $locked = false;

    /** @var string|null */
    public $originCode;

    public function __construct(Adjustment $adjustment)
    {
        $this->id = $adjustment->getId();
        $this->type = $adjustment->getType();
        $this->label = $adjustment->getLabel();
        $this->amount = $adjustment->getAmount();
        $this->neutral = $adjustment->isNeutral();
        $this->locked = $adjustment->isLocked();
        $this->originCode = $adjustment->getOriginCode();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getOrder(): ?OrderInterface
    {
        return $this->order;
    }

    public function getOrderItem(): ?OrderItemInterface
    {
        return $this->orderItem;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function getAmount(): int
    {
        return $this->amount;
    }

    public function isNeutral(): bool
    {
        return $this->neutral;
    }

    public function isLocked(): bool
    {
        return $this->locked;
    }

    public function getOriginCode(): ?string
    {
        return $this->originCode;
    }
}
