<?php

namespace App\Doctrine\Entity\Sylius;

use Sylius\Component\Core\Model\PromotionInterface as BasePromotionInterface;

interface PromotionInterface extends BasePromotionInterface
{
    public const PROMOTION_SPONSOR_CODE = 'SPONSOR_OFFRE_FILLEUL';

    public function isExceptional(): ?bool;

    public function setExceptional(bool $exceptional): void;
}
