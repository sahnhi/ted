<?php

namespace App\Message\DTO;

use App\Doctrine\Entity\Sylius\Promotion;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Sylius\Component\Core\Model\ChannelInterface;

final class PromotionDTO extends ArrayCollection
{
    public $id;

    /** @var string */
    public $code;

    /** @var string */
    public $name;

    /** @var string */
    public $description;

    /** @var ChannelInterface[]|Collection */
    public $channels;

    /** @var bool */
    public $couponBased = true;

    /** @var bool */
    public $exclusive = true;

    /** @var bool */
    public $exceptional = false;

    public function __construct(Promotion $promotion)
    {
        $this->id = $promotion->getId();
        $this->code = $promotion->getCode();
        $this->name = $promotion->getName();
        $this->description = $promotion->getDescription();
        $this->channels = $promotion->getChannels();
        $this->couponBased = $promotion->isCouponBased();
        $this->exclusive = $promotion->isExclusive();
        $this->exceptional = $promotion->isExceptional();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @return Collection|ChannelInterface[]
     */
    public function getChannels()
    {
        return $this->channels;
    }

    public function isCouponBased(): bool
    {
        return $this->couponBased;
    }

    public function isExclusive(): bool
    {
        return $this->exclusive;
    }

    public function isExceptional(): ?bool
    {
        return $this->exceptional;
    }
}
