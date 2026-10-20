<?php

namespace App\Doctrine\Entity\Sylius;

use Sylius\Component\Promotion\Model\PromotionRuleInterface as BasePromotionRuleInterface;

interface PromotionRuleInterface extends BasePromotionRuleInterface
{
    public const TYPE_SPONSORING = 'sponsoring';
}
